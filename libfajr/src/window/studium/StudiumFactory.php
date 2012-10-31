<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains factory for all screens from management of study.
 *
 * @package    Libfajr
 * @subpackage Window__Studium
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace libfajr\window\studium;

use libfajr\window\studium as VSES017;
use libfajr\trace\Trace;

/**
 * Provides instances of screens in study management part of ais.
 */
interface StudiumFactory
{

  /**
   * @returns AdministraciaStudiaScreen
   */
  public function newAdministraciaStudiaScreen(Trace $trace);

  /**
   * @param $paramName id AISoveho parameter bagu
   * @returns TerminyHodnoteniaScreen
   */
  public function newTerminyHodnoteniaScreen(Trace $trace, $paramName);

  /**
   * @param $paramName id AISoveho parameter bagu
   *
   * @returns HodnoteniaPriemeryScreen
   */
  public function newHodnoteniaPriemeryScreen(Trace $trace, $paramName);
}
