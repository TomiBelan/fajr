<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

namespace fajr\libfajr\pub\login;

use fajr\libfajr\pub\data_manipulation\CosignServiceCookie;

interface LoginFactory {
  /**
   * @param CosignServiceCookie $cookie
   * @returns Login
   */
  public function newLoginUsingCookie(CosignServiceCookie $cookie);

  /**
   * @param string $username
   * @param string $password
   * @returns Login
   */
  public function newLoginUsingCosign($username, $password);

  /**
   * @param string $proxyDb Cosign ProxyDB directory
   * @param string $cookieName Name of cosign's proxied cookie
   * @returns Login
   */
  public function newLoginUsingCosignProxy($proxyDb, $cookieName);

  /**
   * @returns Login
   */
  public function newNoLogin();
}
