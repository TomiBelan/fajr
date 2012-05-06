<?php
/**
 * Contains controllers for static pages
 *
 * @copyright  Copyright (c) 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Controller__Static
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 * @filesource
 */
namespace fajr\controller;

use Exception;
use fajr\Context;
use fajr\controller\BaseController;
use libfajr\base\Preconditions;
use libfajr\trace\Trace;
use fajr\Request;
use fajr\rendering\DisplayManager;
use fajr\Router;

/**
 * Controller for displaying static public pages
 *
 * @package    Fajr
 * @subpackage Controller__Static
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 */
class StaticController extends BaseController
{
  public static function getInstance()
  {
    return new StaticController(DisplayManager::getInstance(), Router::getInstance());
  }
  
  public function __construct(DisplayManager $displayManager, Router $router)
  {
    parent::__construct($displayManager, $router);
  }
  
  public function runTermsOfUse(Trace $trace, Context $context)
  {
    return $this->renderResponse('termsOfUse');
  }
  
  public function runAbout(Trace $trace, Context $context)
  {
    return $this->renderResponse('about');
  }
  
}
