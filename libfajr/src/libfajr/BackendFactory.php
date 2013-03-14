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
 * @package    Libfajr
 * @author     Tomi Belan <tomi.belan@gmail.com>
 * @filesource
 */

namespace libfajr;

use libfajr\trace\Trace;
use libfajr\window\studium\StudiumFactory;
use libfajr\window\predmety\PredmetyFactory;
use libfajr\window\AIS2MainScreen;

interface BackendFactory
{
  /**
   * @returns int
   */
  public function getServerTime();

  /**
   * @returns StudiumFactory
   */
  public function newVSES017Factory();

  /**
   * @returns PredmetyFactory
   */
  public function newVSST060Factory();

  /**
   * @returns AIS2MainScreen
   */
  public function newAIS2MainScreen();
}
