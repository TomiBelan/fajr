<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains utility functions for manipulating with strings.
 *
 * @package    Libfajr
 * @subpackage Util
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */

namespace libfajr\util;

use libfajr\base\Preconditions;

/**
 * String utilities.
 *
 * @package    Libfajr
 * @subpackage Util
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
class StrUtil {
  /**
   * Searches haystack for perl-like pattern and
   * returns first sub-match from pattern.
   * I.e. If the pattern is "example:(.*)",
   * the full match is example:something and this
   * function returns "something"
   *
   * If you specify the third parameter,
   * it will try to return submatch with given name.
   *
   * @param string $pattern perl-like pattern with 1 sub-match
   * @param string $haystack
   * @param string|null $name name of submatch
   *
   * @returns string sub-match or false on failure
   */
  public static function match($pattern, $haystack, $name = null)
  {
    Preconditions::checkIsString($pattern);
    Preconditions::checkIsString($haystack);
    $matches = array();
    if (!preg_match($pattern, $haystack, $matches)) {
      return false;
    }
    $index = 1;
    if ($name !== null) {
      $index = $name;
    }
    assert(isset($matches[$index]));
    return $matches[$index];
  }

  /**
   * Searches the haystack for perl-like pattern and
   * return an array of all submatches
   *
   * @param string $pattern perl-like pattern
   * @param string $haystack
   *
   * @returns array of submatches or false on failure
   */
  public static function matchAll($pattern, $haystack)
  {
    Preconditions::checkIsString($pattern);
    Preconditions::checkIsString($haystack);
    $matches = array();
    if (!preg_match_all($pattern, $haystack, $matches, PREG_SET_ORDER)) {
      return false;
    }
    return $matches[0];
  }

  /**
   * Checks whether $haystack starts with a substring $needle
   *
   * @param string $haystack
   * @param string $needle
   *
   * @returns bool true if $haystack starts with $needle, false otherwise
   */
  public static function startsWith($haystack, $needle)
  {
    Preconditions::checkIsString($haystack);
    Preconditions::checkIsString($needle);
    // TODO(ppershing): why can't we use simply
    // substr($haystack, 0, strlen($needle)) === $needle ?
    // anty: substr makes copy, it may have memory and speed problems.
    if ($needle == '') return true;

    $needle_length = strlen($needle);
    if ($needle_length > strlen($haystack)) {
      return false;
    }
    return substr_compare($haystack, $needle, 0, $needle_length) === 0;
  }

  /**
   * Checks whether $haystack ends with a substring $needle
   *
   * @param string $haystack
   * @param string $needle
   *
   * @returns bool true if $haystack ends with $needle, false otherwise
   */
  public static function endsWith($haystack, $needle)
  {
    Preconditions::checkIsString($haystack);
    Preconditions::checkIsString($needle);
    if ($needle == '') return true;

    $needle_length = strlen($needle);
    if ($needle_length > strlen($haystack)) {
      return false;
    }
    return substr_compare($haystack, $needle, -$needle_length, $needle_length) === 0;
  }

  /**
   * Filters-out invalid UTF-8 strings.
   *
   * Currently detects invalid UTF-8 strings and replaces them
   * with an error message.
   *
   * @param string $string
   * 
   * @returns string
   */
  public static function utf8Sanitize($string)
  {
    Preconditions::checkIsString($string, '$string must be string');
    $out = @iconv("UTF-8", "UTF-8//IGNORE", $string);
    if ($string != $out) {
      $out = "Warning: String not shown for security reasons: " .
             "String contains invalid utf-8 charactes.";
    }
    return $out;
  }

  /**
   * Escapes string to be safely used in HTML.
   *
   * @param string $string arbitrary user data
   *
   * @returns string escaped string safe to use as HTML element content
   */
  public static function hescape($string)
  {
    return htmlspecialchars(self::utf8Sanitize($string), ENT_QUOTES, 'UTF-8');
  }
  
  /**
   * Return the length of string in bytes
   * @param string $str 
   */
  public static function byteLength($str) {
    Preconditions::checkIsString($str);
    $overloadMode = ini_get('mbstring.func_overload');
    if (($overloadMode & 2) == 2) {
      // overloaded string functions
      // strlen returns # of chars instead of bytes in this case
      // so we use mb_strlen
      return mb_strlen($str, '8bit');
    }
    return strlen($str);
  }
}
?>
