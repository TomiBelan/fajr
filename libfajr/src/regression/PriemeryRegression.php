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
class PriemeryRegression
{
  /**
   * @returns array(string) description of the table
   */
  public static function get()
  {
    return
      array (
          0 => 'priemerInfoPopisAkadRok',
          1 => 'vazPriemer',
          2 => 'studPriemer',
          3 => 'pocetPredmetov',
          4 => 'pocetNeabs',
          5 => 'pokusyPriemer',
          6 => 'ziskanyKredit',
          7 => 'prerusUkon',
          8 => 'priemerInfoDatum',
          9 => 'priemerInfoDatum1Hodn',
          10 => 'priemerInfoDatum2Hodn',
          11 => 'priemerNazov',
          12 => 'priemerZaAkRok',
          13 => 'priemerZaSemester',
          14 => 'priemerLenStudPlan',
          15 => 'priemerUznanePredm',
          16 => 'priemerAjDatum1Hodn',
          17 => 'priemerAjDatum2Hodn',
          18 => 'priemerPocitatNeabs',
          19 => 'priemerVahaNeabsolvovanych',
          20 => 'priemerSkratkaOrganizacnaJednotka',
          21 => 'priemerPocitatNeabsC',
          22 => 'pocetPredmetovVyp',
          23 => 'priemerInfoStudentiVypoctu',
          24 => 'priemerInfoPopisAkadRokOd',
          25 => 'priemerInfoDatum1HodnOd',
          26 => 'priemerInfoDatum2HodnOd',
          27 => 'semesterKodJ',
          28 => 'semesterOdKodJ',
          29 => 'priemerPocitatNeabsABC',
          );
  }
}
