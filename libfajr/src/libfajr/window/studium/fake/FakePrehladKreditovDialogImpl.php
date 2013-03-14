<?php
// Copyright (c) 2011 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * TODO
 *
 * PHP version 5.3.0
 *
 * @package    Libfajr
 * @subpackage Window__Studium__Fake
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 * @filesource
 */
namespace libfajr\window\studium\fake;

use libfajr\window\studium\PrehladKreditovDialog;

use libfajr\trace\Trace;
use libfajr\window\fake\FakeAbstractDialog;
use libfajr\data\DataTableImpl;
use libfajr\regression\PrehladKreditovRegression;

/**
 * Trieda pre dialóg s prehľadom kreditov
 *
 * @package    Libfajr
 * @subpackage Window__Studium__Fake
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 */
class FakePrehladKreditovDialogImpl extends FakeAbstractDialog
    implements PrehladKreditovDialog
{
  
  public function getPredmety(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    $data = $this->executor->readTable(
          array(),
          'prehladKreditov');
    return new DataTableImpl(PrehladKreditovRegression::get(), $data);
  }
}
