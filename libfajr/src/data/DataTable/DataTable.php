<?php
// Copyright (c) 2013 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

namespace libfajr\data;

use DOMElement;
use DOMDocument;
use libfajr\trace\Trace;
use libfajr\util\StrUtil;
use libfajr\base\Preconditions;
use libfajr\data\ComponentInterface;
use libfajr\exceptions\ParseException;


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
   * Samotné dáta poparsované pri konštrukcii objektu.
   * @var array(array(string=>string))
   */
  private $data = null;

  /**
   * Active row
   * @var integer
   */
  private $activeRow = null;

  /**
   * Selected rows
   * @var array(integer)
   */
  private $selectedRows = null;

  /**
   * Old selected rows
   * @var array(integer)
   */
  private $oldSelectedRows = null;

  /**
   * Create a Table and set its dataViewName and definition
   *
   * @param string $dataViewName name of Table which we want to store here
   */
  public function __construct($dataViewName)
  {
    Preconditions::checkIsString($dataViewName);
    $this->dataViewName = $dataViewName;
    $this->selectedRows = array();
  }

  /**
   * Update Table from aisResponse
   *
   * @param Trace $trace for creating logs, tracking activity
   * @param DOMDocument $aisResponse AIS2 html parsed reply
   */
  public function updateComponentFromResponse(Trace $trace, DOMDocument $aisResponse)
  {
    Preconditions::checkNotNull($aisResponse);
    $element = $aisResponse->getElementById($dataViewName);
    if ($element === null) {
      throw new ParseException("Problem parsing ais2 response: Element '$dataViewName' not found");
    }

    $dom = new DOMDocument();
    $dom->appendChild($dom->importNode($element, true));
    // ok, now we have restricted document

    //informacia ci sa jedna o update, append...
    $element2 = $dom->getElementById("dataTabBodies");
    if ($element2 === null) {
      throw new ParseException("Problem parsing ais2 response: Element dataTabBodies not found");
    }


    //ak sa jedna len o scroll tak tam definicia tabulky nie je
    $dataSendType = $element2->getAttribute("dataSendType");
    if($dataSendType == "update"){
      $this->definition = $this->getDefinitionFromDom($trace->addChild("Getting table definition from DOM."), $dom);
    }
    $trace->tlog("Attribute dataSendType found with value: ".$dataSendType);

    $tdata = $this->getTableDataFromDom($trace->addChild("Getting table data from DOM."), $dom);

    // use column name as array key instead of column index
    assert(is_array($tdata));
    $this->data = array();
    foreach ($tdata as $rowKey=>$tableRow) {
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
   * @param integer $index rowId of row, which we want to get
   * @returns array(string=>string) data in row $data[$index].
   */
  public function getRow($index)
  {
    Preconditions::checkIsNumber($index);
    if ($index < 0 || $index >= count($this->data)) return null;
    return $this->data[$index];
  }

  /**
   * Returns changes on this table (selected rows and which is active)
   *
   * @return DOMDocument XML object
   */
  public function getStateChanges()
  {
    $this->selectedRows = array_unique($this->selectedRows);

    $xml_spec = new DOMDocument();

    if ($this->oldSelectedRows != $this->selectedRows) {
      // it possible that dataViewName will change in future
      $splittedName = preg_split("/_/",$this->dataViewName);

      //creating nodes for final xml
      $changedProperties = $xml_spec->createElement("changedProperties");
      $objName =$xml_spec->createElement('objName', $splittedName[count($splittedName)-2]);
      $propertyValues = $xml_spec->createElement('propertyValues');
      $nameValue = $xml_spec->createElement('nameValue');
      $name = $xml_spec->createElement('name', 'dataView');
      $isXML = $xml_spec->createElement('isXml', 'true');
      $value = $xml_spec->createElement('value');

      //Creating content for CDATA section
      $xml_string = '<root><selection>';
      $xml_string .= '<activeIndex>'.$this->activeRow.'</activeIndex>';
      foreach($this->selectedRows as $index){
          $xml_string .= '<selectedIndexes>'.$index.'</selectedIndexes>';
      }
      $xml_string .= '</selection></root>';

      $CDATA = $xml_spec->createCDATASection($xml_string);
      $nameValue2 = $xml_spec->createElement('nameValue');
      $name2 = $xml_spec->createElement('name', 'editMode');
      $isXML2 = $xml_spec->createElement('isXml', 'false');
      $value2 = $xml_spec->createElement('value', 'false');
      $embObjChProps = $xml_spec->createElement("embObjChProps");
      $atr = $xml_spec->createAttribute("isNull");
      $atr->value = 'true';

      //constructing final xml from Nodes
      $value->appendChild($CDATA);
      $nameValue2->appendChild($name2);
      $nameValue2->appendChild($isXML2);
      $nameValue2->appendChild($value2);
      $changedProperties->appendChild($objName);
      $nameValue->appendChild($name);
      $nameValue->appendChild($isXML);
      $nameValue->appendChild($value);
      $propertyValues->appendChild($nameValue);
      $propertyValues->appendChild($nameValue2);
      $changedProperties->appendChild($propertyValues);
      $embObjChProps->appendChild($atr);
      $changedProperties->appendChild($embObjChProps);
      $xml_spec->appendChild($changedProperties);

      $this->captureSelectionState();
    }

    return $xml_spec;
  }

  /**
   * Add one row to selection
   *
   * @param integer $index rowId of row, which we want to select
   */
  public function selectRow($index)
  {
    Preconditions::checkIsNumber($index);
    $this->selectedRows[] = $index;
  }

  /**
   * Set row as active
   *
   * @param integer $index rowId of row, which we want to set as active
   */
  public function setActiveRow($index)
  {
    Preconditions::checkIsNumber($index);
    $this->activeRow = $index;
  }

 /**
   * Select one record of table
   *
   * @param integer $index rowId of row, which we want to select
   */
  public function selectSingleRow($index)
  {
    Preconditions::checkIsNumber($index);
    $this->clearSelection();
    $this->selectRow($index);
  }

  /**
   * Unselect every selected row.
   *
   */
  public function clearSelection()
  {
    $this->selectedRows = array();
  }

  /**
   * This is for detection of changes...
   * it helps in getStateChanges() function
   * there it compare old selection with new
   * if were some changes.
   *
   */
  private function captureSelectionState()
  {
    $this->oldSelectedRows = $this->selectedRows;
  }

  /**
   * Get table definitions from DOMDocument
   *
   * @param Trace $trace for creating logs, tracking activity
   * @param $dom DOMDocument from ais2ResponseHtml
   * @returns array(string) Definition of table
   */
  private function getDefinitionFromDom(Trace $trace, DOMDocument $dom)
  {
    Preconditions::checkNotNull($dom);
    $trace->tlog("Finding element with id dataTabColGroup");
    $element = $dom->getElementById('dataTabColGroup');
    if ($element == null) {
      throw new ParseException("Can't find table headers");
    }
    $trace->tlog("Element found");
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
   * @param Trace $trace for creating logs, tracking activity
   * @param $dom DOMDocument part of ais2ResponseHTML which contain Table
   * @returns array(string=>array(string)) Returns rows of Table, where index is rowId
   */
  private function getTableDataFromDom(Trace $trace, DOMDocument $dom)
  {
    Preconditions::checkNotNull($dom);
    $tdata = array();
    $trace->tlog("Finding element with id dataTabBody0");
    $element = $dom->getElementById('dataTabBody0');
    if ($element == null) {
      throw new ParseException("Can't find table data");
    }
    $trace->tlog("Element found");

    foreach ($element->childNodes as $aisRow) {
      assert($aisRow->tagName == "tr");
      assert($aisRow->hasAttribute("id"));
      assert($aisRow->hasChildNodes());
      // TODO: asserty prerobit na exceptiony
      $row = array();
      $trace->tlog("Extracting row id.");
      $rowId = $aisRow->getAttribute("id");
      $index = StrUtil::match('@^row_([0-9]+)$@', $rowId);
      if ($index === false) {
        throw new ParseException("Unexpected row id format");
      }
      $trace->tlog("Extraction is correct.");
      $index = intval($index);

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
      $tdata[$index] = $row;
    }
    return $tdata;
  }
}
