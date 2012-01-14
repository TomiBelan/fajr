<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

// TODO(??):missing author

define('_FAJR','INCLUDED');

$errors = '';

if (!defined('PHP_VERSION_ID') or (PHP_VERSION_ID < 50300)) {
  $errors .= 'FAJR potrebuje PHP verzie 5.3 a vyssie.<br/>';
}

if (!extension_loaded('mbstring')) {
  $errors .= 'FAJR potrebuje mat v PHP zapnute rozsirenie mbstring.<br/>';
}

if (!extension_loaded('curl')) {
  $errors .= 'FAJR potrebuje mat v PHP zapnute rozsirenie curl.<br/>';
}

if ($errors !== '') die($errors);

include('../src/index.php');
