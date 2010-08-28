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

use fajr\libfajr\connection\HttpConnection;
use fajr\libfajr\pub\base\NullTrace;
use fajr\libfajr\pub\exceptions\LoginException;
use fajr\libfajr\util;
/**
 * Trieda reprezentujúca prihlasovanie pomocou cosign
 *
 * @author majak, ms
 */
class CosignPasswordLogin extends CosignAbstractLogin {
  private $username = null;
  private $krbpwd = null;

  public function __construct($username, $krbpwd) {
    assert($username != null);
    assert($krbpwd != null);
    $this->username = $username;
    $this->krbpwd = $krbpwd;
  }

  const COSIGN_ERROR_PATTERN1 = 
    '@Pri pokuse o prihlásenie sa vyskytol problém:[^<]*\<div[^>]*\>([^<]*)\<\/div\>@';
  const COSIGN_ERROR_PATTERN2 = 
    '@Pri pokuse o prihlásenie sa vyskytol problém:[^<]*\<b\>([^<]*)\<\/b\>@';
  const COSIGN_ERROR_PATTERN3 = 
    '@Pri pokuse o prihlásenie sa vyskytol problém:[^<]*\<div[^>]*\>\<b\>([^<]*)\<\/b\>@';

  const IIKS_ERROR = '@\<title\>IIKS \- ([^,]*)\<\/title\>@';

  public function login(HttpConnection $connection) {
    $login = $this->username;
    $krbpwd = $this->krbpwd;

    // Username a password si nebudeme pamatat dlhsie ako treba
    $this->username = null;
    $this->krbpwd = null;
    $this->isLoggedIn($connection);
    $data = $connection->post(new NullTrace(), self::COSIGN_LOGIN,
                              array('ref' => '', 'login'=> $login, 'krbpwd' => $krbpwd));
    if (!preg_match(parent::LOGGED_ALREADY_PATTERN, $data)) {
      if (($reason = util\match(self::COSIGN_ERROR_PATTERN1, $data)) ||
          ($reason = util\match(self::COSIGN_ERROR_PATTERN2, $data)) ||
          ($reason = util\match(self::COSIGN_ERROR_PATTERN3, $data)) ||
          ($reason = util\match(self::IIKS_ERROR, $data))) {
        throw new LoginException('Nepodarilo sa prihlásiť, dôvod: <b>'.$reason.'</b>');
      }
      throw new LoginException('Nepodarilo sa prihlásiť, dôvod neznámy.');
    }
  }
}
