<?php
/**
 * Tento súbor obsahuje controller, ktorý implementuje základ časti pre predmety
 *
 * @copyright  Copyright (c) 2010-2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Controller__Predmety
 * @author     Tomi Belan <tomi.belan@gmail.com>
 * @filesource
 */
namespace fajr\controller\predmety;

use Exception;
use fajr\Context;
use fajr\controller\BaseController;
use libfajr\AIS2Utils;
use libfajr\base\Preconditions;
use libfajr\trace\Trace;
use libfajr\window\AIS2ApplicationEnum;
use libfajr\window\predmety as VSST060;
use libfajr\regression;
use fajr\Request;
use fajr\Response;
use fajr\Sorter;
use fajr\BackendProvider;
use fajr\util\FajrUtils;
use libfajr\data\InformacnyListParser;
use fajr\Router;
use fajr\rendering\DisplayManager;

/**
 * Controller, ktory sa stara o register predmetov
 *
 * @package    Fajr
 * @subpackage Controller__Predmety
 * @author     Tomi Belan <tomi.belan@gmail.com>
 */
class PredmetyController extends BaseController
{
  public static function getInstance()
  {
    $backendFactory = BackendProvider::getInstance();
    return new PredmetyController($backendFactory->newVSST060Factory(), $backendFactory->getServerTime(),
        DisplayManager::getInstance(), Router::getInstance());
  }

  // @private
  private $registerPredmetovScreen;

  private $factory;
  private $serverTime;

  public function __construct(VSST060\PredmetyFactory $factory, $serverTime,
      DisplayManager $displayManager, Router $router)
  {
    parent::__construct($displayManager, $router);
    $this->factory = $factory;
    $this->serverTime = $serverTime;
  }

  /**
   * Invoke an action given its name
   *
   * This function requests information necessary to operate on
   * VSST060 AIS application
   *
   * @param Trace $trace trace object
   * @param string $action action name
   * @param Request $request incoming request
   */
  public function invokeAction(Trace $trace, $action, Request $request)
  {
    Preconditions::checkIsString($action);
    Preconditions::checkNotNull($request);
    
    $screenFactory = $this->factory;
    $register = $screenFactory->newRegisterPredmetovScreen($trace);
    
    $this->registerPredmetovScreen = $register;

    $result = parent::invokeAction($trace, $action, $request);
    if ($this->registerPredmetovScreen) $this->registerPredmetovScreen->closeWindow();
    return $result;
  }

  public function runInformacnyList(Trace $trace, Request $request) {
    $searchCode = $request->getParameter('code');
    $format = $request->getParameter('format');

    Preconditions::check(!empty($searchCode), "Nezadaný kód predmetu!");

    // Zistime aktualny akad rok.
    $akadRok = FajrUtils::getAcademicYear();
    $content = $this->registerPredmetovScreen->getInformacnyList($trace, $searchCode, $akadRok);
    
    // Docasny fix, vrati PDF s informacnym listom
    $response = new \Symfony\Component\HttpFoundation\Response($content, 200);
    $response->headers->set('Content-Type', 'application/pdf');
    return $response;

    $ip = new InformacnyListParser();
    $list = $ip->parse($trace, $content);
    
    $params = array();
    $params['list'] = $list->getAllAttributes();
    $params['code'] = $searchCode;
    $name = $list->getAttribute('nazov');
    $code = $list->getAttribute('kod');
    if ($code === false) {
      $code = $searchCode;
    }
    else {
      $code = $code['values'][0];
    }
    if ($name === false) {
      $name = 'Predmet '.$code;
    }
    else {
      $name = $name['values'][0];
    }
    $params['subjectName'] = $name;
    
    return $this->renderResponse('predmety/informacnyList', $params,
        ($format == 'json' ? 'json' : 'html'));
  }
}
