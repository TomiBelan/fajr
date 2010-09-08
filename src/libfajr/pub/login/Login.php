<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * @author Martin Sucha <anty.sk@gmail.com>
 */

namespace fajr\libfajr\pub\login;
use fajr\libfajr\pub\connection\HttpConnection;

interface Login {
  /**
   * Login user.
   *
   * @return void
   * @throws AIS2LoginException on failure.
   */
  public function login(HttpConnection $connection);

  /**
   * Logout user
   *
   * @return void
   * @throws AIS2LoginException on failure.
   */
  public function logout(HttpConnection $connection);

  /**
   * @return bool true if user is currently logged in.
   */
  public function isLoggedIn(HttpConnection $connection);

  /**
   * Try to relogin to ais if neccessary
   * @throws AIS2LoginException
   */
  public function ais2Relogin(HttpConnection $connection);
}
