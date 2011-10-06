<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Reprezentuje diálóg so zoznamom študentov prihlásených na termín.
 *
 * @package    Libfajr
 * @subpackage Window__VSES017_administracia_studia__Fake
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace libfajr\window\VSES017_administracia_studia\fake;


use libfajr\data_manipulation\DataTableImpl;
use libfajr\trace\Trace;
use libfajr\window\VSES017_administracia_studia\ZoznamPrihlasenychDialog;
use libfajr\window\fake\FakeAbstractDialog;
use libfajr\regression\PrihlaseniNaTerminRegression;

/**
 * Trieda pre dialóg so zoznamom prihlásených študentov na termín.
 *
 * @package    Libfajr
 * @subpackage Window__VSES017_administracia_studia__Fake
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class FakeZoznamPrihlasenychDialogImpl extends FakeAbstractDialog
    implements ZoznamPrihlasenychDialog
{

  public function getZoznamPrihlasenych(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    $data = $this->executor->readTable(array(), 'prihlaseni');
    return new DataTableImpl(PrihlaseniNaTerminRegression::get(), $data);
  }
}
?>
