<?php
/**
 * Injector module for login manager
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
 * Injector module for login manager.
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 */
class LoginManagerModule implements Module {
  
  public function configure(sfServiceContainerBuilder $container)
  {
    $container->register('LoginManager.class', '\fajr\LoginManager')
              ->addArgument(new sfServiceReference('Session.Storage.class'))
              ->addArgument(new sfServiceReference('Request.class'))
              ->addArgument(new sfServiceReference('Response.class'))
              ->addArgument(new sfServiceReference('serverConnection.class'))
              ;
  }
}