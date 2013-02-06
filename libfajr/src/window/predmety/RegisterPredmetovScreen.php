<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Libfajr
 * @subpackage Window__Predmety
 * @author     Tomi Belan <tomi.belan@gmail.com>
 * @filesource
 */
namespace libfajr\window\predmety;

use libfajr\window\LazyDialog;
use libfajr\trace\Trace;

interface RegisterPredmetovScreen extends LazyDialog
{
  /**
   * @returns string
   */
  public function getInformacnyList(Trace $trace, $kodPredmetu, $akRok=null);
}
