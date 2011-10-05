<?php
// Copyright (c) 2010-2011 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Reprezentuje obrazovku so zoznamom štúdií a zápisných listov.
 *
 * @package    Libfajr
 * @subpackage Window__VSES017_administracia_studia__Fake
 * @author     Peter Perešini <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace libfajr\window\VSES017_administracia_studia\fake;


use libfajr\base\Preconditions;
use libfajr\data_manipulation\DataTableImpl;
use libfajr\pub\base\Trace;
use libfajr\pub\window\VSES017_administracia_studia\AdministraciaStudiaScreen;
use libfajr\util\StrUtil;
use libfajr\window\fake\FakeAbstractScreen;
use libfajr\window\VSES017_administracia_studia\fake\FakePrehladKreditovDialogImpl;
use libfajr\pub\regression\ZoznamStudiiRegression;
use libfajr\pub\regression\ZoznamZapisnychListovRegression;

/**
 * Trieda reprezentujúca jednu obrazovku so zoznamom štúdií a zápisných listov.
 *
 * @package    Libfajr
 * @subpackage Window__VSES017_administracia_studia__Fake
 * @author     Peter Perešini <ppershing+fajr@gmail.com>
 */
class FakeAdministraciaStudiaScreenImpl extends FakeAbstractScreen
    implements AdministraciaStudiaScreen
{
  protected $idCache = array();

  /**
   * @var AIS2TableParser
   */
  private $parser;

  /**
   * TODO(it is wrong not to pass studiumIndex into getIdFromZapisnyListIndex but
   * it is compatible with current implementation of AdministraciaStudiaScreen.
   * Fix it in both places!
   */
  private $studiumIndex = 0;

  public function getZoznamStudii(Trace $trace)
  {
    $data = $this->executor->readTable(array(), 'zoznamStudii');
    $table = new DataTableImpl(ZoznamStudiiRegression::get(), $data);
    return $table;
  }

  private function getStudiumIdFromIndex($studiumIndex)
  {
    $data = $this->executor->readTable(array(), 'zoznamStudiiId');
    assert(isset($data[$this->studiumIndex]));
    return $data[$this->studiumIndex];
  }

  public function getStudiumIdFromZapisnyListIndex(Trace $trace, $zapisnyListIndex, $action)
  {
    return $this->getStudiumIdFromIndex($this->studiumIndex);
  }

  public function getZapisneListy(Trace $trace, $studiumIndex)
  {
    $this->studiumIndex = $studiumIndex;
    $data = $this->executor->readTable(
          array('studium' => $this->getStudiumIdFromIndex($studiumIndex)),
          'zoznamZapisnychListov');
    return new DataTableImpl(ZoznamZapisnychListovRegression::get(), $data);
  }

  public function getZapisnyListIdFromZapisnyListIndex(Trace $trace, $zapisnyListIndex, $action)
  {
    $data =
      $this->executor->readTable(
          array('studium' => $this->getStudiumIdFromIndex($this->studiumIndex)),
          'zoznamZapisnychListovId');
    assert(isset($data[$zapisnyListIndex]));
    return $data[$zapisnyListIndex];
  }
  
  public function getPrehladKreditovDialog(Trace $trace, $studiumIndex)
  {
    Preconditions::checkContainsInteger($studiumIndex);
    $studiumId = $this->getStudiumIdFromIndex($studiumIndex);
    return new FakePrehladKreditovDialogImpl($trace, $this,
        array('studium' => $studiumId));
  }

}
?>
