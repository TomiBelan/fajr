<?php
/**
 * Injector module for arguments passed to controllers.
 *
 * @copyright  Copyright (c) 2010, 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace fajr\modules;

use fajr\injection\Module;
use fajr\libfajr\pub\connection\AIS2ServerConnection;
use fajr\config\ServerConfig;
use sfServiceContainerBuilder;
use sfServiceReference;
use sfStorage;
use fajr\config\FajrConfigOptions;
use fajr\config\FajrConfig;

/**
 * Injector module for arguments passed to controllers.
 *
 * @package    Fajr
 * @subpackage Modules
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class ControllerInjectorModule implements Module
{
  /** @var FajrConfig */
  private $config;

  public function __construct(FajrConfig $config) {
    $this->config = $config;
  }

  /**
   * Configure injection of arguments of controllers
   *
   * @param sfServiceContainerBuilder $container Symfony container to configure
   */
  public function configure(sfServiceContainerBuilder $container)
  {
    $container->register('studium.controller.class',
                         '\fajr\controller\studium\StudiumController')
              ->addArgument(new sfServiceReference('administracia_studia_screen.factory.class'))
              ->addArgument('%serverTime%')
              ->addArgument(new sfServiceReference('LoginManager.class'))
              ->setShared(false);

    $container->register('predmety.controller.class',
                         '\fajr\controller\predmety\PredmetyController')
              ->addArgument(new sfServiceReference('register_predmetov_screen.factory.class'))
              ->addArgument('%serverTime%')
              ->setShared(false);
    
    $container->register('login.controller.class',
                         '\fajr\controller\user\LoginController')
              ->addArgument(new sfServiceReference('FajrConfig.class'))
              ->addArgument(new sfServiceReference('LoginManager.class'))
              ->addArgument(new sfServiceReference('LoginFactory.class'))
              ->addArgument(new sfServiceReference('ServerManager.class'))
              ->setShared(false);
    
    $container->register('welcome.controller.class',
                         '\fajr\controller\welcome\WelcomeController')
              ->setShared(false);
    
    $container->register('serverConnection.class', '\fajr\LazyServerConnection')
              ->setShared(true);

    switch ($this->config->get(FajrConfigOptions::BACKEND)) {
      case FajrConfigOptions::BACKEND_FAKE:
        $container->register('administracia_studia_screen.factory.class',
                             '\fajr\libfajr\pub\window\VSES017_administracia_studia\VSES017_FakeFactoryImpl')
                  ->addArgument(new sfServiceReference('fake.storage.class'))
                  ->setShared(false);

        $container->register('register_predmetov_screen.factory.class',
                             '\fajr\libfajr\pub\window\VSST060_register_predmetov\VSST060_FakeFactoryImpl')
                  ->addArgument(new sfServiceReference('fake.storage.class'))
                  ->setShared(false);

        $container->register('fake.storage.class', '\fajr\libfajr\storage\TemporarilyModifiableStorage')
                  ->addArgument('%TempStorage.options%');

        $container->setParameter('TempStorage.options',
                    array('permanent_storage' => new sfServiceReference('fake.file.storage.class'),
                          'temporary_storage' => new sfServiceReference('Session.Storage.class')));

        $container->register('fake.file.storage.class', '\fajr\libfajr\storage\FileStorage')
                  ->addArgument('%Fake.FileStorage.options%');

        $container->setParameter('Fake.FileStorage.options',
                    array('root_path' => \fajr\libfajr\pub\regression\fake_data\FakeData::getDirectory()));

        $container->register('AIS2MainScreen.class', '\fajr\libfajr\window\fake\FakeMainScreen')
                  ->setShared(false);

        /**
         * Somewhat arbitrary fixed as 10.1.2011 12:17:53
         **/
        $FAKE_TIME = mktime(12, 17, 53, 1, 10, 2011);

        $container->setParameter('serverTime', $FAKE_TIME);
        break;
      case FajrConfigOptions::BACKEND_LIBFAJR:
        $container->register('administracia_studia_screen.factory.class',
                             '\fajr\libfajr\pub\window\VSES017_administracia_studia\VSES017_FactoryImpl')
                  ->addArgument(new sfServiceReference('serverConnection.class'))
                  ->setShared(false);

        $container->register('register_predmetov_screen.factory.class',
                             '\fajr\libfajr\pub\window\VSST060_register_predmetov\VSST060_FactoryImpl')
                  ->addArgument(new sfServiceReference('serverConnection.class'))
                  ->setShared(false);

        $container->register('AIS2MainScreen.class', '\fajr\libfajr\window\AIS2MainScreenImpl')
                  ->addArgument(new sfServiceReference('serverConnection.class'))
                  ->setShared(false);

        $container->setParameter('serverTime', time());
        break;
      default:
        assert(false);
    }
  }
}
