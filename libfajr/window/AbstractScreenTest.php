<?php
/**
 *
 * @package libfajr
 * @subpackage Tests
 * @author Peter Peresini <ppershing+fajr@gmail.com>
 */

/**
 * @ignore
 */
require_once 'test_include.php';

use fajr\libfajr\window\AbstractScreen;
use fajr\libfajr\NullTrace;
/**
 * @ignore
 */
class AbstractScreenTest extends PHPUnit_Framework_TestCase
{
  public function testAppIdParsing()
  {
    $connection = $this->getMock('fajr\libfajr\connection\SimpleConnection', array());
    $response = file_get_contents(__DIR__.'/testdata/appid.dat');
    $screen = new AIS2AdministraciaStudiaScreen(new NullTrace(), $connection);
    $appId = $screen->parseAppIdFromResponse($response);
    $this->assertEquals($appId, 21767494);
  }

  public function testFormNameParsing()
  {
    $connection = $this->getMock('fajr\libfajr\connection\SimpleConnection', array());
    $response = file_get_contents(__DIR__.'/testdata/formName.dat');
    $screen = new AIS2AdministraciaStudiaScreen(new NullTrace(), $connection);
    $formName = $screen->parseFormNameFromResponse($response);
    $this->assertEquals($formName, "VSES017_StudentZapisneListyDlg0");
  }

}


