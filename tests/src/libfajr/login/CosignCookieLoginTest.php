<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for CosignCookieLogin class
 *
 * @package    Fajr
 * @subpackage Libfajr__Login
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace libfajr\login;

use libfajr\login\CosignCookieLogin;
use libfajr\connection\AIS2ServerConnection;
use libfajr\connection\AIS2ServerUrlMap;
use libfajr\connection\HttpConnection;
use libfajr\exceptions\LoginException;
use libfajr\login\CosignServiceCookie;
use PHPUnit_Framework_TestCase;

/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class CosignCookieLoginTest extends PHPUnit_Framework_TestCase
{
  private $responseAlreadyLogged;
  private $responseNotLogged;

  private $serverConection;
  private $connection;

  public function setUp()
  {
    $this->responseAlreadyLogged = file_get_contents(__DIR__.'/testdata/cosignAlreadyLogged.dat');
    $this->responseNotLogged = file_get_contents(__DIR__.'/testdata/cosignNotLogged.dat');
    $this->connection = $this->getMock('\libfajr\connection\HttpConnection');
    $this->serverConnection = new AIS2ServerConnection($this->connection,
                                                       new AIS2ServerUrlMap("ais2.test"));
  }

  public function testLogin()
  {
    $this->connection->expects($this->once())
                     ->method('addCookie')
                     ->with($this->equalTo('cosign-test'))
                     ->will($this->returnValue(null));
    $login = new CosignCookieLogin(new CosignServiceCookie('cosign-test', 'empty', 'test'));
    $login->login($this->serverConnection);
  }
}

?>
