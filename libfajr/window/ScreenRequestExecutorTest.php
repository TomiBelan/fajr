<?php
/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Window
 * @author     Peter Peresini <ppershing+fajr@gmail.com>
 */
namespace fajr\libfajr\window;

/**
 * @ignore
 */
require_once 'test_include.php';

use \PHPUnit_Framework_TestCase;
use fajr\libfajr\window\RequestBuilder;
use fajr\libfajr\window\ScreenRequestExecutor;
use fajr\libfajr\pub\base\NullTrace;
/**
 * @ignore
 */
class AIS2AbstractScreenTest extends PHPUnit_Framework_TestCase
{
  public function getExecutor()
  {
    $builder = $this->getMock('\fajr\libfajr\window\RequestBuilder',
        array('buildRequestData', 'getRequestUrl', 'newSerial',
              'getAppInitializationUrl'));
    return new ScreenRequestExecutor($builder);
  }

  public function testAppIdParsing()
  {
    $executor = $this->getExecutor();
    $response = file_get_contents(__DIR__.'/testdata/appid.dat');
    $appId = $executor->parseAppIdFromResponse($response);
    $this->assertEquals(21767494, $appId);
  }

  public function testFormNameParsing()
  {
    $executor = $this->getExecutor();
    $response = file_get_contents(__DIR__.'/testdata/formName.dat');
    $formName = $executor->parseFormNameFromResponse($response);
    $this->assertEquals("VSES017_StudentZapisneListyDlg0", $formName);
  }

}


