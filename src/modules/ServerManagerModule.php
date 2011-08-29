<?php
/**
 * Injector module for server manager
 *
 * @copyright  Copyright (c) 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 * @filesource
 */

namespace fajr\modules;

use fajr\injection\Module;
use sfServiceContainerBuilder;
use sfServiceReference;
use sfStorage;
use fajr\config\FajrConfig;

/**
 * Injector module for server manager.
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 */
class ServerManagerModule implements Module
{
  
  public function configure(sfServiceContainerBuilder $container)
  {
      $container->register('ServerManager.class', '\fajr\ServerManager')
          ->addArgument(new sfServiceReference('Session.Storage.class'))
          ->addArgument(new sfServiceReference('Context.class'))
          ->addArgument(new sfServiceReference('FajrConfig.class'))
          ;
  }
}