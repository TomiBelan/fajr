<?php
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
}
