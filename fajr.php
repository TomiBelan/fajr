<?php
/* {{{
Copyright (c) 2010 Martin Králik

 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.
 }}} */

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('Europe/Bratislava');
mb_internal_encoding("UTF-8");

// Pretoze v session ukladam objekty libfajru, treba nacitat definicie
// tried skor, ako sa nacitava session
require_once 'libfajr/libfajr.php';
libfajr_autoload_register();

session_start();
session_cache_expire(300);
$startTime = microtime(true);

require_once 'FajrConfig.php';
require_once 'DisplayManager.php';

if (!FajrConfig::isConfigured()) {
	DisplayManager::addContent('notConfigured', true);
	echo DisplayManager::display();
	session_write_close();
	die();
}

require_once 'Input.php';
require_once 'TabManager.php';
require_once 'Changelog.php';
require_once 'Table.php';
require_once 'TableDefinitions.php';
require_once 'Sorter.php';
require_once 'FajrUtils.php';

require_once 'TabMojeSkusky.php';
require_once 'TabPrihlasenieNaSkusky.php';
require_once 'TabZapisnyList.php';
require_once 'TabHodnoteniaPriemery.php';

$connection = null;
$debugConnection = null;
$statsConnection = null;
$rawStatsConnection = null;
try
{
	$connection = new AIS2CurlConnection(FajrUtils::getCookieFile());

	$rawStatsConnection = new AIS2StatsConnection($connection);
	$connection = $rawStatsConnection;

	$connection = new AIS2DecompressingConnection($connection, FajrUtils::getTempDir());
	$connection = new AIS2ErrorCheckingConnection($connection);

	$statsConnection = new AIS2StatsConnection($connection);
	$connection = $statsConnection;

	if (FajrConfig::get('Debug.Connections')) {
		$debugConnection = new AIS2DebugConnection($connection);
		$connection = $debugConnection;
	}

	AIS2Utils::connection($connection); // toto tu je docasne

	Input::prepare();
	
	if (Input::get('logout') !== null) FajrUtils::logout($connection);
	
	$login = Input::get('login'); Input::set('login', null);
	$krbpwd = Input::get('krbpwd'); Input::set('krbpwd', null);
	$cosignCookie = Input::get('cosignCookie'); Input::set('cosignCookie', null);
	if ($login !== null && $krbpwd !== null) {
		$loggedIn = FajrUtils::login(new AIS2CosignLogin($login, $krbpwd), $connection);
		$login = null;
		$krbpwd = null;
	} else if ($cosignCookie !== null) {
		$loggedIn = FajrUtils::login(new AIS2CookieLogin($cosignCookie), $connection);
		$cosignCookie = null;
	} else {
		$loggedIn = FajrUtils::isLoggedIn();
	}

	if ($loggedIn) {
		DisplayManager::addContent(
		'<div class=\'logout\'><a class="button negative" href="'.FajrUtils::linkUrl(array('logout'=>true)).'">
	  <img src="images/door_in.png" alt=""/>Odhlásiť</a></div>'
		);
		$adminStudia = new AIS2AdministraciaStudiaScreen();
		
		if (Input::get('studium') === null) Input::set('studium',0);
		
		$zoznamStudii = $adminStudia->getZoznamStudii();
		$zoznamStudiiTable = new Table(TableDefinitions::zoznamStudii(),
				'Zoznam štúdií', 'studium', array('tab' => Input::get('tab')));
		$zoznamStudiiTable->addRows($zoznamStudii->getData());
		$zoznamStudiiTable->setOption('selected_key', Input::get('studium'));
		$zoznamStudiiTable->setOption('collapsed', true);
		DisplayManager::addContent($zoznamStudiiTable->getHtml());
		
		
		$zapisneListy = $adminStudia->getZapisneListy(Input::get('studium'));
		
		$zapisneListyTable = new
			Table(TableDefinitions::zoznamZapisnychListov(), 'Zoznam zápisných listov',
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
		DisplayManager::addContent($zapisneListyTable->getHtml());
		
		
		$terminyHodnotenia = new
			AIS2TerminyHodnoteniaScreen($adminStudia->getIdZapisnyList(Input::get('list')),
					$adminStudia->getIdStudium(Input::get('list')));
		
		if (Input::get('tab') === null) Input::set('tab', 'TerminyHodnotenia');
		$tabs = new TabManager('tab', array('studium'=>Input::get('studium'),
					'list'=>Input::get('list')));
		$tabs->setActive(Input::get('tab'));
		$tabs->addTab('TerminyHodnotenia', 'Moje skúšky',
					new MojeTerminyHodnoteniaCallback($terminyHodnotenia));
		// FIXME: chceme to nejak refaktorovat, aby sme nevytvarali zbytocne
		// objekty, ktore v konstruktore robia requesty
		$hodnoteniaScreen = new AIS2HodnoteniaPriemeryScreen(
					$adminStudia->getIdZapisnyList(Input::get('list')));
		$tabs->addTab('ZapisSkusok', 'Prihlásenie na skúšky',
					new ZoznamTerminovCallback($terminyHodnotenia, $hodnoteniaScreen));
		$tabs->addTab('ZapisnyList', 'Zápisný list',
					new ZapisanePredmetyCallback($terminyHodnotenia));
		$tabs->addTab('Hodnotenia', 'Hodnotenia/Priemery',
				new HodnoteniaCallback($hodnoteniaScreen));
		
		DisplayManager::addContent($tabs->getHtml());
		
		$timeDiff = (microtime(true)-$startTime);
		$statistics = "<div> Fajr made ".$statsConnection->getTotalCount().
						" requests and downloaded ".$rawStatsConnection->getTotalSize().
						" bytes (".$statsConnection->getTotalSize().
						" bytes uncompressed) of data from AIS2 in ".
						sprintf("%.3f", $statsConnection->getTotalTime()).
						" seconds. It took ".sprintf("%.3f", $timeDiff).
						" seconds to generate this page.</div>";
		DisplayManager::addContent($statistics);
	}
	else
	{
		DisplayManager::addContent('loginBox', true);	
		DisplayManager::addContent('warnings', true);
		DisplayManager::addContent('terms', true);
		DisplayManager::addContent('credits', true);
		DisplayManager::addContent(Changelog::getChangelog(), false);
	}
}
catch (AIS2LoginException $e) {
	if ($connection) FajrUtils::logout($connection);
	DisplayManager::addException($e);
}
catch (Exception $e)
{
	DisplayManager::addException($e);
}

if ($debugConnection) {
	DisplayManager::dumpRequests($debugConnection->getRequests());
}

echo DisplayManager::display();

session_write_close();
?>
