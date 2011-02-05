<?php
// Copyright (c) 2011 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains *basic* tests for SecureRandom
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
namespace fajr\security;

use PHPUnit_Framework_TestCase;
use fajr\lib\statistics\PearsonChiSquare;

/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class SecureRandomTest extends PHPUnit_Framework_TestCase
{


  public function testOpensslLength() {
    if (!SecureRandom::_isOpensslAvailable()) {
      $this->markTestSkipped('OpenSSL not available');
    }

    $this->assertEquals(1, strlen(SecureRandom::_randomBytesOpenssl(1)));
    $this->assertEquals(2, strlen(SecureRandom::_randomBytesOpenssl(2)));
    $this->assertEquals(10, strlen(SecureRandom::_randomBytesOpenssl(10)));
    $this->assertEquals(20000, strlen(SecureRandom::_randomBytesOpenssl(20000)));
  }

  public function testLinuxRandomDevLength() {
    if (!SecureRandom::_isLinuxRandomDevAvailable()) {
      $this->markTestSkipped('Linux random dev not available');
    }

    $this->assertEquals(1, strlen(SecureRandom::_randomBytesLinuxRandomDev(1)));
    $this->assertEquals(2, strlen(SecureRandom::_randomBytesLinuxRandomDev(2)));
    $this->assertEquals(10, strlen(SecureRandom::_randomBytesLinuxRandomDev(10)));
    $this->assertEquals(20000, strlen(SecureRandom::_randomBytesLinuxRandomDev(20000)));
  }

  public function testWindowsCryptoapiLength() {
    if (!SecureRandom::_isWindowsCryptoapiAvailable()) {
      $this->markTestSkipped('Linux random dev not available');
    }

    $this->assertEquals(1, strlen(SecureRandom::_randomBytesWindowsCryptoapi(1)));
    $this->assertEquals(2, strlen(SecureRandom::_randomBytesWindowsCryptoapi(2)));
    $this->assertEquals(10, strlen(SecureRandom::_randomBytesWindowsCryptoapi(10)));
    $this->assertEquals(20000, strlen(SecureRandom::_randomBytesWindowsCryptoapi(20000)));
  }

  public function testLength() {

    $this->assertEquals(1, strlen(SecureRandom::random(1)));
    $this->assertEquals(2, strlen(SecureRandom::random(2)));
    $this->assertEquals(10, strlen(SecureRandom::random(10)));
    $this->assertEquals(20000, strlen(SecureRandom::random(20000)));
  }


  /**
   * Very limited basic checking on randomness. Checks only that
   * the resulting distribution of binary value of each byte is
   * approximately same.
   *
   * Warning: This is only basic test. Do not test quality of
   * random generators with it. It is supposed to be just test that
   * generator is working and does not have clear problems!
   */
  private function _checkRandomness($bytes) {
    $hist = array(); // histogram
    $freq = array(); // expected frequency
    $N = 256;
    $SENSITIVITY = 0.01;
    for ($i = 0; $i < $N; $i++) {
      $hist[$i] = 0;
      $freq[$i] = 1.0 / $N;
    }

    foreach (unpack('C*', $bytes) as $char) {
      $hist[$char]++;
    }

    $chisqr = PearsonChiSquare::chiSquare($hist, $freq);
    $this->assertGreaterThan($SENSITIVITY, PearsonChiSquare::pvalue($N - 1, $chisqr),
        'Statistical test on randomness failed. This is probably not an error, try to re-run the test.');
  }

  public function testBasicOpensslRandomness() {
    if (!SecureRandom::_isOpensslAvailable()) {
      $this->markTestSkipped('OpenSSL not available');
    }

    $this->_checkRandomness(SecureRandom::_randomBytesOpenssl(20000));
    $this->_checkRandomness(SecureRandom::_randomBytesOpenssl(50000));
  }

  public function testBasicLinuxRandomDevRandomness() {
    if (!SecureRandom::_isLinuxRandomDevAvailable()) {
      $this->markTestSkipped('Linux random dev not available');
    }

    $this->_checkRandomness(SecureRandom::_randomBytesLinuxRandomDev(20000));
    $this->_checkRandomness(SecureRandom::_randomBytesLinuxRandomDev(50000));
  }

  public function testBasicWindowsCryptoapiRandomness() {
    if (!SecureRandom::_isWindowsCryptoapiAvailable()) {
      $this->markTestSkipped('Windows cryptoapi not available');
    }

    $this->_checkRandomness(SecureRandom::_randomBytesWindowsCryptoapi(20000));
    $this->_checkRandomness(SecureRandom::_randomBytesWindowsCryptoapi(50000));
  }

  public function testBasicRandomness() {
    $this->_checkRandomness(SecureRandom::random(20000));
    $this->_checkRandomness(SecureRandom::random(50000));
  }

  public function testNoProvider() {
    SecureRandom::__clearUse();
    $this->setExpectedException('\Exception');
    SecureRandom::random(10);
  }

  public function testBadParameter() {
    $this->setExpectedException('\Exception');
    SecureRandom::random();
  }

  public function testWrongCount() {
    $this->setExpectedException('\Exception');
    SecureRandom::random(0);
  }
}
