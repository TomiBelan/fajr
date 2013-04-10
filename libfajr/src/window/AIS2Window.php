<?php
// Copyright (c) 2013 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

namespace libfajr\window;
use libfajr\data\ComponentInterface;

class AIS2Window
{
  protected ComponentInterface $components = null;
  protected $actions = null;
  protected $trace = null;

  /**
   * Create window object and set some necessary information about it
   *
   */
  public function __construct($appClassName, $additionalParams)
  {

  }

  /**
   * Open window when we want to work with its.
   *
   */
  public function openWindow()
  {
   
  }

  /**
   * Basicly it make a request definied by component action with id $action
   *
   * @param string $action name, id of action
   */
  public doAction($action)
  {

  }

  /**
   * Update all components after some action
   *
   * @param DOMDocument $dom response on some action
   */
  private updateComponents($dom)
  {

  }

  /**
   * Execute a request, send a request to server...
   *
   * @param ???
   */
  private executeRequest($action)
  {

  }

  /**
   * Close window, because we won`t run out of Open windows limit
   *
   */
  public function  __destruct()
  {
      $this->closeWindow();
  }

  /**
   * Close window, because we won`t run out of Open windows limit
   *
   */
  public function closeWindow()
  {

  }
}
?>
