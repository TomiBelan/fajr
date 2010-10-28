<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Bootstraps the whole application.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace fajr;

use fajr\injection\Injector;
use fajr\injection\Module;
use fajr\modules\ControllerModule;
use fajr\modules\CurlConnectionOptionsModule;
use fajr\modules\SessionInitializerModule;
use fajr\modules\TraceModule;
use Loader;
use sfServiceContainerAutoloader;
use Twig_Autoloader;

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

// register Symfony autoloader first, because ours will eat the loading instead.
require_once '../third_party/symfony_di/lib/sfServiceContainerAutoloader.php';
sfServiceContainerAutoloader::register();

// register Twig autoloader
require_once '../third_party/twig/lib/Twig/Autoloader.php';
Twig_Autoloader::register();

// register our autoloader
require_once 'libfajr/libfajr.php';
Loader::register();
Loader::searchForClasses(dirname(__FILE__), true);
// TODO(ppershing): move this to libfajr/Loader.php as that is the right place for it
require_once 'libfajr/Assert.php';

// is there configuration.php file present?
if (!FajrConfig::isConfigured()) {
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

// bootstrapping whole application
$modules = array(
    new ControllerModule(),
    new CurlConnectionOptionsModule(),
    new SessionInitializerModule(),
    new TraceModule());
$injector = new Injector($modules);
$fajr = new Fajr($injector);
$fajr->run();
session_write_close();
