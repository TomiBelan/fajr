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
	
	public function hashNaPrihlasenie($predmet, $row) {
		return
			md5($row['index'].'|'.$row['datum'].'|'.$row['cas'].'|'.$predmet);
		
	}
	
	public function prihlasNaSkusku() {
		return "<h3> Prihlasovanie zatial neimplementovane ale pracuje sa na
			tom! </h3>";
	}
	
	public function callback() {
		$predmetyZapisnehoListu = $this->skusky->getPredmetyZapisnehoListu();
		
		if (Input::get('action') !== null) {
			assert(Input::get("action")=="prihlasNaSkusku");
			return $this->prihlasNaSkusku();
		}
		
		$terminyTable = new
			Table(TableDefinitions::vyberTerminuHodnoteniaJoined(), 'Termíny,
					na ktoré sa môžem prihlásiť');
		
		$actionUrl=buildUrl('',array("studium"=>Input::get("studium"),
					"list"=>Input::get("list"),
					"tab"=>Input::get("tab")));
		
		foreach ($predmetyZapisnehoListu->getData() as $predmetRow) {
			$terminy = $this->skusky->getZoznamTerminov($predmetRow['index']);
			foreach($terminy->getData() as $row) {
				$row['predmet']=$predmetRow['nazov'];
				$row['predmetIndex']=$predmetRow['index'];
				
				$hash = $this->hashNaPrihlasenie($row, $predmetRow['nazov']);
				$prihlasRange = AIS2Utils::parseAISDateTimeRange($row['prihlasovanie']);
				if ($prihlasRange['od'] < time() && $prihlasRange['do']>time()) {
					$row['prihlas']="<form method='post' action='$actionUrl'><div>
							<input type='hidden' name='action' value='prihlasNaSkusku'/>
							<input type='hidden' name='prihlasPredmetIndex'
							value='".$row['predmetIndex']."'/>
							<input type='hidden' name='prihlasTerminIndex'
							value='".$row['index']."'/>
							<input type='hidden' name='hash' value='$hash'/>
						<input type='submit' value='Prihlás ma!' /> </div></form>";
				} else {
					$row['prihlas'] = 'nedá sa';
				}
				$terminyTable->addRow($row, null);
				
			}
		}
		return $terminyTable->getHtml();
	}
}
