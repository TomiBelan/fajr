<?php

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
			$link = '?'.http_build_query(array_merge($this->urlParams,
			             array($this->name => $key)));
			if ($key == $this->active) $class='tab_selected'; else $class='tab';
			$code .= '<span class=\''.$class.'\' \'><a href="'.$link.'">'.$value['title']
				.'</a></span>';
		}
		$code .= '</div>';
		
		$code .= $this->tabs[$this->active]['callback']->callback();
		return $code;
	}

}

?>
