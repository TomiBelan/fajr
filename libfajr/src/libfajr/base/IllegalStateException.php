<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains exception thrown if object went to illegal state.
 *
 * @package    Libfajr
 * @subpackage Base
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace libfajr\base;

use Exception;

/**
 * Exception thrown if object is in illegal state.
 * For example, if you are trying to use object before initialization
 * or after finalization.
 *
 * @package    Libfajr
 * @subpackage Base
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
class IllegalStateException extends Exception
{

}
