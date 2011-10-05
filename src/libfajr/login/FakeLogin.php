<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Libfajr
 * @subpackage Login
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace libfajr\login;

use libfajr\pub\login\Login;
use libfajr\pub\connection\AIS2ServerConnection;
use libfajr\pub\exceptions\AIS2LoginException;

class FakeLogin implements Login
{
  private $loggedIn = false;
  private $shouldLogin = false;

  public function __construct($shouldLogin)
  {
    $this->shouldLogin = $shouldLogin;
  }

  public function login(AIS2ServerConnection $unused)
  {
    if ($this->shouldLogin == true) {
      $this->loggedIn = true;
      return true;
    } else {
      throw new AIS2LoginException("Fake login supposed to fail. (wrong password in real life)");
    }
  }

  public function logout(AIS2ServerConnection $unused)
  {
    $this->loggedIn = false;
  }

  public function isLoggedIn(AIS2ServerConnection $unused)
  {
    return $this->loggedIn;
  }

  public function ais2Relogin(AIS2ServerConnection $unused)
  {
    return true;
  }
}
