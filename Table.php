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
	protected $urlParams = array();
	protected $options = null;

/**
 * Konštruktor.
 * Nastaví atribúty a zo vstupného HTML dá tabuľku do poľa.
 * @param array $definition Definícia tabuľky.
 * @param string $html HTML z AISu.
 * @param string $name Názov tabuľky.
 * @param string|null $newKey Názov nového parametru v url, ktorého hodnota bude závisieť od riadku tabuľky.
 * @param array $urlParams Zvyšné už nastavené parametre pre url.
 */
	public function  __construct($definition, $data, $name = '', $newKey = null, $urlParams = array())
	{
		$this->definition = $definition;
		$this->data = $data;
		$this->name = $name;
		$this->newKey = $newKey;
		$this->urlParams = $urlParams;
	}

	public function setName($name)
	{
		$this->name = $name;
	}

	public function setNewKey($newKey)
	{
		$this->newKey = $newKey;
	}

	public function setUrlParams($urlParams)
	{
		$this->urlParams = $urlParams;
	}
	
	public function setOptions($options)
	{
		$this->options = $options;
	}
	
	public function setOption($name, $value) {
		$this->options[$name] = $value;	
	}
	
	public function getOption($name) {
		if (isset($this->options[$name])) {
			return $this->options[$name];
		}
		return null;
	}

	/**
	 * Pomocou nastavených atribútov vygeneruje tabuľku v HTML formáte aj s linkami.
	 * @return string Vygenerovaná tabuľka v HTML formáte.
	 */
	public function getHtml()
	{
		$id = rand(); // FIXME: rozumny id generator
		
		$table = "\n<!-- ******* Table:{$this->name} ****** -->\n";
		$table .= "<div class='table'>\n";
		if ($this->name) $table .= '<h2> <img id=\'toggle'.$id.'\' src=\'images/toggle.png\' onClick=\'toggleVisibility('.$id.');\'> '.$this->name.'</h2>'."\n";
		if (!is_array($this->data) || empty($this->data[0])) {
			$table .= '<font color="red"> Dáta pre túto tabuľku neboli nájdené.</font><hr class="space" />';
			$table .= "</div>\n";
			return $table;
		}
		
		$table .= '<table id=\''.$id."'class='colstyle-sorting'>\n<thead>\n<tr>\n";
		$columns = array();
		
		foreach ($this->definition as $key => $value) {
			if (! $value['visible']) continue; // skip invisible cells
			$columns[$key] = $value['aisname'];
			$table .= '    <th class="sortable">'.$value['title']."</th>\n";
		}
		
		$table .= "</tr>\n";
		$table .= "\n</thead>\n<tbody>\n";
		foreach ($this->data as $row)
		{
			if ($this->newKey) $link = '?'.http_build_query(array_merge($this->urlParams, array($this->newKey => $row['index'])));
			if (isset($this->options['selected_key'])
					&&($this->options['selected_key'] == $row['index']))
			{
				$table .= "<tr class='selected'>\n";
			}
			else
			{
				$table .= "<tr>\n";
			}
			
			foreach ($columns as $key => $column)
			{
				$table .= '    <td>';
				if ($this->newKey && $key == 'index') $table .= '<a href="'.hescape($link).'">';
				$table .= $row[$column];
				if ($this->newKey && $key == 'index') $table .= '</a>';
				$table .= "</td>\n";
			}
			$table .= "</tr>\n";
		}
	$table .= "</tbody></table>\n";
	if ($this->getOption('collapsed')) {
		$table .= '<script type="text/javascript"> toggleVisibility('.
							$id.") </script>\n";
	}
	$table .= "</div>\n\n\n";
	return $table;
	}

}
?>
