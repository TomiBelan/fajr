<?php
/**
 *
 * @copyright  Copyright (c) 2010,2011 The Fajr authors (see AUTHORS).
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
use libfajr\util\StrUtil;

class EntryStream {
  const ENTRY_TRACE = 'TR';
  const SER_STRING = 'S';
  const SER_INT = 'I';
  const SER_ARRAY = 'A';
  const SER_NULL = 'N';
  
  /** @var resource file to write to */
  private $file = null;
  
  private $nextEntryId = 0;
  
  public function __construct($file) {
    $this->file= $file;
    $this->writeHeader();
  }
  
  public function writeHeader()
  {
    $this->write('FBTR');
  }
  
  public function writeEntry($type, $parent, $data)
  {
    Preconditions::checkIsString($type);
    Preconditions::check(StrUtil::byteLength($type) == 2);
    Preconditions::checkIsNumber($parent);
    Preconditions::checkIsString($data);
    
    $id = $this->nextEntryId++;
    $this->write('BE');
    $this->write($type);
    $this->write(pack('nnN', $id, $parent, StrUtil::byteLength($data)));
    $this->write($data);
    $this->flush();
    return $id;
  }
  
  public function write($text)
  {
    Preconditions::checkIsString($text, 'text must be string');
    fwrite($this->file, $text);
  }
  
  public function flush()
  {
    fflush($this->file);
  }
  
  public function serialize($var)
  {
    if (is_int($var)) {
      return self::SER_INT . pack('N', $var);
    }
    else if (is_array($var)) {
      $serialized = self::SER_ARRAY;
      $serialized .= pack('N', count($var));
      foreach ($var as $key => $value) {
        $serialized .= $this->serialize($key);
        $serialized .= $this->serialize($value);
      }
      return $serialized;
    }
    else if ($var === null) {
      return self::SER_NULL;
    }
    else {
      $var = (string) $var;
      $serialized = self::SER_STRING;
      $serialized .= pack('N', StrUtil::byteLength($var));
      $serialized .= $var;
      return $serialized;
    }
  }
  
}

/**
 * A Trace that writes trace information to a file
 */
class BinaryFileTrace implements Trace
{
  private $constructTime = null;
  private $timer = null;
  
  /** @var EntryStream */
  private $stream = null;
  
  private $id = 0;
  private $parentId = 0;

  /**
   * Construct a FileTrace
   * @param Timer $timer timer to measure time with
   * @param EntryStream|resource $output file resource handle to write to
   * @param string $header header text to be displayed
   */
  public function __construct(Timer $timer, $output, $header = "", $parent = 0)
  {
    if ($output instanceof EntryStream) {
      $this->stream = $output;
    }
    else {
      $this->stream = new EntryStream($output);
    }
    $this->parentId = $parent;
    $this->constructTime = microtime(true);
    $this->timer = $timer;
    
    $this->id = $this->writeEntry($header, null);
  }

  /**
   * Log an event
   * @param string $text text to be displayed
   */
  public function tlog($text)
  {
    Preconditions::checkIsString($text, '$text should be string');
    $this->writeEntry($text, null);
  }

  /**
   * Log data
   * @param string $text text to be displayed as is
   */
  public function tlogData($text)
  {
    Preconditions::checkIsString($text, '$text should be string');
    $this->writeEntry('Long data', $text);
  }

  /**
   * Log contents of a variable
   * @param string $name name of the variable (without dollar sign)
   * @param mixed $variable contents of the variable to be dumped
   */
  public function tlogVariable($name, $variable)
  {
    $this->writeEntry($name, $variable);
  }

  /**
   * Create a new BinaryFileTrace
   * @param string $header text to use as header
   * @returns BinaryFileTrace child trace object
   */
  public function addChild($header = "")
  {
    Preconditions::checkIsString($header, '$header should be string');
    return new BinaryFileTrace($this->timer, $this->stream,
                         $header, $this->id);
  }

  /**
   * Writes information about current Trace event
   *
   */
  private function writeEntry($logMsg, $userData)
  {
    Preconditions::checkIsString($logMsg);
    
    $caller = TraceUtil::getCallerData(2);
    $class = isset($caller['class']) ? $caller['class'] : "N/A";
    $class = preg_replace("@.*\\\\@", "", $class);
    $function = isset($caller['function']) ? $caller['function'] : 'N/A';

    $caller = TraceUtil::getCallerData(1);
    $file = isset($caller['file']) ? $caller['file'] : 'N/A';
    $line = isset($caller['line']) ? $caller['line'] : 'N/A';
    
    $traceInfo = array(
      'elapsed' => $this->timer->getElapsedTime(),
      'timestamp' => time(),
      'class' => $class,
      'function' => $function,
      'file' => $file,
      'line' => $line,
    );
    
    $serialized = $this->stream->serialize($logMsg);
    $serialized .= $this->stream->serialize($traceInfo);
    $serialized .= $this->stream->serialize($userData);
    
    $id = $this->stream->writeEntry(EntryStream::ENTRY_TRACE, $this->parentId,
        $serialized);
    
    return $id;
  }
  
}
