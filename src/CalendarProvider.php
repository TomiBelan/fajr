<?php
// Copyright (c) 2011 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * A provider wrapping iCalcreator library.
 *
 * @package    Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */

namespace fajr;

use fajr\config\FajrConfig;
use fajr\config\FajrConfigOptions;
use fajr\config\FajrConfigLoader;
use fajr\util\FajrUtils;

class CalendarProvider
{
  
  public static function getInstance()
  {
    require_once FajrUtils::joinPath(__DIR__, '..', 'third_party', 'icalcreator', 'iCalcreator.class.php');
    return new \vcalendar();
  }
}

