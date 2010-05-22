<?php
/*
Copyright (c) 2010 Martin Králik

 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.
*/

 	class DisplayManager
	{
		protected static $content = array();
		
		protected static $predefinedContent = array(
			'loginBox' => '
<div class="span-5 prepend-1">
	<form method="post" action="">
		<fieldset>
			<legend>Prihlásenie cez Cosign</legend> 
			<label for="login">Prihlasovacie meno</label>
			<br/>
			<input type="text" name="login" id="login"/>
			<br/>
			<label for="krbpwd">Heslo</label>
			<br/>
			<input type="password" name="krbpwd" id="krbpwd"/>
			<br/>
			<button type="submit" name="submit">
				<img alt="" src="images/key_go.png"/>
				Prihlásiť
			</button>
		</fieldset>
	</form>
</div>
<div class="span-5 last">
	<form method="post" action="">
		<fieldset>
			<legend>Prihlásenie cez cookie</legend> 
			<label for="cosignCookie">cosign-filter-ais2.uniba.sk</label>
			<br/>
			<input type="text" name="cosignCookie" id="cosignCookie"/>
			<br/>
			<button type="submit" name="submit">
				<img alt="" src="images/key_add.png"/>
				Prihlásiť
			</button>
		</fieldset>
	</form>
</div>
<hr class="space" />',

			'header' => '
<!DOCTYPE html
     PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
     "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="sk" lang="sk">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/prototype/1.6.1.0/prototype.js"></script>
	<script type="text/javascript" src="scripts/fajr.js"></script>
	<script type="text/javascript" src="scripts/toggleVisibility.js"></script>
	<script type="text/javascript" src="scripts/tablesort.min.js"></script>
	<script type="text/javascript" src="scripts/tabs.js"></script>
	<link rel="stylesheet" href="css/screen.css" type="text/css" media="screen, projection" />
	<link rel="stylesheet" href="css/plugins/buttons/screen.css" type="text/css" media="screen, projection" />
	<link rel="stylesheet" href="css/custom.css" type="text/css" media="screen, projection" />
	<link rel="stylesheet" href="css/print.css" type="text/css" media="print" />
	<!--[if lt IE 8]><link rel="stylesheet" href="css/ie.css" type="text/css" media="screen, projection" /><![endif]-->
	<link href="images/favicon.ico" rel="icon" type="image/x-icon" />
	
	<title>FAJR</title>
	
</head>
<body>
<div class="container"><h1>FAJR beta</h1>
',

			'footer' => '
</div>
</body>
</html>
',
			'warnings' => '
<div class="span-18 prepend-1 last increase-line-height">
<p>
Vitajte pred bránou do aplikácie FAJR.
Zatiaľ poskytuje jedinú funkcionalitu, a to pohľad na niektoré tabuľky z <a href="https://ais2.uniba.sk/">AISu</a>.
Sú dve možnosti ako sa do nej prihlásiť.
</p>
<ol>
<li>
Pomocou cookie.<br/>
Tento postup je náročnejší, ale bezpečnejší a preto <strong>odporúčaný</strong>.
Funguje to tak, že sa normálne prihlásite do <a href="https://ais2.uniba.sk/">AISu</a>.
Po prihlásení si poziete nastavené cookies.
Hodnotu tej s názvom "cosign-filter-ais2.uniba.sk" skopírujete do ľavého formulára "<em>Prihlásenie cez cookie</em>".
</li>
<li>
Pomocou mena a hesla.<br/>
Do pravého formulára "<em>Prihlásenie cez Cosign</em>" vyplníte svoje meno a heslo, rovnaké ako keď sa prihlasujete do <a href="https://ais2.uniba.sk/">AISu</a>.
Tento postup <strong>nie je odporúčaný</strong>.
</li>
</ol>
<p>
Prečo je prvý postup bezpečnejší?<br/>
Pretože pri ňom neposielate svoje meno a heslo a nevystavujete sa riziku, že ho niekto po ceste ukrade.
S odcudzenou cookie sa dajú meniť len údaje v <a href="https://ais2.uniba.sk/">AISe</a>,
s menom a heslom sa dá dostať všade, na čo a kde sa používa.<br/>
Pri nadväzovaní spojenia s AISom a Cosignom sa neoveruje, či majú platný certifikát,
pretože s tým bol problém (nemali).
</p>
<p>
Táto aplikácia nerobí nič zlé. Ale ak jej aj tak nedôverujete, môžete si ju rozbehnúť u seba.
Stačí vám k tomu webserver, PHP a jej <a href="http://code.google.com/p/fajr/source/checkout">zdrojové kódy</a>.
</p>
</div>
',
		'credits' => '
<div class="span-21 prepend-1 last increase-line-height"><p>
Stránka mohla vzniknúť vďaka
<a href="http://www.prototypejs.org/">Prototype</a>,
<a href="http://www.blueprintcss.org/">Blueprint</a>,
<a href="http://www.frequency-decoder.com/2006/09/16/unobtrusive-table-sort-script-revisited">Unobtrusive Table Sort Script</a>
a <a href="http://www.famfamfam.com/lab/icons/silk/">Silk icons</a>.
</p></div>
',
		'terms' => '
<div class="span-21 prepend-1 last increase-line-height"><p>
Prihlásením do systému Fajr súhlasíte s 
<a href="terms_of_use.php">Podmienkami používania</a>
</p></div>
'
		);
		
		public static function addContent($content, $predefinedContent = false)
		{
			if ($predefinedContent) self::$content[] = self::$predefinedContent[$content];
			else self::$content[] = $content;
		}
		
		public static function display()
		{
			$html = '';
			foreach (self::$content as $item) $html .= $item;
			$html = self::$predefinedContent['header'] . $html . self::$predefinedContent['footer'];
			return $html;
		}
				
	}
	
?>
