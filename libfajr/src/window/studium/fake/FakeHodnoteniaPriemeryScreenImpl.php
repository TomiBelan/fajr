<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Reprezentuje obrazovku s hodnoteniami a priemermi.
 *
 * @package    Libfajr
 * @subpackage Window__Studium__Fake
 * @author     Peter Perešini <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace libfajr\window\studium\fake;


use libfajr\base\Preconditions;
use libfajr\data\DataTableImpl;
use libfajr\trace\Trace;
use libfajr\window\studium\HodnoteniaPriemeryScreen;
use libfajr\window\fake\FakeAbstractScreen;
use libfajr\window\fake\FakeRequestExecutor;
use libfajr\regression\HodnoteniaRegression;
use libfajr\regression\PriemeryRegression;

/**
 * Trieda reprezentujúca jednu obrazovku s hodnoteniami a priemermi za jeden rok.
 *
 * @package    Libfajr
 * @subpackage Window__Studium
 * @author     Peter Perešini <ppershing+fajr@gmail.com>
 */
class FakeHodnoteniaPriemeryScreenImpl extends FakeAbstractScreen
    implements HodnoteniaPriemeryScreen
{
  private $idZapisnyList;

  public function __construct(Trace $trace, FakeRequestExecutor $executor, $idZapisnyList)
  {
    parent::__construct($trace, $executor);
    $this->idZapisnyList = $idZapisnyList;
  }

  public function getHodnotenia(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    $data = $this->executor->readTable(
        array('list' => $this->idZapisnyList),
        'hodnotenia');
    $table = new DataTableImpl(HodnoteniaRegression::get(), $data);
    return $table;
  }

  public function getPriemery(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    $data = $this->executor->readTable(
        array('list' => $this->idZapisnyList),
        'priemery');
    $table = new DataTableImpl(PriemeryRegression::get(), $data);
    return $table;
  }

}

?>
