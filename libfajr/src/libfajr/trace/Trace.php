<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Provides support for logging debug information into
 * tree structure called trace.
 *
 * PHP version 5.3.0
 *
 * @package    Libfajr
 * @subpackage Trace
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */
namespace libfajr\trace;

/**
 * Interface to objects tracing debug information.
 *
 * You can pass this object along your call tree
 * and log important information alogn with it.
 *
 * @package    Libfajr
 * @subpackage Trace
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
interface Trace
{
  /**
   * Append a new text node into tracer.
   *
   * @param string $text Text to append.
   * @param array $tags to use
   *
   * @returns void
   */
  public function tlog($text, array $tags = null);

  /**
   * Convenient way of appending any variable definition to tracer.
   * Implementations should use var_export() or similar function to
   * obtain string version of variable.
   *
   * @param string $name     name of the variable to be dumped
   * @param mixed  $variable variable to be dumped
   * @param array $tags to use
   *
   * @returns void
   */
  public function tlogVariable($name, $variable, array $tags = null);

  /**
   * Create a new child node of this trace object and return it.
   *
   * @param string $message message
   * @param array $tags to use
   *
   * @returns Trace Newly created child node.
   */
  public function addChild($message, array $tags = null);

  /**
   * Determine whether the trace is active or not.
   *
   * @returns true if the log functions would actually do something
   */
  //public function isActive();
}
