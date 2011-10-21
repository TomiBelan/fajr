<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for AIS version parser.
 *
 * @package    Libfajr
 * @subpackage Data
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace libfajr\data;

use PHPUnit_Framework_TestCase;
use libfajr\data\AIS2Version;
use libfajr\data\AIS2VersionParser;

/**
 * @ignore
 */
class AIS2VersionParserTest extends PHPUnit_Framework_TestCase
{
  private $parser;

  public function setUp()
  {
    $this->parser = new AIS2VersionParser();
  }

  public function testParsing()
  {
    $html = file_get_contents(__DIR__ . '/testdata/version.dat');
    $this->assertEquals(
        new AIS2Version(2, 3, 15, 30),
        $this->parser->parseVersionStringFromMainPage($html));
  }

}
