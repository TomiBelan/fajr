<?php
/**
 * This file contains tests for AIS2LoginImpl class
 *
 * @package Fajr
 * @subpackage Tests
 * @author Peter Peresini <ppershing+fajr@gmail.com>
 */
namespace fajr\libfajr\login;
use PHPUnit_Framework_TestCase;
use fajr\libfajr\pub\exceptions\AIS2LoginException;
use fajr\libfajr\login\CosignLogin;
use fajr\libfajr\pub\connection\HttpCoonection;

/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class AIS2LoginImplTest extends PHPUnit_Framework_TestCase
{
  private $responseLoggedIn;
  private $responseNotLogged;
  private $responseLogout;

  public function setUp() {
    $this->responseLoggedIn = file_get_contents(__DIR__.'/testdata/aisLoggedIn.dat');
    $this->responseNotLogged = file_get_contents(__DIR__.'/testdata/aisNotLogged.dat');
    $this->responseLogout = file_get_contents(__DIR__.'/testdata/aisLogout.dat');
  }

  private function newConnection() {
    return $this->getMock('\fajr\libfajr\pub\connection\HttpConnection');
  }

  public function testIsLoggedAlreadyLogged() {
    $connection = $this->newConnection();
    $connection->expects($this->once())
               ->method('get')
               ->will($this->returnValue($this->responseLoggedIn));
    $login = new AIS2LoginImpl();
    $this->assertTrue($login->isLoggedIn($connection));
  }

  public function testIsLoggedNotLogged() {
    $connection = $this->newConnection();
    $connection->expects($this->once())
               ->method('get')
               ->will($this->returnValue($this->responseNotLogged));
    $login = new AIS2LoginImpl();
    $this->assertFalse($login->isLoggedIn($connection));
  }

  public function testIsLoggedFailure() {
    $connection = $this->newConnection();
    $connection->expects($this->once())
               ->method('get')
               ->will($this->returnValue("problem"));
    $login = new AIS2LoginImpl();
    $this->setExpectedException('\Exception');
    $login->isLoggedIn($connection);
  }

  public function testLoginOk() {
    $connection = $this->newConnection();
    $connection->expects($this->once())
               ->method('get')
               ->will($this->returnValue($this->responseLoggedIn));
    $login = new AIS2LoginImpl();
    $login->login($connection);
  }

  public function testLoginFailure() {
    $connection = $this->newConnection();
    $connection->expects($this->once())
               ->method('get')
               ->will($this->returnValue($this->responseNotLogged));
    $login = new AIS2LoginImpl();
    $this->setExpectedException('\Exception');
    $login->login($connection);
  }

}

?>
