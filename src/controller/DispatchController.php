<?php
/**
 * Tento súbor obsahuje controller fajru, ktorý predáva prácu iným controllerom
 *
 * @copyright  Copyright (c) 2010, 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Controller
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\controller;

use Exception;
use fajr\Context;
use fajr\controller\Controller;
use libfajr\base\DisableEvilCallsObject;
use libfajr\base\Preconditions;
use libfajr\base\Trace;

/**
 * Controller dispatching its request to the appropriate controller
 *
 * @package    Fajr
 * @subpackage Controller
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class DispatchController extends DisableEvilCallsObject implements Controller
{
  /* TODO document */
  public static function getInstance()
  {
    $dispatchMap = array(
      'studium' => '\fajr\controller\studium\StudiumController',
      'predmety' => '\fajr\controller\predmety\PredmetyController',
      'userSettings' => '\fajr\controller\user\UserSettingsController',
      'login' => '\fajr\controller\user\LoginController',
      'static' => '\fajr\controller\StaticController',
    );
    return new DispatchController($dispatchMap);
  }

  /** @var array(string=>string) lookup table for class names */
  private $classNameTable;

  /**
   * Construct a new DispatchController
   *
   * @param array(string=>string) $classNameTable mapping from names to classes
   */
  public function __construct(array $classNameTable)
  {
    $this->classNameTable = $classNameTable;
  }

  /**
   * Invoke an action given its name
   *
   * This function lookups the controller to be used in a lookup table,
   * tries to instantiate it and dispatch the request
   *
   * @param Trace $trace trace object
   * @param string $action action name
   * @param Context $context fajr context
   */
  public function invokeAction(Trace $trace, $action, Context $context)
  {
    Preconditions::checkIsString($action, '$action should be string!');

    $parts = explode('.', $action, 2);

    if (count($parts) != 2) {
      throw new Exception('Action name does not contain "."');
    }

    $controllerName = $parts[0];
    $subaction = $parts[1];
    if (empty($this->classNameTable[$controllerName])) {
      throw new Exception('Could not find a mapping for action ' . $action);
    }

    $class = $this->classNameTable[$controllerName];

    $instance = call_user_func(array($class, 'getInstance'));

    if (!($instance instanceof Controller)) {
      throw new Exception('Class "' . $controllerName . '" mapped to action "' . 
                          $action . '" is not a controller');
    }

    $instance->invokeAction($trace->addChild('Target action ' . $subaction),
                            $subaction, $context);
  }

}
