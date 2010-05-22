<?php
class TableDefinitions
{
	public static function zoznamStudii() {
		return array(
			// {{{
			'rocnik' => array(
						'title' => 'ročník',
						'sortorder' => '0',
						'visible' => true,
						'col' => -1),
			'skratka' => array(
						'title' => 'skratka',
						'sortorder' => '0',
						'visible' => true),
			'kruzok' => array(
						'title' => 'krúžok',
						'sortorder' => '0',
						'visible' => false),
			'studijnyProgram' => array(
						'title' => 'študijný program',
						'sortorder' => '0',
						'visible'=>true),
			'doplnujuceUdaje' => array(
						'title' => 'doplňujúce údaje',
						'sortorder' => '0',
						'visible' => true),
			'zaciatokStudia' => array(
						'title' => 'začiatok štúdia',
						'sortorder' => '0',
						'visible' => true),
			'koniecStudia' => array(
						'title' => 'koniec štúdia',
						'sortorder' => '0',
						'visible' => true),
			'dlzkaVSemestroch' => array(
						'title' => 'dĺžka v semestroch',
						'sortorder' => '0',
						'visible' => true),
			'dlzkaStudia' => array(
						'title' => 'dĺžka štúdia',
						'sortorder' => '0',
						'visible' => true),
			'cisloDiplomu' => array(
						'title' => 'číslo diplomu',
						'sortorder' => '0',
						'visible' => false),
			'cisloZMatriky' => array(
						'title' => 'číslo z matriky',
						'sortorder' => '0',
						'visible' => false),
			'cisloVysvedcenia' => array(
						'title' => 'číslo vysvedčenia',
						'sortorder' => '0',
						'visible' => false),
			'cisloDodatku' => array(
						'title' => 'číslo dodatku',
						'sortorder' => '0',
						'visible' => false),
			'cisloEVI' => array(
						'title' => 'číslo EVI',
						'sortorder' => '0',
						'visible' => false),
			'cisloProgramu' => array(
						'title' => 'číslo programu',
						'sortorder' => '0',
						'visible' => true),
			'priznak' => array(
						'title' => 'príznak',
						'sortorder' => '0',
						'visible' => false),
			'organizacnaJednotka' => array(
						'title' => 'organizačná jednotka',
						'sortorder' => '0',
						'visible' => false),
			'rokStudia' => array(
						'title' => 'rok štúdia',
						'sortorder' => '0',
						'visible' => true),
			// }}}
		);
	}
	
	public static function zoznamZapisnychListov() {
		return array(
			// {{{
			'akademickyRok' => array(
						'title' => 'akademický rok',
						'sortorder' => '0',
						'visible' => true),
			'rocnik' => array(
						'title' => 'ročník',
						'sortorder' => '0',
						'visible' => true),
			'studProgramSkratka' => array(
						'title' => 'krúžok',
						'sortorder' => '0',
						'visible' => true),
			'studijnyProgram' => array(
						'title' => 'skratka',
						'sortorder' => '0',
						'visible' => true),
			'doplnujuceUdaje' => array(
						'title' => 'doplňujúce údaje',
						'sortorder' => '0',
						'visible' => true),
			'datumZapisu' => array(
						'title' => 'dátum zápisu',
						'sortorder' => '0',
						'visible' => true),
			'potvrdenyZapis' => array(
						'title' => 'potvrdený zápis',
						'sortorder' => '0',
						'visible' => true),
			'podmienecnyZapis' => array(
						'title' => 'podmienečný zápis',
						'sortorder' => '0',
						'visible' => true),
			'dlzkaVSemestroch' => array(
						'title' => 'dĺžka v semestroch',
						'sortorder' => '0',
						'visible' => true),
			'cisloEVI' => array(
						'title' => 'číslo EVI',
						'sortorder' => '0',
						'visible' => true),
			'cisloProgramu' => array(
						'title' => 'číslo programu',
						'sortorder' => '0',
						'visible' => true),
			'datumSplnenia' => array(
						'title' => 'dátum splnenia',
						'sortorder' => '0',
						'visible' => true),
			'priznak' => array(
						'title' => 'príznak',
						'sortorder' => '0',
						'visible' => false),
			'organizacnaJednotka' => array(
						'title' => 'organizačná jednotka',
						'sortorder' => '0',
						'visible' => false),
			'typFinacovania' => array(
						'title' => 'typ financovania',
						'sortorder' => '0',
						'visible' => false),
			'skratkaTypuFinacovania' => array(
						'title' => 'skratka typu finacovania',
						'sortorder' => '0',
						'visible' => false),
			// }}}
		);
	}
	
	public static function hodnotenia() {
		return array(
			// {{{
			'semester' => array(
			      'title' => 'Semester',
			      'sortorder' => '0',
			      'visible' => true),
			'kodCastSP' => array(
			      'title' => 'Kód časti štúdia',
			      'sortorder' => '0',
			      'visible' => false),
			'kodTypVyucbySP' => array(
			      'title' => 'Kód typu výučby',
			      'sortorder' => '0',
			      'visible' => true),
			'skratka' => array(
			      'title' => 'Skratka predmetu',
			      'sortorder' => '0',
			      'visible' => true),
			'nazov' => array(
			      'title' => 'Názov predmetu',
			      'sortorder' => '0',
			      'visible' => true),
			'kredit' => array(
			      'title' => 'Kredit predmetu',
			      'sortorder' => '0',
			      'visible' => true),
			'kodSposUkon' => array(
			      'title' => 'Spôsob ukončenia',
			      'sortorder' => '0',
			      'visible' => false),
			'termin' => array(
			      'title' => 'Termín',
			      'sortorder' => '0',
			      'visible' => true),
			'znamka' => array(
			      'title' => 'Klasifikačný stupeň',
			      'sortorder' => '0',
			      'visible' => true),
			'datum' => array(
			      'title' => 'Dátum hodnotenia',
			      'sortorder' => '0',
			      'visible' => true),
			'uznane' => array(
			      'title' => 'Uznané hodnotenie',
			      'sortorder' => '0',
			      'visible' => false),
			'blokPopis' => array(
			      'title' => 'Popis bloku',
			      'sortorder' => '0',
			      'visible' => true),
			'poplatok' => array(
			      'title' => 'Zaplatený poplatok a úplný zápis',
			      'sortorder' => '0',
			      'visible' => false),
			'nahradzaMa' => array(
			      'title' => 'Nahrádza ma',
			      'sortorder' => '0',
			      'visible' => false),
			'nahradzam' => array(
			      'title' => 'Nahrádzam',
			      'sortorder' => '0',
			      'visible' => false),
			'dovezene' => array(
			      'title' => 'Dovezené hodnotenie',
			      'sortorder' => '0',
			      'visible' => false),
			'mozePrihlasit' => array(
			      'title' => 'Môže prihlásiť',
			      'sortorder' => '0',
			      'visible' => false),
			'rozsah' => array(
			      'title' => 'Rozsah',
			      'sortorder' => '0',
			      'visible' => true),
			'priebHodn' => array(
			      'title' => 'Existuje priebežné hodnotenie',
			      'sortorder' => '0',
			      'visible' => false),
			// }}}
		);
	}
	
	public static function priemery() {
		return array(
			// {{{
			'priemerInfoPopisAkadRok' => array(
			      'title' => 'Akademický rok',
			      'sortorder' => '0',
			      'visible' => true),
			'priemerInfoKodSemester' => array(
			      'title' => 'Kód semestra',
			      'sortorder' => '0',
			      'visible' => true),
			'vazPriemer' => array(
			      'title' => 'Vážený priemer',
			      'sortorder' => '0',
			      'visible' => true),
			'studPriemer' => array(
			      'title' => 'Štúdijný priemer',
			      'sortorder' => '0',
			      'visible' => true),
			'pocetPredmetov' => array(
			      'title' => 'Celkový počet predmetov',
			      'sortorder' => '0',
			      'visible' => true),
			'pocetNeabs' => array(
			      'title' => 'Počet neabsolvovaných predmetov',
			      'sortorder' => '0',
			      'visible' => true),
			'pokusyPriemer' => array(
			      'title' => 'Priemer na koľký pokus',
			      'sortorder' => '0',
			      'visible' => true),
			'ziskanyKredit' => array(
			      'title' => 'Získaný kredit',
			      'sortorder' => '0',
			      'visible' => true),
			'prerusUkon' => array(
			      'title' => 'Študujúci, prerušený, ukončený',
			      'sortorder' => '0',
			      'visible' => true),
			'priemerInfoDatum' => array(
			      'title' => 'Dátum výpočtu priemeru',
			      'sortorder' => '0',
			      'visible' => true),
			'priemerInfoDatum1Hodn' => array(
			      'title' => 'Dátum jednofázového hodnotenia',
			      'sortorder' => '0',
			      'visible' => false),
			'priemerInfoDatum2Hodn' => array(
			      'title' => 'Dátum dvojfázového hodnotenia',
			      'sortorder' => '0',
			      'visible' => false),
			'priemerNazov' => array(
			      'title' => 'Názov priemeru',
			      'sortorder' => '0',
			      'visible' => true),
			'priemerZaAkRok' => array(
			      'title' => 'Priemer je počítaný za akademický rok',
			      'sortorder' => '0',
			      'visible' => false),
			'priemerZaSemester' => array(
			      'title' => 'Priemer je počítaný za semester',
			      'sortorder' => '0',
			      'visible' => false),
			'priemerLenStudPlan' => array(
			      'title' => 'Započítane len predmety študijného plánu',
			      'sortorder' => '0',
			      'visible' => false),
			'priemerUznanePredm' => array(
			      'title' => 'Počítať uznané predmety',
			      'sortorder' => '0',
			      'visible' => false),
			'priemerAjDatum1Hodn' => array(
			      'title' => 'Brať do úvahy dátum jednofázového hodnotenia',
			      'sortorder' => '0',
			      'visible' => false),
			'priemerAjDatum2Hodn' => array(
			      'title' => 'Brať do úvahy dátum dvojfázového hodnotenia',
			      'sortorder' => '0',
			      'visible' => false),
			'priemerPocitatNeabs' => array(
			      'title' => 'Počítať predmety bez hodnotenia',
			      'sortorder' => '0',
			      'visible' => false),
			'priemerVahaNeabsolvovanych' => array(
			      'title' => 'Váha neabsolvovaných predmetov',
			      'sortorder' => '0',
			      'visible' => false),
			'priemerSkratkaOrganizacnaJednotka' => array(
			      'title' => 'Skratka organizačnej jednotky',
			      'sortorder' => '0',
			      'visible' => false),
			'priemerPocitatNeabsC' => array(
			      'title' => 'Nepočítať výberové bez ukončeného hodnotenia',
			      'sortorder' => '0',
			      'visible' => false),
			'pocetPredmetovVyp' => array(
			      'title' => 'Počet predmetov výpočtu',
			      'sortorder' => '0',
			      'visible' => false),
			'priemerInfoStudentiVypoctu' => array(
			      'title' => 'Určujúca množina študentov výpočtu priemeru',
			      'sortorder' => '0',
			      'visible' => false),
			// }}}
		);
	}
	
	public static function predmetyZapisnehoListu() {
		return array(
			// {{{
			'kodCastStPlanu' => array(
			      'title' => 'kód časti študijného plánu',
			      'sortorder' => '0',
			      'visible' => false),
			'kodTypVyucby' => array(
			      'title' => 'kód typu výučby',
			      'sortorder' => '0',
			      'visible' => true),
			'skratka' => array(
			      'title' => 'skratka',
			      'sortorder' => '0',
			      'visible' => true),
			'nazov' => array(
			      'title' => 'názov predmetu',
			      'sortorder' => '0',
			      'visible' => true,
			      'col' => -1),
			'kredit' => array(
			      'title' => 'kredit',
			      'sortorder' => '0',
			      'visible' => true),
			'semester' => array(
			      'title' => 'semester',
			      'sortorder' => '0',
			      'visible' => true),
			'sposobUkoncenia' => array(
			      'title' => 'spôsob ukončenia',
			      'sortorder' => '0',
			      'visible' => false),
			'pocetTerminov' => array(
			      'title' => 'počet termínov',
			      'sortorder' => '0',
			      'visible' => false),
			'pocetAktualnychTerminov' => array(
			      'title' => 'počet aktuálnych termínov',
			      'sortorder' => '0',
			      'visible' => false),
			'aktualnost' => array(
			      'title' => 'aktuálnosť',
			      'sortorder' => '0',
			      'visible' => false),
			// }}}
		);
	}
	
	public static function mojeTerminyHodnotenia() {
		return array(
			// {{{
			// specialne
			'odhlas' => array(
			      'title' => 'Odhlás',
			      'sortorder' => '0',
			      'visible' => true),
			// originalne
			'prihlaseny' => array(
			      'title' => 'prihlásený',
			      'sortorder' => '0',
			      'visible' => true),
			'faza' => array(
			      'title' => 'fáza',
			      'sortorder' => '0',
			      'visible' => false),
			'datum' => array(
			      'title' => 'dátum',
			      'sortorder' => '0',
			      'visible' => true),
			'cas' => array(
			      'title' => 'čas',
			      'sortorder' => '0',
			      'visible' => true),
			'miestnosti' => array(
			      'title' => 'miestnosť',
			      'sortorder' => '0',
			      'visible' => true),
			'pocetPrihlasenych' => array(
			      'title' => 'počet prihlásených',
			      'sortorder' => '0',
			      'visible' => true),
			'datumPrihlasenia' => array(
			      'title' => 'dátum prihlásenia',
			      'sortorder' => '0',
			      'visible' => false),
			'datumOdhlasenia' => array(
			      'title' => 'dátum odhlásenia',
			      'sortorder' => '0',
			      'visible' => false),
			'zapisal' => array(
			      'title' => 'zapísal',
			      'sortorder' => '0',
			      'visible' => false),
			'pocetHodnotiacich' => array(
			      'title' => 'počet hodnotiacich',
			      'sortorder' => '0',
			      'visible' => false),
			'hodnotiaci' => array(
			      'title' => 'hodnotiaci',
			      'sortorder' => '0',
			      'visible' => true),
			'maxPocet' => array(
			      'title' => 'maximálny počet',
			      'sortorder' => '0',
			      'visible' => true),
			'znamka' => array(
			      'title' => 'známka',
			      'sortorder' => '0',
			      'visible' => false),
			'prihlasovanie' => array(
			      'title' => 'prihlasovanie',
			      'sortorder' => '0',
			      'visible' => true),
			'odhlasovanie' => array(
			      'title' => 'odhlasovanie',
			      'sortorder' => '0',
			      'visible' => true),
			'poznamka' => array(
			      'title' => 'poznámka',
			      'sortorder' => '0',
			      'visible' => true),
			'zaevidoval' => array(
			      'title' => 'zaevidoval',
			      'sortorder' => '0',
			      'visible' => false),
			'mozeOdhlasit' => array(
			      'title' => 'može odhlásiť',
			      'sortorder' => '0',
			      'visible' => true),
			'skratkaPredmetu' => array(
			      'title' => 'skratka predmetu',
			      'sortorder' => '0',
			      'visible' => false),
			'predmet' => array(
			      'title' => 'predmet',
			      'sortorder' => '0',
			      'visible' => true,
			      'col' => -1),
			// }}}
		);
	}
	
	public static function vyberTerminuHodnoteniaJoined() {
		return array(
			// {{{
			// udaje vzniknute pocas joinovania
			'predmet' => array(
			      'title' => 'Predmet',
			      'sortorder' => '0',
			      'visible' => true,
			      'col' => -1),
			'predmetIndex' => array(
			      'title' => 'PredmetIndex',
			      'sortorder' => '0',
			      'visible' => false),
			'prihlas' => array(
			      'title' => 'Prihlás',
			      'sortorder' => '0',
			      'visible' => true),
			// originalne udaje
			'kodFaza' => array(
			      'title' => 'Kód fázy',
			      'sortorder' => '0',
			      'visible' => false),
			'dat' => array(
			      'title' => 'Dátum',
			      'sortorder' => '0',
			      'visible' => true),
			'cas' => array(
			      'title' => 'Čas',
			      'sortorder' => '0',
			      'visible' => true),
			'miestnosti' => array(
			      'title' => 'Miestnosti',
			      'sortorder' => '0',
			      'visible' => true),
			'pocetPrihlasenych' => array(
			      'title' => 'Počet prihlásených študentov',
			      'sortorder' => '0',
			      'visible' => true),
			'maxPocet' => array(
			      'title' => 'Maximálny počet',
			      'sortorder' => '0',
			      'visible' => true),
			'pocetHodn' => array(
			      'title' => 'Počet hodnotiacich',
			      'sortorder' => '0',
			      'visible' => false),
			'hodnotiaci' => array(
			      'title' => 'Hodnotiaci',
			      'sortorder' => '0',
			      'visible' => true),
			'prihlasovanie' => array(
			      'title' => 'Interval pre prihlasovanie',
			      'sortorder' => '0',
			      'visible' => true),
			'odhlasovanie' => array(
			      'title' => 'Interval pre odhlasovanie',
			      'sortorder' => '0',
			      'visible' => true),
			'poznamka' => array(
			      'title' => 'Poznámka',
			      'sortorder' => '0',
			      'visible' => true),
			'zaevidoval' => array(
			      'title' => 'Zaevidoval',
			      'sortorder' => '0',
			      'visible' => false),
			// }}}
		);
	}
}
