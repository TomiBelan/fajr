<?php
/**
 * Contains controller for login/logout
 *
 * @copyright  Copyright (c) 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Controller__User
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 * @filesource
 */
namespace fajr\controller\user;

use Exception;
use fajr\Context;
use fajr\controller\BaseController;
use fajr\libfajr\AIS2Utils;
use fajr\libfajr\base\Preconditions;
use fajr\libfajr\pub\base\Trace;
use fajr\libfajr\pub\login\LoginFactory;
use fajr\Request;
use fajr\Response;
use fajr\util\FajrUtils;
use sfStorage;
use fajr\config\FajrConfig;
use fajr\settings\SkinSettings;
use fajr\LoginManager;
use fajr\ServerManager;

/**
 * Controller for login/logout
 *
 * @package    Fajr
 * @subpackage Controller__User
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 */
class LoginController extends BaseController
{
  /** @var FajrConfig */
  private $config;
  
  /** @var LoginManager */
  private $loginManager;
  
  /** @var LoginFactory */
  private $loginFactory;
  
  /** @var ServerManager */
  private $serverManager;

  public function __construct(FajrConfig $config, LoginManager $loginManager,
      LoginFactory $loginFactory, ServerManager $serverManager)
  {
    $this->config = $config;
    $this->loginManager = $loginManager;
    $this->loginFactory = $loginFactory;
    $this->serverManager = $serverManager;
  }

  public function runLogin(Trace $trace, Context $context)
  {
    $request = $context->getRequest();
    $response = $context->getResponse();
    $server = $this->serverManager->getActiveServer();

    try {
      $this->loginManager->login($trace->addChild("Logging in..."),
          $server, $this->loginFactory);
    } catch (LoginException $e) {
      $this->setException($e);
      try {
        $this->loginManager->logout($trace);
      } catch (LoginException $e) {
        // do nothing
      }
    }
  }
  
  public function runLoginScreen(Trace $trace, Context $context)
  {
    $server = $this->serverManager->getActiveServer();
    $response = $context->getResponse();
    switch ($server->getLoginType()) {
        case 'password':
          $response->setTemplate('welcome');
          break;
        case 'cosign':
          $response->setTemplate('welcomeCosign');
          break;
        case 'cosignproxy':
          $response->setTemplate('welcomeCosignProxy');
          break;
        case 'nologin':
          $response->setTemplate('welcomeDemo');
          break;
        default:
          throw new Exception("Invalid type of login");
      }
  }
  
  public function runLogout(Trace $trace, Context $context)
  {
    $this->loginManager->logout();
  }

}