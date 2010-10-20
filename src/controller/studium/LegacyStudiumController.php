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
    $response->setTemplate('legacy');

    $tab = $request->getParameter('tab', 'TerminyHodnotenia');
    $response->set('tab', $tab); // TODO remove this

    $tabs = new TabManager('tab', array('studium'=>$this->studium,
          'list'=>$this->zapisnyList));


    $tabs->addTab('TerminyHodnotenia', 'Moje skúšky', new Label(''));
    $tabs->addTab('ZapisSkusok', 'Prihlásenie na skúšky', new Label(''));
    $tabs->addTab('ZapisnyList', 'Zápisný list', new Label(''));
    $tabs->addTab('Hodnotenia', 'Hodnotenia/Priemery', new Label(''));

    $tabs->setActive($tab);

    $response->addContent($tabs->getHtml());

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

  public function runZoznamTerminov(Trace $trace, Request $request, Response $response) {
    parent::runZoznamTerminov($trace, $request, $response);

    $baseUrlParams = array("studium"=>Input::get("studium"),
          "list"=>Input::get("list"),
          "tab"=>Input::get("tab"));

    $actionUrl = FajrUtils::linkUrl($baseUrlParams);

    $terminyTable = new
      Table(TableDefinitions::vyberTerminuHodnoteniaJoined(), array('termin'=>'index',
            'predmet'=>'predmetIndex'), $baseUrlParams);

    $terminyCollapsible = new Collapsible(new HtmlHeader('Termíny, na ktoré sa môžem prihlásiť'),
      $terminyTable);

    foreach ($this->terminyData as $row) {
      $mozeSaPrihlasit = $row['mozeSaPrihlasit'];
      if ($mozeSaPrihlasit == self::PRIHLASIT_MOZE ||
            $mozeSaPrihlasit == self::PRIHLASIT_MOZE_ZNAMKA) {
        $row['prihlas']="<form method='post' action='$actionUrl'><div>
            <input type='hidden' name='action' value='prihlasNaSkusku'/>
            <input type='hidden' name='prihlasPredmetIndex'
            value='".$row['predmetIndex']."'/>
            <input type='hidden' name='prihlasTerminIndex'
            value='".$row['index']."'/>
            <input type='hidden' name='hash' value='$hash'/>
            <button name='submit' type='submit' class='tableButton positive'>
              <img src='images/add.png' alt=''>Prihlás ma!
            </button></div></form>";
        if ($mozeSaPrihlasit == self::PRIHLASIT_MOZE_ZNAMKA) {
          $row['prihlas'] = 'Už máš zápísané"'.
            $hodnoteniaData[$row['predmet']]['znamka'].'"'.$row['prihlas'];
        }
      } else if ($mozeSaPrihlasit == self::PRIHLASIT_NEMOZE_CAS) {
        $row['prihlas'] = 'Nedá sa (neskoro)';
      } else if ($mozeSaPrihlasit == self::PRIHLASIT_NEMOZE_POCET) {
        $row['prihlas'] = 'Termín je plný!';
      } else if ($mozeSaPrihlasit == self::PRIHLASIT_NEMOZE_ZNAMKA) {
        $row['prihlas'] = 'Už máš zápísané"'.$row['znamka'].'"';
      } else if ($mozeSaPrihlasit == self::PRIHLASIT_NEMOZE_INE) {
        $row['prihlas'] = 'Nedá sa, dôvod neznámy';
      }
      $terminyTable->addRow($row, null);
    }

    if (Input::get('termin')!=null && Input::get('predmet')!=null) {
      $terminyTable->setOption('selected_key',
          array('index'=>Input::get('termin'),
            'predmetIndex'=>Input::get('predmet')));
    }

    $response->addContent($terminyCollapsible->getHtml());

    if ($this->prihlaseni != null) {
      $zoznamPrihlasenychTable =  new
      Table(TableDefinitions::zoznamPrihlasenych(), null, array('studium', 'list'));

      $zoznamPrihlasenychCollapsible = new Collapsible(
          new HtmlHeader('Zoznam prihlásených na vybratý termín'),
          $zoznamPrihlasenychTable);

      $zoznamPrihlasenychTable->addRows($this->prihlaseni->getData());
      $response->addContent($zoznamPrihlasenychCollapsible->getHtml());
    }

  }

}