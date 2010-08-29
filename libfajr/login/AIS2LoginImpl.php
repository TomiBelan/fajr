<?php
/* {{{
Copyright (c) 2010 Martin Sucha
Copyright (c) 2010 Martin Králik

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
use fajr\libfajr\pub\base\NullTrace;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\pub\login\Login;
use fajr\libfajr\pub\exceptions\LoginException;
/**
 * Trieda reprezentujúca prihlasovanie pomocou cookie
 *
 * @author majak, ms
 */
class AIS2LoginImpl implements Login {
  const MAIN_PAGE = 'https://ais2.uniba.sk/ais/start.do';
  const LOGIN_PAGE = 'https://ais2.uniba.sk/ais/login.do';
  const LOGOUT_PAGE = 'https://ais2.uniba.sk/ais/logout.do';

  // Note: ais response is in win-1250 charset, so we can't match accents
  const NOT_LOGGED_PATTERN = '@Prihl.senie@';
  const LOGGED_IN_PATTERN = '@\<div class="user-name"\>[^<]@';

  const LOGOUT_OK_PATTERN = '@IIKS - Odhlásenie@';

  public function login(HttpConnection $connection) {
    $data = $connection->get(new NullTrace(), self::LOGIN_PAGE);
    if (!preg_match(self::LOGGED_IN_PATTERN, $data)) {
      throw new LoginException("Login failed.");
    }
  }

  public function logout(HttpConnection $connection) {
    $data = $connection->get(new NullTrace(), self::LOGOUT_PAGE);
    if (!preg_match(self::LOGOUT_OK, $data)) {
      throw new LoginException("Unexpected response.");
    }
  }

  public function isLoggedIn(HttpConnection $connection) {
    $data = $connection->get(new NullTrace(), self::MAIN_PAGE);
    if (preg_match(self::NOT_LOGGED_PATTERN, $data)) return false;
    if (preg_match(self::LOGGED_IN_PATTERN, $data)) return true;
    throw new LoginException("Unexpected response.");
  }

}
