<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for CosignAbstractLogin class
 *
 * @package    Libfajr
 * @subpackage Login
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace libfajr\login;

use libfajr\login\CosignAbstractLogin;
use libfajr\connection\HttpConnection;
use libfajr\connection\AIS2ServerConnection;
use libfajr\connection\AIS2ServerUrlMap;
use libfajr\exceptions\LoginException;
use PHPUnit_Framework_TestCase;

/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class CosignAbstractLoginTest extends PHPUnit_Framework_TestCase
{
  public function setUp()
  {
    $this->connection = $this->getMock('\libfajr\connection\HttpConnection');
    $this->serverConnection = new AIS2ServerConnection($this->connection,
                                                       new AIS2ServerUrlMap("ais2.test"));
  }

  public function testLogout()
  {
    $data = file_get_contents(__DIR__.'/testdata/cosignNotLogged.dat');
    $this->connection->expects($this->once())
                     ->method('post')
                     ->with($this->anything(), $this->stringContains('logout'))
                     ->will($this->returnValue($data));

    $login = $this->getMockForAbstractClass('\libfajr\login\CosignAbstractLogin');
    $login->logout($this->serverConnection);
  }

  public function testFailedLogout()
  {
    $this->setExpectedException('\Exception');
    $this->connection->expects($this->once())
                     ->method('post')
                     ->with($this->anything(), $this->stringContains('logout'))
                     ->will($this->returnValue('not valid logout'));

    $login = $this->getMockForAbstractClass('\libfajr\login\CosignAbstractLogin');
    $login->logout($this->serverConnection);
  }
}

?>
