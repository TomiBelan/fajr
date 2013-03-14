<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.
/**
 * Used to locate directory of fake data;
 *
 * @package    Libfajr
 * @subpackage Regression__Fixtures
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace libfajr\regression\fixtures;

/**
 * It is here just to know the location of fake data.
 *
 * @package    Libfajr
 * @subpackage Regression
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
class FakeData
{
  /**
   * @returns string this directory
   */
  public static function getDirectory()
  {
    return __DIR__;
  }
}
