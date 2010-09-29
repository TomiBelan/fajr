<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Tento súbor obsahuje objekt reprezentujúci odpoveď
 *
 * @package    Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr;

use fajr\libfajr\base\Preconditions;

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
    Preconditions::checkIsString($template, 'template');
    $this->template = $template;
  }



}