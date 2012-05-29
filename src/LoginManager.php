<?php
/**
 *
 * @copyright  Copyright (c) 2010, 2011, 2012 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr;

use fajr\config\ServerConfig;
use fajr\exceptions\SecurityException;
use libfajr\trace\Trace;
use libfajr\connection\AIS2ServerConnection;
use libfajr\login\CosignServiceCookie;
use libfajr\login\CosignPasswordLogin;
use libfajr\login\CosignProxyLogin;
use libfajr\login\CosignCookieLogin;
use libfajr\login\AIS2PasswordLogin;
use libfajr\login\AIS2CosignLogin;
use libfajr\login\NoLogin;
use fajr\Request;
use fajr\util\FajrUtils;
use sfStorage;

class LoginManager
{
  /** @var LoginManager $instance */
  private static $instance;

  /* TODO document */
  public static function getInstance()
  {
    if (!isset(self::$instance)) {
      self::$instance = new LoginManager(SessionStorageProvider::getInstance(),
          Request::getInstance(),
          LazyServerConnection::getInstance());
    }
    return self::$instance;
  }

  private $request;
  private $session;
  
  /**
   * Pretoze sa mozme pytat na isLoggedIn() viackrat, cachujeme tuto hodnotu
   * TODO(anty): uplne refaktorovat LoginManager
   * @var boolean 
   */
  private $cachedLoggedIn;
  private $connection;

  public function __construct(sfStorage $session, Request $request,
      AIS2ServerConnection $connection)
  {
    $this->session = $session;
    $this->request = $request;
    $this->connection = $connection;
  }

  public function isLoggedIn()
  {
    $login = $this->session->read('login/login.class');
    if ($login === null) return false;
    if ($this->cachedLoggedIn != null) return $this->cachedLoggedIn;
    
    $this->cachedLoggedIn =  $login->isLoggedIn($this->connection);
    return $this->cachedLoggedIn;
  }
  
  public function relogin()
  {
    $this->cachedLoggedIn = null;
    $login = $this->session->read('login/login.class');
    if ($login === null) return false;
    $this->cachedLoggedIn = $login->ais2Relogin($this->connection);
    return $this->cachedLoggedIn;
  }
  
  /**
   * Properly destroy session and start a new clean one. 
   */
  public function destroySession()
  {
    // Regenerate the session id and delete the old session file.
    // Note that regenerating session id does not in any way alter the
    // session data stored in $_SESSION, so we need to be careful to clear
    // that as well. Otherwise only the session id will be changed with
    // the session data copied to the new session.
    $result = session_regenerate_id(true);

    // Ensure we don't copy any data to the new session
    $_SESSION = array();

    return $result;
  }

  /**
   * Odhlási z Cosignu a zmaže lokálne cookies.
   */
  public function logout()
  {
    $this->cachedLoggedIn = null;
    $login = $this->session->read('login/login.class');
    
    // Destroy the session before requesting AIS logout page
    // to be sure that we logout properly in case the connection
    // or login on AIS side fails (we must assume that the possible
    // error would be permanent)
    $this->destroySession();

    if ($login === null || !$login->logout($this->connection)) {
      return false;
    }
    
    return true;
  }

  public function login(Trace $trace, ServerConfig $serverConfig)
  {
    $this->cachedLoggedIn = null;
    $login = $this->provideLogin($serverConfig, $this->request);
    if ($login === null) return false;
    $trace->tlog("logging in");
    if (!$login->login($this->connection)) {
      return false;
    }
    $trace->tlog("logged in correctly.");
    $this->session->write('login/login.class', $login);
    $this->session->write('server', $serverConfig);

    return true;
  }

  private function assertSecurity($condition, $message)
  {
    if ($condition !== true) {
      throw new SecurityException($message);
    }
  }

  /**
   * Provides login object created from POST-data
   * or null if login info is not (fully) present in the request.
   *
   * This function should be called only once (it will
   * return null on subsequent calls).
   *
   * @returns Login login instance recognized
   */
  private function provideLogin(ServerConfig $serverConfig, Request $request)
  {
    $loginType = $request->getParameter("loginType");
    $login = $request->getParameter('login');
    $password = $request->getParameter('password');
    $cosignCookie = $request->getParameter('cosignCookie');

    // we don't need this info in the global scope anymore
    $request->clearParameter('login');
    $request->clearParameter('password');
    $request->clearParameter('cosignCookie');

    if (empty($loginType)) return null;

    switch ($serverConfig->getLoginType()) {
      case 'password':
        $this->assertSecurity($loginType === 'password',
                              "Wrong login type $loginType");
        $this->assertSecurity($login !== null, 'Login field missing');
        $this->assertSecurity($password !== null, 'Password field missing');

        if ($login === '' || $password === '') {
          return null;
        }
        return new AIS2PasswordLogin($login, $password);
        break;
      case 'cosign':
        if ($loginType === 'cosigncookie') {
          if ($cosignCookie === '') {
            return null;
          }
          $cosignCookie = CosignServiceCookie::fixCookieValue($cosignCookie);
          return new AIS2CosignLogin(new CosignCookieLogin(
              new CosignServiceCookie($serverConfig->getCosignCookieName(),
                $cosignCookie, $serverConfig->getServerName())));
        } else if ($loginType == 'cosignpassword') {
          if ($login === null || $password === null) {
            return null;
          }
          return new AIS2CosignLogin(new CosignPasswordLogin($login, $password));
        } else {
          $this->assertSecurity(false, "Wrong loginType $loginType");
        }
        break;
      case 'cosignproxy':
        $this->assertSecurity($loginType === 'cosignproxy',
                              "Wrong loginType $loginType");
        return new AIS2CosignLogin(new CosignProxyLogin(
            $serverConfig->getCosignProxyDB(),
            $serverConfig->getCosignCookieName()));
      case 'nologin':
        $this->assertSecurity($loginType === 'nologin',
                              "Wrong loginType $loginType");
        return new NoLogin();
      default:
        // TODO(ppershing): throw ConfigError
        assert(false);
    }
  }
}
