<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * The main logic of fajr application.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Králik <majak47@gmail.com>
 */
namespace fajr;
use Exception;
use fajr\htmlgen\Collapsible;
use fajr\htmlgen\HtmlHeader;
use fajr\htmlgen\Table;
use fajr\ArrayTrace;
use fajr\libfajr\pub\base\Trace;
use fajr\injection\Injector;
use fajr\libfajr\AIS2Session;
use fajr\libfajr\base\SystemTimer;
use fajr\libfajr\connection;
use fajr\libfajr\pub\connection\HttpConnection;
use fajr\libfajr\pub\login\CosignServiceCookie;
use fajr\libfajr\pub\base\NullTrace;
use fajr\libfajr\pub\login\AIS2Login;
use fajr\libfajr\pub\login\LoginFactoryImpl;
use fajr\libfajr\pub\window\VSES017_administracia_studia as VSES017; // *
use fajr\presentation\HodnoteniaCallback;
use fajr\presentation\MojeTerminyHodnoteniaCallback;
use fajr\presentation\ZapisanePredmetyCallback;
use fajr\presentation\ZoznamTerminovCallback;
use fajr\TabManager;
use fajr\libfajr\pub\connection\AIS2ServerConnection;
use fajr\libfajr\pub\connection\AIS2ServerUrlMap;

/**
 * This is "main()" of the fajr. It instantiates all neccessary
 * objects, query ais and renders results.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Králik <majak47@gmail.com>
 */
class Fajr {
  /**
   * @var Injector $injector dependency injector.
   */
  private $injector;

  /**
   * Constructor.
   *
   * @param Injector $injector dependency injector.
   */
  public function __construct(Injector $injector)
  {
    $this->injector = $injector;
  }

  /**
   * WARNING: Must be called before provideConnection().
   */
  private function regenerateSessionOnLogin()
  {
    $login = Input::get('login');
    $krbpwd = Input::get('krbpwd');
    $cosignCookie = Input::get('cosignCookie');

    // FIXME this should be refactored
    if (($login !== null && $krbpwd !== null) || ($cosignCookie !== null)) {
      // we are going to log in, so we get a clean session
      // this needs to be done before a connection
      // is created, because we pass cookie file name
      // that contains session_id into AIS2CurlConnection
      // If we regenerated the session id afterwards,
      // we could not find the cookie file after a redirect
      FajrUtils::dropSession();
    }
  }

  /**
   * Provides login object created from POST-data.
   *
   * @returns AIS2Login
   */
  private function provideLogin()
  {
    // TODO(ppershing): use injector here
    $factory = new LoginFactoryImpl();

    $login = Input::get('login'); Input::set('login', null);
    $krbpwd = Input::get('krbpwd'); Input::set('krbpwd', null);
    $cosignCookie = Input::get('cosignCookie'); Input::set('cosignCookie', null);

    //TODO(ppershing): create hidden field "loginType" in the form
    if ($login !== null && $krbpwd !== null) {
      return $factory->newLoginUsingCosign($login, $krbpwd);
    } else if ($cosignCookie !== null) {
      $cosignCookie = CosignServiceCookie::fixCookieValue($cosignCookie);
      // TODO(anty): change to use correct domain and cookie name
      return $factory->newLoginUsingCookie(new CosignServiceCookie('cosign-filter-ais2.uniba.sk', $cosignCookie, 'ais2.uniba.sk'));
    } else {
      return null;
    }
  }

  // TODO(ppershing): We need to do something about these connections.
  // Currently, this is really ugly solution and should be refactored.
  private $rawStatsConnection;
  private $statsConnection;

  private function provideConnection()
  {
    $connection = new connection\CurlConnection(FajrUtils::getCookieFile());

    $this->rawStatsConnection = new connection\StatsConnection($connection, new SystemTimer());

    $connection = new connection\GzipDecompressingConnection($this->rawStatsConnection, FajrConfig::getDirectory('Path.Temporary'));
    $connection = new connection\AIS2ErrorCheckingConnection($connection);

    $this->statsConnection = new connection\StatsConnection($connection, new SystemTimer());
    return $this->statsConnection;
  }

  /**
   * Set an exception to be displayed in DisplayManager
   * @param Exception $ex
   */
  private function setException(Exception $ex) {
    $this->displayManager->set('exception', $ex);
    $this->displayManager->set('showStackTrace', FajrConfig::get('Debug.Exception.ShowStacktrace'));
  }

  /**
   * Runs the whole logic. It is fajr's main()
   *
   * @returns void
   */
  public function run()
  {
    $this->injector->getInstance('SessionInitializer.class')->startSession();

    $timer = new SystemTimer();

    // TODO(ppershing): use injector here!
    $trace = new NullTrace();

    if (FajrConfig::get('Debug.Trace') === true) {
      $trace = new ArrayTrace($timer, "--Trace--");
    }

    // TODO(anty): do we want DisplayManager? If so, use injector here
    $this->displayManager = new DisplayManager();

    $pageName = null;

    try {
      Input::prepare();

      $this->regenerateSessionOnLogin();
      $connection = $this->provideConnection();
      $pageName = $this->runLogic($trace, $connection);
    } catch (LoginException $e) {
      if ($connection) {
        FajrUtils::logout($connection);
      }

      $this->setException($e);
      $pageName = 'exception';
    } catch (Exception $e) {
      $this->setException($e);
      $pageName = 'exception';
    }

    $this->displayManager->setBase(FajrUtils::basePath());

    $trace->tlog("everything done, generating html");

    if (FajrConfig::get('Debug.Trace')===true) {
      $this->displayManager->set('trace', $trace);
    }
    echo $this->displayManager->display($pageName);
  }

  public function runLogic(Trace $trace, HttpConnection $connection)
  {
      $serverConnection = new AIS2ServerConnection($connection,
          new AIS2ServerUrlMap(FajrConfig::get('AIS2.ServerName')));
      $timer = new SystemTimer();

      if (Input::get('logout') !== null) {
        FajrUtils::logout($serverConnection);
        FajrUtils::redirect();
      }

      $loggedIn = FajrUtils::isLoggedIn($serverConnection);

      $cosignLogin = $this->provideLogin();
      if (!$loggedIn && $cosignLogin != null) {
          FajrUtils::login($trace->addChild("logging in"), $cosignLogin, $serverConnection);
          $loggedIn = true;
      }

      if ($loggedIn) {
        $this->displayManager->set('logoutUrl', FajrUtils::linkUrl(array('logout'=>true)));
        $screenFactory = new VSES017\VSES017_factory($serverConnection);
        $adminStudia = $screenFactory->newAdministraciaStudiaScreen($trace);
        
        if (Input::get('studium') === null) Input::set('studium',0);
        
        $zoznamStudii = $adminStudia->getZoznamStudii($trace->addChild("Get Zoznam Studii:"));
        $zoznamStudiiTable = new Table(TableDefinitions::zoznamStudii(), 'studium',
          array('tab' => Input::get('tab')));
        $zoznamStudiiTable->addRows($zoznamStudii->getData());
        $zoznamStudiiTable->setOption('selected_key', Input::get('studium'));
        $zoznamStudiiTable->setOption('collapsed', true);

        $zoznamStudiiCollapsible = new Collapsible(new HtmlHeader('Zoznam štúdií'), $zoznamStudiiTable, true);

        $this->displayManager->addContent($zoznamStudiiCollapsible->getHtml());
        
        $zapisneListy = $adminStudia->getZapisneListy($trace->addChild('getZapisneListy'), Input::get('studium'));
        
        $zapisneListyTable = new
          Table(TableDefinitions::zoznamZapisnychListov(),
            'list', array('studium' => Input::get('studium'),
              'tab'=>Input::get('tab')));
        
        if (Input::get('list') === null) {
          $tmp = $zapisneListy->getData();
          $lastList = end($tmp);
          Input::set('list', $lastList['index']);
        }
        
        $zapisneListyTable->addRows($zapisneListy->getData());
        $zapisneListyTable->setOption('selected_key', Input::get('list'));
        $zapisneListyTable->setOption('collapsed', true);

        $zapisneListyCollapsible = new Collapsible(new HtmlHeader('Zoznam zápisných listov'), $zapisneListyTable, true);

        $this->displayManager->addContent($zapisneListyCollapsible->getHtml());
        
        
        $terminyHodnotenia = $screenFactory->newTerminyHodnoteniaScreen(
              $trace,
              $adminStudia->getZapisnyListIdFromZapisnyListIndex($trace, Input::get('list')),
              $adminStudia->getStudiumIdFromZapisnyListIndex($trace, Input::get('list')));
        
        if (Input::get('tab') === null) Input::set('tab', 'TerminyHodnotenia');
        $tabs = new TabManager('tab', array('studium'=>Input::get('studium'),
              'list'=>Input::get('list')), $this->displayManager);
        // FIXME: chceme to nejak refaktorovat, aby sme nevytvarali zbytocne
        // objekty, ktore v konstruktore robia requesty
        $hodnoteniaScreen = $screenFactory->newHodnoteniaPriemeryScreen(
              $trace,
              $adminStudia->getZapisnyListIdFromZapisnyListIndex($trace, Input::get('list')));
        $tabs->addTab('TerminyHodnotenia', 'Moje skúšky',
              new MojeTerminyHodnoteniaCallback($trace, $terminyHodnotenia, $hodnoteniaScreen));
        $tabs->addTab('ZapisSkusok', 'Prihlásenie na skúšky',
              new ZoznamTerminovCallback($trace, $terminyHodnotenia, $hodnoteniaScreen));
        $tabs->addTab('ZapisnyList', 'Zápisný list',
              new ZapisanePredmetyCallback($trace, $terminyHodnotenia));
        $tabs->addTab('Hodnotenia', 'Hodnotenia/Priemery',
            new HodnoteniaCallback($trace, $hodnoteniaScreen));

        $tabs->setActive(Input::get('tab'));
        $this->displayManager->addContent($tabs->getHtml());
        ;

        $this->displayManager->set("stats_connections",
            $this->statsConnection->getTotalCount());
        $this->displayManager->set("stats_rawBytes",
            $this->rawStatsConnection->getTotalSize());
        $this->displayManager->set("stats_bytes",
            $this->statsConnection->getTotalSize());
        $this->displayManager->set("stats_connectionTime",
            sprintf("%.3f", $this->statsConnection->getTotalTime()));
        $this->displayManager->set("stats_totalTime",
            sprintf("%.3f", $timer->getElapsedTime()));
      }
      else
      {
        return 'welcome';
      }
  }
}
