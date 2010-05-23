<?php
require_once 'TabManager.php';
require_once 'Table.php';
require_once 'libfajr/AIS2Utils.php';
require_once 'libfajr/AIS2AdministraciaStudiaScreen.php';
require_once 'libfajr/AIS2TerminyHodnoteniaScreen.php';
require_once 'libfajr/AIS2HodnoteniaPriemeryScreen.php';
require_once 'TableDefinitions.php';
require_once 'Sorter.php';

class MojeTerminyHodnoteniaCallback implements ITabCallback {
	public function __construct($skusky) {
		$this->skusky = $skusky;
	}
	
	public function callback() {
		$terminyHodnotenia = $this->skusky->getTerminyHodnotenia();
		$terminyHodnoteniaTableActive =  new
			Table(TableDefinitions::mojeTerminyHodnotenia(), 'Aktuálne termíny hodnotenia', null, array('studium', 'list'));
		
		$terminyHodnoteniaTableOld =  new
			Table(TableDefinitions::mojeTerminyHodnotenia(), 'Staré termíny hodnotenia', null, array('studium', 'list'));
		
		foreach($terminyHodnotenia->getData() as $row) {
			$datum=strptime($row['datum']." ".$row['cas'], "%d.%m.%Y %H:%M");
			$datum=mktime($datum["tm_hour"],$datum["tm_min"],0,1+$datum["tm_mon"],$datum["tm_mday"],1900+$datum["tm_year"]);
			$row['odhlas']="";
			if ($datum < time()) {
				$terminyHodnoteniaTableOld->addRow($row, null);
			} else {
				if ($row['mozeOdhlasit']==1) {
					$class='terminmozeodhlasit';
					$row['odhlas']="<form> <input type='submit' value='Odhlás'
							disabled='disabled' /> </form>";
				} else {
					$class='terminnemozeodhlasit';
				}
					
				if ($row['prihlaseny']=='A') {
					$terminyHodnoteniaTableActive->addRow($row, array('class'=>$class));
				}
			}
		}
		$terminyHodnoteniaTableActive->setUrlParams(array('studium' =>
					Input::get('studium'), 'list' => Input::get('list')));
		
		return
				$terminyHodnoteniaTableActive->getHtml().
				$terminyHodnoteniaTableOld->getHtml();
	}
}
