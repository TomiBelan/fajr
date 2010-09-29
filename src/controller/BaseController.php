<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Tento súbor obsahuje základný controller fajru
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
 * Base class for controllers
 *
 * @package    Fajr
 * @subpackage Controller
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
abstract class BaseController
{
 
  /**
   * Invoke an action given its name
   *
   * This function checks if public non-abstract non-static runAction method
   * exists in this object and calls it in such a case with request and response
   * parameters
   *
   * @param string $action action name
   * @param Request $request request from browser
   * @param Response $response response information
   */
  public function invokeAction($action, Request $request, Response $response) {
    Preconditions::checkMatchesPattern('@^[A-Z][a-zA-Z]*$@', $action, 'action');

    $methodName = 'run'.$action;

    if (!method_exists($this, $methodName)) {
      throw new Exception('Action method '.$methodName.' does not exist');
    }

    $method = new ReflectionMethod($this, $methodName);

    if (!$method->isPublic()) {
      throw new Exception('Action method '.$methodName.' is not public');
    }

    if ($method->isAbstract()) {
      throw new Exception('Action method '.$methodName.' is abstract');
    }

    if ($method->isStatic()) {
      throw new Exception('Action method '.$methodName.' is static');
    }

    if ($method->isConstructor()) {
      throw new Exception('Action method '.$methodName.' is constructor');
    }

    if ($method->isDestructor()) {
      throw new Exception('Action method '.$methodName.' is destructor');
    }

    $method->invoke($this, $request, $response);
  }

}