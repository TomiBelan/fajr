#!/usr/bin/php
<?php
use fajr\config\FajrConfigLoader;
use fajr\config\FajrConfigOptions;

// register our autoloader
require_once (__DIR__ . '/../libfajr/src/libfajr.php');
Loader::register();
Loader::searchForClasses(__DIR__ . '/../src', true);
Loader::searchForClasses(__DIR__ . '/../libfajr/src', true);

if (!FajrConfigLoader::isConfigured()) {
  echo 'Chyba: Fajr nie je nakonfigurovany'. "\n";
  return;
}

$config = FajrConfigLoader::getConfiguration();

if (!$config->get(FajrConfigOptions::USE_CACHE)) {
  echo 'Info: Cache je vypnuta, nema zmysel ju mazat'. "\n";
  return;
}

function clearDirectory($path) {
  if (!is_dir($path)) {
    echo 'Info: ' . $path . ' nie je adresar/neexistuje.' . "\n";
    return;
  }
  foreach (new DirectoryIterator($path) as $fileInfo) {
    if (!$fileInfo->isDot() && ($fileInfo->isFile() || $fileInfo->isDir())) {
      if ($fileInfo->isDir()) {
        clearDirectory($fileInfo->getPathname());
        rmdir($fileInfo->getPathname());
      }
      else {
        unlink($fileInfo->getPathname());
      }
    }
  }
}

// Kazdy podtyp cache chceme mazat zvlast, kedze moze byt aj v inom adresari
// Nakoniec vyprazdnime aj hlavny cache adresar

$path = $config->getDirectory(FajrConfigOptions::PATH_TO_TEMPLATE_CACHE);
echo 'Info: Mazem template cache: ' . $path . "\n";
clearDirectory($path);

$path = $config->getDirectory(FajrConfigOptions::PATH_TO_CACHE);
echo 'Info: Mazem zvysok cache: ' . $path . "\n";
clearDirectory($path);