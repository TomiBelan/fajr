<?php
namespace fajr\libfajr\login;

use fajr\libfajr\pub\login\Login;
use fajr\libfajr\pub\connection\HttpConnection;

class NoLogin implements Login {
  public function login(HttpConnection $unused) {
    return false;
  }

  public function logout(HttpConnection $unused) {
  }

  public function isLoggedIn(HttpConnection $unused) {
    return false;
  }
}
