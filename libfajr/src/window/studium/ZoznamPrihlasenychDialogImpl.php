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

use libfajr\window\studium\ZoznamPrihlasenychDialog;

use libfajr\trace\Trace;
use libfajr\data\DataTable;
use libfajr\data\ActionButton;
use libfajr\window\AIS2AbstractDialog;
use libfajr\data\AIS2TableParser;
use libfajr\window\DialogParent;
use libfajr\window\DialogData;

/**
 * Trieda pre dialóg so zoznamom prihlásených študentov na termín.
 *
 * @package    Libfajr
 * @subpackage Window__Studium
 * @author     Martin Králik <majak47@gmail.com>
 */
class ZoznamPrihlasenychDialogImpl extends AIS2AbstractDialog
    implements ZoznamPrihlasenychDialog
{
  /**
   * @var AIS2TableParser
   */
  private $parser;

  public function __construct(Trace $trace, DialogParent $parent,
      DialogData $data, AIS2TableParser $parser = null)
  {
    $components['dataComponents']['prihlaseniTable_dataView'] = new DataTable("prihlaseniTable_dataView");
    $components['actionComponents'] = null;
    parent::__construct($trace, $parent, $data, $components);
    $this->parser = ($parser !== null) ? $parser :  new AIS2TableParser;
  }

  public function getZoznamPrihlasenych(Trace $trace)
  {
    $this->openWindow();
    return $this->components['prihlaseniTable_dataView'];
  }
}
