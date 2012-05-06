<?php
/**
 * Tento súbor obsahuje implementáciu držiacu warningy
 *
 * @copyright  Copyright (c) 2012 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 * @filesource
 */
namespace fajr;

use libfajr\trace\Trace;
use libfajr\base\Preconditions;

class Warnings
{
  /** @var Warnings */
  private static $instance;
  
  /** @return Warnings */
  public static function getInstance()
  {
    if (!isset(self::$instance)) {
      self::$instance = new Warnings();
    }
    return self::$instance;
  }
  
  /** @var array */
  private $warnings;
  
  public function __construct()
  {
    $this->warnings = array();
  }
  
  public function addWarning(array $warning)
  {
    $this->warnings[] = $warning;
  }
  
  public function getAll()
  {
    return $this->warnings;
  }
  
  public function warnWrongTableStructure(Trace $trace, $tableName,
      array $expectedDefinition, array $definition) {
    Preconditions::checkIsString($tableName);
    if ($expectedDefinition != $definition) {
      $message = array('type' => 'unexpectedTableStructure',
                       'tableName' => $tableName);
      $this->addWarning($message);
      
      $child = $trace->addChild("Differences in data table " . $tableName);
      list($del, $both, $ins) =
        self::compareArrays($expectedDefinition, $definition);
      $child->tlogVariable('deleted', $del);
      $child->tlogVariable('unchanged', $both);
      $child->tlogVariable('inserted', $ins);
      $child->tlogVariable('expectedDefinition', $expectedDefinition);
      $child->tlogVariable('definition', $definition);
    }
  }
}