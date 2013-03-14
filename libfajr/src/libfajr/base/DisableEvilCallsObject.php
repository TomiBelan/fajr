<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Object which disallows fishy class access.
 *
 * @package    Libfajr
 * @subpackage Base
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace libfajr\base;
use \Exception;

/**
 * Disallow all access to unset methods/properties of an object.
 *
 * @package    Libfajr
 * @subpackage Base
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class DisableEvilCallsObject
{
  public static function __callStatic($name, $arguments)
  {
    throw new Exception("Evil __callStatic() in "
        . get_called_class() . " --> $name");
  }

  public static function __invoke($name, $arguments)
  {
    throw new Exception("Evil __invoke() in "
        . get_called_class() . " --> $name");
  }

  public function __call($name, $arguments)
  {
    throw new Exception("Evil __call() in "
        . get_called_class() . " --> $name");
  }

  public function __set($name, $arguments)
  {
    throw new Exception("Evil __set() in "
        . get_called_class() . " --> $name");
  }

  public function __get($name)
  {
    throw new Exception("Evil __get() in "
        . get_called_class() . " --> $name");
  }

  public function __isset($name)
  {
    throw new Exception("Evil __isset() in "
        . get_called_class() . " --> $name");
  }

  public function __unset($name)
  {
    throw new Exception("Evil __unset() in "
        . get_called_class() . " --> $name");
  }
}
