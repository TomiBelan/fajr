<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Libfajr
 * @subpackage Window__VSES017_administracia_studia
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace libfajr\window\VSES017_administracia_studia;

use libfajr\window\LazyDialog;
use libfajr\trace\Trace;

interface TerminyHodnoteniaScreen extends LazyDialog
{
  /**
   * @returns SimpleDataTable
   */
  public function getPredmetyZapisnehoListu(Trace $trace);

  /**
   * @returns SimpleDataTable
   */
  public function getTerminyHodnotenia(Trace $trace);

  /**
   * @returns ZoznamTerminovDialog
   */
  public function getZoznamTerminovDialog(Trace $trace, $predmetIndex);

  /**
   * @returns ZoznamPrihlasenychDialog
   */
  public function getZoznamPrihlasenychDialog(Trace $trace, $terminIndex);

  public function odhlasZTerminu(Trace $trace, $terminIndex);
}
