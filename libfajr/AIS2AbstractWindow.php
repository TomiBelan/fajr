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
 * Abstraktná trieda zastrešujúca spoločné funkcie pre screeny a dialogy.
 *
 * @author majak
 */
abstract class AIS2AbstractWindow
{
	protected $formName = null;
	protected $data = null;

	/**
	 * Získa nové sériové číslo používané v XML protokole na komunikáciu s AISom.
	 * @return int Seriové číslo.
	 */
	abstract protected function getSerial();
	
	/**
	* Získa identifikátor AIS aplikácie zastešujúcej daný window.
	* @return int
	*/
	abstract protected function getAppId();

	/**
	 * Vytvorí url XML interfacu pre komunikáciu s "aplikáciou" tejto obrazovky.
	 * @return string Url.
	 */
	protected function getXmlInterfaceLocation()
	{
		return 'https://ais2.uniba.sk/ais/servlets/WebUIServlet?appId='.$this->getAppId().'&antiCache='.random().'&viewer=web&viewer=web';
	}
	
	/**
	* Experimentalna funkcia snažiaca sa zovšeobecniť dodatočné requesty jednotlivých AIS aplikácií.
	* Je veľmi pravdepodobné, že sa bude meniť.
	*/
	protected function requestData($options)
	{
		$dlgName = $this->formName;
		$eventClass = 'avc.ui.event.AVCActionEvent';
		$command = null;
		$compName = null;
		$appProperties = array();
		$embObj = null;
		$appProperties = array();
		$objProperties = array();
		extract($options, EXTR_IF_EXISTS);

		if (!isset($appProperties['activeDlgName'])) $appProperties['activeDlgName'] = $dlgName;


		$xml_spec = '<request><serial>'.$this->getSerial().'</serial><events><ev><dlgName>'.$dlgName.'</dlgName>';
		if ($compName !== null) $xml_spec .= '<compName>'.$compName.'</compName>';
		$xml_spec .= '<event class=\''.$eventClass.'\'>';
		if ($command !== null) $xml_spec .= '<command>'.$command.'</command>';
		$xml_spec .= '</event></ev></events><changedProps><changedProperties><objName>app</objName><propertyValues>';
		foreach ($appProperties as $name => $value) $xml_spec .= '<nameValue><name>'.$name.'</name><value>'.$value.'</value></nameValue>';
		$xml_spec .= '</propertyValues></changedProperties><changedProperties><objName>'.$dlgName.'</objName><propertyValues>';
		foreach ($objProperties as $name => $value) $xml_spec .= '<nameValue><name>'.$name.'</name><value>'.$value.'</value></nameValue>';
		$xml_spec .= '</propertyValues><embObjChProps>';
		
		if ($embObj !== null)
		{
			$xml_spec .= '<changedProperties><objName>'.$embObj['objName'].'</objName><propertyValues><nameValue><name>dataView</name><isXml>true</isXml><value><![CDATA[<root><selection>';
			if (isset($embObj['dataView']) && is_array($embObj['dataView'])) foreach ($embObj['dataView'] as $name => $value) $xml_spec .= '<'.$name.'>'.$value.'</'.$name.'>';
			$xml_spec .= '</selection>';
			if (isset($embObj['visibleBuffers'])) $xml_spec .= '<visibleBuffers>'.$embObj['visibleBuffers'].'</visibleBuffers>';
			if (isset($embObj['loadedBuffers'])) $xml_spec .= '<loadedBuffers>'.$embObj['loadedBuffers'].'</loadedBuffers>';
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
			</changedProperties>';
		}
		$xml_spec .= '</embObjChProps></changedProperties></changedProps></request>';

		return AIS2Utils::request($this->getXmlInterfaceLocation(), array('xml_spec' => $xml_spec));
	}

}
?>
