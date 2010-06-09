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

interface ITabCallback {
	public function callback();
}

class TabManager {

	private $tabs = array();
	private $active = null;
	private $name = '';
	private $urlParams = null;
	
	public function __construct($name, $urlParams) {
		$this->name = $name;
		$this->urlParams = $urlParams;
		
	}
	
	public function addTab($name, $title, ITabCallback $callback) {
		$this->tabs[$name] = array('title' => $title, 'callback' => $callback);
	}
	
	public function setActive($tabName) {
		// FIXME: check for invalid argument
		$this->active = $tabName;
	}

	public function getHtml() {
		$code = '<div class=\'tab_header\'>';
		foreach ($this->tabs as $key => $value) {
			$link = FajrUtils::buildUrl('',array_merge($this->urlParams,
			             array($this->name => $key)));
			if ($key == $this->active) $class='tab_selected'; else $class='tab';
			$code .= '<span class=\''.$class.'\'><a href="'.$link.'">'.$value['title']
				.'</a></span>';
		}
		$code .= '</div>';
		
		if (!isset($this->tabs[$this->active])) {
			throw new Exception("Pokus o zobrazenie neplatného tabu!");
		}
		
		try {
			$code .= $this->tabs[$this->active]['callback']->callback();
		} catch (Exception $e) {
			DisplayManager::addException($e);
		}
		return $code;
	}

}

?>
