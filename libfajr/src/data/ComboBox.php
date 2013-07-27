<?php
// Copyright (c) 2013 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

namespace libfajr\data;

use DOMElement;
use DOMDocument;
use Exception;
use libfajr\trace\Trace;
use libfajr\util\StrUtil;
use libfajr\base\Preconditions;
use libfajr\data\ComponentInterface;
use libfajr\exceptions\ParseException;


/**
 * Class representing comboBoxes.
 *
 * @package    Libfajr
 */
class ComboBox implements ComponentInterface
{
  /**
   * Name of the comboBox in aisHTMLCode
   * @var string
   */
  private $comboBoxName = null;

  /**
   * Options which comboBox provide
   * @var array(integer=>string) where integer is index of option
   */
  private $options = null;

  /**
   * Selected option
   * @var integer
   */
  private $selectedOption = null;

  /**
   * Old selected option
   * @var integer
   */
  private $oldSelectedOption = null;

  /**
   * Indicates if component was loaded
   */
  private $initialized = false;

  /**
   * Create a comboBox and set its comboBoxName
   *
   * @param string $comboBoxName name of comboBox which we want to store here
   */
  public function __construct($comboBoxName)
  {
    Preconditions::checkIsString($comboBoxName);
    $this->comboBoxName = $comboBoxName;
    $this->selectedOptions = array();
  }

  /**
   * Update comboBox from aisResponse
   *
   * @param Trace $trace for creating logs, tracking activity
   * @param DOMDocument $aisResponse AIS2 html parsed reply
   * @param boolean $init if it is first opening of window
   */
  public function updateComponentFromResponse(Trace $trace, DOMDocument $aisResponse, $init = null)
  {
    Preconditions::checkNotNull($aisResponse);
    $element = $aisResponse->getElementById($this->comboBoxName);
    if ($element === null) {
      if ($init) {
        throw new ParseException("Problem parsing ais2 response: Element '$comboBoxName' not found");
      } else {
        return;
      }
    }

    $dom = new DOMDocument();
    $dom->appendChild($dom->importNode($element, true));
    // ok, now we have restricted document

    $this->options = $this->getOptionsFromDom($trace->addChild("Getting comboBox options from DOM."), $dom);

    $this->initialized = true;

    //default option is 0
    if($init) $this->selectOption(0);
  }

  /**
   * Return options of the comboBox
   *
   * @returns array(integer=>string)
   */
  public function getOptions()
  {
    if(!$this->initialized) throw new Exception("ComboBox(".$this->comboBoxName.") wasn`t initialized yet!");
    return $this->options;
  }

  /**
   * Return option from comboBox
   *
   * @param integer $index id of option, which we want to get
   * @returns string value of option $options[$index].
   */
  public function getOption($index)
  {
    if(!$this->initialized) throw new Exception("ComboBox(".$this->comboBoxName.") wasn`t initialized yet!");
    Preconditions::checkIsNumber($index);
    if ($index < 0 || $index >= count($this->options)) throw new Exception($this->comboBoxName.": option is out of range!");
    return $this->options[$index];
  }

  /**
   * Returns changes on this comboBox (selected option)
   *
   * Sample of xml:
   *
   * <changedProperties>
   *   <objName>semesterComboBox</objName>
   *   <propertyValues>
   *     <nameValue>
   *       <name>dataView</name>
   *       <isXml>true</isXml>
   *       <value>
   *         <![CDATA[
   *           <root>
   *             <selection>
   *               <selectedIndexes>0</selectedIndexes>
   *             </selection>
   *           </root>
   *         ]]>
   *       </value>
   *     </nameValue>
   *   </propertyValues>
   *   <embObjChProps isNull='true'/>
   * </changedProperties>
   *
   * @return DOMDocument XML object
   */
  public function getStateChanges()
  {

    $xml_spec = new DOMDocument();

    if ($this->oldSelectedOption != $this->selectedOption) {

      //creating nodes for final xml
      $changedProperties = $xml_spec->createElement("changedProperties");
      $objName =$xml_spec->createElement('objName', $this->comboBoxName);
      $propertyValues = $xml_spec->createElement('propertyValues');
      $nameValue = $xml_spec->createElement('nameValue');
      $name = $xml_spec->createElement('name', 'dataView');
      $isXML = $xml_spec->createElement('isXml', 'true');
      $value = $xml_spec->createElement('value');

      //Creating content for CDATA section
      $root = $xml_spec->createElement('root');
      $selection = $xml_spec->createElement('selection');
      $selectedIndexes = $xml_spec->createElement('selectedIndexes', $this->selectedOptions);
      $selection->appendChild($selectedIndexes);
      $root->appendChild($selection);

      $CDATA = $xml_spec->createCDATASection($xml_spec->saveXML($root));
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
      $changedProperties->appendChild($embObjChProps);
      $xml_spec->appendChild($changedProperties);

      $this->captureSelectionState();
    }

    return $xml_spec;
  }

  /**
   * Select option from comboBox
   *
   * @param integer $index id of option
   */
  public function selectOption($index)
  {
    if(!$this->initialized) throw new Exception("ComboBox wasn`t initialized yet!");
    Preconditions::checkIsNumber($index);
    $this->selectedOption = $index;
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
    $this->oldSelectedOption = $this->selectedOption;
  }

  /**
   * Get table definitions from DOMDocument
   *
   * @param Trace $trace for creating logs, tracking activity
   * @param $dom DOMDocument from ais2ResponseHtml
   * @returns array(string) Definition of table
   */
  private function getOptionsFromDom(Trace $trace, DOMDocument $dom)
  {
    Preconditions::checkNotNull($dom);
    $optionElements = $dom->getElementsByTagName('option');
    if ($optionElements == null) {
      throw new ParseException("Can't find options!");
    }

    $options = array();
    foreach ($optionElements as $option) {
      $options[] = $option->nodeValue;
    }
    return $options;
  }
}
