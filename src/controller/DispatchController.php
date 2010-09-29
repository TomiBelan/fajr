<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Tento súbor obsahuje controller fajru, ktorý predáva prácu iným controllerom
 *
 * @package    Fajr
 * @subpackage Controller
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\controller;

use fajr\libfajr\base\Preconditions;
use ReflectionMethod;

/**
 * Controller dispatching its request to the appropriate controller
 *
 * @package    Fajr
 * @subpackage Controller
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class DispatchController
{

  /** @var array(string=>string) lookup table for class names */
  private $classNameTable;

  /**
   * Invoke an action given its name
   *
   * This function lookups the controller to be used in a lookup table,
   * tries to instantiate it and dispatch the request
   *
   * @param string $action action name
   * @param Request $request request from browser
   * @param Response $response response information
   */
  public function invokeAction($action, Request $request, Response $response)
  {
    Preconditions::checkIsString($action);

    $parts = explode('/', $action, 2);

    if (count($parts) != 2) {
      throw new Exception('Action name does not contain /');
    }

    if (empty($this->classNameTable[$parts[0]])) {
      throw new Exception('Could not find a mapping for action ' . $action);
    }

    $className = $this->classNameTable[$parts[0]];

    $instance = new $className();

    if (!($instance instanceof Controller)) {
      throw new Exception('Class mapped to action '.$action.' is not a controller');
    }

    $instance->invokeAction($parts[1], $request, $response);
  }

}