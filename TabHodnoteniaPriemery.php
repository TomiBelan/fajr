<?php
require_once 'Renderable.php';
require_once 'Container.php';
require_once 'Label.php';
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

		$sucet = array('leto'=>0.0, 'zima'=>0.0);
		$pocet = array('leto'=>0.0, 'zima'=>0.0);
		$sucetKreditov = array('leto'=>0, 'zima'=>0);
		$vsetky = array('leto'=>true, 'zima'=>true);

		$numerickaHodnotaZnamky = array('A'=>1.0,
										'B'=>1.5,
										'C'=>2.0,
										'D'=>2.5,
										'E'=>3.0,
										'Fx'=>4.0);

		foreach(Sorter::sort($hodnotenia->getData(),
					array("semester"=>-1, "nazov"=>1)) as $row) {
			if ($row['semester']=='L') {
				$class='leto';
			}
			else {
				$class='zima';
			}
			if (isset($row['znamka']) && !empty($row['znamka'])) {
				$pocet[$class] += 1;
				$sucet[$class] += $numerickaHodnotaZnamky[$row['znamka']]*$row['kredit'];
				$sucetKreditov[$class] += $row['kredit'];
			}
			else {
				$vsetky[$class] = false;
			}
			$hodnoteniaTable->addRow($row, array('class'=>$class));
		}

		$hodnoteniaCollapsible = new Collapsible('Hodnotenia', $hodnoteniaTable);
		
		$priemery = $this->app->getPriemery();
		$priemeryTable = new Table(TableDefinitions::priemery());
		$priemeryTable->addRows($priemery->getData());

		$priemeryContainer = new Container();
		$priemeryContainer->addChild(new Label('Nasledovné priemery sú prebraté z AISu, čiže to (ne)funguje presne rovnako:'));
		$priemeryContainer->addChild($priemeryTable);

		if ($pocet['zima'] > 0 || $pocet['leto'] > 0) {
			$priemeryFajrText = '<p><br />Nasledovné vážené študijné priemery sú počítané Fajrom z tabuľky Hodnotenia <strong>zo zatiaľ ohodnotených predmetov</strong>:<br />';

			if ($pocet['zima']>0) {
				$priemerZima = $sucet['zima']/$sucetKreditov['zima'];
				$priemeryFajrText .= 'Zimný semester: '.sprintf('%.2f', $priemerZima).'<br />';
			}

			if ($pocet['leto']>0) {
				$priemerLeto = $sucet['leto']/$sucetKreditov['leto'];
				$priemeryFajrText .= 'Letný semester: '.sprintf('%.2f', $priemerLeto).'<br />';
			}

			if ($pocet['zima']>0 && $pocet['leto']>0) {
				$priemerRok = ($sucet['zima']+$sucet['leto'])/($sucetKreditov['zima']+$sucetKreditov['leto']);
				$priemeryFajrText .= 'Celý akad. rok: '.sprintf('%.2f', $priemerRok).'<br />';
			}


			$priemeryContainer->addChild(new Label($priemeryFajrText));
			$priemeryFajrText .= '</p>';
		}
		

		$priemeryCollapsible = new Collapsible('Priemery', $priemeryContainer);
		
		return $hodnoteniaCollapsible->getHtml().$priemeryCollapsible->getHtml();
	}
}
