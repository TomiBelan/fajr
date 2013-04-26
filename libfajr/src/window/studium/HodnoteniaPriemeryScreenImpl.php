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
use libfajr\data\DataTable;
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
      AIS2TableParser $parser, $paramName)
  {
    $data = new ScreenData();
    $data->appClassName = 'ais.gui.vs.es.VSES212App';
    $data->additionalParams = array('kodAplikacie' => 'VSES212',
        'paramName' => $paramName);
    $components['dataComponents']['hodnoteniaTable_dataView'] = new DataTable("hodnoteniaTable_dataView");
    $components['dataComponents']['priemeryTable_dataView'] = new DataTable("priemeryTable_dataView");
    $components['actionComponents'] = null;
    parent::__construct($trace, $executor, $data, $components);
    $this->openIfNotAlready($trace);
    $this->parser = $parser;
  }

  // TODO(ppershing): Maybe cache data between getHodnotenia && getPriemery

  public function getHodnotenia(Trace $trace)
  {
    return $this->components['hodnoteniaTable_dataView'];
  }

  public function getPriemery(Trace $trace)
  {
    return $this->components['priemeryTable_dataView'];
  }
}
?>
