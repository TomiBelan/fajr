<?php
/**
 * Tento súbor obsahuje interface pre controller
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Controller
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\controller;

use fajr\Request;
use libfajr\trace\Trace;

/**
 * Interface which all controllers implement
 *
 * @package    Fajr
 * @subpackage Controller
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
interface Controller
{

  /**
   * Invoke an action given its name
   *
   * @param Trace   $trace
   * @param string  $action name of action to invoke
   * @param Request $request incoming request
   */
  public function invokeAction(Trace $trace, $action, Request $context);

}
