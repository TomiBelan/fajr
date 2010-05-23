<?php
require_once 'TabManager.php';
require_once 'Table.php';
require_once 'libfajr/AIS2Utils.php';
require_once 'libfajr/AIS2AdministraciaStudiaScreen.php';
require_once 'libfajr/AIS2TerminyHodnoteniaScreen.php';
require_once 'libfajr/AIS2HodnoteniaPriemeryScreen.php';
require_once 'TableDefinitions.php';
require_once 'Sorter.php';


class HodnoteniaCallback implements ITabCallback {
	private $app;
	
	public function __construct($app) {
		$this->app = $app;
	}
	
	public function callback() {
		$hodnotenia = $this->app->getHodnotenia();
		$hodnoteniaTable = new Table(TableDefinitions::hodnotenia(), 'Hodnotenia');
		foreach(Sorter::sort($hodnotenia->getData(),
					array("semester"=>-1, "nazov"=>1)) as $row) {
			if ($row['semester']=='L') $class='leto'; else $class='zima';
			$hodnoteniaTable->addRow($row, array('class'=>$class));
		}
		
		$priemery = $this->app->getPriemery();
		$priemeryTable = new Table(TableDefinitions::priemery(), 'Priemery');
		$priemeryTable->addRows($priemery->getData());
		
		return $hodnoteniaTable->getHtml().$priemeryTable->getHtml();
	}
}
