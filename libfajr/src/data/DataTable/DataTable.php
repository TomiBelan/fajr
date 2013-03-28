<?php
// Copyright (c) 2013 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

namespace libfajr\data;

use libfajr\data\AIS2TableParser;
use libfajr\data\ComponentInterface;
use libfajr\trace\Trace;

/**
 * Trieda zastrešujúca tabuľku dát.
 *
 * @package    Libfajr
 */
class DataTable implements ComponentInterface
{
  /**
   * Name of the table in aisHTMLCode
   * @var string
   */
  private $dataViewName = null;

  /**
   * Definícia stĺpcov tabuľky
   * @var array(string)
   */
  private $definition = null;

  /**
   * Definition which the table must have
   * @var array(string)
   */
  private $controlDefinition = null;

  /**
   * Samotné dáta poparsované pri konštrukcii objektu.
   * @var array(array(string=>string))
   */
  private $data = null;

  /**
   * Selected rows
   * @var array(integer)
   */
  private $selectedRows = null;

  /**
   * Create a Table and set its idName and definition
   *
   * @param string $dataViewName name of Table which we want to store here
   * @param array(string) $definition name of columns which table must have
   *                                  if not defined, no control on that will
   *                                  be done during updating the table and 
   *                                  definition will load from aisHTMLCode.
   */
  public function __construct($dataViewName, $definition = null)
  {
    $this->dataViewName = $dataViewName;
    $this->controlDefinition = $definition;
  }

  /**
   * Update Table from aisResponse
   *
   * @param DOMDocument $aisResponse AIS2 html parsed reply
   */
  public function updateComponentFromResponse(DOMDocument $aisResponse)
  {
  
  }

  /**
   * Return data in the Table
   *
   * @returns array(array(string=>string)) all rows of the Table
   */
  public function getData()
  {
    return $this->data;
  }

  /**
   * Return Table definitions.
   *
   * @returns array(string) columns names.
   */
  public function getTableDefinition()
  {
    return $this->definition;
  }

  /**
   * Return one record from table
   *
   * @param integer $index number of row, which we want to get
   * @returns array(string=>string) data in row $data[$index].
   */
  public function getRow($index)
  {
    return $this->data[$index];
  }

  /**
   * Returns changes on this table (selected rows)
   *
   * @return DOMDocument
   */
  public function getStateChanges()
  {
    $this->selectedRows = array_unique($this->selectedRows);   
  }

  /**
   * Add one row to selection
   *
   * @param integer $index number of row, which we want to select
   */
  public function selectRow($index)
  {
    $this->selectedRows = $index;
  }

 /**
   * Select one record of table
   *
   * @param integer $index number of row, which we want to select
   */
  public function selectSingleRow($index)
  {
    $this->clearSelection();
    $this->selectRow($index);
  }

  /**
   * Unselect actually selected row
   *
   */
  public function clearSelection()
  {
    $this->selectedRows = array();
  }
}
