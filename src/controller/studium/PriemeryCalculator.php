<?php
/**
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Controller__Studium
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\controller\studium;

use fajr\libfajr\base\DisableEvilCallsObject;
use fajr\libfajr\base\Preconditions;
use fajr\libfajr\pub\data_manipulation\Znamka;
use InvalidArgumentException;

/**
 *
 * @package    Fajr
 * @subpackage Controller__Studium
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class PriemeryInternal
{
  protected $sucet = 0;
  protected $sucetVah = 0;
  protected $pocetPredmetovOhodnotenych = 0;
  protected $pocetKreditovOhodnotenych = 0;
  protected $pocetPredmetovNeohodnotenych = 0;
  protected $pocetKreditovNeohodnotenych = 0;

  private function addOhodnotene($hodnota, $kredity)
  {
    $this->sucet += $hodnota;
    $this->sucetVah += $hodnota*$kredity;
    $this->pocetPredmetovOhodnotenych += 1;
    $this->pocetKreditovOhodnotenych += $kredity;
  }

  private function addNeohodnotene($kredity)
  {
    $this->pocetPredmetovNeohodnotenych += 1;
    $this->pocetKreditovNeohodnotenych += $kredity;
  }

  public function add($znamkaText, $kredity)
  {
    Preconditions::checkContainsInteger($kredity);
    Preconditions::check($kredity >= 0, "Kreditov musí byť nezáporný počet.");
    Preconditions::checkIsString($znamkaText);

    if ($znamkaText == '') {
      $this->addNeohodnotene($kredity);
      return;
    }

    $znamka = Znamka::fromString($znamkaText);
    
    if ($znamka === null) {
      throw new InvalidArgumentException("Známka '$znamkaText' nie je platná");
    }

    $this->addOhodnotene($znamka->getNumerickaHodnota(), $kredity);
  }

  public function studijnyPriemer($neohodnotene = true)
  {
    $suma = $this->sucet;
    $pocet = $this->pocetPredmetovOhodnotenych;

    if ($neohodnotene) {
      $hodnotaFx = Znamka::fromString('Fx')->getNumerickaHodnota();
      $suma += $this->pocetPredmetovNeohodnotenych * $hodnotaFx;
      $pocet += $this->pocetPredmetovNeohodnotenych;
    }

    if ($pocet == 0) {
      return null;
    }
    return $suma / $pocet;
  }

  public function vazenyPriemer($neohodnotene=true)
  {
    $suma = $this->sucetVah;
    $pocet = $this->pocetKreditovOhodnotenych;
    if ($neohodnotene) {
      $hodnotaFx = Znamka::fromString('Fx')->getNumerickaHodnota();
      $suma += $this->pocetKreditovNeohodnotenych * $hodnotaFx;
      $pocet += $this->pocetKreditovNeohodnotenych;
    }
    if ($pocet == 0) {
      return null;
    }
    return $suma / $pocet;
  }

  public function hasPriemer()
  {
    return $this->pocetPredmetovOhodnotenych > 0;
  }

  public function kreditovCelkom()
  {
    return $this->pocetKreditovOhodnotenych + $this->pocetKreditovNeohodnotenych;
  }

  public function predmetovCelkom()
  {
    return $this->pocetPredmetovOhodnotenych + $this->pocetPredmetovNeohodnotenych;
  }

}

/**
 *
 * @package    Fajr
 * @subpackage Controller__Studium
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class PriemeryCalculator
{
  const SEMESTER_LETNY = 'leto';
  const SEMESTER_ZIMNY = 'zima';
  const AKADEMICKY_ROK = 'rok';

  protected $obdobia = null;

  public function __construct()
  {
    $this->obdobia = array(
        self::SEMESTER_LETNY => new PriemeryInternal(),
        self::SEMESTER_ZIMNY => new PriemeryInternal(),
        self::AKADEMICKY_ROK => new PriemeryInternal()
      );
  }

  public function add($castRoka, $znamka, $kredity)
  {
    Preconditions::check(in_array($castRoka,
                            array(self::SEMESTER_LETNY,
                                  self::SEMESTER_ZIMNY,
                                  self::AKADEMICKY_ROK)),
                         "Neplatná časť študijného roka.");
    $this->obdobia[$castRoka]->add($znamka, $kredity);
    $this->obdobia[self::AKADEMICKY_ROK]->add($znamka, $kredity);
  }

  public function hasPriemer()
  {
    return $this->obdobia[self::AKADEMICKY_ROK]->hasPriemer();
  }

  public function getObdobia() {
    return $this->obdobia;
  }

}
