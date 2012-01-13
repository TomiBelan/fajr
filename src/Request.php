<?php
/**
 * Tento súbor obsahuje objekt reprezentujúci príchodziu požiadavku
 *
 * @copyright  Copyright (c) 2010, 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 * @filesource
 */
namespace fajr;

use libfajr\base\Preconditions;
use libfajr\connection\AIS2ServerConnection;

/**
 * Class representing incoming request from browser
 *
 * @package    Fajr
 * @author     Martin Sucha <anty.sk@gmail.com>
 */
class Request
{
  /** @var Request $instance */
  private static $instance;

  /* TODO document */
  public static function getInstance()
  {
    if (!isset(self::$instance)) {
      self::$instance = new Request(HttpInputParameters::getInstance(), $_SERVER['REQUEST_TIME']);
    }
    return self::$instance;
  }

  private $input;
  private $time;

  /**
   * @param double time of the request as unix timestamp
   */
  public function __construct(InvocationParameters $input, $time)
  {
    $this->input = $input;
    $this->time = $time;
  }

  /**
   * Return a named parameter
   *
   * @param string $name
   * @param string defaultValue
   * @returns string parameter value
   */
  public function getParameter($name, $defaultValue = '')
  {
    // Note: if we want to change default from '' to null,
    // we must fix all controllers
    Preconditions::checkIsString($name, '$name should be string.');
    Preconditions::checkIsString($defaultValue,
        '$defaultValue should be string.');

    $value = $this->input->getParameter($name);
    if ($value === null) {
      return $defaultValue;
    }
    return $value;
  }

  /**
   * Check if the request was called with a given parameter
   *
   * @param string $name parameter name
   * @returns bool true iff the parameter is present
   */
  public function hasParameter($name)
  {
    Preconditions::checkIsString($name, '$name should be string.');
    return $this->input->getParameter($name) !== null;
  }

  /**
   * Ensure a parameter is not set after this call
   *
   * @param string $name parameter name to clear
   */
  public function clearParameter($name)
  {
    Preconditions::checkIsString($name, '$name should be string.');
    $this->input->setParameter($name, null);
  }

  /**
   * @returns double time of the request as unix timestamp
   */
  public function getRequestTime()
  {
    return $this->time;
  }

  /**
   * Get a value of HTTP header from request or null if not present
   * @param string $name
   * @return string|null the header value
   */
  public function getHeader($name)
  {
    Preconditions::checkIsString($name, '$name should be string.');
    $var_name = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
    if (!isset($_SERVER[$var_name])) {
      return null;
    }
    return $_SERVER[$var_name];
  }

  /**
   * Return true if the user wishes not to be tracked, false otherwise
   */
  public function isDoNotTrack()
  {
    $header1 = $this->getHeader('X-Do-Not-Track');
    $header2 = $this->getHeader('DNT');
    return ($header1 === '1') || ($header2 === '1');
  }
  
  /**
   * Return the name of the host the application is running
   */
  public function getHostName()
  {
    return $_SERVER['SERVER_NAME'];
  }

}
