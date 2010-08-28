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
use fajr\libfajr\login\CosignPasswordLogin;

/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class CosignPasswordLoginTest extends PHPUnit_Framework_TestCase
{
  private $responseAlreadyLogged;
  private $responseNotLogged;
  private $responseWrongPassword1;
  private $responseWrongPassword2;
  private $responseWrongPassword3;
  private $responseLoginOk;

  public function setUp() {
    $this->responseAlreadyLogged = file_get_contents(__DIR__.'/testdata/cosignAlreadyLogged.dat');
    $this->responseNotLogged = file_get_contents(__DIR__.'/testdata/cosignNotLogged.dat');
    $this->responseLoginOk = $this->responseAlreadyLogged;
    $this->responseWrongPassword1 = file_get_contents(__DIR__.'/testdata/cosignWrongPassword.dat');
    $this->responseWrongPassword2 = file_get_contents(__DIR__.'/testdata/cosignWrongPassword2.dat');
    $this->responseWrongPassword3 = file_get_contents(__DIR__.'/testdata/cosignWrongPassword3.dat');
  }

  private function newConnection() {
    return $this->getMock('\fajr\libfajr\connection\HttpConnection');
  }

  public function testLoginOk() {
    $connection = $this->newConnection();
    $connection->expects($this->once())
               ->method('get')
               ->will($this->returnValue($this->responseNotLogged));
    $connection->expects($this->once())
               ->method('post')
               ->will($this->returnValue($this->responseLoginOk));
    $login = new CosignPasswordLogin('user', 'passwd');
    $login->login($connection);
  }

  public function testLoginWrong1() {
    $connection = $this->newConnection();
    $connection->expects($this->once())
               ->method('get')
               ->will($this->returnValue($this->responseNotLogged));
    $connection->expects($this->once())
               ->method('post')
               ->will($this->returnValue($this->responseWrongPassword1));
    $login = new CosignPasswordLogin('user', 'passwd');
    try {
      $login->login($connection);
      $this->fail("login should have failed");
    } catch (LoginException $e) {
      $msg = $e->getMessage();
      $this->assertRegExp("@Password or Account Name incorrect@", $msg);
    }
  }

  public function testLoginWrong2() {
    $connection = $this->newConnection();
    $connection->expects($this->once())
               ->method('get')
               ->will($this->returnValue($this->responseNotLogged));
    $connection->expects($this->once())
               ->method('post')
               ->will($this->returnValue($this->responseWrongPassword2));
    $login = new CosignPasswordLogin('user', 'passwd');
    try {
      $login->login($connection);
      $this->fail("login should have failed");
    } catch (LoginException $e) {
      $msg = $e->getMessage();
      $this->assertRegExp("@nesprávne meno alebo heslo@", $msg);
    }
  }

  public function testLoginWrong3() {
    $connection = $this->newConnection();
    $connection->expects($this->once())
               ->method('get')
               ->will($this->returnValue($this->responseNotLogged));
    $connection->expects($this->once())
               ->method('post')
               ->will($this->returnValue($this->responseWrongPassword3));
    $login = new CosignPasswordLogin('user', 'passwd');
    try {
      $login->login($connection);
      $this->fail("login should have failed");
    } catch (LoginException $e) {
      $msg = $e->getMessage();
      $this->assertRegExp("@Chyba - zadané nesprávne meno alebo heslo@", $msg);
    }
  }

}

?>
