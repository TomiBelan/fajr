<?php
// Copyright (c) 2010, 2011 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Implementation of timer dependent on system clock.
 *
 * PHP version 5.3.0
 *
 * @package    Fajr
 * @subpackage Libfajr__Base
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace libfajr\base;

use libfajr\base\MutableTimer;

/**
 * Timer measuring passed time by system clock information.
 */
class SystemTimer implements MutableTimer
{
  /** @var double default initial time used in getInstance */
  private static $initialTime;

  /* TODO document */
  public static function setInitialTime($initialTime)
  {
    self::$initialTime = $initialTime;
  }

  /** @var SystemTimer $instance */
  private static $instance;

  /* TODO document */
  public static function getInstance()
  {
    if (!isset(self::$instance)) {
      self::$instance = new SystemTimer(self::$initialTime);
    }
    return self::$instance;
  }

  /**
   * @var double time of the last reset() event
   */
  private $startTime;

  /**
   * Construct a SystemTimer
   * @param float $time optional time to start with (as returned by microtime)
   */
  public function __construct($time = null)
  {
    if ($time == null) {
      $this->reset();
    }
    else {
      $this->startTime = $time;
    }
  }

  public function reset()
  {
    $this->startTime = microtime(true);
  }

  public function getElapsedTime()
  {
    return microtime(true) - $this->startTime;
  }
}
