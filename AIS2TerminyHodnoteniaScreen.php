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
			array('name' => 'kodCastStPlanu',          'title' => 'kód časti študijného plánu', 'order' => '0'),
			array('name' => 'kodTypVyucby',            'title' => 'kód typu výučby',            'order' => '0'),
			array('name' => 'skratka',                 'title' => 'skratka',                    'order' => '0'),
			array('name' => 'nazov',                   'title' => 'názov',                      'order' => '0'),
			array('name' => 'kredit',                  'title' => 'kredit',                     'order' => '0'),
			array('name' => 'semester',                'title' => 'semester',                   'order' => '0'),
			array('name' => 'sposobUkoncenia',         'title' => 'spôsob ukončenia',           'order' => '0'),
			array('name' => 'pocetTerminov',           'title' => 'počet termínov',             'order' => '0'),
			array('name' => 'pocetAktualnychTerminov', 'title' => 'počet aktuálnych termínov',  'order' => '0'),
			array('name' => 'aktualnost',              'title' => 'aktuálnosť',                 'order' => '0'),
		);
		protected $tabulka_terminy_hodnotenia = array(
			array('name' => 'prihlaseny',        'title' => 'prihlásený',         'order' => '0'),
			array('name' => 'faza',              'title' => 'fáza',               'order' => '0'),
			array('name' => 'datum',             'title' => 'dátum',              'order' => '0'),
			array('name' => 'cas',               'title' => 'čas',                'order' => '0'),
			array('name' => 'miestnosti',        'title' => 'miestnosti',         'order' => '0'),
			array('name' => 'pocetPrihlasenych', 'title' => 'počet prihlásených', 'order' => '0'),
			array('name' => 'datumPrihlasenia',  'title' => 'dátum prihlásenia',  'order' => '0'),
			array('name' => 'datumOdhlasenia',   'title' => 'dátum odhlásenia',   'order' => '0'),
			array('name' => 'zapisal',           'title' => 'zapísal',            'order' => '0'),
			array('name' => 'pocetHodnotiacich', 'title' => 'počet hodnotiacich', 'order' => '0'),
			array('name' => 'hodnotiaci',        'title' => 'hodnotiaci',         'order' => '0'),
			array('name' => 'maxPocet',          'title' => 'maximálny počet',    'order' => '0'),
			array('name' => 'znamka',            'title' => 'známka',             'order' => '0'),
			array('name' => 'prihlasovanie',     'title' => 'prihlasovanie',      'order' => '0'),
			array('name' => 'odhlasovanie',      'title' => 'odhlasovanie',       'order' => '0'),
			array('name' => 'poznamka',          'title' => 'poznámka',           'order' => '0'),
			array('name' => 'zaevidoval',        'title' => 'zaevidoval',         'order' => '0'),
			array('name' => 'mozeOdhlasit',      'title' => 'može odhlásiť',      'order' => '0'),
			array('name' => 'skratkaPredmetu',   'title' => 'skratka predmetu',   'order' => '0'),
			array('name' => 'predmet',           'title' => 'predmet',            'order' => '0'),
		);

		public function __construct($idZapisnyList, $idStudium)
		{
			parent::__construct('ais.gui.vs.es.VSES007App', '&kodAplikacie=VSES007&idZapisnyList='.$idZapisnyList.'&idStudium='.$idStudium);
		}

		public function getPredmetyZapisnehoListu()
		{
			$data = pluckAll($this->data, AIS2Utils::DATA_PATTERN);
			return new Table($this->tabulka_predmety_zapisneho_listu, $data[0][1], 'Predmety zápisného listu');
		}

		public function getTerminyHodnotenia()
		{
			$data = pluckAll($this->data, AIS2Utils::DATA_PATTERN);
			return new Table($this->tabulka_terminy_hodnotenia, $data[1][1], 'Termíny hodnotenia', null, array('studium', 'list'));
		}

	}
	
?>
