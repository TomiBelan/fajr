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
	
	private function odhlasZoSkusky() {
		return "<h3> Odhlasovanie zatial neimplementovane ale pracuje sa na
			tom! </h3>";
		// TODO:
		if (Input::get("hash") != hashNaOdhlasenie($row))
			throw new Exception("Počas odhlasovania nastala závažná chyba -
					nesedia mi predmety!");
		
	}
	
	/**
	 * predpokladame AIS format datumu a casu, t.j.
	 * vo formate "11.01.2010 08:30"
	 */
	public function parseDatumACas($str) {
		// Pozn. strptime() nefunguje na windowse, preto pouzijeme regex
		$pattern =
			'@(?P<tm_mday>[0-3][0-9])\.(?P<tm_mon>[0-1][0-9])\.(?P<tm_year>20[0-9][0-9])'.
			' (?P<tm_hour>[0-2][0-9]):(?P<tm_min>[0-5][0-9]*)@';
		$datum = matchAll($str, $pattern);
		if (!$datum) {
			throw new Exception("Chyba pri parsovaní dátumu a času");
		}
		$datum=$datum[0];
		
		return mktime($datum["tm_hour"],$datum["tm_min"],0,
				$datum["tm_mon"],$datum["tm_mday"],$datum["tm_year"]);
	}
	
	public function callback() {
		$terminyHodnotenia = $this->skusky->getTerminyHodnotenia();
		if (Input::get('action') !== null) {
			assert(Input::get("action")=="odhlasZoSkusky");
			return $this->odhlasZoSkusky();
		}
		
		$terminyHodnoteniaTableActive =  new
			Table(TableDefinitions::mojeTerminyHodnotenia(), 'Aktuálne termíny hodnotenia', null, array('studium', 'list'));
		
		$terminyHodnoteniaTableOld =  new
			Table(TableDefinitions::mojeTerminyHodnotenia(), 'Staré termíny hodnotenia', null, array('studium', 'list'));
		
		$actionUrl=buildUrl('',array("studium"=>Input::get("studium"),
					"list"=>Input::get("list"),
					"tab"=>Input::get("tab")));
		
		foreach($terminyHodnotenia->getData() as $row) {
			$datum = $this->parseDatumACas($row['datum']." ".$row['cas']);
					
			$row['odhlas']="";
			if ($datum < time()) {
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
						<input type='submit' value='Odhlás' /> </div></form>";
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
