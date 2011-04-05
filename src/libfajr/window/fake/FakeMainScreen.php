<?php
// Copyright (c) 2010,2011 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Represents "start" screen of AIS.
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__Fake
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr\libfajr\window\fake;

use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\pub\window\AIS2MainScreen;
use fajr\libfajr\pub\window\AIS2ApplicationEnum;
use fajr\libfajr\data_manipulation\AIS2Version;

/**
 * Represents main page of AIS.
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub__Window
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class FakeMainScreen implements AIS2MainScreen
{
  /**
   * @returns AIS2Version
   */
  public function getAisVersion(Trace $trace)
  {
    $trace->tlog('retrieving ais version');
    return new AIS2Version(2, 3, 22, 52);
  }

  /**
   * Get the names of all available ais applications
   * from ais menu.
   *
   * @returns array(string) names of applications
   */
  public function getAllAvailableApplications(Trace $trace)
  {
    $trace->tlog('getting available applications');
    return array(
          AIS2ApplicationEnum::ADMINISTRACIA_STUDIA,
        );
  }

  /**
   * Get the full name of the user.
   * Note: resulting string is not sanitized and shouldn't be
   * used by file/other access.
   *
   * @returns string
   */
  public function getFullUserName(Trace $trace)
  {
    $trace->tlog('getting ais username');
    return "Ing. Janko Hraško";
  }
}
