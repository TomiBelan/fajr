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
	 * Trieda reprezentujúca jednu obrazovku so zoznamom predmetov zápisného listu
	 * a termínov hodnotenia.
	 *
	 * @author majak
	 */
	class AIS2TerminyHodnoteniaScreen extends AIS2AbstractScreen
	{
		protected $tabulka_predmety_zapisneho_listu = array(
			// {{{
			'kodCastStPlanu',
			'kodTypVyucby',
			'skratka',
			'nazov',
			'kredit',
			'semester',
			'sposobUkoncenia',
			'pocetTerminov',
			'pocetAktualnychTerminov',
			'aktualnost',
			// }}}
		);
		protected $tabulka_terminy_hodnotenia = array(
			// {{{
			'prihlaseny',
			'faza',
			'datum',
			'cas',
			'miestnosti',
			'pocetPrihlasenych',
			'datumPrihlasenia',
			'datumOdhlasenia',
			'zapisal',
			'pocetHodnotiacich',
			'hodnotiaci',
			'maxPocet',
			'znamka',
			'prihlasovanie',
			'odhlasovanie',
			'poznamka',
			'zaevidoval',
			'mozeOdhlasit',
			'skratkaPredmetu',
			'predmet',
			// }}}
		);
		protected $tabulka_vyber_terminu_hodnotenia = array(
			// {{{
			'kodFaza',
			'dat',
			'cas',
			'miestnosti',
			'pocetPrihlasenych',
			'maxPocet',
			'pocetHodn',
			'hodnotiaci',
			'prihlasovanie',
			'odhlasovanie',
			'poznamka',
			'zaevidoval',
			// }}}
		);

		public function __construct($idZapisnyList, $idStudium)
		{
			parent::__construct('ais.gui.vs.es.VSES007App', '&kodAplikacie=VSES007&idZapisnyList='.$idZapisnyList.'&idStudium='.$idStudium);
		}

		public function getPredmetyZapisnehoListu()
		{
			$data = matchAll($this->data, AIS2Utils::DATA_PATTERN);
			return new AIS2Table($this->tabulka_predmety_zapisneho_listu, $data[0][1]);
		}

		public function getTerminyHodnotenia()
		{
			$data = matchAll($this->data, AIS2Utils::DATA_PATTERN);
			return new AIS2Table($this->tabulka_terminy_hodnotenia, $data[1][1]);
		}
		
		public function getZoznamTerminov($predmetIndex)
		{
			$data = $this->requestData(
				'VSES007_StudentZoznamPrihlaseniNaSkuskuDlg0',
				'pridatTerminAction',
				'predmetyTable',
				$appProperties = array(
					'width' => 1326,
					'height' => 650,
				),
				$objProperties = array(
					'width' => 1318,
					'height' => 642,
					'x' => -4,
					'y' => -4,
					'focusedComponent' => 'pridatButton',
				),
				$embObjDataView = array(
					'activeIndex' => $predmetIndex,
					'selectedIndexes' => $predmetIndex,
				),
				0,
				0
			);
			

			$formName = $this->getDialogName($data);
			$location = 'https://ais2.uniba.sk/ais/servlets/WebUIServlet?appId='.$this->appId.'&form='.$formName.'&antiCache='.random();
			$response = AIS2Utils::request($location);
			$data = matchAll($response, AIS2Utils::DATA_PATTERN);
			return new AIS2Table($this->tabulka_vyber_terminu_hodnotenia, $data[0][1]);
		}

	}
	
?>
