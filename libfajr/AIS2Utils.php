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
	require_once 'AIS2CurlConnection.php';

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
	 * @deprecated Bude sa priamo používať inštancia AIS2Connection
	 */
	public static function request($url, $post = null, $checkError = true)
	{
		static $connection = null;

		if ($connection === null) {
			$connection = new AIS2CurlConnection(getCookieFile());
		}

		if (is_array($post)) {
			$response = $connection->post($url, $post);
		}
		else {
			$response = $connection->get($url);
		}
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
	
	/**
	 * predpokladame AIS format datumu a casu, t.j.
	 * vo formate "11.01.2010 08:30"
	 */
	public static function parseAISDateTime($str) {
		// Pozn. strptime() nefunguje na windowse, preto pouzijeme regex
		$pattern =
			'@(?P<tm_mday>[0-3][0-9])\.(?P<tm_mon>[0-1][0-9])\.(?P<tm_year>20[0-9][0-9])'.
			' (?P<tm_hour>[0-2][0-9]):(?P<tm_min>[0-5][0-9]*)@';
		$datum = matchAll($str, $pattern);
		if (!$datum) {
			throw new Exception("Chyba pri parsovaní dátumu a času");
		}
		$datum=$datum[0];
		
		return mktime($datum["tm_hour"],$datum["tm_min"],0,
				$datum["tm_mon"],$datum["tm_mday"],$datum["tm_year"]);
	}
	
	/**
	 * predpokladame range v 2 moznych standardnych ais formatoch
	 * "do [datum a cas]"
	 * "[datum a cas] do [datum a cas]"
	 * @see parseAISDateTime
	 */
	public static function parseAISDateTimeRange($str) {
		$pattern = '@(?P<od>[0-9:. ]*)do (?P<do>[0-9:. ]*)@';
		$data = matchAll($str, $pattern);
		$data = $data[0];
		if ($data['od'] == '') {
			$data['od'] = null;
		} else {
			$data['od'] = self::parseAISDateTime($data['od']);
		}
		$data['do'] = self::parseAISDateTime($data['do']);
		return $data;
	}
	
}


?>
