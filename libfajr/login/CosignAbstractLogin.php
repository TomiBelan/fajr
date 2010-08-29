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
namespace fajr\libfajr\login;
use fajr\libfajr\pub\connection\HttpConnection;
use fajr\libfajr\pub\login\Login;
use fajr\libfajr\base\DisableEvilCallsObject;
use fajr\libfajr\pub\base\NullTrace;
use fajr\libfajr\pub\exceptions\LoginException;

abstract class CosignAbstractLogin extends DisableEvilCallsObject implements Login {
  const COSIGN_LOGIN = 'https://login.uniba.sk/cosign.cgi';
  const COSIGN_LOGOUT = 'https://login.uniba.sk/logout.cgi';

  const LOGGED_ALREADY_PATTERN = '@Moja Univerzita Komenského@';
  const IIKS_LOGIN_PATTERN = '@\<title\>IIKS \- Prihlásenie\</title\>@';

  public function isLoggedIn(HttpConnection $connection) {
    $data = $connection->get(new NullTrace(), self::COSIGN_LOGIN);
    if (preg_match(self::LOGGED_ALREADY_PATTERN, $data)) return true;
    if (preg_match(self::IIKS_LOGIN_PATTERN, $data)) return false;
    return new LoginException("Unexpected response.");
  }

  public function logout(HttpConnection $connection) {
    $data = $connection->post(new NullTrace(), self::COSIGN_LOGOUT,
        array("verify" => "Odhlásiť",
              "url" => "https://login.uniba.sk/"));
    if (!preg_match(self::IIKS_LOGIN_PATTERN, $data)) {
      throw new LoginException("Unexpected response.");
    }
  }
}
