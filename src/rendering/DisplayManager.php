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
  
  /**
   * Generate a page content
   *
   * @param Response $response response data to use to generate output
   *
   * @returns string Generated output to be sent to the browser
   */
  public function display(Response $response)
  {
    Preconditions::checkNotNull($response->getTemplate(), "Template not set");
    
    $format = $response->getFormat();
    
    if ($format == 'json') {
      header('Content-type: application/json');
    }

    $templateName = 'pages/' . $response->getTemplate() . '.' . $format . '.twig';
    $template = $this->twig->loadTemplate($templateName);

    $output = $template->render($response->getData());

    return $output;
  }
}

?>
