<?php
require_once 'TabManager.php';
require_once 'Table.php';
require_once 'libfajr/AIS2Utils.php';
require_once 'libfajr/AIS2AdministraciaStudiaScreen.php';
require_once 'libfajr/AIS2TerminyHodnoteniaScreen.php';
require_once 'libfajr/AIS2HodnoteniaPriemeryScreen.php';
require_once 'TableDefinitions.php';
require_once 'Sorter.php';
require_once 'FajrUtils.php';

class MojeTerminyHodnoteniaCallback implements ITabCallback {
	public function __construct($skusky) {
		$this->skusky = $skusky;
	}
	
	/**
	 * ked odhlasujeme z predmetu, narozdiel od AISu robime opat
	 * inicializaciu vsetkych aplikacii. Just for sure chceme
	 * okontrolovat, ze sa nic nezmenilo a ze sme dostali rovnake data
	 * ako predtym!
	 */
	private function hashNaOdhlasenie($row) {
		return
			md5($row['index'].'|'.$row['datum'].'|'.$row['cas'].'|'.$row['predmet']);
	}
	
	private function odhlasZoSkusky($terminIndex) {
		
		$terminy = $this->skusky->getTerminyHodnotenia()->getData();
		$terminKey = -1;
		foreach ($terminy as $key=>$row) {
			if ($row['index']==$terminIndex) $terminKey = $key;
		}
		if ($terminKey == -1) {
			throw new Exception("Ooops, predmet/termín nenájdený. Pravdepodobne
					zmena dát v AISe.");
		}
		if (Input::get("hash") != $this->hashNaOdhlasenie($terminy[$terminKey])) {
			throw new Exception("Ooops, nesedia údaje o termíne. Pravdepodobne zmena
					dát v AISe spôsobila posunutie tabuliek.");
		}
		return $this->skusky->odhlasZTerminu($terminIndex);
	}
	
	
	public function callback() {
		$terminyHodnotenia = $this->skusky->getTerminyHodnotenia();
		if (Input::get('action') !== null) {
			assert(Input::get("action")=="odhlasZoSkusky");
			if ($this->odhlasZoSkusky(Input::get("odhlasIndex")))
			{
				FajrUtils::redirect();
			}
			else throw new Exception('Z termínu sa nepodarilo odhlásiť.');
		}
		
		$baseUrlParams = array("studium"=>Input::get("studium"),
					"list"=>Input::get("list"),
					"tab"=>Input::get("tab"));
		
		$terminyHodnoteniaTableActive =  new
			Table(TableDefinitions::mojeTerminyHodnotenia(), 'Aktuálne termíny hodnotenia',
					'termin', $baseUrlParams);
		
		$terminyHodnoteniaTableOld =  new
			Table(TableDefinitions::mojeTerminyHodnotenia(), 'Staré termíny hodnotenia',
					'termin', $baseUrlParams);
		
		if (Input::get('termin')!=null) {
			$terminyHodnoteniaTableActive->setOption('selected_key',
					Input::get('termin'));
			$terminyHodnoteniaTableOld->setOption('selected_key',
					Input::get('termin'));
		}
		
		$actionUrl=FajrUtils::buildUrl('', $baseUrlParams);
		
		foreach($terminyHodnotenia->getData() as $row) {
			$datum = AIS2Utils::parseAISDateTime($row['datum']." ".$row['cas']);
					
			if ($datum < time()) {
				$row['odhlas']="Skúška už bola";
				$terminyHodnoteniaTableOld->addRow($row, null);
			} else {
				if ($row['mozeOdhlasit']==1) {
					$class='terminmozeodhlasit';
					$hash=$this->hashNaOdhlasenie($row);
					$row['odhlas']="<form method='post' action='$actionUrl'>
						<div>
						<input type='hidden' name='action' value='odhlasZoSkusky'/>
						<input type='hidden' name='odhlasIndex'
						value='".$row['index']."'/>
						<input type='hidden' name='hash' value='$hash'/>
						<button name='submit' type='submit' class='tableButton negative'>
							<img src='images/cross.png' alt=''>Odhlás
						</button></div></form>";
				} else {
					$row['odhlas']="nedá sa";
					$class='terminnemozeodhlasit';
				}
					
				if ($row['prihlaseny']!='A') {
					$row['odhlas']='Si odhlásený. Ak chceš, opäť sa prihlás.';
					$class='terminodhlaseny';
				}
				$terminyHodnoteniaTableActive->addRow($row, array('class'=>$class));
			}
		}
		
		$html = $terminyHodnoteniaTableActive->getHtml();
		$html .= $terminyHodnoteniaTableOld->getHtml();
		if (Input::get('termin')!=null) {
			$prihlaseni = $this->skusky->getZoznamPrihlasenychDialog(Input::get('termin'))->getZoznamPrihlasenych();
			$zoznamPrihlasenychTable = new
			Table(TableDefinitions::zoznamPrihlasenych(), 'Zoznam prihlásených
					na vybratý termín', null, array('studium', 'list'));
			$zoznamPrihlasenychTable->addRows($prihlaseni->getData());
			$html .= $zoznamPrihlasenychTable->getHtml();
		}
		return $html;
	}
}
