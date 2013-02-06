<?php
// Copyright (c) 2010-2011 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Reprezentuje obrazovku so zoznamom štúdií a zápisných listov.
 *
 * @package    Libfajr
 * @subpackage Window__Predmety__Fake
 * @author     Peter Perešini <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace libfajr\window\predmety\fake;


use libfajr\base\Preconditions;
use libfajr\data\DataTableImpl;
use libfajr\trace\Trace;
use libfajr\window\predmety\RegisterPredmetovScreen;
use libfajr\util\StrUtil;
use libfajr\window\fake\FakeAbstractScreen;

/**
 * Trieda reprezentujúca jednu obrazovku so zoznamom predmetov.
 *
 * @package    Libfajr
 * @subpackage Window__Predmety__Fake
 * @author     Tomi Belan <tomi.belan@gmail.com>
 */
class FakeRegisterPredmetovScreenImpl extends FakeAbstractScreen
    implements RegisterPredmetovScreen
{
  /**
   * @var AIS2TableParser
   */
  private $parser;

  public function getInformacnyList(Trace $trace, $kodPredmetu, $akRok=null)
  {
    // TODO to be implemented
    return "<html>to be implemented</html>";
  }

}
?>
