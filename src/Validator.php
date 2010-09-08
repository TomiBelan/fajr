<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Trieda obsahujúca validátory pre vstupy.
 *
 * @author Martin Králik <majak47@gmail.com>
 */
class Validator
{
  static function isInteger($input, $options)
  {
    return ctype_digit($input);
  }

  static function isString($input, $options)
  {
    $minLength = 0;
    extract($options, EXTR_IF_EXISTS);

    if ((is_string($input) || is_int($input) || is_float($input))
        && (mb_strlen($input) >= $minLength)) return true;
  }
}
?>
