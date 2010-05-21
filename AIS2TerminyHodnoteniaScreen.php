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
			array('aisname' => 'kodCastStPlanu',
			      'title' => 'kód časti študijného plánu',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'kodTypVyucby',
			      'title' => 'kód typu výučby',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'skratka',
			      'title' => 'skratka',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'nazov',
			      'title' => 'názov predmetu',
			      'sortorder' => '0',
			      'visible' => true,
			      'col' => -1),
			array('aisname' => 'kredit',
			      'title' => 'kredit',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'semester',
			      'title' => 'semester',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'sposobUkoncenia',
			      'title' => 'spôsob ukončenia',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'pocetTerminov',
			      'title' => 'počet termínov',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'pocetAktualnychTerminov',
			      'title' => 'počet aktuálnych termínov',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'aktualnost',
			      'title' => 'aktuálnosť',
			      'sortorder' => '0',
			      'visible' => false),
			// }}}
		);
		protected $tabulka_terminy_hodnotenia = array(
			// {{{
			array('aisname' => 'prihlaseny',
			      'title' => 'prihlásený',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'faza',
			      'title' => 'fáza',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'datum',
			      'title' => 'dátum',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'cas',
			      'title' => 'čas',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'miestnosti',
			      'title' => 'miestnosť',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'pocetPrihlasenych',
			      'title' => 'počet prihlásených',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'datumPrihlasenia',
			      'title' => 'dátum prihlásenia',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'datumOdhlasenia',
			      'title' => 'dátum odhlásenia',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'zapisal',
			      'title' => 'zapísal',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'pocetHodnotiacich',
			      'title' => 'počet hodnotiacich',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'hodnotiaci',
			      'title' => 'hodnotiaci',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'maxPocet',
			      'title' => 'maximálny počet',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'znamka',
			      'title' => 'známka',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'prihlasovanie',
			      'title' => 'prihlasovanie',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'odhlasovanie',
			      'title' => 'odhlasovanie',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'poznamka',
			      'title' => 'poznámka',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'zaevidoval',
			      'title' => 'zaevidoval',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'mozeOdhlasit',
			      'title' => 'može odhlásiť',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'skratkaPredmetu',
			      'title' => 'skratka predmetu',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'predmet',
			      'title' => 'predmet',
			      'sortorder' => '0',
			      'visible' => true,
			      'col' => -1),
			// }}}
		);

		public function __construct($idZapisnyList, $idStudium)
		{
			parent::__construct('ais.gui.vs.es.VSES007App', '&kodAplikacie=VSES007&idZapisnyList='.$idZapisnyList.'&idStudium='.$idStudium);
		}

		public function getPredmetyZapisnehoListu()
		{
			$data = matchAll($this->data, AIS2Utils::DATA_PATTERN);
			$tableParser = new
				AIS2TableParser($this->tabulka_predmety_zapisneho_listu);
			$tableData = $tableParser->parseHtml($data[0][1]);
			return new Table($this->tabulka_predmety_zapisneho_listu, $tableData, 'Predmety zápisného listu');
		}

		public function getTerminyHodnotenia()
		{
			$data = matchAll($this->data, AIS2Utils::DATA_PATTERN);
			$tableParser = new
				AIS2TableParser($this->tabulka_terminy_hodnotenia);
			$tableData = $tableParser->parseHtml($data[1][1]);
			return new Table($this->tabulka_terminy_hodnotenia, $tableData, 'Termíny hodnotenia', null, array('studium', 'list'));
		}

	}
	
?>
