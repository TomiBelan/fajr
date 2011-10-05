<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Libfajr
 * @subpackage Login
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace libfajr\login;
use libfajr\connection\HttpConnection;
use libfajr\login\Login;
use libfajr\base\DisableEvilCallsObject;
use libfajr\base\NullTrace;
use libfajr\exceptions\LoginException;
use libfajr\exceptions\NotImplementedException;
use libfajr\connection\AIS2ServerConnection;

abstract class CosignAbstractLogin extends DisableEvilCallsObject implements Login
{
  const COSIGN_LOGIN = 'https://login.uniba.sk/cosign.cgi';
  const COSIGN_LOGOUT = 'https://login.uniba.sk/logout.cgi';

  /* TODO(anty): check actual redirected URL instead of content */
  const LOGGED_ALREADY_PATTERN = '@Portál centrálnych služieb IT na UK@';
  const IIKS_LOGIN_PATTERN = '@\<title\>IIKS \- Prihlásenie\</title\>@';
  const LOGOUT_PATTERN = '@Portál moja.uniba.sk@';

  public function logout(AIS2ServerConnection $serverConnection)
  {
    $connection = $serverConnection->getHttpConnection();
    $response = $connection->post(new NullTrace(), self::COSIGN_LOGOUT,
        array("verify" => "Odhlásiť",
              "url" => "https://login.uniba.sk/"));
    if (!preg_match(self::IIKS_LOGIN_PATTERN, $response)) {
      throw new LoginException("Unexpected response.");
    }
  }

  public function ais2Relogin(AIS2ServerConnection $serverConnection)
  {
    throw new NotImplementedException();
  }
}
