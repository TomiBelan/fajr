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
 * Trieda, ktora poparsuje AIS html tabulku a vyrobi z nej pole
 * jednotlivych riadkov
 *
 * @author ppershing
 */

class AIS2Table {
	private $definition = array();
	private $data = null;
	
	public function __construct($tableDefinition, $html) {
		$this->definition = $tableDefinition;
		$this->data = matchAll($html, $this->getPattern());
		if ($this->data === false) $this->data = array();
	}
	
	public function getData() {
		return $this->data;
	}
	
	public function getTableDefinition() {
		return $this->definition;
	}
	
/**
 * Vráti regulárny výraz použitý na matchovanie tabuľky v HTML výstupe z AISu.
 * Na jeho konštrukciu sa používa definícia tabuľky.
 * @return string Regulárny výraz.
 */
	private function getPattern()
	{
		$pattern = '@\<tr id\=\'row_(?P<index>[^\']*)\' rid\=\'[^\']*\'[^>]*\>';
		foreach ($this->definition as $column)
		{
			$pattern .= '\<td[^>]*\>\<div\>(?P<'.substr($column,0,32).'>[^<]*)\</div\>\</td\>';
		}
		$pattern .= '\</tr\>@';
		return $pattern;
	}
}
