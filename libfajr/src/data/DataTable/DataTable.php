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
   * Create a Table and set its dataViewName and definition
   *
   * @param string $dataViewName name of Table which we want to store here
   * @param Window $parent window where this component is used
   * @param array(string) $definition name of columns which table must have
   *                                  if not defined, no control on that will
   *                                  be done during updating the table and 
   *                                  definition will load from aisHTMLCode.
   */
  public function __construct($dataViewName, $definition = null)
  {
    $this->dataViewName = $dataViewName;
    if($definition != null){
      $this->controlDefinition = $definition;
    }else{
      $this->controlDefinition = array();
    }
    $this->selectedRows = array();
  }

  /**
   * Update Table from aisResponse
   *
   * @param DOMDocument $aisResponse AIS2 html parsed reply
   */
  public function updateComponentFromResponse(DOMDocument $aisResponse)
  {
    $element = $aisResponse->getElementById($dataViewName);
    $dom = new DOMDocument();
    $dom->appendChild($dom->importNode($element, true));
    // ok, now we have restricted document

    //informacia ci sa jedna o update, append...
    $element2 = $dom->getElementById("dataTabBodies");
    if ($element === null || $element2 === null) {
      throw new ParseException("Problem parsing ais2 response: Element not found");
    }
    

    //ak sa jedna len o scroll tak tam definicia tabulky nie je
    if($element2->getAttribute("dataSendType") == "update"){
      $this->definition = $this->getDefinitionFromDom($dom);
      $control = array_diff($this->controlDefinition,$this->definition);
      if($control != null){
        throw new ParseException("Table definition isn`t a subset of controlDefinition");
      }
    }

    $TData = $this->getTableDataFromDom($dom);
    
    // modifying data to better format
    assert(is_array($TData));
    $this->data = array();
    foreach ($TData as $rowKey=>$tableRow) {
      $myRow = array();
      $myRow['index'] = $rowKey;
      assert(count($this->definition) == count($tableRow));

      foreach($tableRow as $key=>$value) {
        assert(is_numeric($key));
        assert(isset($this->definition[$key]));
        $myRow[$this->definition[$key]] = $value;
      }

      $this->data[$rowKey] = $myRow;

    }
  
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
   * @param string $index rowId of row, which we want to get
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
   * @param string $index rowId of row, which we want to select
   */
  public function selectRow($index)
  {
    $this->selectedRows = $index;
  }

 /**
   * Select one record of table
   *
   * @param string $index rowId of row, which we want to select
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

  /**
   * Get table definitions from DOMDocument
   *
   * @param $dom DOMDocument from ais2ResponseHtml
   * @returns array(string) Definition of table
   */
  private function getDefinitionFromDom(DOMDocument $dom)
  {
    $element = $dom->getElementById('dataTabColGroup');
    if ($element == null) {
      throw new ParseException("Can't find table headers");
    }
    $list = $element->getElementsByTagName('col');
    $columns = array();
    foreach ($list as $node) {
      assert($node->hasAttribute('shortname'));
      $columns[] = $node->getAttribute('shortname');
    }
    return $columns;
  }

  /**
   * Extract Table data from DOMDocument
   *
   * @param $dom DOMDocument part of ais2ResponseHTML which contain Table
   * @returns array(string=>array(string)) Returns rows of Table, where index is rowId
   */
  private function getTableDataFromDom(DOMDocument $dom)
  {
    $Tdata = array();
    $element = $dom->getElementById('dataTabBody0');
    if ($element == null) {
      throw new ParseException("Can't find table data");
    }

    foreach ($element->childNodes as $aisRow) {
      assert($aisRow->tagName == "tr");
      assert($aisRow->hasAttribute("id"));
      assert($aisRow->hasChildNodes());
      // TODO: asserty prerobit na exceptiony
      $row = array();
      $rowId = $aisRow->getAttribute("id");
      $index = StrUtil::match('@^row_([0-9]+)$@', $rowId);
      if ($index === false) {
        throw new ParseException("Unexpected row id format");
      }
      foreach ($aisRow->childNodes as $ais_td) {
        assert($ais_td->tagName == "td");
        // get a text content of <td>
        $value = $ais_td->textContent;
        Preconditions::checkIsString($value);
        // special fix for &nbsp;
        $nbsp = chr(0xC2).chr(0xA0);
        $value = str_replace($nbsp, ' ', $value);

        if ($value == ' ') {
          $row[] = '';
        }
        assert($value != ''); // probably there is some inner element which we don't know about
        $row[] = $value;
      }
      $Tdata[$index] = $row;
    }
    return $Tdata;
  }
}
