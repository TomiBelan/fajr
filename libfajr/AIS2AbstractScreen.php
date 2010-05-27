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

require_once 'AIS2AbstractWindow.php';
require_once 'AIS2LoginException.php';
 
/**
 * Abstraktná trieda reprezentujúca jednu obrazovku v AISe.
 *
 * @author majak
 */
abstract class AIS2AbstractScreen extends AIS2AbstractWindow
{
	protected $appId = null;
	protected $serial = null;
	public $openedDialog = false;
	
	/**
	 * Konštruktor.
	 * Nadviaže spojenie, spustí danú "aplikáciu" v AISe
	 * a natiahne prvotné dáta do atribútu $data.
	 *
	 * @param string $appClassName Názov "triedy" obsluhujúcej danú obrazovku v AISe.
	 * @param string $identifiers Konkrétne parametre pre vyvolanie danej obrazovky.
	 */
	public function __construct($appClassName, $identifiers)
	{
		$this->serial = 0;
		$location = 'https://ais2.uniba.sk/ais/servlets/WebUIServlet?appClassName='.$appClassName.$identifiers.'&viewer=web&antiCache='.random();
		$response = AIS2Utils::request($location);
		$this->setAppId($response);

		$response = AIS2Utils::request($this->getXmlInterfaceLocation(), array('xml_spec' => '<request><serial>'.$this->getSerial().'</serial><events><ev><event class=\'avc.ui.event.AVCComponentEvent\'><command>INIT</command></event></ev></events></request>'));
		if (preg_match("/Neautorizovaný prístup!/", $response)) {
			// logoutni aby to nemusel robit uzivatel
			throw new AIS2LoginException("AIS hlási neautorizovaný prístup -
				pravdepodobne vypršala platnosť cookie");
		}
		$this->setFormName($response);

		$this->data = AIS2Utils::request('https://ais2.uniba.sk/ais/servlets/WebUIServlet?appId='.$this->getAppId().'&form='.$this->formName.'&antiCache='.random());
	}

	/**
	 * Deštruktor.
	 * Zatvorí danú "aplikáciu" v AISe,
	 * aby sa nevyčerpal limit otvorených aplikácii na session.
	 */
	public function  __destruct()
	{
		AIS2Utils::request($this->getXmlInterfaceLocation(), array('xml_spec' => '<request><serial>'.$this->getSerial().'</serial><events><ev><event class=\'avc.framework.webui.WebUIKillEvent\'/></ev></events></request>'));
	}

	/**
	 * Vygeneruje nové sériové číslo používané v XML protokole na komunikáciu s AISom.
	 * @return int Nové seriové číslo v poradí.
	 */
	protected function getSerial()
	{
		return $this->serial++;
	}
	
	protected function getAppId()
	{
		return $this->appId;
	}
	
	/**
	 * Nastaví atribút $appId, ktorý pomocou regulárneho výrazu nájde vo vstupných dátach.
	 * @param string $response Odpoveď AISu v HTML formáte z inicializačnej časti komunikácie.
	 */
	protected function setAppId($response)
	{
		$matches = array();
		if (preg_match('@\<body onload\=\'window\.setTimeout\("WebUI_init\(\\\"([0-9]+)\\\", \\\"ais\\\", \\\"ais/webui2\\\"\)", 1\)\'@', $response, $matches))
		{
			$this->appId = $matches[1];
		}
		else throw new Exception('Neviem nájsť appId v odpovedi vo fáze inicializácie triedy '.__CLASS__.'!');
	}

	/**
	 * Nastaví atribút $formName, ktorý pomocou regulárneho výrazu nájde vo vstupných dátach.
	 * @param string $response Odpoveď AISu v HTML formáte z inicializačnej časti komunikácie.
	 */
	protected function setFormName($response)
	{
		$matches = array();
		if (preg_match('@dialogManager\.openMainDialog\("(?P<formName>[^"]*)","(?P<name>[^"]*)","(?P<formId>[^"]*)",[0-9]*,[^,]*,[^,]*,[^,]*,[^,]*,[^,]*,[^,]*,[^,]*,[^,]*\);@', $response, $matches))
		{
			$this->formName = $matches['formName'];
		}
		else throw new Exception('Neviem nájsť formName v odpovedi vo fáze inicializácie triedy '.__CLASS__.'!');
	}

}
?>
