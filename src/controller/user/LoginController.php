<?php
/**
 * Contains controller for login/logout
 *
 * @copyright  Copyright (c) 2011-2012 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Controller__User
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 * @filesource
 */
namespace fajr\controller\user;

use Exception;
use fajr\Context;
use fajr\controller\BaseController;
use libfajr\AIS2Utils;
use libfajr\base\Preconditions;
use libfajr\trace\Trace;
use fajr\Request;
use fajr\util\FajrUtils;
use sfStorage;
use fajr\config\FajrConfig;
use fajr\config\FajrConfigLoader;
use fajr\settings\SkinSettings;
use fajr\LoginManager;
use fajr\ServerManager;
use sfSessionStorage;
use fajr\SessionStorageProvider;
use fajr\Router;
use libfajr\login\CosignProxyLogin;
use Symfony\Component\HttpFoundation\RedirectResponse;
use fajr\rendering\DisplayManager;

/**
 * Controller for login/logout
 *
 * @package    Fajr
 * @subpackage Controller__User
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 */
class LoginController extends BaseController
{
  /* TODO document */
  public static function getInstance()
  {
    return new LoginController(FajrConfigLoader::getConfiguration(),
        LoginManager::getInstance(), ServerManager::getInstance(),
        SessionStorageProvider::getInstance(), DisplayManager::getInstance(),
        Router::getInstance());
  }

  /** @var FajrConfig */
  private $config;
  
  /** @var LoginManager */
  private $loginManager;
  
  /** @var ServerManager */
  private $serverManager;
  
  /** @var sfSessionStorage */
  private $session;

  public function __construct(FajrConfig $config, LoginManager $loginManager,
      ServerManager $serverManager, sfSessionStorage $session,
      DisplayManager $displayManager, Router $router)
  {
    parent::__construct($displayManager, $router);
    $this->config = $config;
    $this->loginManager = $loginManager;
    $this->serverManager = $serverManager;
    $this->session = $session;
  }

  public function runLogin(Trace $trace, Context $context)
  {
    $server = $this->serverManager->getActiveServer();

    try {
      $this->loginManager->login($trace->addChild("Logging in..."), $server);
      // Ak sa niekedy odoberie nasledovny redirect,
      // treba mat na pamati, ze login() moze skoncit s true, false, alebo
      // vynimkou
      return new RedirectResponse($this->generateUrl('homepage', array(), true));
    } catch (LoginException $e) {
      try {
        $this->loginManager->logout($trace);
      } catch (LoginException $e) {
        // do nothing
      }
      throw $e;
    }
  }
  
  public function runLoginScreen(Trace $trace, Context $context)
  {
    $server = $this->serverManager->getActiveServer();

    if ($this->loginManager->isLoggedIn()) {
      return new RedirectResponse($this->generateUrl('studium_moje_skusky'));
    }

    switch ($server->getLoginType()) {
        case 'password':
          return $this->renderResponse('welcome');
        case 'cosign':
          return $this->renderResponse('welcomeCosign');
        case 'cosignproxy':
          return $this->renderResponse('welcomeCosignProxy');
        case 'nologin':
          return $this->renderResponse('welcomeDemo');
        default:
          throw new Exception("Invalid type of login");
      }
  }
  
  public function runLogout(Trace $trace, Context $context)
  {
    $server = $this->session->read('server');
    $result = $this->loginManager->logout();
    if ($result && $server->getLoginType() == 'cosignproxy') {
      // Redirect na hlavnu odhlasovaciu stranku univerzity
      $redirectUrl = CosignProxyLogin::COSIGN_LOGOUT;
      $redirectUrl .= '?';
      $redirectUrl .= $this->generateUrl('homepage', array(), true);
      $response = new RedirectResponse($redirectUrl);
      if (isset($_SERVER[ 'COSIGN_SERVICE' ])) {
        $response->headers->clearCookie($_SERVER[ 'COSIGN_SERVICE' ], '/', '');
      }
      return $response;
    } else {
      return new RedirectResponse($this->generateUrl('homepage', array(), true));
    }
  }

}
