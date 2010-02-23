<?php
class TabManager {

	private $tabs = array();
	private $active = null;
	private $name = '';
	
	public function __construct($name) {
		$this->name = $name;
	}
	
	public function addTab($name, $title, $htmlCode) {
		$this->tabs[$name] = array('title' => $title, 'code' => $htmlCode);
	}
	
	public function setActive($tabName) {
		// FIXME: check for invalid argument
		$this->active = $tabName;
	}

	public function getHtml() {
		$code = '<div class=\'tab_header\'>';
		foreach ($this->tabs as $key => $value) {
			$code .= '<span id=\'tab_label_'.$this->name.'_'.$key.'\' class=\'tab\'
				onClick=\'mytabs.showTab("'.$this->name.'_'.$key.'");\'>' .
				$value['title'] .'</span>';
		}
		$code .= '</div>';
			
		foreach ($this->tabs as $key => $value) {
			$code .= '<div id=\'tab_'.$this->name.'_'.$key.'\'>'.$value['code'].'</div>';
		}
		$code .= '<script type="text/javascript"> mytabs = new Tabs(); ';
		$code .= 'mytabs.tabs = [ ';
		foreach ($this->tabs as $key => $value) {
			$code .= '\''.$this->name.'_'.$key.'\',';
		}
		$keys = array_keys($this->tabs);
		$code .= ']; mytabs.showTab("'.$this->name.'_'.$keys[0].'");</script>'."\n\n";
				
		return $code;
	}

}

?>
