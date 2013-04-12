<?php
// Copyright (c) 2013 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

namespace libfajr\window;
use libfajr\data\ComponentInterface;
use libfajr\trace\Trace;
use libfajr\connection\SimpleConnection;
use libfajr\window\RequestBuilderImpl;
use libfajr\window\ScreenRequestExecutor;
use libfajr\window\ScreenData;
use libfajr\util\StrUtil;
use libfajr\exceptions\ParseException;

class AIS2Window
{
  private $components = null;
  private $actions = null;
  private $trace = null;
  private $executor = null;
  private $data = null;
  private $isOpen = false;

  /**
   * Create window object and set some necessary information about it
   *
   * @param ScreenRequestExecutor $executor execute requests and return response
   * @param string $appClassName eg.: ais.gui.vs.es.VSES017App
   * @param array(string => string) $additionalParams eg.: array('kodAplikacie' => 'VSES017')
   * @param array('dataComponents' => ComponentInterface      //eg.: DataTable
   *              'actionComponents' => ComponentInterface)  //eg.: actionButton
   */
  public function __construct(Trace $trace, ScreenRequestExecutor $executor, $appClassName, $additionalParams, $components)
  {
      $this->trace = $trace;
      $this->executor = $executor;
      $this->components = $components['dataCompomonents'];
      $this->actions = $components['actionComponents'];

      $data = new ScreenData();
      $data->appClassName = $appClassName;
      $data->additionalParams = $additionalParams;
      $this->data = $data;
  }

  /**
   * Open window when we want to work with its
   * and initialize all components.
   *
   */
  public function openWindow()
  {
    if ($this->isOpen) {
      return;
    }
    $this->executor->requestOpen($this->trace, $this->data);
    $this->isOpen = true;

    $response = $this->executor->requestContent($this->trace->addChild("get content"));
    $response = $this->prepareResponse($this->trace->addChild("Converting response from HTML to DOMDocument"), $response);

    $this->updateComponents($response);

  }

  /**
   * Basicly it make a request definied by component action with id $action
   *
   * @param string $action name, id of action
   */
  public function doAction($action)
  {

  }

  /**
   * Update all components after some action
   *
   * @param DOMDocument $dom response on some action
   */
  private function updateComponents($dom)
  {
      foreach($this->components as $component){
          $component->updateComponentFromResponse($this->response, $dom);
      }
  }

  /**
   * Execute a request, send a request to server...
   *
   * @param ???
   */
  private function executeRequest($action)
  {

  }

  /**
   * Close window, because we won`t run out of Open windows limit
   *
   */
  public function closeWindow()
  {
    if (!$this->isOpen) {
      return;
    }
    $this->executor->requestClose($this->trace);
    $this->isOpen = false;
  }

  /**
   * From HTML response make a DOMDocument
   *
   * @param Trace $trace trace for logging
   * @param string $html html response from AIS
   * @returns DOMDocument AIS response in DOMDocument
   */
  private function prepareResponse(Trace $trace, $html)
  {
    // fixing html code, so DOMDocumet it can parse
    Preconditions::checkIsString($html);
    $html = str_replace("<!--", "", $html);
    $html = str_replace("-->", "", $html);
    $html = str_replace("script", "div", $html);
    $trace->tlogVariable("Fixed html", $html);

    // creating DOMDocument
    $dom = new DOMDocument();
    $trace->tlog("Loading html to DOM");
    $loaded = @$dom->loadHTML($html);
    if (!$loaded) {
      throw new ParseException("Problem parsing html to DOM.");
    }

    //fixing id atributes
    $trace->tlog('Fixing id attributes in the DOM');
    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query("//*[@id]");
    foreach ($nodes as $node) {
      // Note: do not erase next line. @see
      // http://www.navioo.com/php/docs/function.dom-domelement-setidattribute.php
      // for explanation!
      $node->setIdAttribute('id', false);
      $node->setIdAttribute('id', true);
    }
    return $dom;
  }
}
?>
