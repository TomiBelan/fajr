<?php
require_once 'Renderable.php';
require_once 'Table.php';
require_once 'libfajr/AIS2Utils.php';
require_once 'libfajr/AIS2AdministraciaStudiaScreen.php';
require_once 'libfajr/AIS2TerminyHodnoteniaScreen.php';
require_once 'libfajr/AIS2HodnoteniaPriemeryScreen.php';
require_once 'TableDefinitions.php';
require_once 'Sorter.php';


class HodnoteniaCallback implements Renderable {
	private $app;
	
	public function __construct($app) {
		$this->app = $app;
	}
	
	public function getHtml() {
		$hodnotenia = $this->app->getHodnotenia();
		$hodnoteniaTable = new Table(TableDefinitions::hodnotenia());
		foreach(Sorter::sort($hodnotenia->getData(),
					array("semester"=>-1, "nazov"=>1)) as $row) {
			if ($row['semester']=='L') $class='leto'; else $class='zima';
			$hodnoteniaTable->addRow($row, array('class'=>$class));
		}

		$hodnoteniaCollapsible = new Collapsible('Hodnotenia', $hodnoteniaTable);
		
		$priemery = $this->app->getPriemery();
		$priemeryTable = new Table(TableDefinitions::priemery());
		$priemeryTable->addRows($priemery->getData());

		$priemeryCollapsible = new Collapsible('Priemery ((ne)funguje presne tak ako v AISe, sťažujte sa tam)',
			$priemeryTable);
		
		return $hodnoteniaCollapsible->getHtml().$priemeryCollapsible->getHtml();
	}
}
