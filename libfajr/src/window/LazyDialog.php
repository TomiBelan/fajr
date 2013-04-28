<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Libfajr
 * @subpackage Window
 * @author Peter Perešíni <ppershing+fajr@gmail.com>
 * @TODO documentation
 * @filesource
 */
namespace libfajr\window;

use libfajr\trace\Trace;

interface LazyDialog
{
  /**
   * Opens the ais screen/dialog. Note that this will have to be called
   * before first object request.
   */
  public function openWindow();

  /**
   * Close screen/dialog. Remember to close screen when you end with it.
   * Note hovewer, that you must close child dialog first!.
   */
  public function closeWindow();
}
