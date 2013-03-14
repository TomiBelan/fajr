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
class ZoznamStudiiRegression
{
  /**
   * @returns array(string) description of the table
   */
  public static function get()
  {
    return
      array (
          0 => 'rokDoporuceny',
          1 => 'studijnyProgramSkratka',
          2 => 'kodKruzok',
          3 => 'studijnyProgramPopis',
          4 => 'studijnyProgramDoplnUdaje',
          5 => 'zaciatokStudia',
          6 => 'koniecStudia',
          7 => 'studijnyProgramDlzka',
          8 => 'dobaStudia',
          9 => 'cisloDiplomu',
          10 => 'cisloMatriky',
          11 => 'cisloVysvedcenia',
          12 => 'cisloDodatku',
          13 => 'studijnyProgramIdEviCRS',
          14 => 'studijnyProgramIdProgramCRS',
          15 => 'priznak',
          16 => 'studijnyProgramSkratkaAkreditOJ',
          17 => 'rokStudia',
          18 => 'rokyStudiaStupen',
          19 => 'idEvi',
          20 => 'modifikator',
          21 => 'poslModif',
          22 => 'nespoplatneRoky',
          23 => 'rokyStudiaProgram',
          24 => 'stupenRoky',
          25 => 'programRoky',
          26 => 'cisloDiplomuIIst',
          27 => 'studijnyProgramIdProgramCRS2',
          );
  }
}
