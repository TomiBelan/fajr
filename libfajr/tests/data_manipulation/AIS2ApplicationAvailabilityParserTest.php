<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for ais2 Table class.
 *
 * @package    Libfajr
 * @subpackage Data
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace libfajr\data;

use \PHPUnit_Framework_TestCase;
use libfajr\data\DataTable;
use libfajr\data\AIS2TableParser;
use libfajr\trace\NullTrace;

/**
 * @ignore
 */
class AIS2ApplicationAvailabilityParserTest extends PHPUnit_Framework_TestCase
{
  private $html;

  public function setUp()
  {
    $this->parser = new AIS2ApplicationAvailabilityParser();
  }

  public function testSingleApplicationParsing()
  {
    $html = file_get_contents(__DIR__.'/testdata/zoznamAplikaciiSimple.dat');
    $applications = $this->parser->findAllApplications($html);
    $this->assertEquals(array('LZ014'), $applications);
  }

  public function testMultipleApplicationParsing()
  {
    $html = file_get_contents(__DIR__.'/testdata/zoznamAplikaciiOriginal.dat');
    $applications = $this->parser->findAllApplications($html);
    $this->assertEquals(array('VSST010', 'VSST178', 'VSST060', 'VSST157'),
                        $applications);
  }
}


