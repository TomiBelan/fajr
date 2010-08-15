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

use fajr\libfajr\connection\HttpToSimpleConnectionAdapter;
/**
 * @ignore
 */
class HttpToSimpleConnectionAdapterTest extends PHPUnit_Framework_TestCase
{
  private function newConnection() {
    return $this->getMock('fajr\libfajr\connection\HttpConnection',
                          array('get', 'post', 'addCookie', 'clearCookies'));
  }

  public function testRequest()
  {
    $mockConnection = $this->newConnection();
    $adapter =
        new HttpToSimpleConnectionAdapter($mockConnection, '/tmp');

    $mockConnection->expects($this->once())
                   ->method('get')
                   ->will($this->returnValue('get'));

    $mockConnection->expects($this->once())
                   ->method('post')
                   ->will($this->returnValue('post'));

    $response = $adapter->request(new NullTrace, 'fmph.uniba.sk');
    $this->assertEquals('get', $reponse);

    $response = $adapter->request(new NullTrace, 'fmph.uniba.sk', array());
    $this->assertEquals('post', $reponse);
  }
}


