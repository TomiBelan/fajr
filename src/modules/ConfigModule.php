<?php
/**
 * Injector module for setting configuration
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
 * Injector module for setting configuration.
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 */
class ConfigModule implements Module
{
  /** @var FajrConfig */
  private $config;
  
  function __construct(FajrConfig $config)
  {
    $this->config = $config;
  }
  
  public function configure(sfServiceContainerBuilder $container)
  {
      $container->setService('FajrConfig.class', $this->config);
  }
}