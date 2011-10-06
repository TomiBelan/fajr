<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Libfajr
 * @subpackage Window__VSES017_administracia_studia
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace libfajr\window\VSES017_administracia_studia;

use PHPUnit_Framework_TestCase;

use libfajr\trace\NullTrace;
use libfajr\connection\SimpleConnection;
/**
 * @ignore
 */
class AdministraciaStudiaScreenTest extends PHPUnit_Framework_TestCase
{
  public function testIdFromZapisnyListIndexParsing()
  {
    $mockExecutor = $this->getMock('libfajr\window\ScreenRequestExecutor');
    $mockParser = $this->getMock('libfajr\data_manipulation\AIS2TableParser');

    $response = file_get_contents(__DIR__.'/testdata/idFromZapisnyList.dat');
    $screen = new AdministraciaStudiaScreenImpl(new NullTrace(), $mockExecutor, $mockParser);
    $data = $screen->parseIdFromZapisnyListIndexFromResponse($response);
    $expected = array("idZapisnyList" => 138174, "idStudium" => "53043");
    $this->assertEquals($expected, $data);
  }

}
