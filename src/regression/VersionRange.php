<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.
/**
 * Contains information about compatible version of AIS.
 *
 * @package    Fajr
 * @subpackage Regression
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr\regression;

use fajr\libfajr\data_manipulation\AIS2Version;

/**
 * Contains information about compatible version of AIS.
 *
 * @package    Fajr
 * @subpackage Regression
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
class VersionRange
{
  /**
   * Returns minimum AIS version which was tested and
   * is compatible with current version of fajr.
   *
   * @returns AIS2Version
   */
  public static function getMinVersion()
  {
    return new AIS2Version(2, 3, 21, 20);
  }

  /**
   * Returns maximum AIS version which was tested and
   * is compatible with current version of fajr.
   *
   * @returns AIS2Version
   */
  public static function getMaxVersion()
  {
    return new AIS2Version(2, 3, 21, 23);
  }

}
