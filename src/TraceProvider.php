<?php
// Copyright (c) 2011 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Creates a Trace using the Fajr configuration.
 *
 * @package    Fajr
 * @author     Tomi Belan <tomi.belan@gmail.com>
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr;

use fajr\config\FajrConfig;
use fajr\config\FajrConfigOptions;
use fajr\config\FajrConfigLoader;
use fajr\util\PHPFile;
use fajr\FileTrace;
use fajr\ArrayTrace;
use libfajr\pub\base\NullTrace;
use libfajr\base\SystemTimer;

class TraceProvider
{
  /** @var BackendFactory $instance */
  private static $instance;

  public static function getInstance()
  {
    if (!isset(self::$instance)) {
      $config = FajrConfigLoader::getConfiguration();
      if($config->get(FajrConfigOptions::DEBUG_TRACE) === true) {
        $debugFile = $config->getDirectory(FajrConfigOptions::DEBUG_TRACE_FILE);
        if ($debugFile !== null) {
          $phpfile = new PHPFile($debugFile, 'a');
          self::$instance = new FileTrace(SystemTimer::getInstance(), $phpfile, 0, '--Trace--');
        }
        else {
          self::$instance = new ArrayTrace(SystemTimer::getInstance(), '--Trace--');
        }
      }
      else {
        self::$instance = new NullTrace();
      }
    }
    return self::$instance;
  }
}

