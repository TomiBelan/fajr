<?php
// Copyright (c) 2011 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * This file contains tests for InformacnyListDataImpl
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub__Data_manipulation
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\pub\data_manipulation;

use PHPUnit_Framework_TestCase;
use fajr\libfajr\pub\data_manipulation\InformacnyListDataImpl;
use fajr\libfajr\pub\data_manipulation\InformacnyListAttributeEnum;
/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class InformacnyListDataImplTest extends PHPUnit_Framework_TestCase
{
 
  private $informacnyList;

  public function setUp()
  {
    $html = file_get_contents(__DIR__.'/testdata/rozsirenyInformacnyListSGarantomPredmetu.dat');
    $this->informacnyList = new InformacnyListDataImpl($html);
  }

  public function testExistenciaAtributov()
  {
    $this->assertTrue($this->informacnyList->hasAttribute(InformacnyListAttributeEnum::NAZOV));
    $this->assertTrue($this->informacnyList->hasAttribute(InformacnyListAttributeEnum::STUDIJNY_PROGRAM));
    $this->assertTrue($this->informacnyList->hasAttribute(InformacnyListAttributeEnum::POCET_KREDITOV));
    $this->assertFalse($this->informacnyList->hasAttribute('b34gh'));
    $this->assertFalse($this->informacnyList->hasAttribute('konfekcna_velkost1'));
    $this->assertFalse($this->informacnyList->hasAttribute('minimalny+vek'));
  }

  public function testExistenciaAtributov()
  {
    foreach ($this->informacnyList->getListOfAttributes() as $attribute)
    {
      $this->assertTrue($this->informacnyList->hasAttribute($attribute));
    }
  }

  public function testHodnotyAtributov()
  {
    $this->assertEquals('Kryptológia (2)', $this->informacnyList->getAttribute(InformacnyListAttributeEnum::NAZOV));
    $this->assertEquals('mINF, mINF/k', $this->informacnyList->getAttribute(InformacnyListAttributeEnum::STUDIJNY_PROGRAM));
    $this->assertEquals('6', $this->informacnyList->getAttribute(InformacnyListAttributeEnum::POCET_KREDITOV));
    $this->assertFalse($this->informacnyList->getAttribute('b34gh'));
    $this->assertFalse($this->informacnyList->getAttribute('konfekcna_velkost1'));
    $this->assertFalse($this->informacnyList->getAttribute('minimalny+vek'));
  }

  public function testKonzistenciaGetAllAttributes()
  {
    $attributes = $this->informacnyList->getAllAttributes();
    foreach ($attributes as $key => $value)
    {
      $this->assertEquals($values, $this->informacnyList->getAttribute($key));
    }
  }

}
