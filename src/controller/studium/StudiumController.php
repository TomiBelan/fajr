<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Tento súbor obsahuje controller, ktorý implementuje základ časti pre štúdium
 *
 * @package    Fajr
 * @subpackage Controller__Studium
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\controller\studium;

use fajr\libfajr\base\Preconditions;
use fajr\libfajr\pub\base\Trace;
use fajr\controller\BaseController;
use fajr\Request;
use fajr\Response;
use fajr\libfajr\pub\window\VSES017_administracia_studia as VSES017; // *

/**
 * Controller, ktory nacita informacie o aktualnom studiu
 *
 * @package    Fajr
 * @subpackage Controller__Studium
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
abstract class StudiumController extends BaseController
{

  /** @var VSES017_factory Screen factory to use */
  protected $screenFactory = null;

  /**
   * Invoke an action given its name
   *
   * This function requests information necessary to operate on
   * VSES017 AIS application
   *
   * @param Trace $trace trace object
   * @param string $action action name
   * @param Request $request request from browser
   * @param Response $response response information
   */
  public function invokeAction(Trace $trace, $action, Request $request, Response $response)
  {
    Preconditions::checkIsString($action);

    $this->screenFactory = new VSES017\VSES017_factory($request->getAisConnection());
    $this->adminStudia = $this->screenFactory->newAdministraciaStudiaScreen($trace);

    $this->studium = $request->getParameter('studium', '0');

    $this->zoznamStudii = $this->adminStudia->getZoznamStudii(
                                      $trace->addChild("Get Zoznam Studii:"));

    $this->zapisneListy = $this->adminStudia->getZapisneListy(
                                      $trace->addChild('getZapisneListy'),
                                      $this->studium);

    $this->zapisnyList = $request->getParameter('list');

    if (empty($this->zapisnyList)) {
      $tmp = $this->zapisneListy->getData();
      $lastList = end($tmp);
      $this->zapisnyList = $lastList['index'];
    }

    $this->terminyHodnotenia = $this->screenFactory->newTerminyHodnoteniaScreen(
              $trace,
              $this->adminStudia->getZapisnyListIdFromZapisnyListIndex($trace, $this->zapisnyList),
              $this->adminStudia->getStudiumIdFromZapisnyListIndex($trace, $this->zapisnyList));

    // FIXME: chceme to nejak refaktorovat, aby sme nevytvarali zbytocne
    // objekty, ktore v konstruktore robia requesty
    $this->hodnoteniaScreen = $this->screenFactory->newHodnoteniaPriemeryScreen(
          $trace,
          $this->adminStudia->getZapisnyListIdFromZapisnyListIndex($trace, $this->zapisnyList));

    parent::invokeAction($trace, $action, $request, $response);
  }

}