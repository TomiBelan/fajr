<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * @author Martin Králik <majak47@gmail.com>
 */
namespace fajr;

use Twig_Loader_Filesystem;
use Twig_Environment;
use Twig_Extension_Escaper;

class DisplayManager
{
  protected $content = array();
  
  protected $base = null;

  private static $nextHtmlId = 1;
  
  protected static $predefinedContent = array(
      'loginBox' => '
<div class="span-5 prepend-1">
  <form method="post" action="">
    <fieldset class="login-box">
    <legend>Prihlásenie cez Cosign</legend>
    <div>
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
    </div>
    </fieldset>
  </form>
</div>
<div class="span-5 last">
  <form method="post" action="">
    <fieldset class="login-box">
      <legend>Prihlásenie cez cookie</legend>
    <div>
    bezpečne sa <a
    href=\'https://login.uniba.sk/?cosign-filter-ais2.uniba.sk&amp;https://ais2.uniba.sk/ais/login.do?\'>
      prihlás</a> do AISu a skopíruj si cookie.
    <hr/>
    <label for="cosignCookie">cosign-filter-ais2.uniba.sk</label>
    <br/>
    <input type="password" name="cosignCookie" id="cosignCookie"/>
    <br/>
    <button type="submit" name="submit">
      <img alt="" src="images/key_add.png"/>
      Prihlásiť
    </button>
    </div>
    </fieldset>
  </form>
</div>
<hr class="space" />',

      'warnings' => '
<div class="span-18 prepend-1 last increase-line-height">
<p>
Vitajte pred bránou do aplikácie FAJR.
Snahou tejto miniaplikácie je poskytovať najčastejšie používané funkcie
<a href="https://ais2.uniba.sk/">AISu</a> a to jednoducho, rýchlo,
bez zbytočných klikaní a na všetkých browseroch.
Sú dve možnosti ako sa môžeš prihlásiť.
</p>
<ol>
<li>
Pomocou cookie.<br/>
Tento postup je náročnejší, ale bezpečnejší a preto <strong>odporúčaný</strong>.
Funguje to tak, že sa normálne prihlásite do <a href="https://ais2.uniba.sk/">AISu</a>.
Po prihlásení si pozriete nastavené cookies.
Hodnotu tej s názvom "cosign-filter-ais2.uniba.sk" skopírujete do pravého formulára "<em>Prihlásenie cez cookie</em>".
</li>
<li>
Pomocou mena a hesla.<br/>
Do ľavého formulára "<em>Prihlásenie cez Cosign</em>" vyplníte svoje meno a heslo, rovnaké ako keď sa prihlasujete do <a href="https://ais2.uniba.sk/">AISu</a>.
Tento postup <strong>nie je odporúčaný</strong>.
</li>
</ol>
<p>
Prečo je prvý postup bezpečnejší?<br/>
Pretože pri ňom neposielate svoje meno a heslo a nevystavujete sa riziku, že ho niekto po ceste ukradne.
S odcudzenou cookie sa dajú meniť len údaje v <a href="https://ais2.uniba.sk/">AISe</a>
(aj to maximálne najbližších 12 hodín),
s menom a heslom sa dá dostať všade, na čo a kde sa používa.<br/>
</p>
<p>
Táto aplikácia nerobí nič zlé (aspoň jej autori o tom nevedia).
Ale ak jej aj tak nedôverujete, môžete si ju rozbehnúť u seba.
Stačí vám k tomu webserver, PHP a jej
<a href="http://code.google.com/p/fajr/source/checkout">zdrojové kódy</a>
(pre viac informácií viď <a href="http://code.google.com/p/fajr/source/browse/trunk/README">README</a>).
Ak jej ani tak nedôverujete, používajte AIS ;-).
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

  public function setBase($base)
  {
    $this->base = $base;
  }

  public function addContent($content, $predefinedContent = false)
  {
    if ($predefinedContent) $this->content[] = self::$predefinedContent[$content];
    else $this->content[] = $content;
  }

  public function addException($ex)
  {
    $stackTrace = '';
    if (FajrConfig::get('Debug.Exception.ShowStacktrace')) {
      $stackTrace = "\n<b>Stacktrace:</b>\n" . hescape($ex->getTraceAsString());
      $stackTrace = nl2br($stackTrace);
    }
    $info = '<h2>Pri spracúvaní požiadavky nastala chyba:</h2>';
    $info .= nl2br(hescape($ex->getMessage()));
    $this->addContent('<div class="error">' . $info . $stackTrace . '</div>');
  }

  public function display()
  {
    $templateDir = FajrUtils::joinPath(__DIR__, 'templates/fajr');
    $loader = new Twig_Loader_Filesystem($templateDir);
    $twig = new Twig_Environment($loader);
    $twig->addExtension(new Twig_Extension_Escaper());

    $template = $twig->loadTemplate('pages/legacy.xhtml');

    $html = '';
    foreach ($this->content as $item) $html .= $item;

    $output = $template->render(array(
      'legacy_content'=>$html,
      'base'=>$this->base,
      'debug_banner'=>FajrConfig::get('Debug.Banner'),
      ));
    
    return $output;
  }

  protected function googleAnalytics()
  {
    $account = FajrConfig::get('GoogleAnalytics.Account');
    if ($account === null) return '';
    return '<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push([\'_setAccount\', \''.$account.'\']);
  _gaq.push([\'_trackPageview\']);

  (function() {
    var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;
    ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';
    var s = document.getElementsByTagName(\'script\')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>';
  }

  public static function getUniqueHTMLId($idType = 'id')
  {
    $uniquePart = self::$nextHtmlId;
    self::$nextHtmlId += 1;

    return $idType.$uniquePart;
  }
}

?>
