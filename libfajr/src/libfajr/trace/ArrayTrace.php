<?php
/**
 * Contains Trace which stores its data in array.
 *
 * @copyright  Copyright (c) 2010 The Fajr authors.
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace libfajr\trace;

use libfajr\base\Preconditions;
use libfajr\base\Timer;
use libfajr\trace\Trace;
use libfajr\util\CodeSnippet;
use libfajr\trace\TraceUtil;

/**
 * A Trace that stores its data in an array
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */
class ArrayTrace implements Trace
{
  private $header;
  private $children = array();
  private $constructTime = null;
  private $timer = null;

  /**
   * Construct an ArrayTrace
   * @param Timer $timer timer to measure time with
   * @param string $header header text to be displayed
   */
  public function __construct(Timer $timer, $header = "")
  {
    Preconditions::checkIsString($header);
    $this->header = $header;
    $this->constructTime = microtime(true);
    $this->timer = $timer;
  }

  /**
   * Log an event
   * @param string $text text to be displayed
   * @param array $tags to use
   */
  public function tlog($text, array $tags = null)
  {
    Preconditions::checkIsString($text, '$text should be string');
    $this->children[] = array('info'=>$this->getInfoArray(),
                              'type'=>'log',
                              'data'=>$text);
  }

  /**
   * Log contents of a variable
   * @param string $name name of the variable (without dollar sign)
   * @param mixed $variable contents of the variable to be dumped
   * @param array $tags to use
   */
  public function tlogVariable($name, $variable, array $tags = null)
  {
    $data = preg_replace("@\\\\'@", "'", var_export($variable, true));
    $this->children[] = array('info'=>$this->getInfoArray(),
                              'type'=>'variable',
                              'name'=>$name,
                              'data'=>$data);
  }

  /**
   * Create a new ArrayTrace at the insertion point
   * @param string $message text of the message
   * @param array $tags
   * @return ArrayTrace child trace object
   */
  public function addChild($message, array $tags = null)
  {
    Preconditions::checkIsString($message, '$message should be string');
    $child = new ArrayTrace($this->timer, $message);
    $this->children[] = array('info'=>$this->getInfoArray(),
                              'type'=>'trace',
                              'trace'=>$child);
    return $child;
  }

  /**
   * Returns information about this particular trace event.
   *
   * @returns array with with time, caller data and code snippet
   */
  private function getInfoArray() {
    $info = array();

    $caller = TraceUtil::getCallerData(2);
    $class = isset($caller['class']) ? $caller['class'] : "";
    $class = preg_replace("@.*\\\\@", "", $class);
    $function = $caller['function'];

    $caller = TraceUtil::getCallerData(1);
    $file = $caller['file'];
    $line = $caller['line'];
    $snippet = CodeSnippet::getCodeSnippet($file, $line, 5);

    $info['class'] = $class;
    $info['function'] = $function;
    $info['line'] = $line;
    $info['file'] = $file;
    $info['snippet'] = $snippet;
    $info['elapsedTime'] = $this->timer->getElapsedTime();

    return $info;
  }

  /**
   * Return this trace's children as an array
   * @return array(array('type'=>..., ...)) children information
   */
  public function getChildren()
  {
    return $this->children;
  }

  /**
   * Return this trace's header text
   * @return string header text
   */
  public function getHeader()
  {
    return $this->header;
  }
  
}
