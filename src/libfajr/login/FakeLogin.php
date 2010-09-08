<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

// TODO(??): missing author

namespace fajr\libfajr\login;

use fajr\libfajr\pub\login\Login;
use fajr\libfajr\pub\connection\HttpConnection;
use fajr\libfajr\pub\exceptions\AIS2LoginException;

class FakeLogin implements Login {
  private $loggedIn = false;
  private $shouldLogin = false;

  public function __construct($shouldLogin) {
    $this->shouldLogin = $shouldLogin;
  }

  public function login(HttpConnection $unused) {
    if ($this->shouldLogin == true) {
      $this->loggedIn = true;
      return true;
    } else {
      throw new AIS2LoginException("Fake login supposed to fail. (wrong password in real life)");
    }
  }

  public function logout(HttpConnection $unused) {
    $this->loggedIn = false;
  }

  public function isLoggedIn(HttpConnection $unused) {
    return $this->loggedIn;
  }

  public function ais2Relogin(HttpConnection $unused) {
    return true;
  }
}
