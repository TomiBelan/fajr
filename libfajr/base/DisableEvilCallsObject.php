<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

// TODO(??): missing author

namespace fajr\libfajr\base;
use \Exception;
class DisableEvilCallsObject {
  public static function __callStatic($name, $arguments) {
    throw new Exception("Evil __callStatic() in "
        . __CLASS__ . " --> $name");
  }

  public static function __invoke($name, $arguments) {
    throw new Exception("Evil __invoke() in "
        . __CLASS__ . " --> $name");
  }

  public function __call($name, $arguments) {
    throw new Exception("Evil __call() in "
        . __CLASS__ . " --> $name");
  }

  public function __set($name, $arguments) {
    throw new Exception("Evil __set() in "
        . __CLASS__ . " --> $name");
  }

  public function __get($name) {
    throw new Exception("Evil __get() in "
        . __CLASS__ . " --> $name");
  }

  public function __isset($name) {
    throw new Exception("Evil __isset() in "
        . __CLASS__ . " --> $name");
  }

  public function __unset($name) {
    throw new Exception("Evil __unset() in "
        . __CLASS__ . " --> $name");
  }
}
