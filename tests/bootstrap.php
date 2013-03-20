<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

require_once __DIR__ . '/../vendor/autoload.php';

ini_set('error_reporting', E_ALL | E_STRICT);

/* Prepare report directory */
$_ds = DIRECTORY_SEPARATOR;
$reportPath = __DIR__ . $_ds . '..' . $_ds . 'report' . $_ds .  'tests';
if (!is_dir($reportPath)) {
  mkdir($reportPath, 0755, true);
}
