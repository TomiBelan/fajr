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

/**
 * Trieda na vygenerovanie tabulky z jej definície a vstupného HTML.
 *
 * @author majak
 */
class Table
{
	protected $definition = null;
	protected $data = null;
	protected $name = null;
	protected $newKey = null;
	protected $params = array();

/**
 * Konštruktor.
 * Nastaví atribúty a zo vstupného HTML dá tabuľku do poľa.
 * @param array $definition Definícia tabuľky.
 * @param string $html HTML z AISu.
 * @param string $name Názov tabuľky.
 * @param string|null $newKey Názov nového parametru v url, ktorého hodnota bude závisieť od riadku tabuľky.
 * @param array $params Zvyšné už nastavené parametre pre url.
 */
	public function  __construct($definition, $html, $name = '', $newKey = null, $params = array())
	{
		$this->definition = $definition;
		$this->data = pluckAll($html, $this->getPattern());
		$this->name = $name;
		$this->newKey = $newKey;
		$this->params = $params;
	}

	public function __toString()
	{
		return $this->getHtml();
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function setNewKey($newKey)
	{
		$this->newKey = $newKey;
	}

	public function setParams($params)
	{
		$this->params = $params;
	}

	/**
	 * Pomocou nastavených atribútov vygeneruje tabuľku v HTML formáte aj s linkami.
	 * @return string Vygenerovaná tabuľka v HTML formáte.
	 */
	public function getHtml()
	{
		$table = '';
		if ($this->name) $table .= '<h2>'.$this->name.'</h2>';
		if (!is_array($this->data) || empty($this->data[0]))
		{
			$table .= 'Dáta pre túto tabuľku neboli nájdené.<hr class="space" />';
		}
		else
		{
			$table .= '<table class="colstyle-sorting"><thead><tr>';
			$columns = array();
			foreach ($this->data[0] as $key => $value) if (is_string($key))
			{
				$columns[] = $key;
				$table .= '<th class="sortable">'.$key.'</th>';
			}
			$table .= '</tr></thead><tbody>';
			foreach ($this->data as $row)
			{
				if ($this->newKey) $link = '?'.http_build_query(array_merge($this->params, array($this->newKey => $row['index'])));
				$table .= '<tr>';
				foreach ($columns as $key => $column)
				{
					$table .= '<td>';
					if ($this->newKey && $key == 'index') $table .= '<a href="'.hescape($link).'">';
					$table .= $row[$column];
					if ($this->newKey && $key == 'index') $table .= '</a>';
					$table .= '</td>';
				}
				$table .= '</tr>';
			}
			$table .= '</tbody></table>';
		}
		return $table;
	}

/**
 * Vráti regulárny výraz použitý na matchovanie tabuľky v HTML výstupe z AISu.
 * Na jeho konštrukciu sa pouŽíva definícia tabuľky.
 * @return string Regulárny výraz.
 */
	public function getPattern()
	{
		$pattern = '@\<tr id\=\'row_(?P<index>[^\']*)\' rid\=\'[^\']*\'\>';
		foreach ($this->definition as $column)
		{
			$pattern .= '\<td[^>]*\>\<div\>(?P<'.$column['name'].'>[^<]*)\</div\>\</td\>';
		}
		$pattern .= '\</tr\>@';
		return $pattern;
	}
}
?>
