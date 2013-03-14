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

interface AdministraciaStudiaScreen extends LazyDialog
{
  const ACTION_HODNOTENIA_PRIEMERY = 'hodnoteniaPriemeryAction';
  const ACTION_TERMINY_HODNOTENIA = 'terminyHodnoteniaAction';
  
  /**
   * @returns SimpleDataTable
   */
  public function getZoznamStudii(Trace $trace);

  /**
   * @returns SimpleDataTable
   */
  public function getZapisneListy(Trace $trace, $studiumIndex);

  public function getParamNameFromZapisnyListIndex(Trace $trace, $zapisnyListIndex, $action);
  
  public function getPrehladKreditovDialog(Trace $trace, $studiumIndex);
}
