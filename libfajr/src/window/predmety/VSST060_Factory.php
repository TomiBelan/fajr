<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains factory for all screens from management of study.
 *
 * @package    Libfajr
 * @subpackage Window__Predmety
 * @author     Tomi Belan <tomi.belan@gmail.com>
 * @filesource
 */
namespace libfajr\window\predmety;

use libfajr\window\predmety as VSST060;
use libfajr\trace\Trace;

/**
 * Provides instances of screens in study management part of ais.
 */
interface VSST060_Factory
{

  /**
   * @returns AdministraciaStudiaScreen
   */
  public function newRegisterPredmetovScreen(Trace $trace);
}
