<?php
/* {{{
Copyright (c) 2010 Martin Sucha

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

require_once 'Renderable.php';

class PriemeryCalculator implements Renderable {

	const SEMESTER_LETNY = 'leto';
	const SEMESTER_ZIMNY = 'zima';
	const AKADEMICKY_ROK = 'rok';

	protected $sucet = null;
	protected $sucetVah = null;
	protected $pocet = null;
	protected $pocetKreditov = null;
	protected $pocetNeohodnotenych = null;
	protected $pocetKreditovNeohodnotenych = null;

	protected static $numerickaHodnotaZnamky = array(	'A'=>1.0,
														'B'=>1.5,
														'C'=>2.0,
														'D'=>2.5,
														'E'=>3.0,
														'Fx'=>4.0);

	private static function nula() {
		return array(self::SEMESTER_LETNY=>0, self::SEMESTER_ZIMNY=>0, self::AKADEMICKY_ROK=>0);
	}

	function __construct() {
		$this->pocet = self::nula();
		$this->pocetKreditov = self::nula();
		$this->sucet = self::nula();
		$this->sucetVah = self::nula();
		$this->pocetNeohodnotenych = self::nula();
		$this->pocetKreditovNeohodnotenych = self::nula();
	}

	private function addImpl($castRoka, $hodnota, $kredity) {
		$this->sucet[$castRoka] += $hodnota;
		$this->sucetVah[$castRoka] += $hodnota*$kredity;
		$this->pocet[$castRoka] += 1;
		$this->pocetKreditov[$castRoka] += $kredity;
	}

	private function addNeohodnotene($castRoka, $kredity) {
		$this->pocetNeohodnotenych[$castRoka] += 1;
		$this->pocetKreditovNeohodnotenych[$castRoka] += $kredity;
	}

	public function add($castRoka, $znamka, $kredity) {
		if (isset(self::$numerickaHodnotaZnamky[$znamka])) {
			$hodnota = self::$numerickaHodnotaZnamky[$znamka];

			$this->addImpl($castRoka, $hodnota, $kredity);
			$this->addImpl(self::AKADEMICKY_ROK, $hodnota, $kredity);
		}
		else {
			$this->addNeohodnotene($castRoka, $kredity);
			$this->addNeohodnotene(self::AKADEMICKY_ROK, $kredity);
		}
	}

	public function hasPriemer($castRoka=self::AKADEMICKY_ROK) {
		return $this->pocet[$castRoka]>0;
	}

	public function studijnyPriemer($castRoka=self::AKADEMICKY_ROK, $neohodnotene=true) {
		$suma = $this->sucet[$castRoka];
		$pocet = $this->pocet[$castRoka];

		if ($neohodnotene) {
			$suma += $this->pocetNeohodnotenych[$castRoka]*self::$numerickaHodnotaZnamky['Fx'];
			$pocet += $this->pocetNeohodnotenych[$castRoka];
		}

		if ($pocet == 0) return null;
		return $suma/$pocet;
	}

	public function vazenyPriemer($castRoka=self::AKADEMICKY_ROK, $neohodnotene=true) {
		$suma = $this->sucetVah[$castRoka];
		$pocet = $this->pocetKreditov[$castRoka];
		if ($neohodnotene) {
			$suma += $this->pocetKreditovNeohodnotenych[$castRoka]*self::$numerickaHodnotaZnamky['Fx'];
			$pocet += $this->pocetKreditovNeohodnotenych[$castRoka];
		}
		if ($pocet == 0) return null;
		return $suma/$pocet;
	}

	private function vypisVazenyPriemer($castRoka) {
		$sNeohodnotenymi = $this->vazenyPriemer($castRoka, true);
		$ibaOhodnotene = $this->vazenyPriemer($castRoka, false);
		$text = sprintf('%.2f', $sNeohodnotenymi);
		if ($sNeohodnotenymi!==$ibaOhodnotene) {
			$text .= ' ('.sprintf('%.2f', $ibaOhodnotene).' iba doteraz ohodnotené predmety)';
		}
		return $text;
	}

	public function getHtml() {
		$html = '';
		if ($this->hasPriemer(self::SEMESTER_ZIMNY)) {
			$html .= 'Zimný semester: '.$this->vypisVazenyPriemer(self::SEMESTER_ZIMNY).'<br />';
		}

		if ($this->hasPriemer(self::SEMESTER_LETNY)) {
			$html .= 'Letný semester: '.$this->vypisVazenyPriemer(self::SEMESTER_LETNY).'<br />';
		}

		if ($this->hasPriemer(self::SEMESTER_ZIMNY) && $this->hasPriemer(self::SEMESTER_LETNY)) {
			$html .= 'Celý akad. rok: '.$this->vypisVazenyPriemer(self::AKADEMICKY_ROK).'<br />';
		}
		return $html;
	}



}