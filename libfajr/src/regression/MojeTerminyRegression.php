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
class MojeTerminyRegression
{
  /**
   * @returns array(string) description of the table
   */
  public static function get()
  {
    return
      array (
          0 => 'jePrihlaseny',
          1 => 'kodFaza',
          2 => 'dat',
          3 => 'cas',
          4 => 'miestnosti',
          5 => 'pocetPrihlasenych',
          6 => 'datumPrihlas',
          7 => 'datumOdhlas',
          8 => 'zapisal',
          9 => 'pocetHodn',
          10 => 'hodnotiaci',
          11 => 'maxPocet',
          12 => 'znamka',
          13 => 'prihlasovanie',
          14 => 'odhlasovanie',
          15 => 'poznamka',
          16 => 'zaevidoval',
          17 => 'mozeOdhlasit',
          18 => 'predmetSkratka',
          19 => 'predmetNazov',
          20 => 'hodnPredmetu',
          21 => 'moznostPrihlasenia',
          22 => 'modifikator',
          23 => 'poslModif',
          );
  }
  
  /**
   * @returns array(string) description of the table
   */
  public static function getPrihlasovanie()
  {
    return
      array (
          0 => 'kodFaza',
          1 => 'dat',
          2 => 'cas',
          3 => 'miestnosti',
          4 => 'pocetPrihlasenych',
          5 => 'maxPocet',
          6 => 'pocetHodn',
          7 => 'hodnotiaci',
          8 => 'prihlasovanie',
          9 => 'odhlasovanie',
          10 => 'poznamka',
          11 => 'zaevidoval',
          12 => 'moznostPrihlasenia',
          );
  }
}
