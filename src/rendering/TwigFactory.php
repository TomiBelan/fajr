<?php
/**
 * Twig factory can create Twig_Environment which can be used to render
 * provided skin.
 *
 * @copyright  Copyright (c) 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\rendering;

use Twig_Environment;
use Twig_Extension_Escaper;
use Twig_Loader_Filesystem;
use fajr\rendering\FajrExtension;
use fajr\config\SkinConfig;
use fajr\config\FajrConfig;
use fajr\config\FajrConfigLoader;
use fajr\config\FajrConfigOptions;

/**
 * Provides Twig_Environment which can be used to render
 * provided skin.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class TwigFactory {
  /** @var TwigFactory $instance */
  private static $instance;

  /* TODO document */
  public static function getInstance()
  {
    if (!isset(self::$instance)) {
      $config = FajrConfigLoader::getConfiguration();
      $options = array(
        'base_template_class' => '\fajr\rendering\Template',
        'cache' => ($config->get(FajrConfigOptions::USE_CACHE) ?
          $config->getDirectory(FajrConfigOptions::PATH_TO_TEMPLATE_CACHE) :
          false),
        'strict_variables' => true
      );
      $fajrExtension = new FajrExtension();
      $extensions = array(new Twig_Extension_Escaper(), $fajrExtension);
      self::$instance = new TwigFactory($options, $extensions);
    }
    return self::$instance;
  }

  /** @var array options for Twig_Environment */
  private $twigOptions;
  /** @var array twig extensions */
  private $extensions;


  /**
   * @param array $twigEnvironmentOptions options for Twig_Environment
   * @param array $extensions extensions for Twig_Environment
   */
  public function __construct(array $twigEnvironmentOptions, array $extensions) {
    $this->twigOptions = $twigEnvironmentOptions;
    $this->extensions = $extensions;
  }

  /**
   * Provide Twig_Environment configured for rendering specific skin.
   *
   * @param SkinConfig $skin skin to use.
   *
   * @returns Twig_Environment
   */
  public function provideTwigForSkin(SkinConfig $skin) {
    $paths = $skin->getAllPaths();
    $loader = new Twig_Loader_Filesystem($paths);
    $twig = new Twig_Environment($loader, $this->twigOptions);
    foreach ($this->extensions as $extension) {
      $twig->addExtension($extension);
    }
    return $twig;
  }

}
