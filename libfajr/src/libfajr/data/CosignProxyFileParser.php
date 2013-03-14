<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Tento súbor obsahuje parser cosignových proxy súborov
 *
 * @package    Libfajr
 * @subpackage Data
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace libfajr\data;

use libfajr\trace\Trace;
use libfajr\exceptions\ParseException;
use libfajr\base\Preconditions;
use libfajr\login\CosignServiceCookie;
use InvalidArgumentException;

/**
 * Trieda na parsovanie proxy súborov, ktoré ukladá cosign
 *
 * @package    Libfajr
 * @subpackage Data
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class CosignProxyFileParser
{
  /**
   * Pattern of one line in proxy file.
   */
  const PROXY_LINE_PATTERN='/^x([^=]+)=([^ ]+) ([^ ]+)$/';

  /**
   * Parse a line of proxy file in a string
   *
   * @param Trace  $trace trace object
   * @param string $line not including line termination characters
   * @returns array service, value and domain from parsed line, indexed by
   *                those strings or false if this is not correct proxy line
   */
  private function parseString(Trace $trace, $line)
  {
    Preconditions::checkIsString($line, '$line should be string.');
    $matches = array();
    if (!preg_match(self::PROXY_LINE_PATTERN, $line, $matches)) {
      $trace->tlog('Line did not match');
      $trace->tlogVariable('line', $line);
      throw new ParseException('Proxy file line does not match');
    }
    try {
      return new CosignServiceCookie($matches[1], $matches[2], $matches[3]);
    }
    catch (InvalidArgumentException $e) {
      throw new ParseException('Proxy file arguments are invalid', null, $e);
    }
  }

  /**
   * Parse a file for cosign proxy cookies
   *
   * @param Trace  $trace trace object
   * @param string $filename
   * @returns array Array of parsed service cookies indexed by name
   */
  public function parseFile(Trace $trace, $filename)
  {
    Preconditions::checkIsString($filename, '$filename should be string.');
    $cookies = array();
    $subTrace = $trace->addChild('Parsing cosign proxy file');
    $subTrace->tlogVariable('filename', $filename);
    @$file = file($filename);
    if ($file === false) {
      $subTrace->tlog('failed');
      throw new ParseException('Cannot read proxy file');
    }
    foreach ($file as $lineContent) {
      $parsed = $this->parseString($subTrace, trim($lineContent));
      
      if (isset($cookies[$parsed->getName()])) {
        throw new ParseException('Duplicate proxy service entry found '.
                            'while parsing proxy cookies');
      }
      $cookies[$parsed->getName()] = $parsed;
    }
    return $cookies;
  }

}
