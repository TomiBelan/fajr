<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.
/**
 * Contains base class for logging into AIS.
 *
 * @package    Libfajr
 * @subpackage Login
 * @author     Martin Králik <majak47@gmail.com>
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */

namespace libfajr\login;

use libfajr\trace\NullTrace;
use libfajr\trace\Trace;
use libfajr\connection\AIS2ServerConnection;
use libfajr\connection\AIS2ServerUrlMap;
use libfajr\connection\HttpConnection;
use libfajr\exceptions\LoginException;
use libfajr\login\Login;

/**
 * Trieda zastrešujúca prihlasovanie do AISu
 *
 * @package    Libfajr
 * @subpackage Login
 * @author     Martin Králik <majak47@gmail.com>
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
abstract class AIS2AbstractLogin implements Login
{
  /* Detekcia z hlavnej AIS stránky, či používateľ je neprihlásený */
  const NOT_LOGGED_IN_PATTERN = '<form name="LoginForm"';
  /* Detekcia z hlavnej AIS stránky, či používateľ je prihlásený*/
  const LOGGED_IN_PATTERN = '@\<div class="user-name"\>[^<]@';

  /**
   * Checks whether logout response is correct
   *
   * @param string $logoutResponse 
   *
   * @throws LoginException on error
   */
  protected abstract function _checkLogoutPattern($logoutResponse) ;

  public function logout(AIS2ServerConnection $serverConnection)
  {
    $connection = $serverConnection->getHttpConnection();
    $urlMap = $serverConnection->getUrlMap();
    $data = $connection->get(new NullTrace(), $urlMap->getLogoutUrl());
    $this->_checkLogoutPattern($data);
    return true;
  }

  public function isLoggedIn(AIS2ServerConnection $serverConnection)
  {
    $connection = $serverConnection->getHttpConnection();
    $urlMap = $serverConnection->getUrlMap();
    $data = $connection->get(new NullTrace(), $urlMap->getStartPageUrl());
    if (preg_match(self::NOT_LOGGED_IN_PATTERN, $data)) return false;
    if (preg_match(self::LOGGED_IN_PATTERN, $data)) return true;
    throw new LoginException("Cannot tell if user is logged in: Unexpected response.");
  }

}
