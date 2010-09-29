<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Tento súbor obsahuje objekt reprezentujúci príchodziu požiadavku
 *
 * @package    Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr;

use fajr\libfajr\base\Preconditions;

/**
 * Class representing incoming request from browser
 *
 * @package    Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class Request
{

  /**
   * Return a named parameter
   *
   * @param string $name
   * @param string defaultValue
   * @returns string parameter value
   */
  public function getParameter($name, $defaultValue = '')
  {
    Preconditions::checkIsString($name, "name");
    Preconditions::checkIsString($defaultValue, 'defaultValue');

    $value = Input::get($name);
    if ($value === null) {
      return $defaultValue;
    }
    return $value;
  }

}