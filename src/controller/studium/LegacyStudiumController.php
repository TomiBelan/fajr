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
use fajr\presentation\HodnoteniaCallback;
use fajr\presentation\MojeTerminyHodnoteniaCallback;
use fajr\presentation\ZapisanePredmetyCallback;
use fajr\presentation\ZoznamTerminovCallback;
use fajr\TabManager;
use fajr\TableDefinitions;
use fajr\Input;

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
    $zoznamStudiiTable = new Table(TableDefinitions::zoznamStudii(), 'studium',
          array('tab' => Input::get('tab')));
    $zoznamStudiiTable->addRows($this->zoznamStudii->getData());
    $zoznamStudiiTable->setOption('selected_key', $this->studium);
    $zoznamStudiiTable->setOption('collapsed', true);

    $zoznamStudiiCollapsible = new Collapsible(new HtmlHeader('Zoznam štúdií'), $zoznamStudiiTable, true);

    $response->addContent($zoznamStudiiCollapsible->getHtml());

    $zapisneListyTable = new Table(TableDefinitions::zoznamZapisnychListov(),
                                  'list',
                                  array('studium' => $this->studium,
                                        'tab'=>Input::get('tab'))
                                  );

    $zapisneListyTable->addRows($this->zapisneListy->getData());
    $zapisneListyTable->setOption('selected_key', $this->zapisnyList);
    $zapisneListyTable->setOption('collapsed', true);

    $zapisneListyCollapsible = new Collapsible(new HtmlHeader('Zoznam zápisných listov'), $zapisneListyTable, true);
    $response->addContent($zapisneListyCollapsible->getHtml());

    $tab = $request->getParameter('tab', 'TerminyHodnotenia');

    $tabs = new TabManager('tab', array('studium'=>$this->studium,
          'list'=>$this->zapisnyList));
    // FIXME: chceme to nejak refaktorovat, aby sme nevytvarali zbytocne
    // objekty, ktore v konstruktore robia requesty
    
    $tabs->addTab('TerminyHodnotenia', 'Moje skúšky',
          new MojeTerminyHodnoteniaCallback($trace, $this->terminyHodnotenia, $this->hodnoteniaScreen));
    $tabs->addTab('ZapisSkusok', 'Prihlásenie na skúšky',
          new ZoznamTerminovCallback($trace, $this->terminyHodnotenia, $this->hodnoteniaScreen));
    $tabs->addTab('ZapisnyList', 'Zápisný list',
          new ZapisanePredmetyCallback($trace, $this->terminyHodnotenia));
    $tabs->addTab('Hodnotenia', 'Hodnotenia/Priemery',
        new HodnoteniaCallback($trace, $this->hodnoteniaScreen));

    $tabs->setActive($tab);
    $response->addContent($tabs->getHtml());

    $response->setTemplate('legacy');
  }

}