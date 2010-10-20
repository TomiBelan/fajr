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
    $response->set('tab', $request->getParameter('tab')); // TODO remove this

    $tab = $request->getParameter('tab', 'TerminyHodnotenia');

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

  /**
   * Akcia pre hodnotenia a priemery, stary kod na generovanie HTML
   *
   * @param Trace $trace trace object
   * @param Request $request request from browser
   * @param Response $response response information
   */
  public function runHodnotenia(Trace $trace, Request $request, Response $response) {
    parent::runHodnotenia($trace, $request, $response);

    $hodnoteniaTable = new Table(TableDefinitions::hodnotenia());

    foreach($this->hodnoteniaData as $row) {
      if ($row['semester']=='L') {
        $class='leto';
      }
      else {
        $class='zima';
      }
      $hodnoteniaTable->addRow($row, array('class'=>$class));
    }

    $hodnoteniaCollapsible = new Collapsible(new HtmlHeader('Hodnotenia'), $hodnoteniaTable);

    $priemeryTable = new Table(TableDefinitions::priemery());
    $priemeryTable->addRows($this->priemery->getData());

    $priemeryContainer = new Container();
    $priemeryContainer->addChild(new Label('Nasledovné priemery sú prebraté z AISu, čiže to (ne)funguje presne rovnako:'));
    $priemeryContainer->addChild($priemeryTable);

    if ($this->priemeryCalculator->hasPriemer()) {
      $priemeryFajrText = '<p><br />Nasledovné vážené študijné priemery sú počítané Fajrom priebežne z tabuľky Hodnotenia, <strong>preto nemôžu byť považované ako oficiálne</strong>:<br /><br />';
      $priemeryFajrText .= $this->priemeryCalculator->getHtml();
      $priemeryFajrText .= '</p>';

      $priemeryContainer->addChild(new Label($priemeryFajrText));
    }

    $priemeryCollapsible = new Collapsible(new HtmlHeader('Priemery'), $priemeryContainer);

    $response->addContent($hodnoteniaCollapsible->getHtml().$priemeryCollapsible->getHtml());
  }

  /**
   * Akcia pre zobrazenie mojich terminov hodnotenia, stary kod na generovanie
   * HTML
   *
   * @param Trace $trace trace object
   * @param Request $request request from browser
   * @param Response $response response information
   */
  public function runMojeTerminyHodnotenia(Trace $trace, Request $request, Response $response) {
    parent::runMojeTerminyHodnotenia($trace, $request, $response);

    $baseUrlParams = array("studium"=>Input::get("studium"),
          "list"=>Input::get("list"),
          "tab"=>Input::get("tab"));

    $terminyHodnoteniaTableActive =  new
      Table(TableDefinitions::mojeTerminyHodnotenia(), 'termin', $baseUrlParams);

    $terminyHodnoteniaCollapsibleActive = new Collapsible(
        new HtmlHeader('Aktuálne termíny hodnotenia'),
        $terminyHodnoteniaTableActive);

    $terminyHodnoteniaTableOld =  new
      Table(TableDefinitions::mojeTerminyHodnotenia(), 'termin', $baseUrlParams);

    $terminyHodnoteniaCollapsibleOld = new Collapsible(
        new HtmlHeader('Staré termíny hodnotenia'),
        $terminyHodnoteniaTableOld);

    if (Input::get('termin')!=null) {
      $terminyHodnoteniaTableActive->setOption('selected_key',
          Input::get('termin'));
      $terminyHodnoteniaTableOld->setOption('selected_key',
          Input::get('termin'));
    }

    $baseUrlParams = array("studium"=>Input::get("studium"),
          "list"=>Input::get("list"),
          "tab"=>'PrihlasNaSkusku');

    $actionUrl=FajrUtils::linkUrl($baseUrlParams);

    foreach ($this->terminyHodnoteniaOld as $row) {
      $row['odhlas'] = "Skúška už bola.";
      $terminyHodnoteniaTableOld->addRow($row, null);
    }    

    foreach ($this->terminyHodnoteniaActive as $row) {
      if ($row['mozeOdhlasit'] == 1) {
        $class='terminmozeodhlasit';
        $row['odhlas']="<form method='post' action='$actionUrl'>
          <div>
          <input type='hidden' name='tab' value='OdhlasZoSkusky'/>
          <input type='hidden' name='odhlasIndex'
          value='".$row['index']."'/>
          <input type='hidden' name='hash' value='".$row['hashNaOdhlasenie']."'/>
          <button name='submit' type='submit' class='tableButton negative'>
            <img src='images/cross.png' alt=''>Odhlás
          </button></div></form>";
      } else {
        $row['odhlas']="nedá sa";
        $class='terminnemozeodhlasit';
      }

      if ($row['prihlaseny']!='A') {
        $row['odhlas']='Si odhlásený. Ak chceš, opäť sa prihlás.';
        $class='terminodhlaseny';
      }
      $terminyHodnoteniaTableActive->addRow($row, array('class'=>$class));
    }

    $response->addContent($terminyHodnoteniaCollapsibleActive->getHtml());
    $response->addContent($terminyHodnoteniaCollapsibleOld->getHtml());

    if ($this->prihlaseni != null) {
      $zoznamPrihlasenychTable = new
      Table(TableDefinitions::zoznamPrihlasenych(), null, array('studium', 'list'));
      $zoznamPrihlasenychTable->addRows($this->prihlaseni->getData());
      $zoznamPrihlasenychCollapsible = new Collapsible(new HtmlHeader('Zoznam prihlásených
          na vybratý termín'), $zoznamPrihlasenychTable);
      $response->addContent($zoznamPrihlasenychCollapsible->getHtml());
    }
  }

  public function runZapisanePredmety(Trace $trace, Request $request, Response $response) {
    parent::runZapisanePredmety($trace, $request, $response);

    $predmetyZapisnehoListuTable = new
      Table(TableDefinitions::predmetyZapisnehoListu());
    $predmetyZapisnehoListuCollapsible = new Collapsible(new HtmlHeader('Predmety zápisného listu'),
      $predmetyZapisnehoListuTable);

    foreach ($this->predmetyZapisnehoListuData as $row) {
      if ($row['kodSemester']=='L') {
        $class='leto';
      }
      else {
        $class='zima';
      }
      $predmetyZapisnehoListuTable->addRow($row, array('class'=>$class));
    }

    $pocetPredmetovText = 'Celkom ';
    $pocetPredmetovText .= FajrUtils::formatPlural($this->pocetPredmetovLeto+$this->pocetPredmetovZima,
        '0 predmetov', '1 predmet', '%d predmety', '%d predmetov');
    if ($this->pocetPredmetovLeto > 0 && $this->pocetPredmetovZima > 0) {
      $pocetPredmetovText .= sprintf(' (%d v zime, %d v lete)', $this->pocetPredmetovZima, $this->pocetPredmetovLeto);
    }

    $kreditovCelkomText = ''. ($this->kreditovCelkomLeto+$this->kreditovCelkomZima);
    if ($this->kreditovCelkomLeto > 0 && $this->kreditovCelkomZima > 0) {
      $kreditovCelkomText .= sprintf(' (%d+%d)', $this->kreditovCelkomZima, $this->kreditovCelkomLeto);
    }

    $predmetyZapisnehoListuTable->addFooter(array('nazov'=>$pocetPredmetovText,'kredit'=>$kreditovCelkomText), array());
    $predmetyZapisnehoListuTable->setUrlParams(array('studium' =>
          Input::get('studium'), 'list' => Input::get('list')));

    $response->addContent($predmetyZapisnehoListuTable->getHtml());

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