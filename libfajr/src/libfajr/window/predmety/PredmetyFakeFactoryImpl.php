<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Libfajr
 * @subpackage Window__Predmety
 * @author     Tomi Belan <tomi.belan@gmail.com>
 * @filesource
 */
namespace libfajr\window\predmety;

use libfajr\window\predmety as VSST060;
use libfajr\window\predmety\fake as VSST060fake;
use libfajr\trace\Trace;
use libfajr\window\fake\FakeRequestExecutor;
use sfStorage;

class PredmetyFakeFactoryImpl implements PredmetyFactory
{
  /** @var sfStorage session storage to save modifications to defaults */
  private $storage;

  public function __construct(sfStorage $sessionStorage)
  {
    $this->storage = $sessionStorage;
  }

  public function newAdministraciaStudiaScreen(Trace $trace)
  {
    return new VSST060fake\FakeRegisterPredmetovScreenImpl($trace,
        new FakeRequestExecutor($this->storage, array()));
  }

  public function newRegisterPredmetovScreen(Trace $trace)
  {
    return new fake\FakeRegisterPredmetovScreenImpl($trace, 
        new FakeRequestExecutor($this->storage, array()));
  }
}
