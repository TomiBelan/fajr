<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.
/**
 * Contains AIS2 table description for regression checking.
 *
 * @package    Libfajr
 * @subpackage Regression
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace libfajr\regression;

/**
 * Contains AIS2 table description.
 *
 * @package    Libfajr
 * @subpackage Regression
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class PrihlaseniNaTerminRegression
{
  /**
   * @returns array(string) description of the table
   */
  public static function get()
  {
    return
      array (
          0 => 'meno',
          1 => 'priezvisko',
          2 => 'skratka',
          3 => 'datumPrihlas',
          4 => 'plneMeno',
          5 => 'rocnik',
          6 => 'kruzok',
          7 => 'liveID',
          8 => 'email',
          9 => 'prerekvizity',
          );
  }
}
