<?php

/**
 * This file contains tests for ais2 Table class.
 *
 * @copyright  Copyright (c) 2010, 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Libfajr__Data_manipulation
 * @author     Jakub Marek <jakub.marek@gmail.com>
 * @filesource
 */

namespace libfajr\data_manipulation;

use \PHPUnit_Framework_TestCase;
use libfajr\data_manipulation\DataTable;
use libfajr\data_manipulation\InformacnyListParser;
use libfajr\data_manipulation\InformacnyListAttributeEnum as Attr;
use libfajr\trace\NullTrace;

/**
 * @ignore
 */
require_once 'test_include.php';

/**
 * @ignore
 */
class InformacnyListParserTest extends PHPUnit_Framework_TestCase
{

  private $html;

  public function setUp()
  {
    $this->html = file_get_contents(__DIR__ . '/testdata/rozsirenyInformacnyListSGarantomPredmetu.dat');
    $this->parser = new InformacnyListParser();
  }
  
  private static function getValue($infoList, $id, $index=0) {
    $attr = $infoList->getAttribute($id);
    if ($attr === false) return false;
    return $attr['values'][$index];
  }

  public function testInformacneListyParsing()
  {
    $infoList = $this->parser->parse(new NullTrace(), $this->html);
    $this->assertEquals('Univerzita Komenského v Bratislave - Fakulta matematiky, fyziky a informatiky',
        self::getValue($infoList, Attr::SKOLA_FAKULTA));
    $this->assertEquals('FMFI.KI/2-INF-235/00', self::getValue($infoList, Attr::KOD));
    $this->assertEquals('Kryptológia (2)', self::getValue($infoList, Attr::NAZOV));
    $this->assertEquals('mINF, mINF/k', self::getValue($infoList, Attr::STUDIJNY_PROGRAM));
    $this->assertEquals(false, self::getValue($infoList, Attr::GARANTUJE));
    $this->assertEquals(false, self::getValue($infoList, Attr::ZABEZPECUJE));
    $this->assertEquals('4', self::getValue($infoList, Attr::OBDOBIE_STUDIA_PREDMETU));
    $this->assertEquals('Kurz', self::getValue($infoList, Attr::FORMA_VYUCBY));
    $this->assertEquals('4', self::getValue($infoList, Attr::VYUCBA_TYZDENNE));
    $this->assertEquals('56', self::getValue($infoList, Attr::VYUCBA_SPOLU));
    $this->assertEquals('6', self::getValue($infoList, Attr::POCET_KREDITOV));
    $this->assertEquals(false, self::getValue($infoList, Attr::PODMIENUJUCE_PREDMETY));
    $this->assertEquals('1-INF-640 Kryptológia (1)', self::getValue($infoList, Attr::OBSAHOVA_PREREKVIZITA));
    $this->assertEquals('Hodnotenie', self::getValue($infoList, Attr::SPOSOB_HODNOTENIA_A_SKONCENIA));
    $this->assertEquals(false, self::getValue($infoList, Attr::PRIEBEZNE_HODNOTENIE));
    $this->assertEquals('skúška', self::getValue($infoList, Attr::ZAVERECNE_HODNOTENIE));
    $this->assertEquals('Prezentovať zložitejšie kryptografické konštrukcie s použitím základných prvkov ako aj prístupy k formálnemu zdôvodneniu ich bezpečnosti.',
        self::getValue($infoList, Attr::CIEL_PREDMETU));
    $this->assertEquals('Schémy na zdieľanie tajomstva, podpisové schémy s dodatočnými vlastnosťami, formálna analýza autentizačných protokolov, bezznalostné dokazovacie systémy, primitíva protokolov s viacerými účastníkmi (oblivious transfer, bit commitment), digitálne peniaze, elektronické voľby, dokázateľná bezpečnosť schém pre asymetrické šifrovanie a digitálne podpisy.',
        self::getValue($infoList, Attr::OSNOVA_PREDMETU));
    $this->assertEquals('Stinson D.: Cryptography: Theory and Practice, 3rd Edition, CRC Press, 2005.',
        self::getValue($infoList, Attr::LITERATURA, 0));
    $this->assertEquals('Menezes A.J., Van Oorschot P.C.: Handbook of Applied Cryptography, CRC Press, 1996.',
        self::getValue($infoList, Attr::LITERATURA, 1));
    $this->assertEquals('Koblitz N.: A Course in Number Theory and Cryptography, Springer-Verlag, 1987.',
        self::getValue($infoList, Attr::LITERATURA, 2));
    $this->assertEquals('Mao W.: Modern Cryptography: Theory and Practice, Hewlett-Packard, 2003.',
        self::getValue($infoList, Attr::LITERATURA, 3));
    $this->assertEquals('anglický, slovenský', self::getValue($infoList, Attr::VYUCOVACI_JAZYK));
    $this->assertEquals('14.08.2009', self::getValue($infoList, Attr::DATUM_POSLEDNEJ_UPRAVY));
  }

}