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

	require_once 'supporting_functions.php';

/**
 * Trieda združujúca rôzne základné veci pre prácu s AISom,
 * ako napríklad prihlásenie a odhlásenie.
 *
 * @author majak
 */
	class AIS2Utils
	{
	
		const COSIGN_LOGIN = 'https://login.uniba.sk/cosign.cgi';
		const COSIGN_LOGOUT = 'https://login.uniba.sk/logout.cgi';
		const LOGIN = 'https://ais2.uniba.sk/ais/login.do';
		const MAIN_PAGE = 'https://ais2.uniba.sk';

		const INTERNAL_ERROR_PATTERN = '@^function main\(\) { (?:alert|webui\.onAppClosedOnServer)\(\'([^\']*)\'\);? }$@m';
		const APP_LOCATION_PATTERN = '@webui\.startApp\("([^"]+)","([^"]+)"\);@';
		const DATA_PATTERN = '@\<tbody id\=\'dataTabBody0\'\>(.*?)\</tbody\>@s';

		public static $requests=0;
		public static $requestsSize=0;
		
		/**
		 * Ak používateľ nie je prihlásený v AISe, tak sa skúsi podľa vstupných parametrov
		 * prihlásiť buď cez cosign, alebo pomocou cookie.
		 * @return boolean Úspešnosť prihlásenia.
		 */
		public static function loginViaCosign($login, $krbpwd)
		{
			$data = download(self::LOGIN);
			if (preg_match('@\<title\>IIKS \- Prihlásenie\</title\>@', $data))
			{
				assert($login !== null && $krbpwd !== null);
				$data = download(self::COSIGN_LOGIN, array('ref' => self::LOGIN, 'login'=> $login, 'krbpwd' => $krbpwd));

				if (!preg_match('@\<base href\="https://ais2\.uniba\.sk/ais/portal/pages/portal_layout\.jsp"\>@', $data))
				{
					if (preg_match('@Pri pokuse o prihlásenie sa vyskytol problém:@', $data))
					{
						if ($reason = match($data, '@\<div style\="color:#FF0000;"\>\<b\>([^<]*)\<\/b\>@'))
						{
							throw new Exception('Nepodarilo sa prihlásiť, dôvod: <b>'.$reason.'</b>');
						}
					}
					throw new Exception('Nepodarilo sa prihlásiť, dôvod neznámy.');
				}
				$_SESSION['cosignLogin'] = true;
				redirect();
				return true;
			}
			return true;
		}
		
		public static function loginViaCookie($cosignCookie)
		{
			assert($cosignCookie !== null);
			$_SESSION['cosignLogin'] = false;
			
			$cookieFile = getCookieFile();
			$fh = fopen($cookieFile, 'a');
			if (!$fh) throw new Exception('Neviem otvoriť súbor s cookies.');
			fwrite($fh, "ais2.uniba.sk	FALSE	/	TRUE	0	cosign-filter-ais2.uniba.sk	".str_replace(' ', '+', $cosignCookie));
			fclose($fh);
			$data = download(self::LOGIN);
			if (preg_match('@\<title\>IIKS \- Prihlásenie\</title\>@', $data))
				return false;
			redirect();
		}

		/**
		 * Odhlási z Cosignu a zmaže lokálne cookies.
		 */
		public static function cosignLogout()
		{
			if (isset($_SESSION['cosignLogin'])) download(self::COSIGN_LOGOUT, array('verify' => 'Odhlásiť', 'url'=> self::MAIN_PAGE));
			unlink(getCookieFile());
			unset($_SESSION['cosignLogin']);
			redirect();
		}

		/**
		 * Stiahne zadanú stránku a skontroluje ci pri tom nenastala chyba v AISe.
		 * @param string Požadovaná url.
		 * @param array Pole s POST dátami.
		 * @param boolean Príznak určujúci, či sa má vykonať kontrola chyby v prijatých dátach.
		 * @return string Načítaná stránka.
		 */
		public static function request($url, $post = null, $checkError = true)
		{
			$response = download($url, $post, true);
			self::$requests++;
			self::$requestsSize+=strlen($response);
			if ($checkError == true) 
			{
				$matches = array();
				if (preg_match(self::INTERNAL_ERROR_PATTERN, $response, $matches))
				{
					throw new Exception('<b>Nastala chyba pri requeste.</b><br/>Zdôvodnenie od AISu:'.hescape($matches[1]).'<br/>Požadovaná url: '.hescape($url));
				}
			}
			return $response;
		}
		
	}

	
?>
