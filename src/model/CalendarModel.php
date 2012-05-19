<?php
/**
 * Calendar model
 *
 * @copyright  Copyright (c) 2012 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 * @filesource
 */
namespace fajr\model;

class CalendarModel {
  
  private $startOfWeekDay = 1; // monday
  private $startTime;
  private $endTime;
  private $events;
  
  public function __construct()
  {
    $now = time();
    $this->startTime = $this->getStartOfMonth($now);
    $this->endTime = $this->getEndOfMonth($this->offsetDays($now, 31));
    $this->events = array();
  }
  
  public function getStartTime()
  {
    return $this->startTime;
  }

  public function getEndTime()
  {
    return $this->endTime;
  }
  
  public function addEvent($timestamp, $data)
  {
    $day = $this->getStartOfDay($timestamp);
    if (!isset($this->events[$day])) {
      $this->events[$day] = array();
    }
    $this->events[$day][] = $data;
  }
  
  private function getStartOfDay($timestamp)
  {
    $info = getdate($timestamp);
    return mktime(0, 0, 0, $info['mon'], $info['mday'], $info['year']);
  }
  
  private function getStartOfWeek($timestamp)
  {
    $info = getdate($timestamp);
    return mktime(0, 0, 0, $info['mon'], $info['mday']-
        (($info['wday'] + 7 - $this->startOfWeekDay) % 7), $info['year']);
  }
  
  private function getStartOfMonth($timestamp)
  {
    $info = getdate($timestamp);
    return mktime(0, 0, 0, $info['mon'], 1, $info['year']);
  }
  
  private function getEndOfMonth($timestamp)
  {
    $info = getdate($timestamp);
    return mktime(0, 0, 0, $info['mon'] + 1, 0, $info['year']);
  }
  
  public function offsetDays($timestamp, $days)
  {
    return $timestamp + $days * 86400;
  }
  
  /**
   * Calculate how many months there is a difference between two dates.
   * @return 0 if same months, 1 if a is the month before b, etc.
   */
  public function monthDiff($a, $b)
  {
    $negative = false;
    if ($a > $b) {
      $tmp = $a;
      $a = $b;
      $b = $tmp;
      $negative = true;
    }
    $aInfo = getdate($a);
    $bInfo = getdate($b);
    $yearDiff = $bInfo['year'] - $aInfo['year'];
    if ($yearDiff == 0) {
      $monthDiff =  $bInfo['mon'] - $aInfo['mon'];
    }
    else {
      $monthDiff = (12 - $aInfo['mon']) + ($yearDiff - 1) * 12 + $bInfo['mon'];
    }
    if ($negative) {
      $monthDiff = -$monthDiff;
    }
    return $monthDiff;
  }
  
  public function getWeeks()
  {
    $start = $this->getStartOfWeek($this->startTime);
    $end = $this->getStartOfWeek($this->endTime);
    $weeks = array();
    $prevMonth = -2;
    for ($week = $start; $week <= $end; $week = $this->offsetDays($week, 7)) {
      $days = array();
      for ($dayIndex = 0; $dayIndex < 5; $dayIndex++) {
        $day = $this->offsetDays($week, $dayIndex);
        $monthIndex = $this->monthDiff($this->startTime, $day);
        $firstDisplayedDayOfMonth = false;
        if ($monthIndex > $prevMonth) {
          $firstDisplayedDayOfMonth = true;
          $prevMonth = $monthIndex;
        }
        $dayInfo = array('timestamp' => $day,
          'month' => $monthIndex,
          'firstDisplayedDayOfMonth' => $firstDisplayedDayOfMonth);
        if (isset($this->events[$day])) {
          $dayInfo['events'] = $this->events[$day];
        }
        else {
          $dayInfo['events'] = array();
        }
        $days[] = $dayInfo;
      }
      $weeks[] = array('timestamp'=>$week, 'days' => $days);
    }
    return $weeks;
  }
  
}