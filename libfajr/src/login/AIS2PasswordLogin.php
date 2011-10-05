<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Trieda reprezentujúca prihlasovanie pomocou hesla.
 *
 * @package    Libfajr
 * @subpackage Login
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace libfajr\login;
use libfajr\connection\HttpConnection;
use libfajr\base\NullTrace;
use libfajr\base\Trace;
use libfajr\login\Login;
use libfajr\exceptions\LoginException;
use libfajr\connection\AIS2ServerConnection;
use libfajr\connection\AIS2ServerUrlMap;
use libfajr\util\Strutil;

/**
 * Trieda reprezentujúca prihlasovanie pomocou hesla.
 *
 * @package    Libfajr
 * @subpackage Login
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class AIS2PasswordLogin extends AIS2AbstractLogin
{
  const LOGIN_ERROR_PATTERN = '@\<div class="login-error"\>([^<]+)\<\/div\>@';

  private $username;
  private $password;

  public function __construct($username, $password) {
    $this->username = $username;
    $this->password = $password;
  }

  public function login(AIS2ServerConnection $serverConnection)
  {
    $connection = $serverConnection->getHttpConnection();
    $urlMap = $serverConnection->getUrlMap();

    $login = $this->username;
    $password = $this->password;
    $this->username = null;
    $this->password = null;

    $data = $connection->post(new NullTrace(), $urlMap->getLoginUrl(),
        array("login" => $login, "password" => $password) );
    if (!preg_match(self::LOGGED_IN_PATTERN, $data)) {
      if ($reason = Strutil::match(self::LOGIN_ERROR_PATTERN, $data)) {
        $reason = iconv("WINDOWS-1250", "UTF-8", $reason);
        throw new LoginException('Login failed, reason: <b>'.$reason.'</b>');
      }
      throw new LoginException("Login failed, unknown reason.");
    }
    return true;
  }

  protected function _checkLogoutPattern($response) {
    if (!preg_match(self::NOT_LOGGED_PATTERN, $response)) {
      throw new LoginException("Unexpected response.");
    }
  }

  public function ais2Relogin(AIS2ServerConnection $serverConnection)
  {
    throw new LoginException("Can't relogin. User credentials weren't saved.");
  }
}
