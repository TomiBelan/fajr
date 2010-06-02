<?php
/* {{{
Copyright (c) 2010 Peter Peresini

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
class Changelog {
	private static $changes = array (
			array('2010-01-xx', '0.1', 'Maják zverejnil prvú verziu Fajr-u'),
			array('2010-02-15', '0.1', 'Fajr sa presunul na google code'),
			array('2010-02-16', '0.1', 'AIS2 sa upgradol, prestali fungovať  niektoré veci'),
			array('2010-05-22', '0.2', 'Pribudli nové tabuľky, skryli sme
			       zbytočné stĺpce a celkovo vylepšili vzhľad'),
			array('2010-05-23', '0.2', 'Fajr prešiel na beta testing :-)'),
			array('2010-05-29', '0.2', 'Implementované prihlasovanie a odhlasovanie zo skúšok'),
			array('2010-06-01', '0.25', 'Pribudol zoznam prihlásených na termín'),
			);

	private static $limit = 6;

	public static function getChangelog() {
		$data = "<div class='changelog prepend-1 span-21 last increase-line-height'>\n<strong>Changelog:</strong><ul>\n";
		$tmp_array = array_slice(array_reverse(Changelog::$changes), 0, Changelog::$limit);
		foreach ($tmp_array as $change) {
			$data .= '<li>'.$change[0].' (verzia ' . $change[1] . ') - ';
			$data .= $change[2]."</li>\n";
		}
		$data .= "</ul></div>\n";
		return $data;
	}
}


?>
