<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Libfajr
 * @subpackage Window
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace libfajr\window;
use libfajr\window\DialogData;
use libfajr\pub\base\Trace;

interface DialogParent
{
  /**
   * @returns DialogRequestExecutor
   */
  public function openDialogAndGetExecutor(Trace $trace, $uid, DialogData $data);
  public function closeDialog($uid);
}
