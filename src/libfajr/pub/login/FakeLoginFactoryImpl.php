<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * @author Martin Sucha <anty.sk@gmail.com>
 */

namespace fajr\libfajr\pub\login;
use fajr\libfajr\login\FakeLogin;
use fajr\libfajr\login\NoLogin;

class FakeLoginFactoryImpl implements LoginFactory {
  /**
   * @returns AIS2Login
   */
  public function newLoginUsingCookie($cookie) {
    $ok = ($cookie != "wrong_cookie");
    return new FakeLogin($ok);
  }

  /**
   * @return AIS2Login
   */
  public function newLoginUsingCosign($username, $password) {
    $ok = ($password != "wrong");
    return new FakeLogin($ok);
  }

  /**
   * @return AIS2Login
   */
  public function newNoLogin() {
    return new NoLogin();
  }
}
