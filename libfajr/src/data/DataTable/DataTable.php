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
   * Definícia stĺpcov tabuľky
   * @var array(string)
   */
  private $definition = null;

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
   * Create a Table and set a definitions
   *
   * @param string $dataViewNama name of Table which we want to store here
   */
  public function __construct($dataViewName)
  {
    $this->definition = $data['definitions'];
  }

  /**
   * Initialize Table from aisResponseHtml
   *
   * @param DOMDocument $aisResponseHtml AIS2 html parsed reply
   */
  public function initComponentFromResponse($aisResponseHtml)
  {
   
  }

  /**
   * Update Table from aisResponseHtml
   *
   * @param DOMDocument $aisResponseHtml AIS2 html parsed reply
   */
  public function updateComponentFromResponse($aisResponseHtml)
  {
  
  }

  /**
   * Return data in Table
   *
   * @returns array(array(string=>string)) all rows of Table
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
   * @returns array(string) data in row $data[$num].
   */
  public function getRow($index)
  {
    return $this->data[$index];
  }

  /**
   * Returns ids of rows which were selected
   *
   * @return array(string)
   */
  public function getStateChanges()
  {
    $result = array();

    foreach($this->change as $index){
      $result[] = $data[$index]['id'];
    }
    
    return $result;
  }

 /**
   * Select one record of table
   *
   * @param integer $index number of row, which we want to select
   */
  public function selectSingleRow($index)
  {
    $this->selectedRows = array();
    $this->selectedRows = $index;
  }

  /**
   * Unselect actually selected row
   *
   */
  public function clearSelection()
  {
    $this->change = array();
  }
}
