<?php
// Copyright (c) 2011 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Libfajr
 * @author     Tomi Belan <tomi.belan@gmail.com>
 * @filesource
 */
namespace libfajr;

use libfajr\BackendFactory;
use libfajr\window\studium\StudiumFakeFactoryImpl;
use libfajr\window\predmety\PredmetyFakeFactoryImpl;
use libfajr\window\fake\FakeMainScreen;
use libfajr\storage\TemporarilyModifiableStorage;
use libfajr\storage\FileStorage;
use libfajr\regression\fixtures\FakeData;
use sfSessionStorage;

class FakeBackendFactory implements BackendFactory
{
  private $storage;

  public function __construct(sfSessionStorage $sessionStorage)
  {
    $options = array(
      'permanent_storage' => new FileStorage(array('root_path' => FakeData::getDirectory())),
      'temporary_storage' => $sessionStorage,
    );
    $this->storage = new TemporarilyModifiableStorage($options);
  }

  public function getServerTime()
  {
    // Somewhat arbitrarily fixed as 10.1.2011 12:17:53
    return mktime(12, 17, 53, 1, 10, 2011);
  }

  public function newVSES017Factory()
  {
    return new StudiumFakeFactoryImpl($this->storage);
  }

  public function newVSST060Factory()
  {
    return new PredmetyFakeFactoryImpl($this->storage);
  }

  public function newAIS2MainScreen()
  {
    return new FakeMainScreen();
  }
}
