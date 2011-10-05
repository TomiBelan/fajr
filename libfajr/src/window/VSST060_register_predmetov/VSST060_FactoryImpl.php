<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Libfajr
 * @subpackage Pub__Window__VSST060_register_predmetov
 * @author     Tomi Belan <tomi.belan@gmail.com>
 * @filesource
 */
namespace libfajr\window\VSST060_register_predmetov;

use libfajr\window\VSST060_register_predmetov as VSST060;
use libfajr\base\Trace;
use libfajr\connection\SimpleConnection;
use libfajr\window\RequestBuilderImpl;
use libfajr\window\ScreenRequestExecutorImpl;
use libfajr\data_manipulation\AIS2TableParser;
use libfajr\connection\AIS2ServerConnection;

class VSST060_FactoryImpl implements VSST060_Factory
{
  private $connection;

  public function __construct(AIS2ServerConnection $serverConnection)
  {
    $this->connection = $serverConnection;
  }

  public function newRegisterPredmetovScreen(Trace $trace)
  {
    $requestBuilder = new RequestBuilderImpl($this->connection->getUrlMap());
    $executor = new ScreenRequestExecutorImpl($requestBuilder,
        $this->connection->getSimpleConnection());
    $parser = new AIS2TableParser();
    return new VSST060\RegisterPredmetovScreenImpl($trace, $executor, $parser);
  }
}
