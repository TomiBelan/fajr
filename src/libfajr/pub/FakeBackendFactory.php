<?php
// Copyright (c) 2011 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Pub
 * @author     Tomi Belan <tomi.belan@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\pub;

use fajr\libfajr\pub\BackendFactory;
use fajr\libfajr\pub\window\VSES017_administracia_studia\VSES017_FakeFactoryImpl;
use fajr\libfajr\pub\window\VSST060_register_predmetov\VSST060_FakeFactoryImpl;
use fajr\libfajr\window\fake\FakeMainScreen;
use fajr\libfajr\storage\TemporarilyModifiableStorage;
use fajr\libfajr\storage\FileStorage;
use fajr\libfajr\pub\regression\fake_data\FakeData;
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
    return new VSES017_FakeFactoryImpl($this->storage);
  }

  public function newVSST060Factory()
  {
    return new VSST060_FakeFactoryImpl($this->storage);
  }

  public function newAIS2MainScreen()
  {
    return new FakeMainScreen();
  }
}
