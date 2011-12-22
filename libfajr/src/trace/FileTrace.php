<?php
/**
 *
 * @copyright  Copyright (c) 2010 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Libfajr
 * @subpackage Trace
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace libfajr\Trace;
use libfajr\trace\Trace;
use libfajr\base\Timer;
use libfajr\util\CodeSnippet;
use libfajr\base\Preconditions;
use InvalidArgumentException;
use libfajr\trace\TraceUtil;

/**
 * A Trace that writes trace information to a file
 */
class FileTrace implements Trace
{
  private $constructTime = null;
  private $timer = null;

  /** @var resource file to write to */
  private $file = null;

  /** @var int Number of parent trace objects */
  private $depthLevel = 0;

  /**
   * Construct a FileTrace
   * @param Timer $timer timer to measure time with
   * @param resource $file file resource handle to write to
   * @param string $header header text to be displayed
   */
  public function __construct(Timer $timer, $file, $depthLevel = 0, $header = "")
  {
    $this->file = $file;
    $this->constructTime = microtime(true);
    $this->timer = $timer;
    $this->depthLevel = $depthLevel;
    $this->writeIndent();
    $this->write($header . "\n");
    $this->writeInfoLine("Type: Trace\n");
    $this->writeInfo();
    $this->flush();
    $this->depthLevel += 1;
  }

  /**
   * Log an event
   * @param string $text text to be displayed
   */
  public function tlog($text)
  {
    Preconditions::checkIsString($text, '$text should be string');
    $this->writeIndent();
    $this->write($text . "\n");
    $this->writeInfoLine("Type: log\n");
    $this->writeInfo();
    $this->flush();
  }

  /**
   * Log data
   * @param string $text text to be displayed as is
   */
  public function tlogData($text)
  {
    Preconditions::checkIsString($text, '$text should be string');
    $this->writeIndent();
    $this->write($text . "\n");
    $this->writeInfoLine("Type: data\n");
    $this->writeInfo();
    $this->flush();
  }

  /**
   * Log contents of a variable
   * @param string $name name of the variable (without dollar sign)
   * @param mixed $variable contents of the variable to be dumped
   */
  public function tlogVariable($name, $variable)
  {
    $data = preg_replace("@\\\\'@", "'", var_export($variable, true));
    
    $this->writeIndent();
    $this->write($name . ' = ' . $data. "\n");
    $this->writeInfoLine("Type: variable\n");
    $this->writeInfo();
    $this->flush();
  }

  /**
   * Create a new FileTrace
   * @param string $header text to use as header
   * @returns FileTrace child trace object
   */
  public function addChild($header = "")
  {
    Preconditions::checkIsString($header, '$header should be string');
    return new FileTrace($this->timer, $this->file,
                         $this->depthLevel + 1, $header);
  }

  /**
   * Writes information about current Trace event
   *
   */
  private function writeInfo() {
    $caller = TraceUtil::getCallerData(2);
    $class = isset($caller['class']) ? $caller['class'] : "N/A";
    $class = preg_replace("@.*\\\\@", "", $class);
    $function = isset($caller['function']) ? $caller['function'] : 'N/A';

    $caller = TraceUtil::getCallerData(1);
    $file = isset($caller['file']) ? $caller['file'] : 'N/A';
    $line = isset($caller['line']) ? $caller['line'] : 'N/A';

    $this->writeInfoLine("Time: " . $this->timer->getElapsedTime() .
                          ' (' . date('d.M.Y H:i:s') . ')');
    $this->writeInfoLine("Class: " . $class);
    $this->writeInfoLine("Function: " . $function);
    $this->writeInfoLine("File: " . $file);
    $this->writeInfoLine("Line: " . $line);
  }

  private function writeIndent()
  {
    $this->write(str_repeat("\t", $this->depthLevel));
  }

  private function writeInfoLine($text)
  {
    Preconditions::checkIsString($text, 'text must be string');
    $this->writeIndent();
    $this->write("\t" . $text . "\n");
  }
  
  private function write($text) {
    Preconditions::checkIsString($text, 'text must be string');
    fwrite($this->file, $text);
  }
  
  private function flush() {
    fflush($this->file);
  }
  
}
