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

	require_once 'displayManager.php'; // kvoli generovaniu html tabuliek, chcelo by to spravit inak
	require_once 'supporting_functions.php';

	class AIS2
	{
		public static $appId = null;
		public static $identity = null;
		
		private static $serial = 0;
	
		const COSIGN_LOGIN = 'https://login.uniba.sk/cosign.cgi';
		const COSIGN_LOGOUT = 'https://login.uniba.sk/logout.cgi';
		const LOGIN = 'https://ais2.uniba.sk/ais/login.do';
		const MAIN_PAGE = 'https://ais2.uniba.sk';
		const EVIDENTCIA_STUDIA = 'https://ais2.uniba.sk/ais/servlets/WebUIServlet?appClassName=ais.gui.vs.es.VSES017App&kodAplikacie=VSES017&viewer=web';
		const XML_INTERFACE = 'https://ais2.uniba.sk/ais/servlets/WebUIServlet?appId=###APPID###&antiCache=###RANDOM###&viewer=web&viewer=web';
		const STUDENT_ZAPISNE_LISTY = 'https://ais2.uniba.sk/ais/servlets/WebUIServlet?appId=###APPID###&form=VSES017_StudentZapisneListyDlg0&antiCache=###RANDOM###';
		const STUDENT_ZOZNAM_PRIHLASENI_NA_SKUSKU = 'https://ais2.uniba.sk/ais/servlets/WebUIServlet?appId=###APPID###&form=VSES007_StudentZoznamPrihlaseniNaSkuskuDlg0&antiCache=###RANDOM###';
		const TERMINY_HODNOTENIA = 'https://ais2.uniba.sk/ais/servlets/WebUIServlet?appClassName=###APPCLASSNAME######RESOURCEID###&viewer=web&antiCache=###RANDOM###';
		
		const APP_ID_PATTERN = '@\<body onload\=\'window\.setTimeout\("WebUI_init\(\\\"([0-9]+)\\\", \\\"ais\\\", \\\"ais/webui2\\\"\)", 1\)\'@';
		const INTERNAL_ERROR_PATTERN = '@^function main\(\) { (?:alert|webui\.onAppClosedOnServer)\(\'([^\']*)\'\);? }$@m';
		const APP_LOCATION_PATTERN = '@webui\.startApp\("([^"]+)","([^"]+)"\);@';
		
		const IDENTITA_PATTERN = '@\<input class\=\'textFieldDefault\' jsct\=\'textField\' onDragStart\=\'comfac\.onDragStart\(event, this,window\)\' ondrop\=\'comfac\.onDrop\(event, this,window\)\'   ondeactivate\=\'comfac\.onDeactivate\(event, this, window\)\' onblur\=\'comfac\.onBlur\(event, this,window\)\' onfocus\=\'comfac\.handleEvent\(event, this, window\)\' onkeydown\=\'comfac\.onKeyDown\(event, this,window\)\' onkeypress\=\'comfac\.onKeyPress\(event, this,window\)\' onchange\=\'comfac\.onChange\(event, this,window\)\' onpaste\=\'comfac\.onPaste\(event, this,window\)\' oncut\=\'comfac\.onCut\(event, this,window\)\' oncontextmenu\=\'comfac\.onContextMenu\(event, this,window\)\' value\=\'([^\']+)\' tabIndex\=\'1\' pFCName\=\'helpButton\' nFCName\=\'detailStudentaButton\'@';
		
		const DATA_PATTERN = '@\<tbody id\=\'dataTabBody0\'\>(.*?)\</tbody\>@s';
		
		const INIT_DATA = '<request><serial>###NEWSERIAL###</serial><events><ev><event class=\'avc.ui.event.AVCComponentEvent\'><command>INIT</command></event></ev></events></request>';
		const EVIDENCIA_STUDIA_KILL_DATA = '<request><serial>###SERIAL###</serial><events><ev><event class=\'avc.framework.webui.WebUIKillEvent\'/></ev></events></request>';
		const NACITAJ_ZAPISNE_LISTY_DATA = '<request> <serial>###SERIAL###</serial> <events> <ev> <dlgName>VSES017_StudentZapisneListyDlg0</dlgName> <compName>nacitatDataAction</compName> <event class=\'avc.ui.event.AVCActionEvent\'></event> </ev> </events> <changedProps> <changedProperties><objName>app</objName><propertyValues> <nameValue><name>activeDlgName</name><value>VSES017_StudentZapisneListyDlg0</value></nameValue> </propertyValues></changedProperties> <changedProperties><objName>VSES017_StudentZapisneListyDlg0</objName> <propertyValues> <nameValue> <name>x</name> <value>-4</value> </nameValue> <nameValue> <name>y</name> <value>-4</value> </nameValue> <nameValue> <name>focusedComponent</name> <value>nacitatButton</value> </nameValue> </propertyValues> <embObjChProps> <changedProperties> <objName>studiaTable</objName> <propertyValues> <nameValue>  <name>dataView</name>  <isXml>true</isXml>  <value><![CDATA[  <root>  <selection>  <activeIndex>###STUDIUM###</activeIndex>  <selectedIndexes>###STUDIUM###</selectedIndexes>  </selection>  </root>  ]]></value> </nameValue><nameValue> <name>editMode</name>  <isXml>false</isXml>  <value>false</value></nameValue></propertyValues> <embObjChProps isNull=\'true\'/> </changedProperties> </embObjChProps> </changedProperties> </changedProps> </request>';
		const NACITAJ_PREDEMTY_ZAPISNEHO_LISTU = '<request><serial>###SERIAL###</serial><events><ev><dlgName>VSES017_StudentZapisneListyDlg0</dlgName><compName>terminyHodnoteniaAction</compName><event class=\'avc.ui.event.AVCActionEvent\'></event></ev></events><changedProps><changedProperties><objName>app</objName><propertyValues><nameValue><name>activeDlgName</name><value>VSES017_StudentZapisneListyDlg0</value></nameValue></propertyValues></changedProperties><changedProperties><objName>VSES017_StudentZapisneListyDlg0</objName><propertyValues><nameValue><name>x</name><value>-4</value></nameValue><nameValue><name>y</name><value>-4</value></nameValue><nameValue><name>focusedComponent</name><value>zapisneListyTable</value></nameValue></propertyValues><embObjChProps><changedProperties><objName>zapisneListyTable</objName><propertyValues><nameValue><name>dataView</name><isXml>true</isXml><value><![CDATA[<root><selection><activeIndex>###LIST###</activeIndex><selectedIndexes>###LIST###</selectedIndexes></selection></root>]]></value></nameValue><nameValue><name>editMode</name><isXml>false</isXml><value>false</value></nameValue></propertyValues><embObjChProps isNull=\'true\'/></changedProperties></embObjChProps></changedProperties></changedProps></request>';
		
		private static $tabulka_zoznam_studii = array(
			array('name' => 'rocnik',              'title' => 'ročník',               'order' => '0'),
			array('name' => 'skratka',             'title' => 'skratka',              'order' => '0'),
			array('name' => 'kruzok',              'title' => 'krúžok',               'order' => '0'),
			array('name' => 'studijnyProgram',     'title' => 'študijný program',     'order' => '0'),
			array('name' => 'doplnujuceUdaje',     'title' => 'doplňujúce údaje',     'order' => '0'),
			array('name' => 'zaciatokStudia',      'title' => 'začiatok štúdia',      'order' => '0'),
			array('name' => 'koniecStudia',        'title' => 'koniec štúdia',        'order' => '0'),
			array('name' => 'dlzkaVSemestroch',    'title' => 'dĺžka v semestroch',   'order' => '0'),
			array('name' => 'dlzkaStudia',         'title' => 'dĺžka štúdia',         'order' => '0'),
			array('name' => 'cisloDiplomu',        'title' => 'číslo diplomu',        'order' => '0'),
			array('name' => 'cisloZMatriky',       'title' => 'číslo z matriky',      'order' => '0'),
			array('name' => 'cisloVysvedcenia',    'title' => 'číslo vysvedčenia',    'order' => '0'),
			array('name' => 'cisloDodatku',        'title' => 'číslo dodatku',        'order' => '0'),
			array('name' => 'cisloEVI',            'title' => 'číslo EVI',            'order' => '0'),
			array('name' => 'cisloProgramu',       'title' => 'číslo programu',       'order' => '0'),
			array('name' => 'priznak',             'title' => 'príznak',              'order' => '0'),
			array('name' => 'organizacnaJednotka', 'title' => 'organizačná jednotka', 'order' => '0'),
			array('name' => 'rokStudia',           'title' => 'rok štúdia',           'order' => '0'),
		);	
		private static $tabulka_zoznam_zapisnych_listov = array(
			array('name' => 'akademickyRok',          'title' => 'akademický rok',           'order' => '0'),
			array('name' => 'rocnik',                 'title' => 'ročník',                   'order' => '0'),
			array('name' => 'studProgramSkratka',     'title' => 'krúžok',                   'order' => '0'),
			array('name' => 'studijnyProgram',        'title' => 'skratka',                  'order' => '0'),
			array('name' => 'doplnujuceUdaje',        'title' => 'doplňujúce údaje',         'order' => '0'),
			array('name' => 'datumZapisu',            'title' => 'dátum zápisu',             'order' => '0'),
			array('name' => 'potvrdenyZapis',         'title' => 'potvrdený zápis',          'order' => '0'),
			array('name' => 'podmienecnyZapis',       'title' => 'podmienečný zápis',        'order' => '0'),
			array('name' => 'dlzkaVSemestroch',       'title' => 'dĺžka v semestroch',       'order' => '0'),
			array('name' => 'cisloEVI',               'title' => 'číslo EVI',                'order' => '0'),
			array('name' => 'cisloProgramu',          'title' => 'číslo programu',           'order' => '0'),
			array('name' => 'datumSplnenia',          'title' => 'dátum splnenia',           'order' => '0'),
			array('name' => 'priznak',                'title' => 'príznak',                  'order' => '0'),
			array('name' => 'organizacnaJednotka',    'title' => 'organizačná jednotka',     'order' => '0'),
			array('name' => 'typFinacovania',         'title' => 'typ financovania',         'order' => '0'),
			array('name' => 'skratkaTypuFinacovania', 'title' => 'skratka typu finacovania', 'order' => '0'),
		);
		private static $tabulka_predmety_zapisneho_listu = array(
			array('name' => 'kodCastStPlanu',          'title' => 'kód časti študijného plánu', 'order' => '0'),
			array('name' => 'kodTypVyucby',            'title' => 'kód typu výučby',            'order' => '0'),
			array('name' => 'skratka',                 'title' => 'skratka',                    'order' => '0'),
			array('name' => 'nazov',                   'title' => 'názov',                      'order' => '0'),
			array('name' => 'kredit',                  'title' => 'kredit',                     'order' => '0'),
			array('name' => 'semester',                'title' => 'semester',                   'order' => '0'),
			array('name' => 'sposobUkoncenia',         'title' => 'spôsob ukončenia',           'order' => '0'),
			array('name' => 'pocetTerminov',           'title' => 'počet termínov',             'order' => '0'),
			array('name' => 'pocetAktualnychTerminov', 'title' => 'počet aktuálnych termínov',  'order' => '0'),
			array('name' => 'aktualnost',              'title' => 'aktuálnosť',                 'order' => '0'),
		);
		private static $tabulka_terminy_hodnotenia = array(
			array('name' => 'prihlaseny',          'title' => 'prihlásený', 'order' => '0'),
			array('name' => 'faza',            'title' => 'fáza',            'order' => '0'),
			array('name' => 'datum',            'title' => 'dátum',            'order' => '0'),
			array('name' => 'cas',            'title' => 'čas',            'order' => '0'),
			array('name' => 'miestnosti',            'title' => 'miestnosti',            'order' => '0'),
			array('name' => 'pocetPrihlasenych', 'title' => 'počet prihlásených',            'order' => '0'),
			array('name' => 'datumPrihlasenia', 'title' => 'dátum prihlásenia',            'order' => '0'),
			array('name' => 'datumOdhlasenia', 'title' => 'dátum odhlásenia',    'order' => '0'),
			array('name' => 'zapisal', 'title' => 'zapísal',    'order' => '0'),
			array('name' => 'pocetHodnotiacich', 'title' => 'počet hodnotiacich',    'order' => '0'),
			array('name' => 'hodnotiaci', 'title' => 'hodnotiaci',    'order' => '0'),
			array('name' => 'maxPocet', 'title' => 'maximálny počet',    'order' => '0'),
			array('name' => 'znamka', 'title' => 'známka',    'order' => '0'),
			array('name' => 'prihlasovanie', 'title' => 'prihlasovanie',    'order' => '0'),
			array('name' => 'odhlasovanie', 'title' => 'odhlasovanie',    'order' => '0'),
			array('name' => 'poznamka', 'title' => 'poznámka',    'order' => '0'),
			array('name' => 'zaevidoval', 'title' => 'zaevidoval',    'order' => '0'),
			array('name' => 'mozeOdhlasit', 'title' => 'može odhlásiť',    'order' => '0'),
			array('name' => 'skratkaPredmetu', 'title' => 'skratka predmetu',    'order' => '0'),
			array('name' => 'predmet', 'title' => 'predmet',    'order' => '0'),

		);
		
		public static $inputParameters = array();
		
		public static function prepareInputParameters()
		{
			if (isset($_GET['studium']))
			{
				if (!ctype_digit($_GET['studium'])) throw new Exception('Vstupný parameter "studium" musí byť typu integer.');
				self::$inputParameters['studium'] = $_GET['studium'];
			}

			if (isset($_GET['list']))
			{
				if (!ctype_digit($_GET['list'])) throw new Exception('Vstupný parameter "list" musí byť typu integer.');
				self::$inputParameters['list'] = $_GET['list'];
			}

			if (isset($_POST['login']))
			{
				if (empty($_POST['login'])) throw new Exception('Vstupný parameter "login" nesmie byť prázdny.');
				self::$inputParameters['login'] = $_POST['login'];
			}

			if (isset($_POST['krbpwd']))
			{
				if (empty($_POST['krbpwd'])) throw new Exception('Vstupný parameter "krbpwd" nesmie byť prázdny.');
				self::$inputParameters['krbpwd'] = $_POST['krbpwd'];
			}

			if (isset($_POST['cosignCookie']))
			{
				if (empty($_POST['cosignCookie'])) throw new Exception('Vstupný parameter "cosignCookie" nesmie byť prázdny.');
				self::$inputParameters['cosignCookie'] = $_POST['cosignCookie'];
			}
			
			if (isset($_GET['logout'])) self::$inputParameters['logout'] = true;
		}
		
		public static function getInputParameter($key = null)
		{
			if ($key === null) return self::$inputParameters;
			if (!isset(self::$inputParameters[$key])) return null;
			else return self::$inputParameters[$key];
		}
		
		public static	function prepareUrl($url)
		{
			$url = str_replace('###RANDOM###', rand(100000,999999), $url);
			$url = str_replace('###APPID###', self::$appId, $url);
			if (strpos($url, '###NEWSERIAL###') !== false)
			{
				self::$serial = 0;
				$url = str_replace('###NEWSERIAL###', 0, $url);
			}
			if (strpos($url, '###SERIAL###') !== false) $url = str_replace('###SERIAL###', self::getSerial(), $url);
			foreach (self::$inputParameters as $key => $value) $url = str_replace('###'.strtoupper($key).'###', $value, $url);
			return $url;
		}
		
		public static	function prepareData($data)
		{
			foreach ($data as &$item) $item = self::prepareUrl($item);
			return $data ;
		}
		
		public static function setAppId($response)
		{
			$matches = array();
			if (preg_match(self::APP_ID_PATTERN, $response, $matches))
			{
				if (self::$appId !== null) self::disconnect(); //otvorene "aplikacie" treba aj zatvarat, aby sa predislo chybe "Prekročený maximálny počet aplikácií v session."
				self::$appId = $matches[1];
			}
			else throw new Exception('Neviem najst appId v odpovedi v prvom kroku!');
		}
		
		public static function getSerial()
		{
			self::$serial++;
			return self::$serial;
		}
		
		public static function getStudentZoznamStudii($generateHtml = true)
		{
			$data = self::download(self::EVIDENTCIA_STUDIA);
			self::checkError($data, 'getStudentZoznamStudii @ first step');
			self::setAppId($data);

			$data = self::download(self::XML_INTERFACE, array('xml_spec' => self::INIT_DATA));
			self::checkError($data, 'getStudentZoznamStudii @ second step');
			
			$data = self::download(self::STUDENT_ZAPISNE_LISTY);
			self::checkError($data, 'getStudentZoznamStudii @ third step');
			self::$identity = pluck($data, self::IDENTITA_PATTERN);

			if ($generateHtml)
			{
				$data = pluck($data, self::DATA_PATTERN);
				$table = pluckAll($data, generatePattern(self::$tabulka_zoznam_studii));
				
				return DisplayManager::generateHtmlTable($table, 'Zoznam štúdií', 'studium');
			}
			else return true;	
		}
		
		public static function getStudentZapisneListy($generateHtml = true)
		{
			$data = self::download(self::XML_INTERFACE, array('xml_spec' => self::NACITAJ_ZAPISNE_LISTY_DATA));
			self::checkError($data, 'getStudentZapisneListy @ first step');

			if ($generateHtml)
			{
				$data = pluck($data, self::DATA_PATTERN);
				if ($data)
				{
					$table = pluckAll($data, generatePattern(self::$tabulka_zoznam_zapisnych_listov));
					return DisplayManager::generateHtmlTable($table, 'Zoznam zápisných listov', 'list', array('studium'));
				}
				else throw new Exception('getStudentZapisneListy @ data extraction');
			}
			else return true;
		}
		
		public static function getPredmetyZapisnehoListu($generateHtml = true)
		{
			$data = self::download(self::XML_INTERFACE, array('xml_spec' => self::NACITAJ_PREDEMTY_ZAPISNEHO_LISTU));
			self::checkError($data, 'getPredmetyZapisnehoListu @ first step');
			$data = pluckAll($data, self::APP_LOCATION_PATTERN);
			self::$inputParameters['appclassname'] = $data[0][1];
			self::$inputParameters['resourceid'] = $data[0][2];
			
			$data = self::download(self::TERMINY_HODNOTENIA);
			self::checkError($data, 'getPredmetyZapisnehoListu @ second step');
			self::setAppId($data);

			$data = self::download(self::XML_INTERFACE, array('xml_spec' => self::INIT_DATA));
			self::checkError($data, 'getPredmetyZapisnehoListu @ third step');

			$data = self::download(self::STUDENT_ZOZNAM_PRIHLASENI_NA_SKUSKU);
			//dump($data);
			self::checkError($data, 'getPredmetyZapisnehoListu @ fourth step');
			
			if ($generateHtml)
			{
				$data = pluckAll($data, self::DATA_PATTERN);

				$predmety = pluckAll($data[0][1], generatePattern(self::$tabulka_predmety_zapisneho_listu));
				$table1 = DisplayManager::generateHtmlTable($predmety, 'Predmety zápisného listu', null, array('studium', 'list'));
				
				$terminy = pluckAll($data[1][1], generatePattern(self::$tabulka_terminy_hodnotenia));
				$table2 = DisplayManager::generateHtmlTable($terminy, 'Termíny hodnotenia', null, array('studium', 'list'));

				return $table1.$table2;
			}
			else return true;	
		}
		
		public static function disconnect()
		{
			self::download(self::XML_INTERFACE, array('xml_spec' => self::EVIDENCIA_STUDIA_KILL_DATA));
		}
		
		public static function connect()
		{
			$data = self::download(self::LOGIN);
			if (preg_match('@\<title\>IIKS \- Prihlásenie\</title\>@', $data))
			{
				$login = self::getInputParameter('login');
				$krbpwd = self::getInputParameter('krbpwd');
				$cosignCookie = self::getInputParameter('cosignCookie');
				if ($login !== null && $krbpwd !== null)
				{
					$data = self::download(self::COSIGN_LOGIN, array('ref' => self::LOGIN, 'login'=> $login, 'krbpwd' => $krbpwd));
					if (!preg_match('@\<base href\="https://ais2\.uniba\.sk/ais/portal/pages/portal_layout\.jsp"\>@', $data)) throw new Exception('Nepodarilo sa prihlásiť.');
					$_SESSION['cosignLogin'] = true;
					redirect();
					return true;
				}
				else if ($cosignCookie !== null)
				{
					$_SESSION['cosignLogin'] = false;
					
					$cookieFile = getCookieFile();
					$fh = fopen($cookieFile, 'a');
					if (!$fh) throw new Exception('Neviem otvoriť súbor s cookies.');
					fwrite($fh, "ais2.uniba.sk	FALSE	/	TRUE	0	cosign-filter-ais2.uniba.sk	".str_replace(' ', '+', $cosignCookie));
					fclose($fh);
					redirect();
				}
				else return false;
			}
			return true;
		}
		
		public static function cosignLogout()
		{
			if ($_SESSION['cosignLogin']) self::download(self::COSIGN_LOGOUT, array('verify' => 'Odhlásiť', 'url'=> self::MAIN_PAGE));
			unlink(getCookieFile());
			redirect();
		}
		
		protected static function download($url, $post = null, $xWwwFormUrlencoded = true)
		{
			$url = self::prepareUrl($url);
			$ch = curl_init($url); 
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_COOKIEFILE, getCookieFile()); 
			curl_setopt($ch, CURLOPT_COOKIEJAR, getCookieFile()); 
			curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; sk; rv:1.9.1.7) Gecko/20091221 Firefox/3.5.7');
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
			curl_setopt($ch, CURLOPT_VERBOSE, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // AIS2 nema koser certifikat
			
			if (is_array($post))
			{
					curl_setopt($ch, CURLOPT_POST, true);
					$post = self::prepareData($post);
					if ($xWwwFormUrlencoded === true)
					{
						$newPost = '';
						foreach ($post as $key => $value) $newPost .= urlencode($key).'='.urlencode($value).'&';
						$post = substr($newPost, 0, -1);
					}
					curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			}
			
			$output = curl_exec($ch);
			if (curl_errno($ch)) echo curl_error($ch);

			if (strpos($output, "\x1f\x8b\x08\x00\x00\x00\x00\x00") === 0) $output = gzdecode($output); //ak to zacina ako gzip, tak to odzipujeme
			curl_close($ch);
			return $output;
		}
		
		protected static function checkError($response, $exceptionMessage = null)
		{
			if (preg_match(self::INTERNAL_ERROR_PATTERN, $response, $matches))
			{
				if (is_string($exceptionMessage)) throw new Exception('<b>'.$exceptionMessage.':</b> '.htmlspecialchars($matches[1]));
				return $matches[1][0];
			}
			else return null;
		}
		
	}

	
?>
