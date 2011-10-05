<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Reprezentuje obrazovku s hodnoteniami a priemermi.
 *
 * @package    Libfajr
 * @subpackage Libfajr__Window__VSES017_administracia_studia__Fake
 * @author     Peter Perešini <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace libfajr\window\VSES017_administracia_studia\fake;


use libfajr\base\Preconditions;
use libfajr\data_manipulation\DataTableImpl;
use libfajr\pub\base\Trace;
use libfajr\pub\window\VSES017_administracia_studia\HodnoteniaPriemeryScreen;
use libfajr\window\fake\FakeAbstractScreen;
use libfajr\window\fake\FakeRequestExecutor;
use libfajr\pub\regression\HodnoteniaRegression;
use libfajr\pub\regression\PriemeryRegression;

/**
 * Trieda reprezentujúca jednu obrazovku s hodnoteniami a priemermi za jeden rok.
 *
 * @package    Libfajr
 * @subpackage Libfajr__Window__VSES017_administracia_studia
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
