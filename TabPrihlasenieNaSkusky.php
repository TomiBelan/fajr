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
	private $hodnotenia;
	
	public function __construct($skusky, $hodnotenia) {
		$this->skusky = $skusky;
		$this->hodnotenia = $hodnotenia;
	}
	
	public function hashNaPrihlasenie($predmet, $row) {
		return
			md5($row['index'].'|'.$row['dat'].'|'.$row['cas'].'|'.$predmet);
		
	}
	
	public function prihlasNaSkusku($predmetIndex, $terminIndex)
	{
		return $this->skusky->getZoznamTerminovDialog($predmetIndex)->prihlasNaTermin($terminIndex);
	}
	
	const PRIHLASIT_MOZE = 0;
	const PRIHLASIT_NEMOZE_CAS = 1;
	const PRIHLASIT_NEMOZE_POCET = 2;
	const PRIHLASIT_NEMOZE_ZNAMKA = 3;
	
	public function mozeSaPrihlasit($row) {
		$prihlasRange = AIS2Utils::parseAISDateTimeRange($row['prihlasovanie']);
		if (isset($this->hodnoteniaData[$row['predmet']])) {
			$znamka=$this->hodnoteniaData[$row['predmet']];
		} else $znamka="";
		if ($znamka!="") {
			return self::PRIHLASIT_NEMOZE_ZNAMKA;
		}
		if (!($prihlasRange['od'] < time() && $prihlasRange['do']>time())) {
			return self::PRIHLASIT_NEMOZE_CAS;
		}
		if ($row['maxPocet'] != '' &&
				$row['maxPocet']==$row['pocetPrihlasenych']) {
			return self::PRIHLASIT_NEMOZE_POCET;
		}
		return self::PRIHLASIT_MOZE;
	}
	
	public function callback() {
		$predmetyZapisnehoListu = $this->skusky->getPredmetyZapisnehoListu();
		$hodnoteniaData = array();
		
		foreach ($this->hodnotenia->getHodnotenia()->getData() as $row) {
			$hodnoteniaData[$row['nazov']]=$row['znamka'];
		}
		$this->hodnoteniaData = $hodnoteniaData;
		
		if (Input::get('action') !== null) {
			assert(Input::get("action")=="prihlasNaSkusku");
			if ($this->prihlasNaSkusku(Input::get("prihlasPredmetIndex"), Input::get("prihlasTerminIndex")))
			{
				redirect(array('tab' => 'TerminyHodnotenia'));
			}
			else throw new Exception('Na skúšku sa nepodarilo prihlásiť.');
		}
		
		$terminyTable = new
			Table(TableDefinitions::vyberTerminuHodnoteniaJoined(), 'Termíny,
					na ktoré sa môžem prihlásiť');
		
		$actionUrl=buildUrl('',array("studium"=>Input::get("studium"),
					"list"=>Input::get("list"),
					"tab"=>Input::get("tab")));
		
		foreach ($predmetyZapisnehoListu->getData() as $predmetRow) {
			
			$terminy = $this->skusky->getZoznamTerminovDialog($predmetRow['index'])->getZoznamTerminov();
			foreach($terminy->getData() as $row) {
				$row['predmet']=$predmetRow['nazov'];
				$row['predmetIndex']=$predmetRow['index'];
				
				$hash = $this->hashNaPrihlasenie($predmetRow['nazov'], $row);
				$mozeSaPrihlasit = $this->mozeSaPrihlasit($row);
				if ($mozeSaPrihlasit == self::PRIHLASIT_MOZE) {
					$row['prihlas']="<form method='post' action='$actionUrl'><div>
							<input type='hidden' name='action' value='prihlasNaSkusku'/>
							<input type='hidden' name='prihlasPredmetIndex'
							value='".$row['predmetIndex']."'/>
							<input type='hidden' name='prihlasTerminIndex'
							value='".$row['index']."'/>
							<input type='hidden' name='hash' value='$hash'/>
						<input type='submit' value='Prihlás ma!' /> </div></form>";
				} else if ($mozeSaPrihlasit == self::PRIHLASIT_NEMOZE_CAS) {
					$row['prihlas'] = 'nedá sa';
				} else if ($mozeSaPrihlasit == self::PRIHLASIT_NEMOZE_POCET) {
					$row['prihlas'] = 'termín je plný!';
				} else if ($mozeSaPrihlasit == self::PRIHLASIT_NEMOZE_ZNAMKA) {
					$row['prihlas'] = 'Už máš zápísané "'.$hodnoteniaData[$row['predmet']].'"';
				} else {
					assert(false);
				}
				$terminyTable->addRow($row, null);
				
			}
		}
		return $terminyTable->getHtml();
	}
}
