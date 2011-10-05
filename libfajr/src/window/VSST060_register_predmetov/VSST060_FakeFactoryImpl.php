<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Libfajr
 * @subpackage Pub__Window__VSST060_register_predmetov
 * @author     Tomi Belan <tomi.belan@gmail.com>
 * @filesource
 */
namespace libfajr\window\VSST060_register_predmetov;

use libfajr\window\VSST060_register_predmetov as VSST060;
use libfajr\window\VSST060_register_predmetov\fake as VSST060fake;
use libfajr\base\Trace;
use libfajr\window\fake\FakeRequestExecutor;
use sfStorage;

class VSST060_FakeFactoryImpl implements VSST060_Factory
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
