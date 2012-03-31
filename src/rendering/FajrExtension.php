<?php
// Copyright (c) 2010-2012 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Tento súbor obsahuje template Twig extension pre Fajr
 *
 * @package    Fajr
 * @subpackage Rendering
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\rendering;

use Twig_Extension;
use Twig_Function_Method;
use fajr\Router;

/**
 * Provides Fajr customizations for the Twig templating engine
 *
 * @package    Fajr
 * @subpackage Rendering
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class FajrExtension extends Twig_Extension
{
  /** @var int */
  private $nextUniqueId;
  /** @var Router */
  private $router;
  
  function __construct(Router $router)
  {
    $this->router = $router;
  }

  /**
   * Return an extension name
   * @returns string extension name
   */
  public function getName()
  {
    return 'fajr';
  }

  public function getFunctions()
  {
    return array(
      'unique_id' => new Twig_Function_Method($this, 'generateUniqueId'),
      'path' => new Twig_Function_Method($this, 'generatePath'),
      'current_path' => new Twig_Function_Method($this, 'generateCurrentPath'),
    );
  }
  
  /**
   * Generate a new id unique for the output
   * @param string|null $type
   * @return string the new unique id optionally prefixed with $type
   */
  public function generateUniqueId()
  {
    return $this->nextUniqueId++;
  }

  public function generatePath($name, $parameters = array(), $absolute = false) {
    return $this->router->generateUrl($name, $parameters, $absolute);
  }

  public function generateCurrentPath($parameters = array(), $absolute = false) {
    return $this->router->generateUrlForCurrentPage($parameters, $absolute);
  }
}
