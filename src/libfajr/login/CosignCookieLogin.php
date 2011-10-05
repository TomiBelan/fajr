<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Login
 * @author     Martin Králik <majak47@gmail.com>
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace libfajr\login;
use libfajr\pub\connection\HttpConnection;
use libfajr\pub\login\CosignServiceCookie;
use libfajr\pub\base\NullTrace;
use libfajr\pub\exceptions\NotImplementedException;
use libfajr\pub\exceptions\LoginException;
use libfajr\AIS2ServerInstance;
use libfajr\pub\connection\AIS2ServerConnection;
use libfajr\pub\connection\AIS2ServerUrlMap;

/**
 * Trieda reprezentujúca prihlasovanie pomocou cookie
 *
 * @package    Fajr
 * @subpackage Libfajr__Login
 * @author     Martin Králik <majak47@gmail.com>
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class CosignCookieLogin extends CosignAbstractLogin
{
  /** @var CosignServiceCookie $cookie */
  protected $cookie = null;

  public function  __construct(CosignServiceCookie $cookie)
  {
    $this->cookie = $cookie;
  }

  public function login(AIS2ServerConnection $serverConnection)
  {
    $connection = $serverConnection->getHttpConnection();
    $connection->addCookie($this->cookie->getName(), $this->cookie->getValue(),
                           0, '/', $this->cookie->getDomain());
    return true;
  }

  public function isLoggedIn(AIS2ServerConnection $unused)
  {
    throw new NotImplementedException("We are not able to verify cosign service cookie.");
  }
}
