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

	require_once 'supporting_functions.php';

/**
 * Trieda združujúca rôzne základné veci pre prácu s AISom
 *
 * @author majak
 */
class AIS2Utils
{

	const INTERNAL_ERROR_PATTERN = '@^function main\(\) { (?:alert|webui\.onAppClosedOnServer)\(\'([^\']*)\'\);? }$@m';
	const APP_LOCATION_PATTERN = '@webui\.startApp\("([^"]+)","([^"]+)"\);@';
	const DIALOG_NAME_PATTERN = '@dialogManager\.openDialog\("([^"]+)",@';
	const DATA_PATTERN = '@\<tbody id\=\'dataTabBody0\'\>(.*?)\</tbody\>@s';

	public static $requests=0;
	public static $requestsSize=0;

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