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
require_once 'AIS2Table.php';
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
		'rocnik',
		'skratka',
		'kruzok',
		'studijnyProgram',
		'doplnujuceUdaje',
		'zaciatokStudia',
		'koniecStudia',
		'dlzkaVSemestroch',
		'dlzkaStudia',
		'cisloDiplomu',
		'cisloZMatriky',
		'cisloVysvedcenia',
		'cisloDodatku',
		'cisloEVI',
		'cisloProgramu',
		'priznak',
		'organizacnaJednotka',
		'rokStudia',
		// }}}
	);

	protected $tabulka_zoznam_zapisnych_listov = array(
		// {{{
		'akademickyRok',
		'rocnik',
		'studProgramSkratka',
		'studijnyProgram',
		'doplnujuceUdaje',
		'datumZapisu',
		'potvrdenyZapis',
		'podmienecnyZapis',
		'dlzkaVSemestroch',
		'cisloEVI',
		'cisloProgramu',
		'datumSplnenia',
		'priznak',
		'organizacnaJednotka',
		'typFinacovania',
		'skratkaTypuFinacovania',
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
		return new AIS2Table($this->tabulka_zoznam_studii, $data);
	}

	public function getZapisneListy($studiumIndex)
	{
		$data = $this->requestData(
			'VSES017_StudentZapisneListyDlg0',
			'nacitatDataAction',
			'studiaTable',
			$appProperties = array(),
			$objProperties = array(
				'x' => -4,
				'y' => -4,
				'focusedComponent' => 'nacitatButton',
			),
			$embObjDataView = array(
				'activeIndex' => $studiumIndex,
				'selectedIndexes' => $studiumIndex,
			)
		);
		
		$data = match($data, AIS2Utils::DATA_PATTERN);
		return new AIS2Table($this->tabulka_zoznam_zapisnych_listov, $data);
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
			$data = $this->requestData(
				'VSES017_StudentZapisneListyDlg0',
				'terminyHodnoteniaAction',
				'zapisneListyTable',
				$appProperties = array(),
				$objProperties = array(
					'x' => -4,
					'y' => -4,
					'focusedComponent' => 'zapisneListyTable',
				),
				$embObjDataView = array(
					'activeIndex' => $zapisnyListIndex,
					'selectedIndexes' => $zapisnyListIndex,
				)
			);
		
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
