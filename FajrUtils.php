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

	require_once 'libfajr/libfajr.php';

class FajrUtils {

	public static function login(AIS2Login $login, AIS2Connection $connection) {
		$session = new AIS2Session($login);

		if (!$session->login($connection)) return false;

		$_SESSION['AISSession'] = $session;
		self::redirect();
		return true;
	}

	/**
	 * Odhlási z Cosignu a zmaže lokálne cookies.
	 */
	public static function logout(AIS2Connection $connection)
	{
		if (!isset($_SESSION['AISSession'])) return false;
		if ($_SESSION['AISSession']->logout($connection)) {
			unset($_SESSION['AISSession']);
		}
		self::redirect();
	}

	public static function isLoggedIn() {
		if (!isset($_SESSION['AISSession'])) return false;
		return $_SESSION['AISSession']->isLoggedIn();
	}

	public static function redirect($newParams = array(), $base = 'fajr.php')
	{
		header('Location: '.$base.'?'.http_build_query(array_merge(Input::getUrlParams(), $newParams)));
		exit();
	}

}