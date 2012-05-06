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
use fajr\settings\SkinSettings;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use fajr\Warnings;

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
   * @var DisplayManager
   */
  private $displayManager;

  /**
   * Constructor.
   */
  public function __construct(FajrConfig $config)
  {
    $this->config = $config;
    $this->serverManager = ServerManager::getInstance();
    $this->router = Router::getInstance();
    $this->displayManager = DisplayManager::getInstance();
  }

  /**
   * Set an exception to be displayed.
   * @param Exception $ex
   */
  private function renderExceptionResponse(Exception $ex) {
    if ($this->context == null) {
      // May happen if exception occured before or in context
      // instantiation. We don't know how to handle the
      // exception in this case, so just pass it to the
      // outer exception handler
      throw $ex;
    }
    
    // Note: We can't store function arguments from
    // stacktrace for template rendering, because
    // it can hold cyclic dependency to Context
    // and thus makes order of destruction unpredictable.
    $info = FajrUtils::extractExceptionInfo($ex);
    $params = array(
      'exception' => $info,
      'showStackTrace' => $this->config->get('Debug.Exception.ShowStacktrace'),
    );
    
    return $this->displayManager->renderResponse('exception', $params, null, 500);
  }

  /**
   * Save information about security violation for analysis.
   * @param SecurityException
   * @returns void
   */
  private function logSecurityException(Exception $e) {
    
  }

  /**
   * Runs the whole logic. It is fajr's main()
   *
   * @returns void
   */
  public function run()
  {
    $response = null;
    try {
      $trace = TraceProvider::getInstance();
      $this->context = Context::getInstance();

      $this->setResponseFields($this->context->getRequest());
      $response = $this->runLogic($trace);
    } catch (SecurityException $e) {
      $this->logSecurityException($e);
      if (!$this->config->get('Debug.Exception.ShowStacktrace')) {
        die("Internal error");
      } else {
        die($e);
      }
    } catch (Exception $e) {
      $response = $this->renderExceptionResponse($e);
      // Note: We MUST unset this exception, because it's
      // stacktrace holds cyclic references to context
      // and therefore the order of destruction of all objects 
      // is really random.
      unset($e);
    }
    
    if ($response == null) {
      $response = new \Symfony\Component\HttpFoundation\Response('Response missing', 500);
      $response->headers->set('Content-Type', 'text/html;charset=UTF-8');
    }

    if ($trace !== null) {
      $trace->tlog("everything done");
    }
    
    $this->adjustResponse($response);
    $response->prepare(\Symfony\Component\HttpFoundation\Request::createFromGlobals());
    $response->send();
  }

  /**
   * Sets dafault template fields.
   * TODO: set as twig globals?
   */
  private function setResponseFields(Request $request)
  {
    $skinSettings = SkinSettings::getInstance();
    if ($request->isMobileBrowser()) {
      $skinSettings->setDefaultSkinName('mobile');
    }
    $this->displayManager->setSkin($skinSettings->getUserSkin());
    
    $params = array();
    
    $referrer = $request->getHeader('Referer');
    $pageMovedCookie = isset($_COOKIE['FajrPageMoved']);
    $pageMovedReferer = preg_match('#^https?://fajr.dcs.fmph.uniba.sk#', $referrer) === 1;
    if ($pageMovedCookie || $pageMovedReferer) {
      Warnings::getInstance()->addWarning(array('type' => 'pageMoved'));
    }

    $params['version'] = new Version();
    $params['banner_debug'] = $this->config->get(FajrConfigOptions::DEBUG_BANNER);
    if ($request->isDoNotTrack()) {
      $params['google_analytics'] = null;
    }
    else {
      $params['google_analytics'] = 
                   $this->config->get(FajrConfigOptions::GOOGLE_ANALYTICS_ACCOUNT);
    }
    $params['base'] = FajrUtils::basePath();
    $params['language'] = 'sk';

    $params['availableServers'] = array();
    $params['currentServer'] = array('isBeta'=>false, 'instanceName'=>'Chyba');

    $server = $this->serverManager->getActiveServer();
    $serverList = $this->config->get(FajrConfigOptions::AIS_SERVERLIST);
    $params['availableServers'] = $serverList;
    $params['currentServer'] = $server;
    $params['backendType'] = $this->config->get(FajrConfigOptions::BACKEND);

    $params['aisVersion'] = null;
    $params['aisVersionIncompatible'] = false;
    $params['loggedIn'] = false;
    $params['developmentVersion'] = $this->config->get(FajrConfigOptions::IS_DEVEL);
    
    $params['statistics'] = Statistics::getInstance();
    $params['warnings'] = Warnings::getInstance();
    
    $this->displayManager->setDefaultParams($params);
  }
  
  private function adjustResponse(\Symfony\Component\HttpFoundation\Response $response) {
    $pageMovedCookie = isset($_COOKIE['FajrPageMoved']);
    if ($pageMovedCookie) {
      $response->headers->clearCookie('FajrPageMoved','/','fajr.fmph.uniba.sk');
    }
    
    // https://developer.mozilla.org/en/The_X-FRAME-OPTIONS_response_header
    $response->headers->set('X-Frame-Options', 'DENY');
    if (FajrUtils::isHTTPS()) {
      $hstsExpireTime = $this->config->get(FajrConfigOptions::STRICT_TRANSPORT_SECURITY);
      if ($hstsExpireTime !== null && intval($hstsExpireTime) > 0) {
        $response->headers->set('Strict-Transport-Security', 'max-age='.intval($hstsExpireTime));
      }
    }
  }

  public function runLogic(Trace $trace)
  {
    try {
      $params = $this->router->routeCurrentRequest();
    }
    catch (ResourceNotFoundException $e) {
      return $this->displayManager->renderResponse('notfound', array(), 'html', 404);
    }
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
    
    $loggedIn = $loginManager->isLoggedIn($serverConnection);
    if (!$loggedIn) {
      try {
        $loggedIn = $loginManager->relogin();
      }
      catch (ReloginFailedException $ex) {
        $loginManager->destroySession();
        return new \Symfony\Component\HttpFoundation\RedirectResponse($this->router->generateUrl('homepage'));
      }
    }
    $this->displayManager->setDefaultParams(array('loggedIn' => $loggedIn));
    
    if ($loggedIn) {
      $backendFactory = BackendProvider::getInstance();
      $mainScreen = $backendFactory->newAIS2MainScreen();

      if (($aisVersion = $session->read('ais/aisVersion')) == null) {
        $aisVersion = $mainScreen->getAisVersion($trace->addChild('Get AIS version'));
        $session->write('ais/aisVersion', $aisVersion);
      }
      $this->displayManager->setDefaultParams(array(
        'aisVersion' => $aisVersion,
        'aisVersionIncompatible' =>
        !($aisVersion >= regression\VersionRange::getMinVersion() &&
          $aisVersion <= regression\VersionRange::getMaxVersion())));

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
      $this->displayManager->setDefaultParams(array('aisUserName' => $userName));
    }

    $controller = call_user_func(array($controllerClass, 'getInstance'));
    if (!($controller instanceof Controller)) {
      throw new Exception('Class "' . $controllerClass . '" is not a controller');
    }

    try {
      $subTrace = $trace->addChild('Action ' . $controllerClass . '->' . $action);
      return $controller->invokeAction($subTrace, $action, $this->context);
    }
    catch (AuthenticationRequiredException $ex) {
      return new \Symfony\Component\HttpFoundation\RedirectResponse($this->router->generateUrl('homepage'));
    }
  }
}
