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

require_once 'FajrRouter.php';

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

	public static function redirect($newParams = array())
	{
		header('Location: '.self::buildUrl(array_merge(Input::getUrlParams(), $newParams)));
		exit();
	}

	public static function getTempDir()
	{
		return dirname(__FILE__).DIRECTORY_SEPARATOR.'temp';
	}

	public static function getCookieDir()
	{
		return self::getTempDir().DIRECTORY_SEPARATOR.'cookies';
	}

	public static function getCookieFile()
	{
		return self::getCookieDir().DIRECTORY_SEPARATOR.session_id();
	}

	public static function buildUrl($params) {
		$path = FajrRouter::paramsToPath($params);
		$query = http_build_query($params);
		if (strlen($query)>0) $query = '?'.$query;

		$base = '';

		if (!FajrConfig::get('URL.Rewrite')) {
			$base = 'fajr.php';
			if (strlen($path)>0) $base .= '/';
		}

		return self::basePath().$base.$path.$query;
	}

	public static function linkUrl($params) {
		return hescape(self::buildUrl($params));
	}

	public static function pathInfo() {
		if (!isset($_SERVER['PATH_INFO'])) return '';
		$path = $_SERVER['PATH_INFO'];
		if (substr_compare($path, '/', 0, 1)==0) $path = substr($path, 1);
		return $path;
	}
	
	public static function isHTTPS() {
		return ((isset($_SERVER['HTTPS'])) && 
					($_SERVER['HTTPS']!=='off') && 
					($_SERVER['HTTPS'])) || 
				((isset($_SERVER['SERVER_PORT'])) && $_SERVER['SERVER_PORT']=='443');
	}

	public static function basePath() {
		$url = '';
		if (self::isHTTPS()) {
			$url = 'https://';
		}
		else {
			$url = 'http://';
		}
		$url .= $_SERVER['SERVER_NAME'];
		if (isset($_SERVER['SERVER_PORT'])) {
			$port = $_SERVER['SERVER_PORT'];
			if ($port != '80' && $port != '443') {
				$url .= ':'.$port;
			}
		}
		$url .= dirname($_SERVER['SCRIPT_NAME']);

		return $url;
	}

}