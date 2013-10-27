<?php
/**
 * Router.
 *
 * @copyright  Copyright (c) 2012 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */

namespace fajr;

use fajr\config\FajrConfigLoader;
use fajr\config\FajrConfigOptions;
use fajr\util\FajrUtils;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\Routing\Router as SymfonyRouter;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use libfajr\util\StrUtil;
use libfajr\base\IllegalStateException;

/**
 * Router.
 *
 * @package Fajr
 * @author  Martin Sucha <anty.sk@gmail.com>
 */
class Router
{
  /** @var Router $instance */
  private static $instance;

  /** Return an instance of Router
   * @return Router
   */
  public static function getInstance() {
    if (!isset(self::$instance)) {
      $config = FajrConfigLoader::getConfiguration();
      $cachePath = $config->getDirectory(FajrConfigOptions::PATH_TO_ROUTER_CACHE);
      $controllerPath = FajrUtils::joinPath(FajrUtils::getProjectRootDirectory(), '/src/controller');

      $locator = new FileLocator(array($controllerPath));
      
      $request = SymfonyRequest::createFromGlobals();

      $requestContext = new RequestContext();
      $requestContext->fromRequest($request);

      $sfRouter = new SymfonyRouter(
          new YamlFileLoader($locator),
          "routes.yml",
          array('cache_dir' => ($config->get(FajrConfigOptions::USE_CACHE) ? $cachePath : null)),
          $requestContext
      );
      
      self::$instance = new Router($sfRouter, $request);
    }
    return self::$instance;
  }
  
  /** @var Symfony\Component\Routing\Router */
  private $sfRouter;
  
  /** @var string|null */
  private $currentRoute;
  
  private function __construct(SymfonyRouter $sfRouter, SymfonyRequest $request)
  {
    $this->sfRouter = $sfRouter;
    $this->currentRoute = null;
    $this->currentRequest = $request;
  }
  
  /**
   * Resolve current request and remember the matched route
   */
  public function routeCurrentRequest() {
    $path = $this->currentRequest->getPathInfo();
    
    $matched = $this->sfRouter->match($path);
    $this->currentRoute = $matched['_route'];
    
    return $matched;
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
    return $this->sfRouter->generate($name, $parameters, $absolute);
  }

  /**
   * Generate a URL for a page determined by current route
   * @param array $parameters parameters to use
   * @param boolean $absolute if true, absolute URL is generated
   * @return string the generated URL 
   * @throws IllegalStateException if the current route has not been set yet
   * @throws RouteNotFoundException if the route doesn't exist
   */
  public function generateUrlForCurrentPage($parameters=array(), $absolute=false)
  {
    if ($this->currentRoute === null) {
      throw new IllegalStateException('Current route has not been set yet');
    }
    
    return $this->generateUrl($this->currentRoute, $parameters, $absolute);
  }
  
}
