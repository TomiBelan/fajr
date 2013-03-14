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
use libfajr\data\SimpleDataTable;
use libfajr\trace\Trace;

interface ZoznamPrihlasenychDialog extends LazyDialog
{
  /**
   * @returns SimpleDataTable
   */
  public function getZoznamPrihlasenych(Trace $trace);
}
