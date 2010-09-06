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
  public function testLogout() {
    $this->markTestIncomplete();
  }
}

?>
