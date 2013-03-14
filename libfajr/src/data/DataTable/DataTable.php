<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Tento súbor obsahuje objekt zaobaľujúci tabuľku dát.
 *
 * @package    Libfajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace libfajr\data;

use libfajr\data\AIS2TableParser;
use libfajr\trace\Trace;

/**
 * Trieda zastrešujúca tabuľku dát.
 *
 * @package    Libfajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class DataTable implements SimpleDataTable
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
   * Riadky tabuľky ktoré sa zmenili
   * @var array(integer)
   */
  private $change = null;

  /**
   * Konštruktor, z aisResponseHTML vyrobí tabuľku,
   * identifikovanú cez $dataViewName
   * @param Trace    $trace            na vedenie logu
   * @param string $aisResponseHtml    HTML odpoved z aisu
   * @param string $dataViewName       názov tabuľky ktorú chceme načítať
   */
  public function __construct(Trace $trace, $aisResponseHtml, $dataViewName)
  {
    $this->loadTable($trace, $aisResponseHtml, $dataViewName);
  }

  /**
   * Načíta tabuľlku z aisResponseHtml
   *
   */
  public function loadTable(Trace $trace, $aisResponseHtml, $dataViewName)
  {
    $parser = new AIS2TableParser();
    $data = $parser->createTableFromHtml2($trace, $aisResponseHtml, $dataViewName);
    $this->definition = $data['definitions'];
    assert(is_array($data['data']));
    $this->data = array();
    foreach ($data['data'] as $rowKey=>$tableRow) {
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
   * Vráti riadky tabuľky.
   *
   * @returns array(array(string=>string)) riadky tabuľky
   */
  public function getData()
  {
    return $this->data;
  }

  /**
   * Vráti definíciu stĺpcov použitú a pri parsovaní.
   *
   * @returns array(string) názvy stĺpcov.
   */
  public function getTableDefinition()
  {
    return $this->definition;
  }

  /**
   * Vráti definíciu stĺpcov použitú pri parsovaní.
   *
   * @param integer $num číslo riadku ktoré chceme
   * @returns array(string) dáta v riadku $data[$num].
   */
  public function getRow($num)
  {
    return $this->data[$num];
  }

  /**
   * Zmení jeden riadok v tabuľke a zaregistruje zmenu
   *
   * @param integer $num číslo riadku ktorý chceme zmeniť
   * @param array(string=>string) jeden riadok z tabulky ktorým nahradíme pôvodný
   */
  public function setRow($num, $row)
  {
    assert(count($row) != count($this->definition));
    $this->change[] = $num;

    $this->data[$num] = $row;
  }

  /**
   * Vráti riadky tabuľky ktoré sa zmenili, a zmenu už nepovažuje za zmenu
   *
   * @return array(array(string=>string))
   */
  public function getChange()
  {
    $result = null;

    foreach($this->change as $key){
      $result[] = $data[$key];
    }
    
    $this->change = null;
    return $result;
  }
}
