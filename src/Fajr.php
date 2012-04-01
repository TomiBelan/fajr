<?php
/**
 * The main logic of fajr application.
 *
 * @copyright  Copyright (c) 2010, 2011, 2012 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace fajr;

use Exception;
use fajr\ArrayTrace;
use fajr\Context;
use fajr\controller\DispatchController;
use fajr\exceptions\AuthenticationRequiredException;
use fajr\exceptions\SecurityException;
use fajr\exceptions\ValidationException;
use libfajr\AIS2Session;
use libfajr\trace\Trace;
use libfajr\connection\AIS2ServerConnection;
use libfajr\connection\AIS2ServerUrlMap;
use libfajr\login\Login;
use libfajr\regression;
use libfajr\window\AIS2MainScreenImpl;
use fajr\Request;
use fajr\Response;
use fajr\Statistics;
use fajr\util\FajrUtils;
use fajr\Version;
use fajr\config\ServerConfig;
use fajr\config\FajrConfig;
use fajr\config\FajrConfigOptions;
use fajr\ServerManager;
use fajr\rendering\DisplayManager;
use libfajr\exceptions\ReloginFailedException;
use fajr\controller\Controller;

/**
 * This is "main()" of the fajr. It instantiates all neccessary
 * objects, query ais and renders results.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Králik <majak47@gmail.com>
 */
class Fajr {

  /**
   * @var Context $context application context
   */
  private $context;

  /**
   * @var ServerManager
   */
  private $serverManager;
  
  /**
   * @var Router
   */
  private $router;

  /**
   * Constructor.
   */
  public function __construct(FajrConfig $config)
  {
    $this->config = $config;
    $this->serverManager = ServerManager::getInstance();
    $this->router = Router::getInstance();
  }

  /**
   * Set an exception to be displayed.
   * @param Exception $ex
   */
  private function setException(Exception $ex) {
    if ($this->context == null) {
      // May happen if exception occured before or in context
      // instantiation. We don't know how to handle the
      // exception in this case, so just pass it to the
      // outer exception handler
      throw $ex;
    }
    $response = $this->context->getResponse();

    // Note: We can't store function agruments from
    // stacktrace for template rendering, because
    // it can hold cyclic dependency to Context
    // and thus makes order of destruction unpredictable.
    $info = FajrUtils::extractExceptionInfo($ex);
    $response->set('exception', $info);

    $response->set('showStackTrace',
                   $this->config->get('Debug.Exception.ShowStacktrace'));
    
    $response->setTemplate('exception');
  }

  /**
   * Save information about security violation for analysis.
   * @param SecurityException
   * @returns void
   */
  private function logSecurityException(Exception $e) {
    
  }

  public function render(Response $response)
  {
    try {
      $displayManager = DisplayManager::getInstance();
      echo $displayManager->display($response);
    } catch (Exception $e) {
      throw new Exception('Chyba pri renderovaní template '.
          $response->getTemplate().':' .$e->getMessage(),
                          null, $e);
    }
  }


  /**
   * Runs the whole logic. It is fajr's main()
   *
   * @returns void
   */
  public function run()
  {
    try {
      $trace = TraceProvider::getInstance();
      $this->context = Context::getInstance();

      $this->setResponseFields($this->context->getRequest(), $this->context->getResponse());
      $this->runLogic($trace);
    } catch (SecurityException $e) {
      $this->logSecurityException($e);
      if (!$this->config->get('Debug.Exception.ShowStacktrace')) {
        die("Internal error");
      } else {
        die($e);
      }
    } catch (Exception $e) {
      $this->setException($e);
      // Note: We MUST unset this exception, because it's
      // stacktrace holds cyclic references to context
      // and therefore the order of destruction of all objects 
      // is really random.
      unset($e);
    }

    if ($trace !== null) {
      $trace->tlog("everything done, rendering template");

      if ($trace instanceof \libfajr\trace\ArrayTrace) {
        $this->context->getResponse()->set('trace', $trace);
      } else {
        $this->context->getResponse()->set('trace', null);
      }
    }

    $this->render($this->context->getResponse());
  }

  /**
   * Sets common template fields.
   */
  private function setResponseFields(Request $request, Response $response)
  {
    // https://developer.mozilla.org/en/The_X-FRAME-OPTIONS_response_header
    $response->setHeader('X-Frame-Options', 'DENY');
    if (FajrUtils::isHTTPS()) {
      $hstsExpireTime = $this->config->get(FajrConfigOptions::STRICT_TRANSPORT_SECURITY);
      if ($hstsExpireTime !== null && $hstsExpireTime > 0) {
        $response->setStrictTransportSecurity($hstsExpireTime);
      }
    }

    $response->set('version', new Version());
    $response->set('banner_debug', $this->config->get(FajrConfigOptions::DEBUG_BANNER));
    if ($request->isDoNotTrack()) {
      $response->set('google_analytics', null);
    }
    else {
      $response->set('google_analytics',
                   $this->config->get(FajrConfigOptions::GOOGLE_ANALYTICS_ACCOUNT));
    }
    $response->set('base', FajrUtils::basePath());
    $response->set('language', 'sk');

    $response->set('availableServers', array());
    $response->set('currentServer', array('isBeta'=>false, 'instanceName'=>'Chyba'));

    $server = $this->serverManager->getActiveServer();
    $serverList = $this->config->get(FajrConfigOptions::AIS_SERVERLIST);
    $response->set('availableServers', $serverList);
    $response->set('currentServer', $server);
    $response->set('backendType', $this->config->get(FajrConfigOptions::BACKEND));

    $response->set('aisVersion', null);
    $response->set('aisVersionIncompatible', false);
    $response->set('loggedIn', false);
    $response->set('developmentVersion', $this->config->get(FajrConfigOptions::IS_DEVEL));
  }

  public function runLogic(Trace $trace)
  {
    $params = $this->router->routeCurrentRequest();
    $action = $params['_action'];
    $controllerClass = $params['_controller'];
    
    $session = $this->context->getSessionStorage();
    $loginManager = LoginManager::getInstance();
    // If we are going to log in, we need a clean session.
    // This needs to be done before a connection
    // is created, because we pass cookie file name
    // that contains session_id into AIS2CurlConnection
    if ($action == 'Login') {
      $loginManager->destroySession();
    }

    $connection = ConnectionProvider::getInstance();
    $server = $this->serverManager->getActiveServer();
    $serverConnection = new AIS2ServerConnection($connection,
        new AIS2ServerUrlMap($server->getServerName()));
    $connService = LazyServerConnection::getInstance();
    $connService->setReal($serverConnection);

    $response = $this->context->getResponse();
    
    $loggedIn = $loginManager->isLoggedIn($serverConnection);
    if (!$loggedIn) {
      try {
        $loggedIn = $loginManager->relogin();
      }
      catch (ReloginFailedException $ex) {
        $loginManager->destroySession();
        $response->redirect($this->router->generateUrl('homepage'));
        return;
      }
    }
    $response->set('loggedIn', $loggedIn);
    
    if ($loggedIn) {
      $backendFactory = BackendProvider::getInstance();
      $mainScreen = $backendFactory->newAIS2MainScreen();

      if (($aisVersion = $session->read('ais/aisVersion')) == null) {
        $aisVersion = $mainScreen->getAisVersion($trace->addChild('Get AIS version'));
        $session->write('ais/aisVersion', $aisVersion);
      }
      $response->set('aisVersion', $aisVersion);
      $response->set('aisVersionIncompatible',
        !($aisVersion >= regression\VersionRange::getMinVersion() &&
          $aisVersion <= regression\VersionRange::getMaxVersion()));

      if (($aisApps = $session->read('ais/aisApps')) == null) {
        $aisModules = array('SP', 'LZ', 'ES', 'ST', 'RH', 'UB', 'AS', 'RP');
        $aisApps = $mainScreen->
            getAllAvailableApplications($trace->addChild('Get all applications'),
                                        $aisModules);
        $session->write('ais/aisApps', $aisApps);
      }
      if (($userName = $session->read('ais/aisUserName')) == null) {
        $userName = $mainScreen->getFullUserName($trace->addChild('Get user name'));
        $session->write('ais/aisUserName', $userName);
      }
      $response->set('aisUserName', $userName);
    }
    else {
      $response->set('aisVersion', null);
      $response->set('aisVersionIncompatible', null);
    }

    $controller = call_user_func(array($controllerClass, 'getInstance'));
    if (!($controller instanceof Controller)) {
      throw new Exception('Class "' . $controllerClass . '" is not a controller');
    }

    try {
      $subTrace = $trace->addChild('Action ' . $controllerClass . '->' . $action);
      $controller->invokeAction($subTrace, $action, $this->context);
    }
    catch (AuthenticationRequiredException $ex) {
      $response->redirect($this->router->generateUrl('homepage'));
    }
    $response->set('statistics', Statistics::getInstance());
  }
}
