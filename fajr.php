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
	require_once 'TableDefinitions.php';
	require_once 'Sorter.php';

	class TerminyHodnoteniaCallback implements ITabCallback {
		public function __construct($skusky) {
			$this->skusky = $skusky;
		}
		
		public function callback() {
			$terminyHodnotenia = $this->skusky->getTerminyHodnotenia();
			$terminyHodnoteniaTableActive =  new
				Table(TableDefinitions::mojeTerminyHodnotenia(), 'Aktuálne termíny hodnotenia', null, array('studium', 'list'));
			
			$terminyHodnoteniaTableOld =  new
				Table(TableDefinitions::mojeTerminyHodnotenia(), 'Staré termíny hodnotenia', null, array('studium', 'list'));
			
			foreach($terminyHodnotenia->getData() as $row) {
				$datum=strptime($row['datum']." ".$row['cas'], "%d.%m.%Y %H:%M");
				$datum=mktime($datum["tm_hour"],$datum["tm_min"],0,1+$datum["tm_mon"],$datum["tm_mday"],1900+$datum["tm_year"]);
				$row['odhlas']="";
				if ($datum < time()) {
					$terminyHodnoteniaTableOld->addRow($row, null);
				} else {
					if ($row['mozeOdhlasit']==1) {
						$class='terminmozeodhlasit';
						$row['odhlas']="<form> <input type='submit' value='Odhlás'
								disabled='disabled' /> </form>";
					} else {
						$class='terminnemozeodhlasit';
					}
						
					if ($row['prihlaseny']=='A') {
						$terminyHodnoteniaTableActive->addRow($row, array('class'=>$class));
					}
				}
			}
			$terminyHodnoteniaTableActive->setUrlParams(array('studium' =>
						Input::get('studium'), 'list' => Input::get('list')));
			
			return
					$terminyHodnoteniaTableActive->getHtml().
					$terminyHodnoteniaTableOld->getHtml();
		}
	}

	class ZoznamTerminovCallback implements ITabCallback {
		private $skusky;
		
		public function __construct($skusky) {
			$this->skusky = $skusky;
		}
		
		public function callback() {
			$predmetyZapisnehoListu = $this->skusky->getPredmetyZapisnehoListu();
			$terminyTable = new
				Table(TableDefinitions::vyberTerminuHodnoteniaJoined(), 'Termíny,
						na ktoré sa môžem prihlásiť');
			foreach ($predmetyZapisnehoListu->getData() as $row) {
				$terminy = $this->skusky->getZoznamTerminov($row['index']);
				foreach($terminy->getData() as $row2) {
					$row2['predmet']=$row['nazov'];
					$row2['predmetIndex']=$row['index'];
					$row2['prihlas']="<form> <input type='submit' value='Prihlás ma!'
							disabled='disabled'/> </form>";
					$terminyTable->addRow($row2, null);
					
				}
			}
			return $terminyTable->getHtml();
		}
	}
	
	class ZapisanePredmetyCallback implements ITabCallback {
		private $skusky;
		
		public function __construct($skusky) {
			$this->skusky = $skusky;
		}
		
		public function callback() {
			$predmetyZapisnehoListu = $this->skusky->getPredmetyZapisnehoListu();
			$predmetyZapisnehoListuTable = new
				Table(TableDefinitions::predmetyZapisnehoListu(), 'Predmety zápisného listu');
			foreach (Sorter::sort($predmetyZapisnehoListu->getData(),
						array("semester"=>-1, "nazov"=>1)) as $row) {
				if ($row['semester']=='L') $class='leto'; else $class='zima';
				$predmetyZapisnehoListuTable->addRow($row, array('class'=>$class));
			}
;
			$predmetyZapisnehoListuTable->setUrlParams(array('studium' =>
						Input::get('studium'), 'list' => Input::get('list')));
			
			return $predmetyZapisnehoListuTable->getHtml();
		}
	}

	class HodnoteniaCallback implements ITabCallback {
		private $app;
		
		public function __construct($app) {
			$this->app = $app;
		}
		
		public function callback() {
			$hodnotenia = $this->app->getHodnotenia();
			$hodnoteniaTable = new Table(TableDefinitions::hodnotenia(), 'Hodnotenia');
			foreach(Sorter::sort($hodnotenia->getData(),
						array("semester"=>-1, "nazov"=>1)) as $row) {
				if ($row['semester']=='L') $class='leto'; else $class='zima';
				$hodnoteniaTable->addRow($row, array('class'=>$class));
			}
			
			$priemery = $this->app->getPriemery();
			$priemeryTable = new Table(TableDefinitions::priemery(), 'Priemery');
			$priemeryTable->addRows($priemery->getData());
			
			return $hodnoteniaTable->getHtml().$priemeryTable->getHtml();
		}
	}

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
			
			if (Input::get('studium') === null) Input::set('studium',0);
			
			$zoznamStudii = $adminStudia->getZoznamStudii();
			$zoznamStudiiTable = new Table(TableDefinitions::zoznamStudii(),
					'Zoznam štúdií', 'studium', array('tab' => Input::get('tab')));
			$zoznamStudiiTable->addRows($zoznamStudii->getData());
			$zoznamStudiiTable->setOption('selected_key', Input::get('studium'));
			$zoznamStudiiTable->setOption('collapsed', true);
			DisplayManager::addContent($zoznamStudiiTable->getHtml());
			
			
			$zapisneListy = $adminStudia->getZapisneListy(Input::get('studium'));
			if (Input::get('list') === null) Input::set('list', 0);
			
			$zapisneListyTable = new
				Table(TableDefinitions::zoznamZapisnychListov(), 'Zoznam zápisných listov',
					'list', array('studium' => Input::get('studium'),
						'tab'=>Input::get('tab')));
			
			$zapisneListyTable->addRows($zapisneListy->getData());
			$zapisneListyTable->setOption('selected_key', Input::get('list'));
			$zapisneListyTable->setOption('collapsed', true);
			DisplayManager::addContent($zapisneListyTable->getHtml());
			
			$tabs = new TabManager('tab', array('studium'=>Input::get('studium'),
						'list'=>Input::get('list')));
			if (Input::get('tab') === null) Input::set('tab', 'TerminyHodnotenia');
			
			$tabs->setActive(Input::get('tab'));
			
			$skusky = new
				AIS2TerminyHodnoteniaScreen($adminStudia->getIdZapisnyList(Input::get('list')),
						$adminStudia->getIdStudium(Input::get('list')));
			
			$callback = new TerminyHodnoteniaCallback($skusky);
			$tabs->addTab('TerminyHodnotenia', 'Moje skúšky', $callback);
			
			$callback = new ZoznamTerminovCallback($skusky);
			$tabs->addTab('ZapisSkusok', 'Prihlásenie na skúšky', $callback);

			$callback = new ZapisanePredmetyCallback($skusky);
			$tabs->addTab('ZapisnyList', 'Zápisný list', $callback);
			$callback = new HodnoteniaCallback(
					new AIS2HodnoteniaPriemeryScreen(
						$adminStudia->getIdZapisnyList(Input::get('list'))));
			$tabs->addTab('Hodnotenia', 'Hodnotenia/Priemery', $callback);
			
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
