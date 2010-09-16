<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains wrapper for usual argument-checking in function calls.
 *
 * @package    Fajr
 * @subpackage Libfajr__Base
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\base;
use InvalidArgumentException;

/**
 * Provides easy way to do common argument-checking
 * for function calls.
 *
 * Note: Use only for argument checking!
 *
 * @package    Fajr
 * @subpackage Libfajr__Base
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class Preconditions
{
  /**
   * Checks that $variable is not null
   *
   * @param mixed $variable variable to check
   * @param string $name variable name to display in error
   *
   * @returns void
   * @throws InvalidArgumentException
   */
  public static function checkNotNull($variable, $name = null)
  {
    if ($variable === null) {
      if ($name === null) $name = "Argument";
      throw new InvalidArgumentException("$name should not be null!");
    }
  }

  /**
   * Checks that $variable is a string
   *
   * @param mixed $variable variable to check
   * @param string $name variable name to display in error
   *
   * @returns void
   * @throws InvalidArgumentException
   */
  public static function checkIsString($variable, $name = null)
  {
    if (!is_string($variable)) {
      if ($name === null) $name = "Argument";
      throw new InvalidArgumentException("$name should be a string!");
    }
  }

  /**
   * Checks that $variable is a string
   *
   * @param bool $expression boolean result of an expression
   * @param string $message error message
   *
   * @returns void
   * @throws InvalidArgumentException
   */
  public static function check($expression, $message)
  {
    assert(is_bool($expression));
    if (!$expression) {
      throw new InvalidArgumentException($message);
    }
  }

}
