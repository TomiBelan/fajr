<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

namespace fajr\libfajr\login;
use fajr\libfajr\pub\connection\HttpConnection;
use fajr\libfajr\pub\base\NullTrace;
use fajr\libfajr\pub\exceptions\NotImplementedException;
use fajr\libfajr\pub\exceptions\LoginException;
/**
 * Trieda reprezentujúca prihlasovanie pomocou cookie
 *
 * @author Martin Králik <majak47@gmail.com>
 * @author Martin Sucha <anty.sk@gmail.com>
 */
class CosignCookieLogin extends CosignAbstractLogin {
  private $cookie = null;

  public function  __construct($cookie) {
    assert($cookie !== null);
    $this->cookie = $cookie;
  }

  public function login(HttpConnection $connection) {
    $connection->addCookie('cosign-filter-ais2.uniba.sk', $this->cookie,
                  0, '/', 'ais2.uniba.sk');
    return true;
  }

  public function isLoggedIn(HttpConnection $connection) {
    throw new NotImplementedException("We are not able to verify cosign service cookie.");
  }
}
