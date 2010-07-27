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

class FajrUtils
{

  public static function login(AIS2Login $login, AIS2Connection $connection)
  {
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

  public static function isLoggedIn()
  {
    if (!isset($_SESSION['AISSession'])) return false;
    return $_SESSION['AISSession']->isLoggedIn();
  }

  public static function redirect($newParams = array())
  {
    header('Location: ' . self::buildUrl(array_merge(Input::getUrlParams(), $newParams)));
    exit();
  }

  public static function getTempDir()
  {
    return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'temp';
  }

  public static function getCookieDir()
  {
    return self::getTempDir() . DIRECTORY_SEPARATOR . 'cookies';
  }

  public static function getCookieFile()
  {
    return self::getCookieDir() . DIRECTORY_SEPARATOR . session_id();
  }

  public static function buildUrl($params)
  {
    $path = '';
    if (FajrConfig::get('URL.Path')) {
      $path = FajrRouter::paramsToPath($params);
    }
    $query = http_build_query($params);
    if (strlen($query) > 0) $query = '?' . $query;

    $base = '';

    if (!FajrConfig::get('URL.Rewrite')) {
      $base = 'fajr.php';
      if (strlen($path) > 0) $base .= '/';
    }

    return self::basePath() . $base . $path . $query;
  }

  /**
   * creates htmlescaped url
   */
  public static function linkUrl($params)
  {
    return hescape(self::buildUrl($params));
  }

  public static function pathInfo()
  {
    if (!isset($_SERVER['PATH_INFO'])) return '';
    $path = $_SERVER['PATH_INFO'];
    if (substr_compare($path, '/', 0, 1) == 0) $path = substr($path, 1);
    return $path;
  }

  /**
   * @returns bool whether the current connection is secured
   */
  public static function isHTTPS()
  {
    return ((isset($_SERVER['HTTPS'])) &&
    ($_SERVER['HTTPS'] !== 'off') &&
    ($_SERVER['HTTPS'])) ||
    ((isset($_SERVER['SERVER_PORT'])) && $_SERVER['SERVER_PORT'] == '443');
  }

  public static function basePath()
  {
    $url = '';
    if (self::isHTTPS()) {
      $url = 'https://';
    } else {
      $url = 'http://';
    }
    $url .= $_SERVER['SERVER_NAME'];
    if (isset($_SERVER['SERVER_PORT'])) {
      $port = $_SERVER['SERVER_PORT'];
      if ($port != '80' && $port != '443') {
        $url .= ':' . $port;
      }
    }

    $dname = dirname($_SERVER['SCRIPT_NAME']);

    if ($dname !== '/') {
      $dname .= '/';
    }

    $url .= $dname;

    return $url;
  }

  /**
   * Checks whether $haystack starts with a substring $needle
   * @param string $haystack
   * @param string $needle
   * @return bool true if $haystack starts with $needle, false otherwise
   */
  public static function startsWith($haystack, $needle)
  {
    $needle_length = strlen($needle);
    if ($needle_length > strlen($haystack)) {
      return false;
    }
    return substr_compare($haystack, $needle, 0, $needle_length) === 0;
  }

  /**
   * Checks whether $haystack ends with a substring $needle
   * @param string $haystack
   * @param string $needle
   * @return bool true if $haystack ends with $needle, false otherwise
   */
  public static function endsWith($haystack, $needle)
  {
    $needle_length = strlen($needle);
    if ($needle_length > strlen($haystack)) {
      return false;
    }
    return substr_compare($haystack, $needle, -$needle_length, $needle_length) === 0;
  }

  /**
   * Determines, whether given $path is an absolute path or not.
   * A path is absolute if it starts with filesystem root definition
   * (i.e. / on unix like systems and C:\ or \\ on Windows)
   * @param string $path true if the path is relative
   */
  public static function isAbsolutePath($path)
  {
    // check for unix-like /
    if (self::startsWith($path, '/')) {
      return true;
    }

    // check for Windows UNC path
    if (self::startsWith($path, '\\\\')) {
      return true;
    }

    // check for Windows drive letter
    if (preg_match('/^[A-Z]:/', $path)) {
      return true;
    }

    return false;
  }

  /**
   * Joins two or more path components.
   * If joining more than two path components, the result is
   * the same as calling two-argument joinPath successively.
   * Moreover, this function is associative, i.e.
   * joinPath('a','b','c') has the same effect as
   * joinPath(joinPath('a', 'b'), 'c') or joinPath('a', joinPath('b', 'c'))
   * @param string $a first path component
   * @param string $b second path component
   * @param string ... any other path components to join
   * @return string all the paths joined using a directory separator
   */
  public static function joinPath($a, $b)
  {
    $args = func_get_args();
    $num_args = count($args);

    // start with $a, omit trailing directory separator
    if ($a != DIRECTORY_SEPARATOR && self::endsWith($a, DIRECTORY_SEPARATOR)) {
      $path = substr($a, strlen($a) - 1);
    } else {
      $path = $a;
    }

    // add other components
    for ($i = 1; $i < $num_args; $i++) {
      $part = $args[$i];
      // DIRECTORY_SEPARATOR is a special case
      if ($part == DIRECTORY_SEPARATOR) continue;

      // first extract range of part without leading or trailing DS
      if (self::startsWith($part, DIRECTORY_SEPARATOR)) {
        $start = 1;
      } else {
        $start = 0;
      }
      if (self::endsWith($part, DIRECTORY_SEPARATOR)) {
        $end = strlen($part) - 1;
      } else {
        $end = strlen($part);
      }

      // append a path component
      $path .= DIRECTORY_SEPARATOR . substr($part, $start, $end);
    }

    return $path;
  }

}
