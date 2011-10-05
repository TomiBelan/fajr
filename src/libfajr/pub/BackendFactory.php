<?php
// Copyright (c) 2011 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * The main backend factory. All backend operations go through this.
 * The active backend is specified by which backend factory the program uses.
 *
 * At the moment, the backend factory mostly creates other factories.
 * TODO(tomi): Refactor that. Also, reorganize the directory structure maybe.
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub
 * @author     Tomi Belan <tomi.belan@gmail.com>
 * @filesource
 */

namespace libfajr\pub;

use libfajr\pub\base\Trace;
use libfajr\pub\window\VSES017_administracia_studia\VSES017_Factory;
use libfajr\pub\window\VSST060_register_predmetov\VSST060_Factory;
use libfajr\pub\window\AIS2MainScreen;

interface BackendFactory
{
  /**
   * @returns int
   */
  public function getServerTime();

  /**
   * @returns VSES017_Factory
   */
  public function newVSES017Factory();

  /**
   * @returns VSST060_Factory
   */
  public function newVSST060Factory();

  /**
   * @returns AIS2MainScreen
   */
  public function newAIS2MainScreen();
}
