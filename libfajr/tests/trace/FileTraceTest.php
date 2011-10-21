<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for FileTrace class
 *
 * @package    Libfajr
 * @subpackage Trace
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr;

use PHPUnit_Framework_TestCase;
use libfajr\base\SystemTimer;
use libfajr\util\TraceUtil;
use libfajr\trace\FileTrace;

/**
 * @ignore
 */
class FileTraceTest extends PHPUnit_Framework_TestCase
{

  /** @var FileTrace */
  private $trace;

  /** @var resource file descriptor */
  private $file;

  public function setUp()
  {
    $this->file = fopen('php://memory', 'r+');
    $this->trace = new FileTrace(new SystemTimer(), $this->file,
                                 0, '--Header--');
  }
  
  public function tearDown()
  {
    fclose($this->file);
    $this->file = null;
    $this->trace = null;
  }
  
  private function getFileAsString() {
    $seek = fseek($this->file, 0);
    if ($seek == -1) {
      throw new Exception('Failed to seek memory file');
    }
    $str = '';
    // we test for false=ERROR and ''=EOF simultaneously
    while (($read = fread($this->file, 4096))) {
      $str .= $read;
    }
    if ($read === false) {
      throw new Exception('Failed to read from memory file');
    }
    return $str;
  }

  public function testTlog()
  {
    $this->trace->tlog('<<MESSAGE>>');
    $this->assertRegExp('/<<MESSAGE>>/', $this->getFileAsString());
  }

  public function testTlogData()
  {
    $this->trace->tlogData('<<MESSAGE>>');
    $this->assertRegExp('/<<MESSAGE>>/', $this->getFileAsString());
  }

  public function testTlogVariable()
  {
    $this->trace->tlogVariable('<<VARIABLE>>', '<<VALUE>>');
    $this->assertRegExp('/<<VARIABLE>>/', $this->getFileAsString());
    $this->assertRegExp('/<<VALUE>>/', $this->getFileAsString());
  }

  public function testAddChild()
  {
    $child = $this->trace->addChild('<<HEADER>>');
    $this->assertNotNull($child);
    $this->assertRegExp('/<<HEADER>>/', $this->getFileAsString());
  }

}
