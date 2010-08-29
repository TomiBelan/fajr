<?php
/**
 * This file contains tests for Validator class
 *
 * @package Fajr
 * @subpackage Tests
 * @author Peter Peresini <ppershing+fajr@gmail.com>
 */

/**
 * @ignore
 */
require_once 'test_include.php';
require_once 'Validator.php';

/**
 * @ignore
 */
class ValidatorTest extends PHPUnit_Framework_TestCase
{
  public function testIntegers()
  {
    $this->assertTrue(Validator::isInteger("0",null));
    $this->assertTrue(Validator::isInteger("47",null));
    $this->assertFalse(Validator::isInteger("abcd",null));
    $this->assertFalse(Validator::isInteger("12abcd",null));
    $this->assertFalse(Validator::isInteger("47.47",null));
  }

  public function testStrings() {
    $this->markTestIncomplete('treba dopisat');
  }
}

?>
