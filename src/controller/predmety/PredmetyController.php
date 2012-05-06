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
   * @param Context $context fajr context
   */
  public function invokeAction(Trace $trace, $action, Context $context)
  {
    Preconditions::checkIsString($action);

    $request = $context->getRequest();
    $session = $context->getSessionStorage();
    Preconditions::checkNotNull($request);
    Preconditions::checkNotNull($session);
    $screenFactory = $this->factory;
    $register = $screenFactory->newRegisterPredmetovScreen($trace);
    
    $this->registerPredmetovScreen = $register;

    return parent::invokeAction($trace, $action, $context);
  }

  public function runInformacnyList(Trace $trace, Context $context) {
    $request = $context->getRequest();
    
    $searchCode = $request->getParameter('code');
    $format = $request->getParameter('format');

    Preconditions::check(!empty($searchCode), "Nezadaný kód predmetu!");

    $content = $this->registerPredmetovScreen->getInformacnyList($trace, $searchCode);
    
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
