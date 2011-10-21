<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Libfajr
 * @subpackage Window__Studium
 * @author     Martin Králik <majak47@gmail.com>
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace libfajr\window\studium;

use libfajr\window\studium as VSES017;
use libfajr\trace\Trace;
use libfajr\connection\SimpleConnection;
use libfajr\window\RequestBuilderImpl;
use libfajr\window\ScreenRequestExecutorImpl;
use libfajr\data\AIS2TableParser;
use libfajr\connection\AIS2ServerConnection;

class VSES017_FactoryImpl implements VSES017_Factory
{
  private $connection;

  public function __construct(AIS2ServerConnection $serverConnection)
  {
    $this->connection = $serverConnection;
  }

  public function newAdministraciaStudiaScreen(Trace $trace)
  {
    $requestBuilder = new RequestBuilderImpl($this->connection->getUrlMap());
    $executor = new ScreenRequestExecutorImpl($requestBuilder,
        $this->connection->getSimpleConnection());
    $parser = new AIS2TableParser();
    return new VSES017\AdministraciaStudiaScreenImpl($trace, $executor, $parser);
  }

  public function newTerminyHodnoteniaScreen(Trace $trace, $idZapisnyList, $idStudium)
  {
    $requestBuilder = new RequestBuilderImpl($this->connection->getUrlMap());
    $executor = new ScreenRequestExecutorImpl($requestBuilder,
        $this->connection->getSimpleConnection());
    $parser = new AIS2TableParser();
    return new VSES017\TerminyHodnoteniaScreenImpl(
        $trace, $executor, $parser, $idZapisnyList, $idStudium);
  }

  public function newHodnoteniaPriemeryScreen(Trace $trace, $idZapisnyList)
  {
    $requestBuilder = new RequestBuilderImpl($this->connection->getUrlMap());
    $executor = new ScreenRequestExecutorImpl($requestBuilder,
        $this->connection->getSimpleConnection());
    $parser = new AIS2TableParser();
    return new VSES017\HodnoteniaPriemeryScreenImpl(
        $trace, $executor, $parser, $idZapisnyList);
  }
}
