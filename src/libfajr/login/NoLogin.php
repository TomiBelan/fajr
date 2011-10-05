<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Login
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace libfajr\login;

use libfajr\pub\login\Login;
use libfajr\pub\connection\HttpConnection;
use libfajr\pub\connection\AIS2ServerConnection;

class NoLogin implements Login
{
  public function login(AIS2ServerConnection $unused)
  {
    return true;
  }

  public function logout(AIS2ServerConnection $unused)
  {
  }

  public function isLoggedIn(AIS2ServerConnection $unused)
  {
    return true;
  }

  public function ais2Relogin(AIS2ServerConnection $unused)
  {
    return true;
  }
}
