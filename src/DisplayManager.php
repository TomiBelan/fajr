<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * @author Martin Králik <majak47@gmail.com>
 */
namespace fajr;

use Twig_Loader_Filesystem;
use Twig_Environment;
use Twig_Extension_Escaper;
use fajr\libfajr\base\Preconditions;
use fajr\rendering\Extension;

class DisplayManager
{
  
  protected $base = null;

  private static $nextHtmlId = 10000;
  
  public function setBase($base)
  {
    $this->base = $base;
  }

  /**
   * Generate a page content
   * @param Response $response response data to use to generate output
   * @return string Generated output to be sent to the browser
   */
  public function display(Response $response)
  {
    $templateDir = FajrUtils::joinPath(__DIR__, '../templates/fajr');
    $loader = new Twig_Loader_Filesystem($templateDir);
    $twig = new Twig_Environment($loader, array('base_template_class' => '\fajr\rendering\Template'));
    $twig->addExtension(new Twig_Extension_Escaper());
    // Register fajr's rendering extension
    $twig->addExtension(new Extension());

    if ($response->getTemplate() === null) {
      $templateName = 'pages/legacy.xhtml';
    }
    else {
      $templateName = 'pages/'.$response->getTemplate().'.xhtml';
    }

    $template = $twig->loadTemplate($templateName);

    $html = '';
    foreach ($response->getContent() as $item) $html .= $item;

    $output = $template->render(array_merge(array(
      'legacy_content'=>$html,
      'base'=>$this->base,
      'debug_banner'=>FajrConfig::get('Debug.Banner'),
      'google_analytics'=>FajrConfig::get('GoogleAnalytics.Account'),
      'fajr_version'=>Version::getVersionString(),
      'fajr_changelog'=>Version::getChangelog(),
      ), $response->getData()));
    
    return $output;
  }

  public static function getUniqueHTMLId($idType = 'id')
  {
    $uniquePart = self::$nextHtmlId;
    self::$nextHtmlId += 1;

    return $idType.$uniquePart;
  }
}

?>
