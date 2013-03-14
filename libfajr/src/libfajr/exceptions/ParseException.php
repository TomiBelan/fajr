<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains Exception thrown while parsing AIS response.
 *
 * @package    Libfajr
 * @subpackage Exceptions
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace libfajr\exceptions;
use Exception;

/**
 * Exception representing error while parsing AIS response.
 *
 * @package    Libfajr
 * @subpackage Exceptions
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class ParseException extends Exception
{
}
