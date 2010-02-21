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

require_once 'AIS2AbstractScreen.php';
require_once 'Table.php';

	/**
	 * Trieda reprezentujúca jednu obrazovku so zoznamom štúdií a zápisných listov.
	 *
	 * @author majak
	 */
	class AIS2AdministraciaStudiaScreen extends AIS2AbstractScreen
	{
		protected $tabulka_zoznam_studii = array(
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
		protected $tabulka_zoznam_zapisnych_listov = array(
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

		protected $idCache = array();

		public function __construct()
		{
			parent::__construct('ais.gui.vs.es.VSES017App', '&kodAplikacie=VSES017');
		}

		public function getZoznamStudii()
		{
			$data = pluck($this->data, AIS2Utils::DATA_PATTERN);
			return new Table($this->tabulka_zoznam_studii, $data, 'Zoznam štúdií', 'studium');
		}

		public function getZapisneListy($studiumIndex)
		{
			$data = AIS2Utils::request($this->getXmlInterfaceLocation(), array('xml_spec' => '<request> <serial>'.$this->getSerial().'</serial> <events> <ev> <dlgName>VSES017_StudentZapisneListyDlg0</dlgName> <compName>nacitatDataAction</compName> <event class=\'avc.ui.event.AVCActionEvent\'></event> </ev> </events> <changedProps> <changedProperties><objName>app</objName><propertyValues> <nameValue><name>activeDlgName</name><value>VSES017_StudentZapisneListyDlg0</value></nameValue> </propertyValues></changedProperties> <changedProperties><objName>VSES017_StudentZapisneListyDlg0</objName> <propertyValues> <nameValue> <name>x</name> <value>-4</value> </nameValue> <nameValue> <name>y</name> <value>-4</value> </nameValue> <nameValue> <name>focusedComponent</name> <value>nacitatButton</value> </nameValue> </propertyValues> <embObjChProps> <changedProperties> <objName>studiaTable</objName> <propertyValues> <nameValue>  <name>dataView</name>  <isXml>true</isXml>  <value><![CDATA[  <root>  <selection>  <activeIndex>'.$studiumIndex.'</activeIndex>  <selectedIndexes>'.$studiumIndex.'</selectedIndexes>  </selection>  </root>  ]]></value> </nameValue><nameValue> <name>editMode</name>  <isXml>false</isXml>  <value>false</value></nameValue></propertyValues> <embObjChProps isNull=\'true\'/> </changedProperties> </embObjChProps> </changedProperties> </changedProps> </request>'));
			$data = pluck($data, AIS2Utils::DATA_PATTERN);
			return new Table($this->tabulka_zoznam_zapisnych_listov, $data, 'Zoznam zápisných listov', 'list', array('studium' => $studiumIndex));
		}

		public function getIdZapisnyList($zapisnyListIndex)
		{
			return $this->getIdFromZapisnyListIndex($zapisnyListIndex, 'idZapisnyList');
		}

		public function getIdStudium($zapisnyListIndex)
		{
			return $this->getIdFromZapisnyListIndex($zapisnyListIndex, 'idStudium');
		}

		protected function getIdFromZapisnyListIndex($zapisnyListIndex, $idType)
		{
			if (empty($this->idCache[$zapisnyListIndex]))
			{
				$data = AIS2Utils::request($this->getXmlInterfaceLocation(), array('xml_spec' => '<request><serial>'.$this->getSerial().'</serial><events><ev><dlgName>VSES017_StudentZapisneListyDlg0</dlgName><compName>terminyHodnoteniaAction</compName><event class=\'avc.ui.event.AVCActionEvent\'></event></ev></events><changedProps><changedProperties><objName>app</objName><propertyValues><nameValue><name>activeDlgName</name><value>VSES017_StudentZapisneListyDlg0</value></nameValue></propertyValues></changedProperties><changedProperties><objName>VSES017_StudentZapisneListyDlg0</objName><propertyValues><nameValue><name>x</name><value>-4</value></nameValue><nameValue><name>y</name><value>-4</value></nameValue><nameValue><name>focusedComponent</name><value>zapisneListyTable</value></nameValue></propertyValues><embObjChProps><changedProperties><objName>zapisneListyTable</objName><propertyValues><nameValue><name>dataView</name><isXml>true</isXml><value><![CDATA[<root><selection><activeIndex>'.$zapisnyListIndex.'</activeIndex><selectedIndexes>'.$zapisnyListIndex.'</selectedIndexes></selection></root>]]></value></nameValue><nameValue><name>editMode</name><isXml>false</isXml><value>false</value></nameValue></propertyValues><embObjChProps isNull=\'true\'/></changedProperties></embObjChProps></changedProperties></changedProps></request>'));
				$data = pluckAll($data, AIS2Utils::APP_LOCATION_PATTERN, true);
				$data = pluckAll($data[2], '@&idZapisnyList\=(?P<idZapisnyList>[0-9]*)&idStudium\=(?P<idStudium>[0-9]*)@', true);
				foreach (array_keys($data) as $key) if (is_numeric($key)) unset($data[$key]);
				$this->idCache[$zapisnyListIndex] = $data;
			}
			return $this->idCache[$zapisnyListIndex][$idType];
		}

	}
	
?>
