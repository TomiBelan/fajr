<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

// TODO(??): missing author

namespace fajr\libfajr\login;

use fajr\libfajr\pub\login\Login;
use fajr\libfajr\pub\connection\HttpConnection;

class NoLogin implements Login {
  public function login(HttpConnection $unused) {
    return true;
  }

  public function logout(HttpConnection $unused) {
  }

  public function isLoggedIn(HttpConnection $unused) {
    return true;
  }

  public function ais2Relogin(HttpConnection $unused) {
    return true;
  }
}
