<?php
/**
 * Display manager provides a way to render a Response
 * @copyright  Copyright (c) 2010-2012 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace fajr\rendering;

use Twig_Environment;
use Twig_Extension_Escaper;
use Twig_Loader_Filesystem;
use Twig_Function_Function;
use fajr\rendering\FajrExtension;
use fajr\config\FajrConfig;
use fajr\Router;
use libfajr\base\Preconditions;
use fajr\config\SkinConfig;
use fajr\Response;
use fajr\config\FajrConfigLoader;
use fajr\config\FajrConfigOptions;
use fajr\settings\SkinSettings;

/**
 * Display manager provides a way to render a Response
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Králik <majak47@gmail.com>
 */
class DisplayManager
{
  /** @var DisplayManager $instance */
  private static $instance;

  /* TODO document */
  public static function getInstance()
  {
    if (!isset(self::$instance)) {
      $config = FajrConfigLoader::getConfiguration();
      
      $twigOptions = array(
        'cache' => ($config->get(FajrConfigOptions::USE_CACHE) ?
          $config->getDirectory(FajrConfigOptions::PATH_TO_TEMPLATE_CACHE) :
          false),
        'strict_variables' => true
      );
      
      $router = Router::getInstance();
      $skinSettings = SkinSettings::getInstance();
      
      $twig = new Twig_Environment(null, $twigOptions);
      $twig->addExtension(new Twig_Extension_Escaper());
      $twig->addExtension(new FajrExtension($router));
      
      self::$instance = new DisplayManager($twig);
      self::$instance->setSkin($skinSettings->getUserSkin());
    }
    return self::$instance;
  }

  /** @var Twig_Environment */
  private $twig;
  
  /** @var array */
  private $defaultParams = array();
  
  /**
   * Construct a DisplayManager using Twig_Environment
   * @param Twig_Environment $twig
   */
  public function __construct(Twig_Environment $twig)
  {
    $this->twig = $twig;
  }
  
  public function setSkin(SkinConfig $skin)
  {
    $this->twig->setLoader(new Twig_Loader_Filesystem($skin->getAllPaths()));
  }
  
  public function setDefaultParams(array $params) {
    $this->defaultParams = array_merge($this->defaultParams, $params);
  }
  
  /**
   * Generate a page content
   * 
   * @param string $template name of the template to render
   * @param array $params parameters to use when generating the template
   * @param string|null template format to render, if null use html
   * @return string rendered template output
   */
  public function render($template, array $params = array(), $format=null) {
    Preconditions::checkIsString($template, 'Template name must be string');
    if ($format == null) {
      $format = 'html';
    }
    Preconditions::checkIsString($format, 'Format must be string');
    
    $templateName = 'pages/' . $template . '.' . $format . '.twig';
    $template = $this->twig->loadTemplate($templateName);

    return $template->render(array_merge($this->defaultParams, $params));
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
  public function renderResponse($template, array $params = array(), $format = null, $statusCode = 200) {
    Preconditions::checkIsString($template, 'Template name must be string');
    if ($format == null) {
      $format = 'html';
    }
    Preconditions::checkIsString($format, 'Format must be string');
    
    $output = $this->render($template, $params, $format);
    $response = new \Symfony\Component\HttpFoundation\Response($output, $statusCode);
    
    if ($format == 'html') {
      $response->headers->set('Content-Type', 'text/html');
    }
    else if ($format == 'json') {
      $response->headers->set('Content-Type', 'application/json');
    }
    else if ($format == 'xml') {
      $response->headers->set('Content-Type', 'application/xml');
    }
    
    return $response;
  }
}

?>
