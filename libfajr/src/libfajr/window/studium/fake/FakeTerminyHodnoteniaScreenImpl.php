<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Reprezentuje obrazovku so zoznamom termínov hodnotení a predmetov.
 *
 * @package    Libfajr
 * @subpackage Window__Studium__Fake
 * @author     Peter Perešini <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace libfajr\window\studium\fake;

use Exception;
use libfajr\base\Preconditions;
use libfajr\data\DataTableImpl;
use libfajr\trace\Trace;
use libfajr\window\studium\TerminyHodnoteniaScreen;
use libfajr\window\fake\FakeAbstractScreen;
use libfajr\window\fake\FakeRequestExecutor;
use libfajr\regression\MojeTerminyRegression;
use libfajr\regression\ZapisanePredmetyRegression;

/**
 * Trieda reprezentujúca jednu obrazovku so zoznamom predmetov zápisného listu
 * a termínov hodnotenia.
 *
 * @package    Libfajr
 * @subpackage Window__Studium
 * @author     Peter Perešini <ppershing+fajr@gmail.com>
 */
class FakeTerminyHodnoteniaScreenImpl extends FakeAbstractScreen
    implements TerminyHodnoteniaScreen
{

  public function __construct(Trace $trace, FakeRequestExecutor $executor, $idZapisnyList)
  {
    parent::__construct($trace, $executor->spawnChild(array('list' => $idZapisnyList)));
  }

  public function getPredmetyZapisnehoListu(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    $data = $this->executor->readTable(array(), 'zapisanePredmety');
    $table = new DataTableImpl(ZapisanePredmetyRegression::get(), $data);
    return $table;
  }

  public function getTerminyHodnotenia(Trace $trace)
  {
    $result = array();

    $this->openIfNotAlready($trace);
    $predmety = $this->executor->readTable(array(), 'zapisanePredmety');
    foreach($predmety as $predmetIndex=>$unused) {
      $terminy = $this->executor->readTable(
          array('predmet' => $predmetIndex),
          'terminy');
      foreach($terminy as $terminIndex=>$unused2) {
        $info = $this->executor->readTable(
          array('predmet' => $predmetIndex,
                'termin' => $terminIndex,
               ),
          'prihlas');
        if (isset($info['jePrihlaseny'])) {
          $terminData = $info['prihlasenyData'];
          $terminData[0] = $info['jePrihlaseny'] ? 'TRUE' : 'FALSE';
          $result[] = $terminData;
        }
      }
    }
    return new DataTableImpl(MojeTerminyRegression::get(), $result);
  }

  public function getZoznamTerminovDialog(Trace $trace, $predmetIndex)
  {
    Preconditions::checkContainsInteger($predmetIndex);
    $data = $this->executor->readTable(array(), 'zapisanePredmety');
    if (!array_key_exists($predmetIndex, $data)) {
      throw new Exception("Zadaný predmet neexistuje!");
    }
    return new FakeTerminyDialogImpl($trace, $this,
        array('predmet' => $predmetIndex));
  }

  private function getPredmetTerminInternalIndex($terminIndex_)
  {
    $__id = 0;
    $predmety = $this->executor->readTable(array(), 'zapisanePredmety');
    foreach($predmety as $predmetIndex=>$unused) {
      $predmetExecutor = $this->executor->spawnChild(array('predmet' => $predmetIndex));
      $terminy = $predmetExecutor->readTable(array(), 'terminy');
      foreach($terminy as $terminIndex=>$unused2) {
        $info = $predmetExecutor->readTable(
            array('termin' => $terminIndex),
            'prihlas');
        if (isset($info['jePrihlaseny'])) {
          if ($__id == $terminIndex_) {
            return array($predmetIndex, $terminIndex);
          }
          $__id++;
        }
      }
    }

    throw new Exception("Termín sa nepodarilo nájsť!");
  }

  public function getZoznamPrihlasenychDialog(Trace $trace, $terminIndex)
  {
    $indexy = $this->getPredmetTerminInternalIndex($terminIndex);
    return new FakeZoznamPrihlasenychDialogImpl($trace, $this,
        array('predmet' => $indexy[0], 'termin' => $indexy[1]));
  }
  
  public function odhlasZTerminu(Trace $trace, $terminIndex)
  {
    $this->openIfNotAlready($trace);
  
    $indexy = $this->getPredmetTerminInternalIndex($terminIndex);

    $info = $this->executor->readTable(
        array('predmet' => $indexy[0],
              'termin' => $indexy[1],
             ),
        'prihlas');

    if (!$info['mozeOdhlasit']) {
      throw new Exception("Ais by povedal: Z termínu nie je možné sa odhlásiť!");
    }

    $info['jePrihlaseny'] = false;
    $this->executor->writeTable(
        array('predmet' => $indexy[0],
              'termin' => $indexy[1],
             ),
        'prihlas', $info);

    return true;
  }

}

?>
