<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Libfajr
 * @subpackage Window__Studium
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace libfajr\window\studium;

use libfajr\window\studium as VSES017;
use libfajr\window\studium\fake as VSES017fake;
use libfajr\trace\Trace;
use libfajr\window\fake\FakeRequestExecutor;
use sfStorage;

class StudiumFakeFactoryImpl implements StudiumFactory
{
  /** @var sfStorage session storage to save modifications to defaults */
  private $storage;

  public function __construct(sfStorage $sessionStorage)
  {
    $this->storage = $sessionStorage;
  }

  public function newAdministraciaStudiaScreen(Trace $trace)
  {
    return new VSES017fake\FakeAdministraciaStudiaScreenImpl($trace,
        new FakeRequestExecutor($this->storage, array()));
  }

  public function newTerminyHodnoteniaScreen(Trace $trace, $paramName)
  {
    // v fake TerminyHodnotenia screen pouzivame $paramName ako id zapisneho listu
    return new VSES017fake\FakeTerminyHodnoteniaScreenImpl($trace,
        new FakeRequestExecutor($this->storage, array()), $paramName);
  }

  public function newHodnoteniaPriemeryScreen(Trace $trace, $paramName)
  {
    // v fake HodnoteniaPriemery screen pouzivame $paramName ako id zapisneho listu
    return new VSES017fake\FakeHodnoteniaPriemeryScreenImpl($trace,
        new FakeRequestExecutor($this->storage, array()),
        $paramName);
  }
}
