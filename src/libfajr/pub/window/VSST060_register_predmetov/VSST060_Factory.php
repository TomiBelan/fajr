<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains factory for all screens from management of study.
 *
 * @package    Libfajr
 * @subpackage Libfajr__Pub__Window__VSST060_register_predmetov
 * @author     Tomi Belan <tomi.belan@gmail.com>
 * @filesource
 */
namespace libfajr\pub\window\VSST060_register_predmetov;

use libfajr\window\VSST060_register_predmetov as VSST060;
use libfajr\pub\base\Trace;

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
