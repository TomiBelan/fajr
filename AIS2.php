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
		
		const TABULKA_ZOZNAM_STUDII = '@\<tr id\=\'row_(?P<index>[^\']*)\' rid\=\'[^\']*\'\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<rocnik>[^<]*)\</div\>\</td\>\<td\>\<div\>(?P<skratka>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<kod>[^<]*)\</div\>\</td\>\<td\>\<div\>(?P<studijnyProgram>[^<]*)\</div\>\</td\>\<td\>\<div\>(?P<doplnujuceUdaje>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<zaciatokStudia>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<koniecStudia>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:right; \'\>\<div\>(?P<dlzkaVSemestroch>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:right; \'\>\<div\>(?P<dlzkaStudia>[^<]*)\</div\>\</td\>\<td\>\<div\>(?P<cisloDiplomu>[^<]*)\</div\>\</td\>\<td\>\<div\>(?P<cisloZMatriky>[^<]*)\</div\>\</td\>\<td\>\<div\>(?P<cisloVysvedcenia>[^<]*)\</div\>\</td\>\<td\>\<div\>(?P<cisloDodatku>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<priznak>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<rokStudia>[^<]*)\</div\>\</td\>\</tr\>@';
		const TABULKA_ZOZNAM_ZAPISNYCH_LISTOV = '@\<tr id\=\'row_(?P<index>[^\']*)\' rid\=\'[^\']*\'\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<akademickyRok>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<rocnik>[^<]*)\</div\>\</td\>\<td\>\<div\>(?P<studProgramSkratka>[^<]*)\</div\>\</td\>\<td\>\<div\>(?P<studijnyProgram>[^<]*)\</div\>\</td\>\<td\>\<div\>(?P<doplnujuceUdaje>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<datumZapisu>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<potvrdenyZapis>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<podmienecnyZapis>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:right; \'\>\<div\>(?P<dlzkaVSemestroch>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<datumSplnenia>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<priznak>[^<]*)\</div\>\</td\>\<td\>\<div\>(?P<typFinacovania>[^<]*)\</div\>\</td\>\<td\>\<div\>(?P<skratkaTypuFinacovania>[^<]*)\</div\>\</td\>\</tr\>@';
		const TABULKA_PREDMETY_ZAPISNEHO_LISTU = '@\<tr id\=\'row_(?P<index>[^\']*)\' rid\=\'[^\']*\'\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<kodCastStPlanu>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<kodTypVyucby>[^<]*)\</div\>\</td\>\<td\>\<div\>(?P<skratka>[^<]*)\</div\>\</td\>\<td\>\<div\>(?P<nazov>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<kredit>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<semester>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<sposobUkoncenia>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:right; \'\>\<div\>(?P<pocetTerminov>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:right; \'\>\<div\>(?P<pocetAktualnychTerminov>[^<]*)\</div\>\</td\>\<td\>\<div\>(?P<aktualnost>[^<]*)\</div\>\</td\>\</tr\>@';
		const TABULKA_TERMINY_HODNOTENIA = '@\<tr id\=\'row_(?P<index>[^\']*)\' rid\=\'[^\']*\'\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<prihlaseny>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<faza>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<datum>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<cas>[^<]*)\</div\>\</td\>\<td\>\<div\>(?P<miestnosti>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<pocetPrihlasenych>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<datumPrihlasenia>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<datumOdhlasenia>[^<]*)\</div\>\</td\>\<td\>\<div\>(?P<zapisal>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<pocetHodnotiacich>[^<]*)\</div\>\</td\>\<td\>\<div\>(?P<hodnotiaci>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<maxPocet>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<znamka>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<prihlasovanie>[^<]*)\</div\>\</td\>\<td style\=\' text\-align\:center; \'\>\<div\>(?P<odhlasovanie>[^<]*)\</div\>\</td\>\<td\>\<div\>(?P<poznamka>[^<]*)\</div\>\</td\>\<td\>\<div\>(?P<zaevidoval>[^<]*)\</div\>\</td\>\<td style\=\'visibility\:hidden\'\>\<div\>(?P<mozeOdhlasit>[^<]*)\</div\>\</td\>\<td\>\<div\>(?P<skratkaPredmetu>[^<]*)\</div\>\</td\>\<td\>\<div\>(?P<predmet>[^<]*)\</div\>\</td\>\</tr\>@';
		
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
				$table = pluckAll($data, self::TABULKA_ZOZNAM_STUDII);

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
					$table = pluckAll($data, self::TABULKA_ZOZNAM_ZAPISNYCH_LISTOV);
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

				$predmety = pluckAll($data[0][1], self::TABULKA_PREDMETY_ZAPISNEHO_LISTU);
				$table1 = DisplayManager::generateHtmlTable($predmety, 'Predmety zápisného listu', null, array('studium', 'list'));
				
				$terminy = pluckAll($data[1][1], self::TABULKA_TERMINY_HODNOTENIA);
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
					if (!preg_match('@\<base href\="https://ais2\.uniba\.sk/ais/portal/pages/index\.jsp"\>@', $data)) throw new Exception('Nepodarilo sa prihlásiť.');
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
