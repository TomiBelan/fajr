<?php
/**
 * Bootstraps the whole application.
 *
 * @copyright  Copyright (c) 2010-2012 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr;

use libfajr\base\SystemTimer;
use Loader;
use sfStorageAutoloader;
use Exception;
use fajr\util\FajrUtils;
use fajr\config\FajrConfig;
use fajr\config\FajrConfigOptions;
use fajr\config\FajrConfigLoader;

$startTime = microtime(true);

/**
 * Exception handler. This handles any uncaught exception in Fajr application.
 *
 * Only fatal errors are not handled in Fajr class itself.
 *
 * @param Exception $e
 */
function fajr_uncaught_exception($e)
{
  // TODO(anty): replace function call arguments with types so
  //             that sensitive information is not revealed
  //             also respect debug configuration for stack traces
  //             if possible
  echo '<pre class="fatalError">'."\n";
  echo $e;
  echo "\n</pre>";
}

// register the exception handler
set_exception_handler('\fajr\fajr_uncaught_exception');

/**
 * Function for exitting bootstrap code in case of error
 *
 * This function stops php execution.
 *
 * @param string $description HTML description of the error to display
 */
function fajr_bootstrap_error($description)
{
  die('<html><head><title>Chyba - Fajr</title>'.
      '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />'.
      '</head><body>'.
      '<h1>Chyba</h1>'.
      $description.
      '</body></html>');
}

/**
 * Wrong www root detection.
 */
if (!defined('_FAJR')) {
  fajr_bootstrap_error('
    <p>
      Máte zle nastavený server, tento súbor by nemal byť priamo prístupný.
      Prosím nastavte server tak, aby sa dalo dostať len k podadresáru
      <code>web</code> a použite <code>index.php</code> v ňom
    </p>
  ');
}

// TODO(ppershing): create helper objects and configuration modules for these constants
error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('Europe/Bratislava');
mb_internal_encoding("UTF-8");

// register Symfony Storage autoloader
require_once '../third_party/symfony_storage/sfStorageAutoloader.php';
sfStorageAutoloader::register();

require_once __DIR__.'/../third_party/Symfony/Component/ClassLoader/UniversalClassLoader.php';

use Symfony\Component\ClassLoader\UniversalClassLoader;

$loader = new UniversalClassLoader();

$loader->registerNamespaces(array(
  'Symfony' => __DIR__.'/../third_party',
));

$loader->registerPrefixes(array(
    'Twig_'  => __DIR__.'/../third_party/twig/lib',
));

$loader->register();

require_once __DIR__ . '/../vendor/autoload.php';

// is there configuration.php file present?
if (!FajrConfigLoader::isConfigured()) {
  fajr_bootstrap_error('
    <p>
      Fajr nie je nakonfigurovaný, prosím skopírujte súbor
      <code>config/configuration.example.php</code> do
      <code>config/configuration.php</code>. Prednastavené hodnoty
      konfiguračných volieb by mali byť vhodné pre väčšinu inštalácií, no
      napriek tomu ponúkame možnosť ich pohodlne zmeniť na jednom mieste - v
      tomto súbore.
    </p>

    <p>
      <strong>Dôležité:</strong> Pred používaním aplikácie je ešte nutné správne
      nastaviť skupinu na <code>www-data</code> (alebo pod čím beží webserver) a
      práva na adresáre <code>./temp</code>, <code>./temp/cookies</code> a
      <code>./temp/sessions</code> (alebo na tie, čo ste nastavili v 
      konfigurácii), tak, aby boli nastavené práva len na zapisovanie a použitie
      , t.j. <code>d----wx---</code>.
    </p>
  ');
}

$config = FajrConfigLoader::getConfiguration();
if ($config->get(FajrConfigOptions::REQUIRE_SSL) && !FajrUtils::isHTTPS()) {
  fajr_bootstrap_error('
     <p>
       Pre túto inštanciu fajr-u je vyžadované HTTPS spojenie.
       Prosím skontrolujte prepisovacie pravidlá v <code>.htaccess</code>
       (alebo konfigurácii web servera), ktoré presmerovávajú HTTP spojenia na HTTPS.
       Ak nechcete vyžadovať SSL spojenie, je možné túto kontrolu
       vypnúť v konfiguračnom súbore, <strong>avšak na produkčných inštaláciách,
       alebo inštaláciách s funkčným SSL sa neodporúča túto kontrolu vypínať.
       </strong>
     </p>
   ');
}

// bootstrapping whole application
// TODO: DEBUG_EXCEPTION_SHOWSTACKTRACE sa nejako vytratilo...
SystemTimer::setInitialTime($startTime);
$fajr = new Fajr($config);
$fajr->run();
session_write_close();
