<?php
/**
 * Tento súbor obsahuje controller, ktorý implementuje základ časti pre štúdium
 *
 * @copyright  Copyright (c) 2010-2012 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Controller__Studium
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr\controller\studium;

use Exception;
use fajr\Context;
use fajr\controller\BaseController;
use fajr\controller\studium\PriemeryCalculator;
use libfajr\AIS2Utils;
use libfajr\base\Preconditions;
use libfajr\trace\Trace;
use libfajr\window\AIS2ApplicationEnum;
use libfajr\window\studium as VSES017;
use libfajr\regression;
use fajr\Request;
use fajr\Sorter;
use fajr\util\FajrUtils;
use fajr\LoginManager;
use fajr\Router;
use fajr\BackendProvider;
use fajr\CalendarProvider;
use fajr\exceptions\AuthenticationRequiredException;
use libfajr\exceptions\ParseException;
use fajr\rendering\DisplayManager;
use fajr\Warnings;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use fajr\SessionStorageProvider;
use fajr\model\CalendarModel;

fields::autoload();

/**
 * Controller, ktory nacita informacie o aktualnom studiu
 *
 * @package    Fajr
 * @subpackage Controller__Studium
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class StudiumController extends BaseController
{
  public static function getInstance()
  {
    $backendFactory = BackendProvider::getInstance();
    return new StudiumController($backendFactory->newVSES017Factory(),
        $backendFactory->getServerTime(),
        LoginManager::getInstance(),
        DisplayManager::getInstance(), Router::getInstance(),
        Warnings::getInstance(), SessionStorageProvider::getInstance());
  }

  // @input
  private $studium;
  private $zapisnyList;
  
  // @private
  private $zapisnyListObj;
  private $zoznamStudii;
  private $zapisneListy;
  private $terminyHodnoteniaScreen;
  private $hodnoteniaScreen;
  private $administraciaStudiaScreen;

  private $factory;
  private $serverTime;
  private $actionInfo;
  
  private $loginManager;
  
  private $templateParams;
  
  /** @var Warnings */
  private $warnings;
  
  /** @var \sfSessionStorage */
  private $session;

  public function __construct(VSES017\StudiumFactory $factory, $serverTime,
      LoginManager $loginManager, DisplayManager $displayManager, Router $router,
      Warnings $warnings, \sfSessionStorage $session)
  {
    parent::__construct($displayManager, $router);
    $this->factory = $factory;
    $this->serverTime = $serverTime;
    $this->actionInfo = array('MojeTerminyHodnotenia' => array('tabName' => 'TerminyHodnotenia', 'requiresZapisnyList' => true),
                              'ZoznamTerminov' => array('tabName' => 'ZapisSkusok', 'requiresZapisnyList' => true),
                              'ZapisanePredmety' => array('tabName' => 'ZapisnyList', 'requiresZapisnyList' => true),
                              'Hodnotenia' => array('tabName' => 'Hodnotenia', 'requiresZapisnyList' => true),
                              'PrehladKreditov' => array('tabName' => 'PrehladKreditov', 'requiresZapisnyList' => false),
                              );
    $this->loginManager = $loginManager;
    $this->templateParams = array();
    $this->warnings = $warnings;
    $this->session = $session;
  }

  /**
   * Invoke an action given its name
   *
   * This function requests information necessary to operate on
   * VSES017 AIS application
   *
   * @param Trace $trace trace object
   * @param string $action action name
   * @param Request $request incoming request
   */
  public function invokeAction(Trace $trace, $action, Request $request)
  {
    Preconditions::checkIsString($action);
    
    if (!$this->loginManager->isLoggedIn()) {
      throw new AuthenticationRequiredException();
    }

    Preconditions::checkNotNull($request);
    // check access to application
    $apps = $this->session->read('ais/aisApps');
    if (!is_array($apps)) {
      throw new Exception("Interná chyba - zoznam AIS aplikácii je nekorektný.");
    }
    if (!in_array(AIS2ApplicationEnum::ADMINISTRACIA_STUDIA,
                  $apps)) {
      return $this->renderResponse('studium/notAvailable');
    }

    $screenFactory = $this->factory;
    $adminStudia = $screenFactory->newAdministraciaStudiaScreen($trace);
    
    $this->administraciaStudiaScreen = $adminStudia;

    $this->studium = $request->getParameter('studium', '0');

    $this->zoznamStudii = $adminStudia->getZoznamStudii(
                                      $trace->addChild("Get Zoznam Studii:"));

    $this->zapisneListy = $adminStudia->getZapisneListy(
                                      $trace->addChild('getZapisneListy'),
                                      $this->studium);

    $this->warnings->warnWrongTableStructure($trace, 'zoznam studii',
        regression\ZoznamStudiiRegression::get(),
        $this->zoznamStudii->getTableDefinition());

    $this->warnings->warnWrongTableStructure($trace, 'zoznam zapisnych listov',
        regression\ZoznamZapisnychListovRegression::get(),
        $this->zapisneListy->getTableDefinition());

    $zapisneListyData = $this->zapisneListy->getData();
    if (count($zapisneListyData) == 0) {
      $this->zapisnyList = null;
      $this->terminyHodnoteniaScreen = null;
      $this->hodnoteniaScreen = null;
      $this->templateParams['zapisnyListObj'] = null;
    }
    else {
      $this->zapisnyList = $request->getParameter('list');

      if ($this->zapisnyList === '') {
        $lastList = end($zapisneListyData);
        $this->zapisnyList = $lastList['index'];
      }
      $this->zapisnyList = intval($this->zapisnyList);
      $this->zapisnyListObj = $zapisneListyData[$this->zapisnyList];
      $this->templateParams['zapisnyListObj'] = $this->zapisnyListObj;

      try {
        $this->terminyHodnoteniaScreen = $screenFactory->newTerminyHodnoteniaScreen(
                $trace,
                $adminStudia->getZapisnyListIdFromZapisnyListIndex($trace, $this->zapisnyList,
                    VSES017\AdministraciaStudiaScreen::ACTION_TERMINY_HODNOTENIA),
                $adminStudia->getStudiumIdFromZapisnyListIndex($trace, $this->zapisnyList,
                    VSES017\AdministraciaStudiaScreen::ACTION_TERMINY_HODNOTENIA));
      } catch (ParseException $e) {
        $this->terminyHodnoteniaScreen = null;
      }

      // FIXME: chceme to nejak refaktorovat, aby sme nevytvarali zbytocne
      // objekty, ktore v konstruktore robia requesty
      $this->hodnoteniaScreen = $screenFactory->newHodnoteniaPriemeryScreen(
            $trace,
            $adminStudia->getZapisnyListIdFromZapisnyListIndex($trace, $this->zapisnyList,
                VSES017\AdministraciaStudiaScreen::ACTION_HODNOTENIA_PRIEMERY));
    }

    $this->templateParams['currentTab'] = '';
    $this->templateParams['zoznamStudii'] = $this->zoznamStudii;
    $this->templateParams['studium'] = $this->studium;
    // TODO(anty): refactor
    $zoznamStudiiData = $this->zoznamStudii->getData();
    $this->templateParams['studiumObj'] = $zoznamStudiiData[$this->studium];
    $this->templateParams['zapisneListy'] = $this->zapisneListy;
    $this->templateParams['zapisnyList'] = $this->zapisnyList;
    // TODO(anty): refactor

    if (array_key_exists($action, $this->actionInfo)) {
      $info = $this->actionInfo[$action];
      if ($info['requiresZapisnyList'] && $this->zapisnyList === null) {
        $this->templateParams['activeTab'] = $info['tabName'];
        return $this->renderResponse('studium/chybaZapisnyList', $this->templateParams);
      }
    }

    return parent::invokeAction($trace, $action, $request);
  }
  
  public function runPrehladKreditov(Trace $trace, Request $request) {
    $format = $request->getParameter('format', 'html');
    
    $prehladKreditovDialog = $this->administraciaStudiaScreen->
        getPrehladKreditovDialog($trace, $this->studium);
    
    $predmety = $prehladKreditovDialog->getPredmety($trace);
    
    $prehladKreditovDialog->closeIfNeeded($trace);
    
    $this->warnings->warnWrongTableStructure($trace, 'prehlad kreditov',
        regression\PrehladKreditovRegression::get(),
        $predmety->getTableDefinition());
    
    $predmetyData = $hodnoteniaData = Sorter::sort($predmety->getData(),
          array("akRok"=>1, "semester"=>-1, "nazov"=>1));
    
    $priemeryCalculator = new PriemeryCalculator();
    
    foreach ($predmetyData as &$predmetyRow) {
      $semester = $predmetyRow['semester'] == 'L' ?
          PriemeryCalculator::SEMESTER_LETNY : PriemeryCalculator::SEMESTER_ZIMNY;
      
      try {
        $predmetyRow['timestamp'] = AIS2Utils::parseAISDate($predmetyRow['datum']);
      }
      catch (\Exception $e) {
        $predmetyRow['timestamp'] = null;
      }
      
      $znamka = $predmetyRow['znamka'];
      $priemeryCalculator->add($semester, $znamka, $predmetyRow[PredmetyFields::KREDIT]);
    }
    
    $this->templateParams['currentTab'] = 'PrehladKreditov';
    $this->templateParams['predmety'] = $predmetyData;
    $this->templateParams['predmetyStatistika'] = $priemeryCalculator;
    return $this->renderResponse('studium/prehladKreditov', $this->templateParams,
        ($format == 'xml' ? 'xml' : 'html'));
  }

  /**
   * Akcia pre hodnotenia a priemery
   *
   * @param Trace $trace trace object
   * @param Request $request
   */
  public function runHodnotenia(Trace $trace, Request $request) {
    $format = $request->getParameter('format', 'html');
    
    $priemeryCalculator = new PriemeryCalculator();
    
    $hodnotenia = $this->hodnoteniaScreen->getHodnotenia($trace->addChild('get hodnotenia'));

    $this->warnings->warnWrongTableStructure($trace, 'hodnotenia',
        regression\HodnoteniaRegression::get(),
        $hodnotenia->getTableDefinition());

    $hodnoteniaData = Sorter::sort($hodnotenia->getData(),
          array("semester"=>-1, "nazov"=>1));

    foreach($hodnoteniaData as &$hodnoteniaRow) {
      $semester = $hodnoteniaRow[HodnoteniaFields::SEMESTER] == 'L' ?
          PriemeryCalculator::SEMESTER_LETNY : PriemeryCalculator::SEMESTER_ZIMNY;
      try {
        $hodnoteniaRow['timestamp'] = AIS2Utils::parseAISDate($hodnoteniaRow['datum']);
      }
      catch (\Exception $e) {
        $hodnoteniaRow['timestamp'] = null;
      }
      $hodnoteniaRow['akRok'] = $this->zapisnyListObj['popisAkadRok'];

      $priemeryCalculator->add($semester,
                               $hodnoteniaRow[HodnoteniaFields::ZNAMKA],
                               $hodnoteniaRow[HodnoteniaFields::KREDIT]);
    }
    
    $priemery = $this->hodnoteniaScreen->getPriemery($trace->addChild('get priemery'));

    $this->warnings->warnWrongTableStructure($trace, 'priemery',
        regression\PriemeryRegression::get(),
        $priemery->getTableDefinition());

    $this->templateParams['currentTab'] = 'Hodnotenia';
    $this->templateParams['hodnotenia'] = $hodnoteniaData;
    $this->templateParams['priemery'] = $priemery->getData();
    $this->templateParams['priemeryCalculator'] = $priemeryCalculator;
    return $this->renderResponse('studium/hodnotenia', $this->templateParams,
        ($format == 'xml' ? 'xml' : 'html'));
  }


  /**
   * Akcia ktora odhlasi cloveka z danej skusky
   *
   * @param Trace $trace trace object
   * @param Request $request
   */
  public function runOdhlasZoSkusky(Trace $trace, Request $request) {

    if ($this->terminyHodnoteniaScreen == null) {
      return $this->renderResponse('studium/terminyHodnoteniaNedostupne',
          $this->templateParams);
    }

    $terminIndex = $request->getParameter("odhlasIndex");

    $terminy = $this->terminyHodnoteniaScreen
        ->getTerminyHodnotenia($trace->addChild('get terminy hodnotenia '));
    $this->warnings->warnWrongTableStructure($trace, 'moje terminy',
        regression\MojeTerminyRegression::get(),
        $terminy->getTableDefinition());

    $terminyData = $terminy->getData();

    $terminKey = -1;
    foreach ($terminyData as $key=>$row) {
      if ($row['index'] == $terminIndex) $terminKey = $key;
    }

    if ($terminKey == -1) {
      throw new Exception("Ooops, predmet/termín nenájdený. Pravdepodobne
          zmena dát v AISe.");
    }
    if ($request->getParameter("hash") !== StudiumUtils::hashNaOdhlasenie($terminyData[$terminKey])) {
      throw new Exception("Ooops, nesedia údaje o termíne. Pravdepodobne zmena
          dát v AISe spôsobila posunutie tabuliek.");
    }

    if (!$this->terminyHodnoteniaScreen->odhlasZTerminu($trace->addChild('odhlasujem'), $terminIndex)) {
      throw new Exception('Z termínu sa nepodarilo odhlásiť.');
    }

    return new RedirectResponse($this->generateUrl('studium_moje_skusky',
        array('studium' => $this->studium, 'list' => $this->zapisnyList), true));
  }
  
  /**
   * Akcia pre zobrazenie kalendara
   *
   * @param Trace $trace trace object
   * @param Request $request request from browser
   */
  public function runKalendar(Trace $trace, Request $request) {

    $this->templateParams['currentTab'] = 'Kalendar';
    
    if ($this->terminyHodnoteniaScreen == null) {
      return $this->renderResponse('studium/terminyHodnoteniaNedostupne',
          $this->templateParams);
    }

    $terminyHodnotenia = $this->terminyHodnoteniaScreen->getTerminyHodnotenia(
        $trace->addChild("get terminy hodnotenia"));
    $this->warnings->warnWrongTableStructure($trace, 'moje terminy hodnotenia',
        regression\MojeTerminyRegression::get(),
        $terminyHodnotenia->getTableDefinition());
    
    $calendarDate = time();
    if ($request->hasParameter('year') && $request->hasParameter('month')) {
      $month = intval($request->getParameter('month'));
      $year = intval($request->getParameter('year'));
      $calendarDate = mktime(0, 0, 0, $month, 1, $year);
    }
    
    $calendarMode = CalendarModel::MODE_WORKWEEK;
    $mode = $request->getParameter('mode');
    if ($mode == 'week') {
      $calendarMode = CalendarModel::MODE_WEEK;
    }
    
    $calendar = new CalendarModel($calendarDate, $calendarMode);
    
    $info = getdate($calendarDate);
    $prevMonth = mktime(0, 0, 0, $info['mon'] - 1, 1, $info['year']);
    $nextMonth = mktime(0, 0, 0, $info['mon'] + 1, 1, $info['year']);
    
    foreach($terminyHodnotenia->getData() as $terminyRow) {
        $casSkusky = AIS2Utils::parseAISDateTime($terminyRow[TerminyFields::DATUM]." ".$terminyRow[TerminyFields::CAS]);
        
        $calendar->addEvent($casSkusky, $terminyRow);
    }
    
    $this->templateParams['calendar'] = $calendar;
    $this->templateParams['prevMonth'] = $prevMonth;
    $this->templateParams['nextMonth'] = $nextMonth;
    
    return $this->renderResponse('studium/kalendar',
        $this->templateParams);
  }

  /**
   * Akcia pre zobrazenie mojich terminov hodnotenia
   *
   * @param Trace $trace trace object
   * @param Request $request request from browser
   */
  public function runMojeTerminyHodnotenia(Trace $trace, Request $request) {

    $this->templateParams['currentTab'] = 'TerminyHodnotenia';
    
    if ($this->terminyHodnoteniaScreen == null) {
      return $this->renderResponse('studium/terminyHodnoteniaNedostupne',
          $this->templateParams);
    }

    $termin = $request->getParameter('termin');

    $terminyHodnotenia = $this->terminyHodnoteniaScreen->getTerminyHodnotenia(
        $trace->addChild("get terminy hodnotenia"));
    $this->warnings->warnWrongTableStructure($trace, 'moje terminy hodnotenia',
        regression\MojeTerminyRegression::get(),
        $terminyHodnotenia->getTableDefinition());

    $hodnotenia = $this->hodnoteniaScreen->getHodnotenia(
        $trace->addChild("get hodnotenia"));
    $this->warnings->warnWrongTableStructure($trace, 'hodnotenia',
        regression\HodnoteniaRegression::get(),
        $hodnotenia->getTableDefinition());

    $hodnoteniePredmetu = array();
    foreach($hodnotenia->getData() as $hodnoteniaRow) {
      $hodnoteniePredmetu[$hodnoteniaRow[HodnoteniaFields::PREDMET_SKRATKA]] =
            $hodnoteniaRow[HodnoteniaFields::ZNAMKA];
    }
    
    if ($request->getParameter('format') === 'ics') {
      $calendar = CalendarProvider::getInstance();
      $calendar->setConfig('unique_id', $request->getHostName());
      $calendar->setProperty( 'METHOD', 'PUBLISH');
      $calendar->setProperty( "x-wr-calname", 'Moje termíny hodnotenia' );
      $calendar->setProperty( "X-WR-CALDESC", "Kalendár skúšok vyexportovaný z aplikácie FAJR" );
      $calendar->setProperty( "X-WR-TIMEZONE", 'Europe/Bratislava' );
      $datetimeFields = array('TZID=Europe/Bratislava');
      foreach($terminyHodnotenia->getData() as $terminyRow) {
        $casSkusky = AIS2Utils::parseAISDateTime($terminyRow[TerminyFields::DATUM]." ".$terminyRow[TerminyFields::CAS]);
        $vevent = new \vevent();
        $vevent->setProperty( 'dtstart', FajrUtils::datetime2icsdatetime($casSkusky), $datetimeFields);
        // koniec dame povedzme 4 hodiny po konci, kedze nevieme kolko skuska trva
        $vevent->setProperty( 'dtend', FajrUtils::datetime2icsdatetime($casSkusky + 4 * 3600), $datetimeFields);
        $vevent->setProperty( 'location', $terminyRow['miestnosti'] );
        $vevent->setProperty( 'summary',  $terminyRow['predmetNazov'] );
        // TODO: toto uid je unikatne, len pokial sa nezmeni niektora z vlastnosti skusok
        // co znamena, ze to nie je uplne OK podla standardu
        // - sposobi to, ze pridavanie novych skusok bude fungovat OK
        // ale mazanie/presuvanie nebude fungovat
        // najlepsie by bolo, ak by sme mali nejake unikatne id-cko skusky priamo z AISu
        $uid = $casSkusky . '-' . $terminyRow[TerminyFields::PREDMET_SKRATKA];
        $uid .= '-' . $terminyRow['miestnosti'];
        $uid .= '@' . $request->getHostName();
        $uid = str_replace(' ', '-', $uid);
        $vevent->setProperty( 'uid', $uid);
        $description = 'Prihlasovanie: ' . $terminyRow['prihlasovanie'] . "\r\n";
        $description .= 'Odhlasovanie: ' . $terminyRow['odhlasovanie'] . "\r\n";
        $description .= 'Poznámka: ' . $terminyRow['poznamka'];
        $vevent->setProperty( 'description', $description );
        $calendar->setComponent ( $vevent );
      }
      $response = new Response($calendar->createCalendar());
      $response->headers->set('Content-Type', 'text/calendar; charset=utf-8');
      $response->headers->set('Content-Disposition', 'attachment; filename="MojeSkusky.ics"');
      $response->setMaxAge(10);
      return $response;
    }

    $terminyHodnoteniaActive = array();
    $terminyHodnoteniaOld = array();

    foreach($terminyHodnotenia->getData() as $terminyRow) {
      $mojeTerminyRow = $terminyRow;

      if ($terminyRow[TerminyFields::ZNAMKA] == '') { // skusme najst znamku v hodnoteniach
        $predmet = $terminyRow[TerminyFields::PREDMET_SKRATKA];
        if (isset($hodnoteniePredmetu[$predmet]) &&
              $hodnoteniePredmetu[$predmet] != '' ) {
            // TODO(ppershing): move this to another field and deal with it in template!
            $mojeTerminyRow[TerminyFields::ZNAMKA] =
                $hodnoteniePredmetu[$predmet]." (nepriradená k termínu)";
            }
      }

      $datum = AIS2Utils::parseAISDateTime($terminyRow[TerminyFields::DATUM]." ".$terminyRow[TerminyFields::CAS]);
      if ($datum < $this->serverTime) {
        if ($terminyRow[TerminyFields::JE_PRIHLASENY]=='TRUE') {
          $terminyHodnoteniaOld[] = $mojeTerminyRow;
        }
      } else {
        if ($terminyRow[TerminyFields::MOZE_ODHLASIT] == 1) {
          $mojeTerminyRow[MojeTerminyFields::HASH_ODHLASENIE] = StudiumUtils::hashNaOdhlasenie($terminyRow);
        }

        $terminyHodnoteniaActive[] = $mojeTerminyRow;
      }
    }

    $this->templateParams['prihlaseni'] = null;
    if ($request->getParameter('termin') !== '') {
      $prihlaseni = $this->terminyHodnoteniaScreen->
            getZoznamPrihlasenychDialog($trace, $termin)->
              getZoznamPrihlasenych($trace);
      $this->warnings->warnWrongTableStructure($trace, 'prihlaseni na termin',
          regression\PrihlaseniNaTerminRegression::get(),
          $prihlaseni->getTableDefinition());
      $this->templateParams['prihlaseni'] = $prihlaseni->getData();
    }

    $this->templateParams['terminyActive'] = $terminyHodnoteniaActive;
    $this->templateParams['terminyOld'] = $terminyHodnoteniaOld;
    $this->templateParams['termin'] = $termin;

    return $this->renderResponse('studium/mojeTerminyHodnotenia',
        $this->templateParams);
  }

  /**
   * Akcia ktora zobrazi predmety zapisane danym clovekom
   *
   * @param Trace $trace trace object
   * @param Request $request
   */
  public function runZapisanePredmety(Trace $trace, Request $request) {
    $this->templateParams['currentTab'] = 'ZapisnyList';
    
    if ($this->terminyHodnoteniaScreen == null) {
      return $this->renderResponse('studium/terminyHodnoteniaNedostupne',
          $this->templateParams);
    }

    $predmetyZapisnehoListu = $this->terminyHodnoteniaScreen->getPredmetyZapisnehoListu($trace);
    $this->warnings->warnWrongTableStructure($trace, 'terminy hodnotenia-predmety',
        regression\ZapisanePredmetyRegression::get(),
        $predmetyZapisnehoListu->getTableDefinition());

    $priemeryCalculator = new PriemeryCalculator();

    $predmetyZapisnehoListuData = Sorter::sort($predmetyZapisnehoListu->getData(),
          array("kodSemester"=>-1, "nazov"=>1));

    foreach ($predmetyZapisnehoListuData as $predmetyRow) {
      $semester = $predmetyRow[PredmetyFields::SEMESTER] == 'L' ?
          PriemeryCalculator::SEMESTER_LETNY : PriemeryCalculator::SEMESTER_ZIMNY;
      $priemeryCalculator->add($semester, '', $predmetyRow[PredmetyFields::KREDIT]);
    }

    $this->templateParams['predmetyZapisnehoListu'] = $predmetyZapisnehoListuData;
    $this->templateParams['predmetyStatistika'] = $priemeryCalculator;
    return $this->renderResponse('studium/zapisanePredmety',
        $this->templateParams);
  }

  /**
   * Akcia ktora sa pokusi prihlasit cloveka na danu skusku
   *
   * @param Trace $trace trace object
   * @param Request $request
   */
  public function runPrihlasNaSkusku(Trace $trace, Request $request)
  {
    if ($this->terminyHodnoteniaScreen == null) {
      return $this->renderResponse('studium/terminyHodnoteniaNedostupne',
          $this->templateParams);
    }

    $predmetIndex = $request->getParameter("prihlasPredmetIndex");
    $terminIndex = $request->getParameter("prihlasTerminIndex");

    $predmety = $this->terminyHodnoteniaScreen
          ->getPredmetyZapisnehoListu($trace->addChild('Predmety zapisneho listu'));
    $this->warnings->warnWrongTableStructure($trace, 'terminy hodnotenia - predmety',
        regression\ZapisanePredmetyRegression::get(),
        $predmety->getTableDefinition());

    $predmetyData = $predmety->getData();

    $predmetKey = -1;
    foreach ($predmetyData as $key=>$row) {
      if ((string) $row[PredmetyFields::INDEX] === $predmetIndex) {
        $predmetKey = $key;
      }
    }

    $childTrace = $trace->addChild('Zoznam terminov');
    $terminyDialog = $this->terminyHodnoteniaScreen
        ->getZoznamTerminovDialog($childTrace, $predmetIndex);

    $terminy = $terminyDialog->getZoznamTerminov($childTrace);
    $this->warnings->warnWrongTableStructure($trace, 'zoznam mojich terminov',
        regression\MojeTerminyRegression::getPrihlasovanie(),
        $terminy->getTableDefinition());

    $terminyData = $terminy->getData();
    $terminKey = -1;
    foreach($terminyData as $key=>$terminyRow) {
      if ((string) $terminyRow[TerminyFields::INDEX] === $terminIndex) {
        $terminKey = $key;
      }
    }

    if ($predmetKey == -1 || $terminKey == -1) {
      throw new Exception("Ooops, predmet/termín nenájdený. Pravdepodobne
          zmena dát v AISe.");
    }

    $hash = StudiumUtils::hashNaPrihlasenie($predmetyData[$predmetKey][PredmetyFields::SKRATKA],
                                     $terminyData[$terminIndex]);
    if ($hash != $request->getParameter('hash')) {
      throw new Exception("Ooops, nesedia údaje o termíne. Pravdepodobne zmena
          dát v AISe spôsobila posunutie tabuliek.");
    }
    if (!$terminyDialog->prihlasNaTermin($trace->addChild('prihlasujem na termin'), $terminIndex)) {
      throw new Exception('Na skúšku sa nepodarilo prihlásiť.');
    }

    return new RedirectResponse($this->generateUrl('studium_moje_skusky',
        array('studium' => $this->studium, 'list' => $this->zapisnyList), true));
  }


  /**
   * Akcia ktora zobrazi terminy, na ktore je mozne potencialne sa prihlasit.
   *
   * @param Trace $trace trace object
   * @param Request $request
   */
  public function runZoznamTerminov(Trace $trace, Request $request) {
    $this->templateParams['currentTab'] = 'ZapisSkusok';

    $schovajUznane = ($request->getParameter('displayFilter', 'uznane')) === 'uznane';
    
    if ($this->terminyHodnoteniaScreen == null) {
      return $this->renderResponse('studium/terminyHodnoteniaNedostupne',
          $this->templateParams);
    }

    $predmetyZapisnehoListu = $this->terminyHodnoteniaScreen->getPredmetyZapisnehoListu($trace);
    $this->warnings->warnWrongTableStructure($trace, 'terminy hodnotenia - predmety',
        regression\ZapisanePredmetyRegression::get(),
        $predmetyZapisnehoListu->getTableDefinition());

    $hodnotenia = $this->hodnoteniaScreen->getHodnotenia($trace);
    $this->warnings->warnWrongTableStructure($trace, 'hodnotenia',
        regression\HodnoteniaRegression::get(),
        $hodnotenia->getTableDefinition());
    $hodnoteniaData = array();

    foreach ($hodnotenia->getData() as $row) {
      $hodnoteniaData[$row[HodnoteniaFields::PREDMET_SKRATKA]] = $row;
    }

    $mozePrihlasitHelper = new MozePrihlasitNaTerminHelper($hodnoteniaData);

    $terminyData = array();

    $pocetSchovanychPredmetov = 0;
    foreach ($predmetyZapisnehoListu->getData() as $predmetRow) {
      $predmetSkratka = $predmetRow[PredmetyFields::SKRATKA];
      $predmetId = $predmetRow[PredmetyFields::INDEX];
      $predmet = $predmetRow[PredmetyFields::NAZOV];
      if ($schovajUznane && 
          $hodnoteniaData[$predmetSkratka][HodnoteniaFields::UZNANE] == 'TRUE') {
        $pocetSchovanychPredmetov++;
        continue;
      }

      $childTrace = $trace->addChild('Zoznam terminov k predmetu ' . $predmet);
      $dialog = $this->terminyHodnoteniaScreen->getZoznamTerminovDialog(
          $childTrace, $predmetId);
      $terminy = $dialog->getZoznamTerminov($childTrace);
      $this->warnings->warnWrongTableStructure($trace, 'zoznam terminov k predmetu ' . $predmet,
          regression\TerminyKPredmetuRegression::get(),
          $terminy->getTableDefinition());
      // explicitly close this dialog otherwise we will be blocked for next iteration!
      $dialog->closeIfNeeded($childTrace);

      foreach($terminy->getData() as $row) {
        $prihlasTerminyRow = $row;
        $prihlasTerminyRow[PrihlasTerminyFields::PREDMET] = $predmet;
        $prihlasTerminyRow[PrihlasTerminyFields::PREDMET_INDEX] = $predmetId;
        $prihlasTerminyRow[PrihlasTerminyFields::PREDMET_SKRATKA] = $predmetSkratka;
        $prihlasTerminyRow[PrihlasTerminyFields::ZNAMKA] = $hodnoteniaData[$predmetSkratka][HodnoteniaFields::ZNAMKA];
        
        $prihlasTerminyRow[PrihlasTerminyFields::HASH_PRIHLASENIE] =
            StudiumUtils::hashNaPrihlasenie($predmetSkratka, $row);

        // PrihlasTerminyFields::ZNAMKA, PREDMET_SKRATKA must be set before!
        $prihlasTerminyRow[PrihlasTerminyFields::FAJR_MOZE_PRIHLASIT] =
          $mozePrihlasitHelper->mozeSaPrihlasit($prihlasTerminyRow, $this->serverTime);

        $terminyData[] = $prihlasTerminyRow;
      }
    }

    $this->templateParams['prihlaseni'] = null;
    if ($request->getParameter('termin') !== '' &&
        $request->getParameter('predmet') !== '') {
      $prihlaseni = $this->terminyHodnoteniaScreen->getZoznamTerminovDialog($trace, $request->getParameter('predmet'))
        ->getZoznamPrihlasenychDialog($trace, $request->getParameter('termin'))
        ->getZoznamPrihlasenych($trace);
      $this->warnings->warnWrongTableStructure($trace, 'zoznam prihlasenych k terminu',
          regression\PrihlaseniNaTerminRegression::get(),
          $prihlaseni->getTableDefinition());
      $this->templateParams['prihlaseni'] = $prihlaseni->getData();
    }

    $this->templateParams['predmetyZapisnehoListu'] = $predmetyZapisnehoListu;
    $this->templateParams['terminy'] = $terminyData;
    $this->templateParams['termin'] = $request->getParameter('termin');
    $this->templateParams['predmet'] = $request->getParameter('predmet');
    $this->templateParams['pocetSchovanychPredmetov'] = $pocetSchovanychPredmetov;

    return $this->renderResponse('studium/zoznamTerminov', $this->templateParams);
  }
}
