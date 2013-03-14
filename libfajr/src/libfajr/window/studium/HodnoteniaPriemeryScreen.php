<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Libfajr
 * @subpackage Window__Studium
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace libfajr\window\studium;

use libfajr\window\LazyDialog;
use libfajr\trace\Trace;

interface HodnoteniaPriemeryScreen extends LazyDialog
{
  /**
   * @returns SimpleDataTable tabulka hodnoteni
   */
  public function getHodnotenia(Trace $trace);

  /**
   * @returns SimpleDataTable tabulka priemerov
   */
  public function getPriemery(Trace $trace);

}
