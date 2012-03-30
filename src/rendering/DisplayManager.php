<?php
/**
 * Display manager provides a way to render a Response
 * @copyright  Copyright (c) 2010-2011 The Fajr authors (see AUTHORS).
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
      $skins = $config->get(FajrConfigOptions::TEMPLATE_SKINS);
      $skinName = $config->get(FajrConfigOptions::TEMPLATE_DEFAULT_SKIN);
      if (!isset($skins, $skinName)) {
        throw new RuntimeException("Default skin is not present!");
      }
      $twigOptions = array(
        'cache' => ($config->get(FajrConfigOptions::USE_CACHE) ?
          $config->getDirectory(FajrConfigOptions::PATH_TO_TEMPLATE_CACHE) :
          false),
        'strict_variables' => true
      );
      
      $router = Router::getInstance();
      
      self::$instance = new DisplayManager($twigOptions, $skins[$skinName], $router);
    }
    return self::$instance;
  }

  /** @var array */
  private $twigOptions;
  /** @var SkinConfig */
  private $defaultSkin;
  /** @var Router */
  private $router;

  /**
   * Construct a DisplayManager using Twig_Environment
   * @param Twig_Environment $twig
   * @param SkinConfig skin for which we are going to render templates.
   */
  public function __construct(array $twigOptions, SkinConfig $defaultSkin,
      Router $router)
  {
    $this->twigOptions = $twigOptions;
    $this->defaultSkin = $defaultSkin;
    $this->router = $router;
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
    if ($response->getSkin()) {
      $skin = $response->getSkin();
    } else {
      $skin = $this->defaultSkin;
    }
    
    $loader = new Twig_Loader_Filesystem($skin->getAllPaths());
    $twig = new Twig_Environment($loader, $this->twigOptions);
    $twig->addExtension(new Twig_Extension_Escaper());
    $twig->addExtension(new FajrExtension($this->router));
    
    $format = $response->getFormat();
    
    if ($format == 'json') {
      header('Content-type: application/json');
    }

    $templateName = 'pages/' . $response->getTemplate() . '.' . $format;
    $template = $twig->loadTemplate($templateName);

    $output = $template->render($response->getData());

    return $output;
  }
}

?>
