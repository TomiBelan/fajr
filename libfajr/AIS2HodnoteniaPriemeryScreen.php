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
	 * Trieda reprezentujúca jednu obrazovku s hodnoteniami a priemermi za jeden rok.
	 *
	 * @author majak
	 */
	class AIS2HodnoteniaPriemeryScreen extends AIS2AbstractScreen
	{
		protected $tabulka_hodnotenia = array(
			// {{{
			array('aisname' => 'semester',
			      'title' => 'Semester',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'kodCastSP',
			      'title' => 'Kód časti štúdia',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'kodTypVyucbySP',
			      'title' => 'Kód typu výučby',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'skratka',
			      'title' => 'Skratka predmetu',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'nazov',
			      'title' => 'Názov predmetu',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'kredit',
			      'title' => 'Kredit predmetu',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'kodSposUkon',
			      'title' => 'Spôsob ukončenia',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'termin',
			      'title' => 'Termín',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'znamka',
			      'title' => 'Klasifikačný stupeň',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'datum',
			      'title' => 'Dátum hodnotenia',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'uznane',
			      'title' => 'Uznané hodnotenie',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'blokPopis',
			      'title' => 'Popis bloku',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'poplatok',
			      'title' => 'Zaplatený poplatok a úplný zápis',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'nahradzaMa',
			      'title' => 'Nahrádza ma',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'nahradzam',
			      'title' => 'Nahrádzam',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'dovezene',
			      'title' => 'Dovezené hodnotenie',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'mozePrihlasit',
			      'title' => 'Môže prihlásiť',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'rozsah',
			      'title' => 'Rozsah',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'priebHodn',
			      'title' => 'Existuje priebežné hodnotenie',
			      'sortorder' => '0',
			      'visible' => false),
			// }}}
		);
		protected $tabulka_priemery = array(
			// {{{
			array('aisname' => 'priemerInfoPopisAkadRok',
			      'title' => 'Akademický rok',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'priemerInfoKodSemester',
			      'title' => 'Kód semestra',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'vazPriemer',
			      'title' => 'Vážený priemer',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'studPriemer',
			      'title' => 'Štúdijný priemer',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'pocetPredmetov',
			      'title' => 'Celkový počet predmetov',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'pocetNeabs',
			      'title' => 'Počet neabsolvovaných predmetov',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'pokusyPriemer',
			      'title' => 'Priemer na koľký pokus',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'ziskanyKredit',
			      'title' => 'Získaný kredit',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'prerusUkon',
			      'title' => 'Študujúci, prerušený, ukončený',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'priemerInfoDatum',
			      'title' => 'Dátum výpočtu priemeru',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'priemerInfoDatum1Hodn',
			      'title' => 'Dátum jednofázového hodnotenia',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'priemerInfoDatum2Hodn',
			      'title' => 'Dátum dvojfázového hodnotenia',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'priemerNazov',
			      'title' => 'Názov priemeru',
			      'sortorder' => '0',
			      'visible' => true),
			array('aisname' => 'priemerZaAkRok',
			      'title' => 'Priemer je počítaný za akademický rok',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'priemerZaSemester',
			      'title' => 'Priemer je počítaný za semester',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'priemerLenStudPlan',
			      'title' => 'Započítane len predmety študijného plánu',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'priemerUznanePredm',
			      'title' => 'Počítať uznané predmety',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'priemerAjDatum1Hodn',
			      'title' => 'Brať do úvahy dátum jednofázového hodnotenia',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'priemerAjDatum2Hodn',
			      'title' => 'Brať do úvahy dátum dvojfázového hodnotenia',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'priemerPocitatNeabs',
			      'title' => 'Počítať predmety bez hodnotenia',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'priemerVahaNeabsolvovanych',
			      'title' => 'Váha neabsolvovaných predmetov',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'priemerSkratkaOrganizacnaJednotka',
			      'title' => 'Skratka organizačnej jednotky',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'priemerPocitatNeabsC',
			      'title' => 'Nepočítať výberové bez ukončeného hodnotenia',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'pocetPredmetovVyp',
			      'title' => 'Počet predmetov výpočtu',
			      'sortorder' => '0',
			      'visible' => false),
			array('aisname' => 'priemerInfoStudentiVypoctu',
			      'title' => 'Určujúca množina študentov výpočtu priemeru',
			      'sortorder' => '0',
			      'visible' => false),
			// }}}
		);

		public function __construct($idZapisnyList)
		{
			parent::__construct('ais.gui.vs.es.VSES212App', '&kodAplikacie=VSES212&idZapisnyList='.$idZapisnyList);
		}

		public function getHodnotenia()
		{
			$data = matchAll($this->data, AIS2Utils::DATA_PATTERN);
			return new AIS2Table($this->tabulka_hodnotenia, $data[0][1]);
		}

		public function getPriemery()
		{
			$data = matchAll($this->data, AIS2Utils::DATA_PATTERN);
			return new AIS2Table($this->tabulka_priemery, $data[1][1]);
		}

	}
	
?>
