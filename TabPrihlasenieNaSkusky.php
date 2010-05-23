<?php
require_once 'TabManager.php';
require_once 'Table.php';
require_once 'libfajr/AIS2Utils.php';
require_once 'libfajr/AIS2AdministraciaStudiaScreen.php';
require_once 'libfajr/AIS2TerminyHodnoteniaScreen.php';
require_once 'libfajr/AIS2HodnoteniaPriemeryScreen.php';
require_once 'TableDefinitions.php';
require_once 'Sorter.php';

class ZoznamTerminovCallback implements ITabCallback {
	private $skusky;
	
	public function __construct($skusky) {
		$this->skusky = $skusky;
	}
	
	public function callback() {
		$predmetyZapisnehoListu = $this->skusky->getPredmetyZapisnehoListu();
		$terminyTable = new
			Table(TableDefinitions::vyberTerminuHodnoteniaJoined(), 'Termíny,
					na ktoré sa môžem prihlásiť');
		foreach ($predmetyZapisnehoListu->getData() as $row) {
			$terminy = $this->skusky->getZoznamTerminov($row['index']);
			foreach($terminy->getData() as $row2) {
				$row2['predmet']=$row['nazov'];
				$row2['predmetIndex']=$row['index'];
				$row2['prihlas']="<form> <input type='submit' value='Prihlás ma!'
						disabled='disabled'/> </form>";
				$terminyTable->addRow($row2, null);
				
			}
		}
		return $terminyTable->getHtml();
	}
}
