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

// Pretoze v session ukladam objekty libfajru, treba nacitat definicie
// tried skor, ako sa nacitava session
require_once 'libfajr/libfajr.php';

session_start();
session_cache_expire(300);
$startTime = microtime(true);

require_once 'Input.php';
require_once 'DisplayManager.php';
require_once 'TabManager.php';
require_once 'Changelog.php';
require_once 'Table.php';
require_once 'libfajr/AIS2AdministraciaStudiaScreen.php';
require_once 'libfajr/AIS2TerminyHodnoteniaScreen.php';
require_once 'libfajr/AIS2HodnoteniaPriemeryScreen.php';
require_once 'TableDefinitions.php';
require_once 'Sorter.php';
require_once 'FajrUtils.php';

require_once 'TabMojeSkusky.php';
require_once 'TabPrihlasenieNaSkusky.php';
require_once 'TabZapisnyList.php';
require_once 'TabHodnoteniaPriemery.php';

try
{
	Input::prepare();
	
	if (Input::get('logout') !== null) FajrUtils::logout();
	
	$login = Input::get('login'); Input::set('login', null)
	$krbpwd = Input::get('krbpwd'); Input::set('krbpwd', null);
	$cosignCookie = Input::get('cosignCookie'); Input::set('cosignCookie', null);
	if ($login !== null && $krbpwd !== null) {
		$loggedIn = FajrUtils::login(new AIS2CosignLogin($login, $krbpwd));
		$login = null;
		$krbpwd = null;
	} else if ($cosignCookie !== null) {
		$loggedIn = FajrUtils::login(new AIS2CookieLogin($cosignCookie));
		$cosignCookie = null;
	} else {
		$loggedIn = FajrUtils::isLoggedIn();
	}

	if ($loggedIn) {
		DisplayManager::addContent(
				'<div class=\'logout\'><a href="?logout">Odhlásiť</a></div>');
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
		$tabs->addTab('ZapisSkusok', 'Prihlásenie na skúšky',
					new ZoznamTerminovCallback($terminyHodnotenia));
		$tabs->addTab('ZapisnyList', 'Zápisný list',
					new ZapisanePredmetyCallback($terminyHodnotenia));
		$tabs->addTab('Hodnotenia', 'Hodnotenia/Priemery',
				new HodnoteniaCallback(
					new AIS2HodnoteniaPriemeryScreen(
						$adminStudia->getIdZapisnyList(Input::get('list')))));
		
		DisplayManager::addContent($tabs->getHtml());
		
		$timeDiff = (microtime(true)-$startTime);
		$statistics = "<div> Fajr made ".AIS2Utils::$requests.
									" requests and downloaded ".AIS2Utils::$requestsSize.
									" bytes of data from AIS2. It took ".sprintf("%.3f",
											$timeDiff).
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
catch (Exception $e)
{
	DisplayManager::addContent('<h2>ERROR!</h2><div class="error">'.$e->getMessage().'<br/>'.nl2br($e->getTraceAsString()).'</div>');
}

echo DisplayManager::display();

session_write_close();
?>
