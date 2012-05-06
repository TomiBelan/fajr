<?php
/**
 * Tento súbor obsahuje základný controller fajru
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
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
use libfajr\trace\Trace;
use fajr\Request;
use fajr\Response;
use ReflectionMethod;
use fajr\rendering\DisplayManager;
use fajr\Router;

/**
 * Base class for controllers
 *
 * @package    Fajr
 * @subpackage Controller
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
abstract class BaseController extends DisableEvilCallsObject implements Controller
{
 
  /** @var DisplayManager */
  protected $displayManager;
  
  /** @var Router */
  protected $router;
  
  function __construct(DisplayManager $displayManager, Router $router)
  {
    $this->displayManager = $displayManager;
    $this->router = $router;
  }

  /**
   * Generate a Response
   * 
   * @param string $template name of the template to render
   * @param array $params parameters to use when generating the template
   * @param string|null response format to render, if null use html
   * @param int statusCode
   * @return Symfony\Component\HttpFoundation\Response rendered response
   */
  public function renderResponse($template, array $params=array(), $format = null, $statusCode = 200)
  {
    return $this->displayManager->renderResponse($template, $params, $format, $statusCode);
  }
  
  
  /**
   * Generate a URL for a page with the given route
   * @param string $name name of the route
   * @param array $parameters parameters to use
   * @param boolean $absolute if true, absolute URL is generated
   * @return string the generated URL 
   * @throws RouteNotFoundException if route doesn't exist
   */
  public function generateUrl($name, $parameters=array(), $absolute=false)
  {
    return $this->router->generateUrl($name, $parameters, $absolute);
  }
  
  /**
   * Invoke an action given its name
   *
   * This function checks if public non-abstract non-static runAction method
   * exists in this object and calls it in such a case with request and response
   * parameters
   *
   * @param Trace $trace trace object
   * @param string $action action name
   * @param Context $context fajr context
   */
  public function invokeAction(Trace $trace, $action, Context $context)
  {
    Preconditions::checkIsString($action);
    Preconditions::checkMatchesPattern('@^[A-Z][a-zA-Z]*$@', $action,
        '$action must start with capital letter and ' .
        'contain only letters.');

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

    return $method->invoke($this, $trace, $context);
  }

}
