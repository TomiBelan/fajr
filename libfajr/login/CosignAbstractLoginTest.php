<?php
/**
 * This file contains tests for Validator class
 *
 * @package Fajr
 * @subpackage Tests
 * @author Peter Peresini <ppershing+fajr@gmail.com>
 */
namespace fajr\libfajr\login;
use PHPUnit_Framework_TestCase;
use fajr\libfajr\pub\exceptions\LoginException;
use fajr\libfajr\login\CosignAbstractLogin;
use fajr\libfajr\pub\connection\HttpConnection;
/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class CosignAbstractLoginTest extends PHPUnit_Framework_TestCase
{
  private $responseAlreadyLogged;
  private $responseNotLogged;
  private $connection;

  public function setUp() {
    $this->responseAlreadyLogged = file_get_contents(__DIR__.'/testdata/cosignAlreadyLogged.dat');
    $this->responseNotLogged = file_get_contents(__DIR__.'/testdata/cosignNotLogged.dat');
    $this->connection = $this->getMock('\fajr\libfajr\pub\connection\HttpConnection');
    $this->login = $this->getMockForAbstractClass('\fajr\libfajr\login\CosignAbstractLogin');
  }

  public function testLoggedIn() {
    $this->connection->expects($this->once())
                     ->method('get')
                     ->will($this->returnValue($this->responseAlreadyLogged));
    $this->assertTrue($this->login->isLoggedIn($this->connection));
  }

  public function testNotLogged() {
    $this->connection->expects($this->once())
                     ->method('get')
                     ->will($this->returnValue($this->responseNotLogged));
    $this->assertFalse($this->login->isLoggedIn($this->connection));
  }


}

?>
