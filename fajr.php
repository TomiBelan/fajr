<?php
/*
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
*/
	error_reporting(E_ALL | E_STRICT);
	session_start();
	$startTime = microtime(true);

	require_once 'Input.php';
	require_once 'DisplayManager.php';
	require_once 'TabManager.php';
	require_once 'Changelog.php';
	require_once 'Table.php';
	require_once 'libfajr/AIS2Utils.php';
	require_once 'libfajr/AIS2AdministraciaStudiaScreen.php';
	require_once 'libfajr/AIS2TerminyHodnoteniaScreen.php';
	require_once 'libfajr/AIS2HodnoteniaPriemeryScreen.php';
 
	try
	{
		Input::prepare();
		
		if (Input::get('logout') !== null) AIS2Utils::cosignLogout();
		
		$login = Input::get('login');
		$krbpwd = Input::get('krbpwd');
		$cosignCookie = Input::get('cosignCookie');
		if ($login !== null && $krbpwd !== null) {
			$loggedIn = AIS2Utils::loginViaCosign($login, $krbpwd);
		} else if ($cosignCookie !== null) {
			$loggedIn = AIS2Utils::loginViaCookie($cosignCookie);
		} else {
			$loggedIn = isset($_SESSION['cosignLogin']);
		}

		if ($loggedIn) {
			DisplayManager::addContent('<a href="?logout">odhlásiť</a><hr/>');
			$adminStudia = new AIS2AdministraciaStudiaScreen();
			
			if (Input::get('studium') === null) Input::set('studium', 0);
			
			$zoznamStudii = $adminStudia->getZoznamStudii();
			$zoznamStudiiTable = new Table($zoznamStudii, 'Zoznam štúdií', 'studium');
			$zoznamStudiiTable->setOption('selected_key',Input::get('studium'));
			$zoznamStudiiTable->setOption('collapsed', Input::get('studium') !== null);
			DisplayManager::addContent($zoznamStudiiTable->getHtml());
			
			
			$zapisneListy = $adminStudia->getZapisneListy(Input::get('studium'));
			if (Input::get('list') === null) Input::set('list', 0);
			
			$zapisneListyTable = new Table($zapisneListy, 'Zoznam zápisných listov', 'list', array('studium' => Input::get('studium')));
			$zapisneListyTable->setOption('selected_key', Input::get('list'));
			$zapisneListyTable->setOption('collapsed', Input::get('list') !== null);
			DisplayManager::addContent($zapisneListyTable->getHtml());
			
			$skusky = new AIS2TerminyHodnoteniaScreen($adminStudia->getIdZapisnyList(Input::get('list')), $adminStudia->getIdStudium(Input::get('list')));
			$tabs = new TabManager('akcie');
			
			$terminyHodnotenia = $skusky->getTerminyHodnotenia();
			$terminyHodnoteniaTable =  new Table($terminyHodnotenia, 'Termíny hodnotenia', null, array('studium', 'list'));
			$terminyHodnoteniaTable->setUrlParams(array('studium' => Input::get('studium'), 'list' => Input::get('list')));
			$tabs->addTab('TerminyHodnotenia', 'Moje skúšky', $terminyHodnoteniaTable->getHtml());

			$tabs->addTab('ZapisSkusok', 'Prihlásenie na skúšky', '');
			
			$predmetyZapisnehoListu = $skusky->getPredmetyZapisnehoListu();
			$predmetyZapisnehoListuTable = new Table($predmetyZapisnehoListu, 'Predmety zápisného listu', 'predmet');
			$predmetyZapisnehoListuTable->setOption('selected_key', Input::get('predmet'));
			$predmetyZapisnehoListuTable->setUrlParams(array('studium' => Input::get('studium'), 'list' => Input::get('list')));
			
			$tabs->addTab('ZapisnyList', 'Zápisný list', $predmetyZapisnehoListuTable->getHtml());
			
			$hodnoteniaPriemery = new AIS2HodnoteniaPriemeryScreen($adminStudia->getIdZapisnyList(Input::get('list')));
			$hodnotenia = $hodnoteniaPriemery->getHodnotenia();
			$hodnoteniaTable = new Table($hodnotenia, 'Hodnotenia');
			$priemery = $hodnoteniaPriemery->getPriemery();
			$priemeryTable = new Table($priemery, 'Priemery');
			
			$tabs->addTab('Hodnotenia', 'Hodnotenia/Priemery',
			              $hodnoteniaTable->getHtml().$priemeryTable->getHtml());
			
			if (Input::get('predmet') !== null)
			{
				$tabs->setActive('ZapisnyList');
			}
			
			DisplayManager::addContent($tabs->getHtml());
			
			if (Input::get('predmet') !== null)
			{
				$terminy = $skusky->getZoznamTerminov(Input::get('predmet'));
				$terminyTable = new Table($terminy, 'Terminy');
				DisplayManager::addContent($terminyTable->getHtml());
			}
			
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
		DisplayManager::addContent('<h2>ERROR!</h2><div class="error">'.$e->getMessage().'<br/>'.$e->getTraceAsString().'</div>');
	}
	
	echo DisplayManager::display();
	
	session_write_close();
?>
