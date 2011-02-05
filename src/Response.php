<?php
/**
 * Tento súbor obsahuje objekt reprezentujúci odpoveď
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr;

use fajr\libfajr\base\Preconditions;
use fajr\config\SkinConfig;
use fajr\util\FajrUtils;

/**
 * Class for holding response information
 *
 * @package    Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class Response
{

  /** @var string template name to be used */
  private $template = null;

  /** SkinConfig skin configuration */
  private $skin = null;

  protected $data = array('warnings' => array());

  /**
   * Set a variable to be available to the display subsystem
   * @param string $name Name of the variable to be available as
   * @param mixed $value Value
   */
  public function set($name, $value)
  {
    Preconditions::checkIsString($name, '$name should be string.');
    $this->data[$name] = $value;
  }

  /**
   * Return all data associated with this response as array
   *
   * @return array(key=>value) response data
   */
  public function getData()
  {
    return $this->data;
  }

  /**
   * Return a template name to be used
   *
   * @return string template name
   */
  public function getTemplate()
  {
    return $this->template;
  }

  /**
   * Set a template name to be used
   *
   * Note that the rendering subsystem may choose the actual template
   * used based on other parameters as well.
   *
   * @param string $template template name
   */
  public function setTemplate($template)
  {
    Preconditions::checkIsString($template, '$template should be string.');
    $this->template = $template;
  }

  public function addWarning($message)
  {
    $this->data['warnings'][] = $message;
  }

  public function setSkin(SkinConfig $skin)
  {
    $this->skin = $skin;
  }

  public function getSkin()
  {
    return $this->skin;
  }

  /**
   * Sends redirect headers.
   *
   * Note that this will not end script execution!
   *
   * @param array $parameters query params
   * @param string $file file to which redirect
   * @todo set http response code to 302/303.
   *
   * @returns void
   */
  public function redirect($parameters = array(), $file = 'fajr.php')
  {
    $url = FajrUtils::buildUrl($parameters, $file);
    // Note: It is tempting to end script execution here.
    // However, it is not wise. Calling exit() will start
    // php shutdown phase and according to manual
    // there is unpredictable object destruction order
    // in this phase
    header('Location: ' . $url);
    $this->set('redirectUrl', $url);
    $this->setTemplate('redirect');
  }
}
