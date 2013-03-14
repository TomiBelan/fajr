#!/usr/bin/php
<?php
// Copyright (c) 2011-2012 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

use fajr\config\FajrConfigLoader;
use fajr\config\FajrConfigOptions;

require_once __DIR__ . '/../vendor/autoload.php';

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

$path = $config->getDirectory(FajrConfigOptions::PATH_TO_ROUTER_CACHE);
echo 'Info: Mazem route cache: ' . $path . "\n";
clearDirectory($path);

$path = $config->getDirectory(FajrConfigOptions::PATH_TO_CACHE);
echo 'Info: Mazem zvysok cache: ' . $path . "\n";
clearDirectory($path);
