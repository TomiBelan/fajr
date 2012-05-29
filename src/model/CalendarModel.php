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
  
  const MODE_WORKWEEK = 'workweek';
  const MODE_WEEK = 'week';
  
  private $startOfWeekDay = 1; // monday
  private $startTime;
  private $endTime;
  private $events;
  private $mode;
  
  public function __construct($now = null, $mode = self::MODE_WEEK)
  {
    if ($now == null) {
      $now = time();
    }
    $this->startTime = $this->getStartOfMonth($now);
    $this->endTime = $this->getEndOfMonth($this->offsetMonths($this->startTime, 1));
    $this->mode = $mode;
    $this->events = array();
  }
  
  public function getMode()
  {
    return $this->mode;
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
  
  private function offsetMonths($timestamp, $months)
  {
    $info = getdate($timestamp);
    return mktime(0, 0, 0, $info['mon'] + $months, $info['mday'], $info['year']);
  }
  
  public function getWeekDayCount()
  {
    if ($this->mode == self::MODE_WORKWEEK) {
      return 5;
    }
    return 7;
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
    if ($this->offsetDays($start, $this->getWeekDayCount()) <= $this->startTime) {
      // zaciatok intervalu, ktory spada do prveho tyzdna nie je zobrazeny
      $start = $this->offsetDays($start, 7);
    }
    $end = $this->getStartOfWeek($this->endTime);
    $weeks = array();
    $prevMonth = -2;
    $today = $this->getStartOfDay(time());
    for ($week = $start; $week <= $end; $week = $this->offsetDays($week, 7)) {
      $days = array();
      for ($dayIndex = 0; $dayIndex < $this->getWeekDayCount(); $dayIndex++) {
        $day = $this->offsetDays($week, $dayIndex);
        $monthIndex = $this->monthDiff($this->startTime, $day);
        $firstDisplayedDayOfMonth = false;
        if ($monthIndex > $prevMonth) {
          $firstDisplayedDayOfMonth = true;
          $prevMonth = $monthIndex;
        }
        $dayInfo = array('timestamp' => $day,
          'month' => $monthIndex,
          'firstDisplayedDayOfMonth' => $firstDisplayedDayOfMonth,
          'today' => ($day == $today),
          'past' => ($day < $today),
          'future' => ($day > $today),
        );
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