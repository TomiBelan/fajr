<?php
/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Window__Administracia-studia
 * @author     Peter Peresini <ppershing+fajr@gmail.com>
 */

/**
 * @ignore
 */
require_once 'test_include.php';

use fajr\libfajr\NullTrace;
use fajr\libfajr\connection\SimpleConnection;
/**
 * @ignore
 */
class AdministraciaStudiaScreenTest extends PHPUnit_Framework_TestCase
{
  public function testIdFromZapisnyListIndexParsing()
  {
    $mockConnection = $this->getMock('fajr\libfajr\connection\SimpleConnection', array('request'));

    $response = file_get_contents(__DIR__.'/testdata/idFromZapisnyList.dat');
    $screen = new AIS2AdministraciaStudiaScreen(new NullTrace(), $mockConnection);
    $data = $screen->parseIdFromZapisnyListIndexFromResponse($response);
    $expected = array("idZapisnyList" => 138174, "idStudium" => "53043");
    $this->assertEquals($expected, $data);
  }

}


