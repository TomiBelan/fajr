<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * TODO
 *
 * PHP version 5.3.0
 *
 * @package    Libfajr
 * @subpackage Window__Studium
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace libfajr\window\studium;

use libfajr\window\studium\HodnoteniaPriemeryScreen;
use libfajr\window\AIS2AbstractScreen;
use libfajr\window\ScreenData;
use libfajr\window\ScreenRequestExecutor;
use libfajr\window\RequestBuilderImpl;
use libfajr\trace\Trace;
use libfajr\connection\SimpleConnection;
use libfajr\data\AIS2TableParser;

/**
 * Trieda reprezentujúca jednu obrazovku s hodnoteniami a priemermi za jeden rok.
 *
 * @package    Libfajr
 * @subpackage Window__Studium
 * @author     Martin Králik <majak47@gmail.com>
 */
class HodnoteniaPriemeryScreenImpl extends AIS2AbstractScreen
    implements HodnoteniaPriemeryScreen
{
  /**
   * @var AIS2TableParser
   */
  private $parser;

  public function __construct(Trace $trace, ScreenRequestExecutor $executor,
      AIS2TableParser $parser, $idZapisnyList)
  {
    $data = new ScreenData();
    $data->appClassName = 'ais.gui.vs.es.VSES212App';
    $data->additionalParams = array('kodAplikacie' => 'VSES212',
        'idZapisnyList' => $idZapisnyList);
    parent::__construct($trace, $executor, $data);
    $this->parser = $parser;
  }

  // TODO(ppershing): Maybe cache data between getHodnotenia && getPriemery

  public function getHodnotenia(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    $data = $this->executor->requestContent($trace);
    return $this->parser->createTableFromHtml($trace->addChild("Parsing table"),
                $data, 'hodnoteniaTable_dataView');
  }

  public function getPriemery(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    $data = $this->executor->requestContent($trace);
    return $this->parser->createTableFromHtml($trace->addChild("Parsing table"),
                $data, 'priemeryTable_dataView');
  }

}

?>
