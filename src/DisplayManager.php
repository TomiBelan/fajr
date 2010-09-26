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

class DisplayManager
{
  protected $content = array();

  protected $data = array();
  
  protected $base = null;

  private static $nextHtmlId = 1;
  
  public function setBase($base)
  {
    $this->base = $base;
  }

  public function addContent($content, $predefinedContent = false)
  {
    if ($predefinedContent) $this->content[] = self::$predefinedContent[$content];
    else $this->content[] = $content;
  }

  public function addException($ex)
  {
    $stackTrace = '';
    if (FajrConfig::get('Debug.Exception.ShowStacktrace')) {
      $stackTrace = "\n<b>Stacktrace:</b>\n" . hescape($ex->getTraceAsString());
      $stackTrace = nl2br($stackTrace);
    }
    $info = '<h2>Pri spracúvaní požiadavky nastala chyba:</h2>';
    $info .= nl2br(hescape($ex->getMessage()));
    $this->addContent('<div class="error">' . $info . $stackTrace . '</div>');
  }

  /**
   * Set a variable to be available to the display subsystem
   * @param string $name Name of the variable to be available as
   * @param mixed $value Value
   */
  public function set($name, $value)
  {
    $this->data[$name] = $value;
  }

  /**
   * Generate a page content
   * @param string $pageName Name of the page (template) to display
   * @return string Generated output to be sent to the browser
   */
  public function display($pageName=null)
  {
    $templateDir = FajrUtils::joinPath(__DIR__, 'templates/fajr');
    $loader = new Twig_Loader_Filesystem($templateDir);
    $twig = new Twig_Environment($loader);
    $twig->addExtension(new Twig_Extension_Escaper());

    if ($pageName === null) {
      $templateName = 'pages/legacy.xhtml';
    }
    else {
      $templateName = 'pages/'.$pageName.'.xhtml';
    }

    $template = $twig->loadTemplate($templateName);

    $html = '';
    foreach ($this->content as $item) $html .= $item;

    $output = $template->render(array_merge(array(
      'legacy_content'=>$html,
      'base'=>$this->base,
      'debug_banner'=>FajrConfig::get('Debug.Banner'),
      'google_analytics'=>FajrConfig::get('GoogleAnalytics.Account'),
      'fajr_version'=>Version::getVersionString(),
      'fajr_changelog'=>Version::getChangelog(),
      ), $this->data));
    
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
