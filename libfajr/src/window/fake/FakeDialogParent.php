<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Libfajr
 * @subpackage Window__Fake
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace libfajr\window\fake;
use libfajr\window\DialogData;
use libfajr\trace\Trace;

interface FakeDialogParent
{
  /**
   * @returns DialogRequestExecutor
   */
  public function openDialogAndGetExecutor(Trace $trace, $uid, array $data);
  public function closeDialog($uid);
}
