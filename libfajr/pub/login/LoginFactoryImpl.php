<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * @author Martin Sucha <anty.sk@gmail.com>
 */

namespace fajr\libfajr\pub\login;
use fajr\libfajr\login\CosignPasswordLogin;
use fajr\libfajr\login\CosignCookieLogin;
use fajr\libfajr\login\NoLogin;
use fajr\libfajr\login\TwoPhaseLogin;
use fajr\libfajr\login\AIS2LoginImpl;

class LoginFactoryImpl implements LoginFactory {
  /**
   * @returns AIS2Login
   */
  public function newLoginUsingCookie($cookie) {
    return new TwoPhaseLogin(new CosignCookieLogin($cookie),
                             new AIS2LoginImpl());
  }

  /**
   * @return AIS2Login
   */
  public function newLoginUsingCosign($username, $password) {
    return new TwoPhaseLogin(new CosignPasswordLogin($username, $password),
                             new AIS2LoginImpl());
  }

  /**
   * @return AIS2Login
   */
  public function newNoLogin() {
    return new NoLogin();
  }
}
