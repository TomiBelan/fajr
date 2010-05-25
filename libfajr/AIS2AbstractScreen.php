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

/**
 * Abstraktná trieda reprezentujúca jednu obrazovku v AISe.
 *
 * @author majak
 */
abstract class AIS2AbstractScreen
{
	protected $appId = null;
	protected $formName = null;
	protected $serial = null;
	protected $data = null;

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
			// TODO FIXME Tu treba vyhodit vynimku spravneho typu a odhlasenie osetrit v aplikacii
			//AIS2Utils::cosignLogout(); // logoutni aby to nemusel robit uzivatel
			throw new Exception("AIS hlási neautorizovaný prístup -
				pravdepodobne vypršala platnosť cookie");
		}
		$this->setFormName($response);

		$this->data = AIS2Utils::request('https://ais2.uniba.sk/ais/servlets/WebUIServlet?appId='.$this->appId.'&form='.$this->formName.'&antiCache='.random());
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

	/**
	 * Vytvorí url XML interfacu pre komunikáciu s "aplikáciou" tejto obrazovky.
	 * @return string Url.
	 */
	protected function getXmlInterfaceLocation()
	{
		return 'https://ais2.uniba.sk/ais/servlets/WebUIServlet?appId='.$this->appId.'&antiCache='.random().'&viewer=web&viewer=web';
	}
	
	/**
	* Experimentalna funkcia snažiaca sa zovšeobecniť dodatočné requesty jednotlivých AIS aplikácií.
	* Je veľmi pravdepodobné, že sa bude meniť.
	*/
	protected function requestData($dlgName, $compName, $embObjName, $appProperties = array(), $objProperties = array(), $embObjDataView = array(), $visibleBuffers = null, $loadedBuffers = null)
	{
		if (!isset($appProperties['activeDlgName'])) $appProperties['activeDlgName'] = $dlgName;
		$xml_spec = '
<request>
<serial>'.$this->getSerial().'</serial>
<events><ev>
	<dlgName>'.$dlgName.'</dlgName>
	<compName>'.$compName.'</compName>
	<event class=\'avc.ui.event.AVCActionEvent\'></event>
</ev></events>
<changedProps>
	<changedProperties>
		<objName>app</objName>
		<propertyValues>';
		foreach ($appProperties as $name => $value) $xml_spec .= '<nameValue><name>'.$name.'</name><value>'.$value.'</value></nameValue>';
		$xml_spec .= '
		</propertyValues>
	</changedProperties>
	<changedProperties>
		<objName>'.$dlgName.'</objName>
		<propertyValues>';
		foreach ($objProperties as $name => $value) $xml_spec .= '<nameValue><name>'.$name.'</name><value>'.$value.'</value></nameValue>';
		$xml_spec .= '
		</propertyValues>
		<embObjChProps><changedProperties>
			<objName>'.$embObjName.'</objName>
			<propertyValues>
				<nameValue>
					<name>dataView</name>
					<isXml>true</isXml>
					<value><![CDATA[
						<root><selection>';
		foreach ($embObjDataView as $name => $value) $xml_spec .= '<'.$name.'>'.$value.'</'.$name.'>';
		$xml_spec .= '
						</selection>';
		if ($visibleBuffers !== null) $xml_spec .= '<visibleBuffers>'.$visibleBuffers.'</visibleBuffers>';
		if ($loadedBuffers !== null) $xml_spec .= '<loadedBuffers>'.$loadedBuffers.'</loadedBuffers>';
		$xml_spec .= '
						</root>
					]]></value>
				</nameValue>
				<nameValue>
					<name>editMode</name>
					<isXml>false</isXml>
					<value>false</value>
				</nameValue>
			</propertyValues>
			<embObjChProps isNull=\'true\'/>
		</changedProperties></embObjChProps>
	</changedProperties>
</changedProps>
</request>';

		return AIS2Utils::request($this->getXmlInterfaceLocation(), array('xml_spec' => $xml_spec));
	}

	protected function getDialogName($response)
	{
		return match($response, AIS2Utils::DIALOG_NAME_PATTERN);
	}
}
?>
