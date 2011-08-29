<?php
/**
 * Contains controller for login/logout
 *
 * @copyright  Copyright (c) 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Controller__Welcome
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 * @filesource
 */
namespace fajr\controller\welcome;

use Exception;
use fajr\Context;
use fajr\controller\BaseController;
use fajr\libfajr\base\Preconditions;
use fajr\libfajr\pub\base\Trace;
use fajr\Request;
use fajr\Response;

/**
 * Controller for displaying public pages
 *
 * @package    Fajr
 * @subpackage Controller__Welcome
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 */
class WelcomeController extends BaseController
{
  
  public function runTermsOfUse(Trace $trace, Context $context)
  {
    $context->getResponse()->setTemplate('termsOfUse');
  }
  
}