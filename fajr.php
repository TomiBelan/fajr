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
	require_once 'libfajr/AIS2Utils.php';
	require_once 'libfajr/AIS2AdministraciaStudiaScreen.php';
	require_once 'libfajr/AIS2TerminyHodnoteniaScreen.php';
	require_once 'libfajr/AIS2HodnoteniaPriemeryScreen.php';
 
	try
	{
		Input::prepare();
		
		if (Input::get('logout') !== null) AIS2Utils::cosignLogout();
		
		if (AIS2Utils::connect())
		{
			DisplayManager::addContent('<a href="?logout">odhlásiť</a><hr/>');
			$adminStudia = new AIS2AdministraciaStudiaScreen();
			
			$zoznamStudiiTable = $adminStudia->getZoznamStudii();
			$zoznamStudiiTable->setOption('selected_key',Input::get('studium'));
			$zoznamStudiiTable->setOption('collapsed', Input::get('studium') !== null);
			DisplayManager::addContent($zoznamStudiiTable->getHtml());
			
			if (Input::get('studium') !== null) {
				$studium_id = Input::get('studium');
			} else {
				$studium_id = 0;	
			}
			
			$zapisneListy = $adminStudia->getZapisneListy($studium_id);
			$zapisneListy->setOption('selected_key', Input::get('list'));
			$zapisneListy->setOption('collapsed', Input::get('list') !== null);
			DisplayManager::addContent($zapisneListy->getHtml());
			
			if (Input::get('list') !== null) {
				$list = Input::get('list');
			} else {
				$list = 0;
			}
			$skusky = new AIS2TerminyHodnoteniaScreen($adminStudia->getIdZapisnyList($list), $adminStudia->getIdStudium($list));
			$tabs = new TabManager('akcie');
			
			$terminyHodnotenia = $skusky->getTerminyHodnotenia();
			$terminyHodnotenia->setUrlParams(array('studium' => Input::get('studium'), 'list' => $list));
			$tabs->addTab('TerminyHodnotenia', 'Moje skúšky', $terminyHodnotenia->getHtml());

			$tabs->addTab('ZapisSkusok', 'Prihlásenie na skúšky', '');
			
			$predmetyZapisnehoListu = $skusky->getPredmetyZapisnehoListu();
			$predmetyZapisnehoListu->setUrlParams(array('studium' => Input::get('studium'), 'list' => $list));
			
			$tabs->addTab('ZapisnyList', 'Zápisný list', $predmetyZapisnehoListu->getHtml());
			
			$skusky = new AIS2HodnoteniaPriemeryScreen($adminStudia->getIdZapisnyList($list));
			$hodnotenia = $skusky->getHodnotenia();
			$tabs->addTab('Hodnotenia', 'Hodnotenia', $hodnotenia->getHtml());
			$priemery = $skusky->getPriemery();
			$tabs->addTab('Priemery', 'Priemery', $priemery->getHtml());
			
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
		DisplayManager::addContent('<h2>ERROR!</h2><div class="error">'.$e->getMessage().'<br/>'.$e->getTraceAsString().'</div>');
	}
	
	echo DisplayManager::display();
	
	session_write_close();
?>
