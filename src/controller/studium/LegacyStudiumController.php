<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Tento súbor obsahuje controller, ktorý obsahuje starý kód z Fajr.php
 *
 * @package    Fajr
 * @subpackage Controller__Studium
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @author     Martin Králik <majak47@gmail.com>
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr\controller\studium;

use fajr\libfajr\base\Preconditions;
use fajr\libfajr\pub\base\Trace;
use fajr\controller\BaseController;
use fajr\Request;
use fajr\Response;
use fajr\htmlgen\Table;
use fajr\htmlgen\Collapsible;
use fajr\htmlgen\HtmlHeader;
use fajr\htmlgen\Container;
use fajr\htmlgen\Label;
use fajr\TabManager;
use fajr\TableDefinitions;
use fajr\Input;
use fajr\PriemeryCalculator;
use fajr\Sorter;
use fajr\FajrUtils;

/**
 * Controller na stare veci, postupne zmizne
 *
 * @package    Fajr
 * @subpackage Controller__Studium
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @deprecated
 */
class LegacyStudiumController extends StudiumController
{

  /**
   * Legacy code from Fajr.php
   *
   * @param Trace $trace trace object
   * @param Request $request request from browser
   * @param Response $response response information
   */
  public function runLegacy(Trace $trace, Request $request, Response $response)
  {
    $tab = $request->getParameter('tab', 'TerminyHodnotenia');

    // temporary fix so I can test the app

    if ($tab == 'TerminyHodnotenia') {
      $this->runMojeTerminyHodnotenia($trace->addChild('sub action'), $request, $response);
    }
    else if ($tab == 'ZapisSkusok') {
      $this->runZoznamTerminov($trace, $request, $response);
    }
    else if ($tab == 'ZapisnyList') {
      $this->runZapisanePredmety($trace, $request, $response);
    }
    else if ($tab == 'Hodnotenia') {
      $this->runHodnotenia($trace, $request, $response);
    }
    else if ($tab == 'OdhlasZoSkusky') {
      $this->runOdhlasZoSkusky($trace, $request, $response);
    }
    else if ($tab == 'PrihlasNaSkusku') {
      $this->runPrihlasNaSkusku($trace, $request, $response);
    } 
    
  }

}