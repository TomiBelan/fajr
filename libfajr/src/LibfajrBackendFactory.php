<?php
// Copyright (c) 2011 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Libfajr
 * @author     Tomi Belan <tomi.belan@gmail.com>
 * @filesource
 */
namespace libfajr;

use libfajr\BackendFactory;
use libfajr\window\VSES017_administracia_studia\VSES017_FactoryImpl;
use libfajr\window\VSST060_register_predmetov\VSST060_FactoryImpl;
use libfajr\window\AIS2MainScreenImpl;
use libfajr\connection\AIS2ServerConnection;


class LibfajrBackendFactory implements BackendFactory
{
  private $connection;
  private $time;

  public function __construct(AIS2ServerConnection $serverConnection)
  {
    $this->connection = $serverConnection;
    $this->time = time();   // TODO: the old code uses time() so we kept it, but $_SERVER['REQUEST_TIME'] might be better
  }

  public function getServerTime()
  {
    return $this->time;
  }

  public function newVSES017Factory()
  {
    return new VSES017_FactoryImpl($this->connection);
  }

  public function newVSST060Factory()
  {
    return new VSST060_FactoryImpl($this->connection);
  }

  public function newAIS2MainScreen()
  {
    return new AIS2MainScreenImpl($this->connection);
  }
}
