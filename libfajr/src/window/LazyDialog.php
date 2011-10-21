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
   * Opens the ais screen/dialog. Note that this will be called
   * automatically on first object request.
   */
  public function openIfNotAlready(Trace $trace);

  /**
   * Close screen/dialog. This will be automatically called at the destructor,
   * but you may find it handy to terminate dialog earlier. Note hovewer, that
   * you must close child dialog first!.
   */
  public function closeIfNeeded(Trace $trace);
}
