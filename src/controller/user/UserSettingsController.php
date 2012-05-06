<?php
/**
 * Contains controller managing user preferences.
 *
 * @copyright  Copyright (c) 2010-2012 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Controller__User
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\controller\user;

use Exception;
use fajr\Context;
use fajr\controller\BaseController;
use libfajr\AIS2Utils;
use libfajr\base\Preconditions;
use libfajr\trace\Trace;
use fajr\Request;
use fajr\util\FajrUtils;
use sfStorage;
use fajr\settings\SkinSettings;
use fajr\LoginManager;
use fajr\exceptions\AuthenticationRequiredException;
use fajr\rendering\DisplayManager;
use fajr\Router;

/**
 * Controller, which manages user settings.
 *
 * @package    Fajr
 * @subpackage Controller__Settings
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class UserSettingsController extends BaseController
{
  /** @var SkinSettings */
  private $skinSettings;
  
  /** @var LoginManager */
  private $loginManager;
  
  public static function getInstance()
  {
    return new UserSettingsController(SkinSettings::getInstance(),
        LoginManager::getInstance(), DisplayManager::getInstance());
  }

  public function __construct(SkinSettings $skinSettings,
      LoginManager $loginManager, DisplayManager $displayManager,
      Router $router)
  {
    parent::__construct($displayManager, $router);
    $this->skinSettings = $skinSettings;
    $this->loginManager = $loginManager;
  }

  public function invokeAction(Trace $trace, $action, Context $context)
  {
    Preconditions::checkIsString($action);

    if (!$this->loginManager->isLoggedIn()) {
      throw new AuthenticationRequiredException();
    }

    return parent::invokeAction($trace, $action, $context);
  }

  public function runSettings(Trace $trace, Context $context)
  {
    return $this->renderResponse('settings/settings');
  }

  public function runSkin(Trace $trace, Context $context)
  {
    $request = $context->getRequest();
    
    // set skin
    if ($request->getParameter('skinSelect')) {
      $this->skinSettings->setUserSkinName($request->getParameter('skinSelect'));
      // apply the skin for current request(user skin may be applied before this function)
      $this->displayManager->setSkin($this->skinSettings->getUserSkin());
    }

    $params = array(
      'availableSkins' => $this->skinSettings->getAvailableSkins(),
      'currentSkin' => $this->skinSettings->getUserSkinName(),
    );

    return $this->renderResponse('settings/skin', $params);
  }
}
