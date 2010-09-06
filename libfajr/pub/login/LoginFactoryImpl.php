<?php
/* {{{
Copyright (c) 2010 Martin Sucha

 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.
 }}} */

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
