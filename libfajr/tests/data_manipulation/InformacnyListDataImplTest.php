<?php

/**
 * This file contains tests for InformacnyListDataImpl
 *
 * @copyright  Copyright (c) 2010, 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Libfajr
 * @subpackage Data
 * @author     Martin Králik <majak47@gmail.com>
 * @author     Jakub Marek <jakub.marek@gmail.com>
 * @filesource
 */

namespace libfajr\data;

use PHPUnit_Framework_TestCase;
use libfajr\data\InformacnyListDataImpl;
use libfajr\data\InformacnyListAttributeEnum as Attr;

/**
 * @ignore
 */
class InformacnyListDataImplTest extends PHPUnit_Framework_TestCase {

    public $informacnyList;

    public function setUp() {

        $list = array(
            array(
              'id' => Attr::NAZOV,
              'values' => array("Kryptologia (2)"),
              ),
            array(
              'id' => Attr::STUDIJNY_PROGRAM,
              'values' => array("mINF, mINF/k"),
              ),
            array(
              'id' => Attr::POCET_KREDITOV,
              'values' => array("6"),
              ),
            );
        $this->informacnyList = new InformacnyListDataImpl($list);

    }

    public function testExistenciaAtributov() {
        $this->assertTrue($this->informacnyList->hasAttribute(Attr::NAZOV));
        $this->assertTrue($this->informacnyList->hasAttribute(Attr::STUDIJNY_PROGRAM));
        $this->assertFalse($this->informacnyList->hasAttribute('b34gh'));
    }

    public function testExistenciaAtributov2() {
        foreach ($this->informacnyList->getListOfAttributes() as $attribute) {
            $this->assertTrue($this->informacnyList->hasAttribute($attribute));
        }
    }

    public function testHodnotyAtributov() {
        $studProgram = $this->informacnyList->getAttribute(Attr::STUDIJNY_PROGRAM);
        $this->assertEquals('mINF, mINF/k', $studProgram['values'][0]);
        
        $pocetKreditov = $this->informacnyList->getAttribute(Attr::POCET_KREDITOV);
        $this->assertEquals('6', $pocetKreditov['values'][0]);
        
        $this->assertFalse($this->informacnyList->getAttribute('b34gh'));
    }

    public function testKonzistenciaGetAllAttributes() {
        $attributes = $this->informacnyList->getAllAttributes();
        foreach ($attributes as $attr) {
            $this->assertEquals($attr, $this->informacnyList->getAttribute($attr['id']));
        }
    }

}
