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
use libfajr\trace\FileTrace;
use libfajr\trace\BinaryFileTrace;
use libfajr\trace\ArrayTrace;
use libfajr\trace\NullTrace;
use libfajr\base\SystemTimer;
use fajr\util\FajrUtils;

class TraceProvider
{
  /** @var BackendFactory $instance */
  private static $instance;

  public static function getInstance()
  {
    if (!isset(self::$instance)) {
      $config = FajrConfigLoader::getConfiguration();
      $type = $config->get(FajrConfigOptions::DEBUG_TRACE);
      $uniqueID = sha1(uniqid('trace', true));
      $header = 'Trace (id: '.$uniqueID.')';
      if($type === FajrConfigOptions::DEBUG_TRACE_NONE) {
        self::$instance = new NullTrace();
      }
      else if ($type === FajrConfigOptions::DEBUG_TRACE_ARRAY) {
        self::$instance = new ArrayTrace(SystemTimer::getInstance(), $header);
      }
      else {
        // File-based trace
        $traceDir = $config->getDirectory(FajrConfigOptions::DEBUG_TRACE_DIR);
        if ($traceDir === null) {
          throw new \LogicException(FajrConfigOptions::DEBUG_TRACE_DIR .
              ' is not set, but is required for file-based traces');
        }
        $ext = $type == FajrConfigOptions::DEBUG_TRACE_TEXT ? 'txt' : 'bin';
        $filename = FajrUtils::joinPath($traceDir, 'trace'.$uniqueID.'.'.$ext);
        $file = @fopen($filename, 'ab');
        if ($file === false) {
          throw new \Exception('Cannot open trace file');
        }
        if ($type == FajrConfigOptions::DEBUG_TRACE_TEXT) {
          self::$instance = new FileTrace(SystemTimer::getInstance(), $file, 0, $header);
        }
        self::$instance = new BinaryFileTrace(SystemTimer::getInstance(), $file, $header);
      }
    }
    return self::$instance;
  }
}

