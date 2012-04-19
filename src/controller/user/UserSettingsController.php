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
use fajr\Response;
use fajr\util\FajrUtils;
use sfStorage;
use fajr\settings\SkinSettings;
use fajr\LoginManager;
use fajr\exceptions\AuthenticationRequiredException;
use fajr\rendering\DisplayManager;

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
  
  /** @var DisplayManager */
  private $displayManager;
  
  public static function getInstance()
  {
    return new UserSettingsController(SkinSettings::getInstance(),
        LoginManager::getInstance(), DisplayManager::getInstance());
  }

  public function __construct(SkinSettings $skinSettings,
      LoginManager $loginManager, DisplayManager $displayManager)
  {
    $this->skinSettings = $skinSettings;
    $this->loginManager = $loginManager;
    $this->displayManager = $displayManager;
  }

  public function invokeAction(Trace $trace, $action, Context $context)
  {
    Preconditions::checkIsString($action);

    if (!$this->loginManager->isLoggedIn()) {
      throw new AuthenticationRequiredException();
    }

    parent::invokeAction($trace, $action, $context);
  }

  public function runSettings(Trace $trace, Context $context)
  {
    $response = $context->getResponse();

    $response->setTemplate('settings/settings');
  }

  public function runSkin(Trace $trace, Context $context)
  {
    $request = $context->getRequest();
    $response = $context->getResponse();
    
    // set skin
    if ($request->getParameter('skinSelect')) {
      $this->skinSettings->setUserSkinName($request->getParameter('skinSelect'));
      // apply the skin for current request(user skin may be applied before this function)
      $this->displayManager->setSkin($this->skinSettings->getUserSkin());
    }

    $response->set('availableSkins', $this->skinSettings->getAvailableSkins());
    $response->set('currentSkin', $this->skinSettings->getUserSkinName());

    $response->setTemplate('settings/skin');
  }
}
