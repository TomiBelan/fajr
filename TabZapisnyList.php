<?php
require_once 'TabManager.php';
require_once 'Table.php';
require_once 'libfajr/AIS2Utils.php';
require_once 'libfajr/AIS2AdministraciaStudiaScreen.php';
require_once 'libfajr/AIS2TerminyHodnoteniaScreen.php';
require_once 'libfajr/AIS2HodnoteniaPriemeryScreen.php';
require_once 'TableDefinitions.php';
require_once 'Sorter.php';

class ZapisanePredmetyCallback implements ITabCallback {
	private $skusky;
	
	public function __construct($skusky) {
		$this->skusky = $skusky;
	}
	
	public function callback() {
		$predmetyZapisnehoListu = $this->skusky->getPredmetyZapisnehoListu();
		$predmetyZapisnehoListuTable = new
			Table(TableDefinitions::predmetyZapisnehoListu(), 'Predmety zápisného listu');
		foreach (Sorter::sort($predmetyZapisnehoListu->getData(),
					array("semester"=>-1, "nazov"=>1)) as $row) {
			if ($row['semester']=='L') $class='leto'; else $class='zima';
			$predmetyZapisnehoListuTable->addRow($row, array('class'=>$class));
		}
;
		$predmetyZapisnehoListuTable->setUrlParams(array('studium' =>
					Input::get('studium'), 'list' => Input::get('list')));
		
		return $predmetyZapisnehoListuTable->getHtml();
	}
}
