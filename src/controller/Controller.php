<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Tento súbor obsahuje interface pre controller
 *
 * @package    Fajr
 * @subpackage Controller
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\controller;

use fajr\libfajr\pub\base\Trace;
use fajr\Request;
use fajr\Response;

/**
 * Provides Fajr template customizations for the Twig templating engine
 *
 * @package    Fajr
 * @subpackage Controller
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
interface Controller {

  /**
   * Invoke an action given its name
   *
   * @param Trace $trace
   * @param string $action name of action to invoke
   * @param Request $request request from browser
   * @param Response $response response information
   */
  public function invokeAction(Trace $trace, $action, Request $request, Response $response);

}