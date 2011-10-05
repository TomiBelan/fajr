<?php
// Copyright (c) 2010-2011 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Reprezentuje obrazovku so zoznamom štúdií a zápisných listov.
 *
 * @package    Libfajr
 * @subpackage Libfajr__Window__VSST060_register_predmetov__Fake
 * @author     Peter Perešini <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace libfajr\window\VSST060_register_predmetov\fake;


use libfajr\base\Preconditions;
use libfajr\data_manipulation\DataTableImpl;
use libfajr\pub\base\Trace;
use libfajr\pub\window\VSST060_register_predmetov\RegisterPredmetovScreen;
use libfajr\util\StrUtil;
use libfajr\window\fake\FakeAbstractScreen;

/**
 * Trieda reprezentujúca jednu obrazovku so zoznamom predmetov.
 *
 * @package    Libfajr
 * @subpackage Libfajr__Window__VSST060_register_predmetov__Fake
 * @author     Tomi Belan <tomi.belan@gmail.com>
 */
class FakeRegisterPredmetovScreenImpl extends FakeAbstractScreen
    implements RegisterPredmetovScreen
{
  /**
   * @var AIS2TableParser
   */
  private $parser;

  public function getInformacnyList(Trace $trace, $kodPredmetu)
  {
    // TODO to be implemented
    return "<html>to be implemented</html>";
  }

}
?>
