<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Libfajr
 * @subpackage Pub__Window__VSST060_register_predmetov
 * @author     Tomi Belan <tomi.belan@gmail.com>
 * @filesource
 */
namespace libfajr\pub\window\VSST060_register_predmetov;

use libfajr\pub\window\LazyDialog;
use libfajr\pub\base\Trace;

interface RegisterPredmetovScreen extends LazyDialog
{
  /**
   * @returns string
   */
  public function getInformacnyList(Trace $trace, $kodPredmetu);
}
