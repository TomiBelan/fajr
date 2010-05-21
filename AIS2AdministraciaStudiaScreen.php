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

require_once 'AIS2AbstractScreen.php';
require_once 'AIS2TableParser.php';
require_once 'Table.php';

/**
 * Trieda reprezentujúca jednu obrazovku so zoznamom štúdií a zápisných listov.
 *
 * @author majak
 */
class AIS2AdministraciaStudiaScreen extends AIS2AbstractScreen
{
	protected $tabulka_zoznam_studii = array(
		// {{{
		array('aisname' => 'rocnik',
		      'title' => 'ročník',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'skratka',
		      'title' => 'skratka',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'kruzok',
		      'title' => 'krúžok',
		      'sortorder' => '0',
		      'visible' => false),
		array('aisname' => 'studijnyProgram',
		      'title' => 'študijný program',
		      'sortorder' => '0',
		      'visible'=>true),
		array('aisname' => 'doplnujuceUdaje',
		      'title' => 'doplňujúce údaje',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'zaciatokStudia',
		      'title' => 'začiatok štúdia',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'koniecStudia',
		      'title' => 'koniec štúdia',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'dlzkaVSemestroch',
		      'title' => 'dĺžka v semestroch',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'dlzkaStudia',
		      'title' => 'dĺžka štúdia',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'cisloDiplomu',
		      'title' => 'číslo diplomu',
		      'sortorder' => '0',
		      'visible' => false),
		array('aisname' => 'cisloZMatriky',
		      'title' => 'číslo z matriky',
		      'sortorder' => '0',
		      'visible' => false),
		array('aisname' => 'cisloVysvedcenia',
		      'title' => 'číslo vysvedčenia',
		      'sortorder' => '0',
		      'visible' => false),
		array('aisname' => 'cisloDodatku',
		      'title' => 'číslo dodatku',
		      'sortorder' => '0',
		      'visible' => false),
		array('aisname' => 'cisloEVI',
		      'title' => 'číslo EVI',
		      'sortorder' => '0',
		      'visible' => false),
		array('aisname' => 'cisloProgramu',
		      'title' => 'číslo programu',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'priznak',
		      'title' => 'príznak',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'organizacnaJednotka',
		      'title' => 'organizačná jednotka',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'rokStudia',
		      'title' => 'rok štúdia',
		      'sortorder' => '0',
		      'visible' => true),
		// }}}
	);


	protected $tabulka_zoznam_zapisnych_listov = array(
		// {{{
		array('aisname' => 'akademickyRok',
		      'title' => 'akademický rok',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'rocnik',
		      'title' => 'ročník',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'studProgramSkratka',
		      'title' => 'krúžok',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'studijnyProgram',
		      'title' => 'skratka',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'doplnujuceUdaje',
		      'title' => 'doplňujúce údaje',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'datumZapisu',
		      'title' => 'dátum zápisu',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'potvrdenyZapis',
		      'title' => 'potvrdený zápis',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'podmienecnyZapis',
		      'title' => 'podmienečný zápis',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'dlzkaVSemestroch',
		      'title' => 'dĺžka v semestroch',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'cisloEVI',
		      'title' => 'číslo EVI',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'cisloProgramu',
		      'title' => 'číslo programu',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'datumSplnenia',
		      'title' => 'dátum splnenia',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'priznak',
		      'title' => 'príznak',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'organizacnaJednotka',
		      'title' => 'organizačná jednotka',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'typFinacovania',
		      'title' => 'typ financovania',
		      'sortorder' => '0',
		      'visible' => true),
		array('aisname' => 'skratkaTypuFinacovania',
		      'title' => 'skratka typu finacovania',
		      'sortorder' => '0',
		      'visible' => true),
		// }}}
	);

	protected $idCache = array();

	public function __construct()
	{
		parent::__construct('ais.gui.vs.es.VSES017App', '&kodAplikacie=VSES017');
	}

	public function getZoznamStudii()
	{
		$data = match($this->data, AIS2Utils::DATA_PATTERN);
		$tableParser = new
			AIS2TableParser($this->tabulka_zoznam_studii);
		$tableData = $tableParser->parseHtml($data);
		return new Table($this->tabulka_zoznam_studii, $tableData, 'Zoznam štúdií', 'studium');
	}

	public function getZapisneListy($studiumIndex)
	{
		$data = AIS2Utils::request($this->getXmlInterfaceLocation(), array('xml_spec' => '
<request>
	<serial>'.$this->getSerial().'</serial>
	<events><ev>
		<dlgName>VSES017_StudentZapisneListyDlg0</dlgName>
		<compName>nacitatDataAction</compName>
		<event class=\'avc.ui.event.AVCActionEvent\'></event>
	</ev></events>
	<changedProps>
		<changedProperties>
			<objName>app</objName>
			<propertyValues><nameValue>
				<name>activeDlgName</name><value>VSES017_StudentZapisneListyDlg0</value>
			</nameValue></propertyValues>
		</changedProperties>
		<changedProperties>
			<objName>VSES017_StudentZapisneListyDlg0</objName>
			<propertyValues>
				<nameValue><name>x</name><value>-4</value></nameValue>
				<nameValue><name>y</name><value>-4</value></nameValue>
				<nameValue><name>focusedComponent</name><value>nacitatButton</value></nameValue>
			</propertyValues>
			<embObjChProps><changedProperties>
				<objName>studiaTable</objName>
				<propertyValues>
					<nameValue>
						<name>dataView</name>
						<isXml>true</isXml>
						<value><![CDATA[
							<root><selection>
								<activeIndex>'.$studiumIndex.'</activeIndex>
								<selectedIndexes>'.$studiumIndex.'</selectedIndexes>
							</selection></root>
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
</request>'));
		$data = match($data, AIS2Utils::DATA_PATTERN);
		$tableParser = new
			AIS2TableParser($this->tabulka_zoznam_zapisnych_listov);
		$tableData = $tableParser->parseHtml($data);
		return new Table($this->tabulka_zoznam_zapisnych_listov, $tableData, 'Zoznam zápisných listov', 'list', array('studium' => $studiumIndex));
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
			$data = AIS2Utils::request($this->getXmlInterfaceLocation(), array('xml_spec' => '
<request>
	<serial>'.$this->getSerial().'</serial>
	<events><ev>
		<dlgName>VSES017_StudentZapisneListyDlg0</dlgName>
		<compName>terminyHodnoteniaAction</compName>
		<event class=\'avc.ui.event.AVCActionEvent\'></event>
	</ev></events>
	<changedProps>
		<changedProperties>
			<objName>app</objName>
			<propertyValues><nameValue>
				<name>activeDlgName</name><value>VSES017_StudentZapisneListyDlg0</value>
			</nameValue></propertyValues>
		</changedProperties>
		<changedProperties>
			<objName>VSES017_StudentZapisneListyDlg0</objName>
			<propertyValues>
				<nameValue><name>x</name><value>-4</value></nameValue>
				<nameValue><name>y</name><value>-4</value></nameValue>
				<nameValue><name>focusedComponent</name><value>zapisneListyTable</value></nameValue>
			</propertyValues>
			<embObjChProps><changedProperties>
				<objName>zapisneListyTable</objName>
				<propertyValues>
					<nameValue>
						<name>dataView</name>
						<isXml>true</isXml>
						<value><![CDATA[
							<root><selection>
								<activeIndex>'.$zapisnyListIndex.'</activeIndex>
								<selectedIndexes>'.$zapisnyListIndex.'</selectedIndexes>
							</selection></root>
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
</request>'));
			// FIXME: toto tunak spravit nejak krajsie
			$data = matchAll($data, AIS2Utils::APP_LOCATION_PATTERN, true);
			$data = matchAll($data[2], '@&idZapisnyList\=(?P<idZapisnyList>[0-9]*)&idStudium\=(?P<idStudium>[0-9]*)@', true);
			foreach (array_keys($data) as $key) if (is_numeric($key)) unset($data[$key]);
			$this->idCache[$zapisnyListIndex] = $data;
		}
		return $this->idCache[$zapisnyListIndex][$idType];
	}

}
?>
