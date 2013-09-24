<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Libfajr
 * @subpackage Window
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace libfajr\window;

use DOMXPath;
use libfajr\trace\Trace;
use libfajr\window\DialogData;
use libfajr\window\DialogParent;
use libfajr\base\Preconditions;
use libfajr\base\DisableEvilCallsObject;
use libfajr\window\LazyDialog;
use DOMDocument;
use libfajr\util\MiscUtil;

/**
 * Abstraktná trieda reprezentujúca jednu obrazovku v AISe.
 *
 * @package    Libfajr
 * @subpackage Window
 * @author     Martin Králik <majak47@gmail.com>
 */
class AIS2AbstractDialog extends DisableEvilCallsObject
    implements DialogParent, LazyDialog
{
  protected $parent = null;
  protected $terminated = false;
  protected $formName = null;
  protected $inUse = false;
  protected $executor;
  protected $openedDialog = null;

  private $trace = null;
  private $uid;
  protected $data;

  /**
   * All data components which are in Window and we
   * want to use them..., where key is an ID of that
   * component
   *
   * @var array(string => ComponentInterface object)
   */
  public $components = null;

  /**
   * All action components which are in Window and we
   * want to use them..., where key is an ID of that
   * component
   *
   * @var array(string => ComponentInterface object)
   */
  protected $actions = null;

  const DIALOG_NAME_PATTERN = '@dm\(\)\.openDialog\("(?P<dialogName>[^"]+)",@';

  /**
   * Konštruktor.
   *
   * @param string $appClassName Názov "triedy" obsluhujúcej danú obrazovku v AISe.
   * @param string $identifiers  Konkrétne parametre pre vyvolanie danej obrazovky.
   */
  public function __construct(Trace $trace, DialogParent $parent, DialogData $data, $components)
  {
    $this->trace = $trace;
    $this->parent = $parent;
    $this->data = $data;
    $this->uid = MiscUtil::random();
    $this->components = $components['dataComponents'];
    $this->actions = $components['actionComponents'];
  }

  /**
   * Nadviaže spojenie, spustí danú "aplikáciu" v AISe
   * a natiahne prvotné dáta do atribútu $data.
   */
  public function openWindow()
  {
    if ($this->inUse) {
      return;
    }
    $this->executor = $this->parent->openDialogAndGetExecutor($this->trace, $this->uid, $this->data);
    $this->formName = $this->executor->requestOpen($this->trace);
    $this->inUse = true;
    $this->terminated = false;

    $response = $this->executor->requestContent($this->trace->addChild("get content"));
    $response = $this->prepareResponse($this->trace->addChild("Converting response from HTML to DOMDocument"), $response);

    $this->updateComponents($response, true);


  }

  /**
   * Zatvorí danú "aplikáciu" v AISe
   */
  public function closeWindow()
  {
    if (!$this->inUse) {
      return;
    }
    if (!$this->terminated) {
      $this->executor->requestClose($this->trace);
    }
    $this->inUse = false;
    $this->parent->closeDialog($this->uid);
  }

  public function openDialogAndGetExecutor(Trace $trace, $dialogUid, DialogData $data)
  {
    $this->openWindow();
    if ($this->openedDialog !== null) {
      throw new IllegalStateException('V AIS2 screene "' . $this->formName .
          '" už existuje otvorený dialog. Pre otvorenie nového treba pôvodný zatvoriť.');
    }
    $this->openedDialog = $dialogUid;
    $executor = $this->executor->spawnChild($data, $this->formName);
    return $executor;
  }

  public function doAction($action)
  {
    assert(isset($this->actions[$action]));
    $button = $this->actions[$action];
    $action = new DOMDocument();

    //get part of xml action request
    $actionComponent = $button->getActionXML($this->formName);
    $actionComponent = $actionComponent->documentElement;

    $action->appendChild($action->importNode($actionComponent, true));

    $changedProps = new DOMDocument();
    $props = $changedProps->createElement("changedProps");
    $changedProps->appendChild($props);

    //get a changed properites from data components
    foreach($this->components as $component){
      $change = $component->getStateChanges();
      $change = $change->documentElement;
      if($change){
        $changedProps->documentElement->appendChild($changedProps->importNode($change, true));
      }
    }

    $changedProps = $changedProps->documentElement;
    $action->appendChild($action->importNode($changedProps, true));

    //make a request
    $response = $this->executor->doActionRequest($this->trace, $action);
    $response = $this->prepareResponse($this->trace->addChild("Converting response from HTML to DOMDocument"), $response);

    //update component from response
    $this->updateComponents($response);

    return $response;
  }

  /**
   * Update all components after some action
   *
   * @param DOMDocument $dom response on some action
   * @param boolean $init if this is called from openWindow()
   */
  public function updateComponents($dom, $init = null)
  {
      if(empty($this->components)) return;
      foreach($this->components as $component){
          $component->updateComponentFromResponse($this->trace->addChild("updatujem"), $dom, $init);
      }
  }

  private function prepareResponse(Trace $trace, $html)
  {
    // fixing html code, so DOMDocumet it can parse
    Preconditions::checkIsString($html);
    $html = str_replace("<!--", "", $html);
    $html = str_replace("-->", "", $html);

    // just first javascript code contain data which we want
    $count = 1;
    $html = str_replace("type='application/javascript'", "id='init-data'", $html, $count);
    $html = str_replace("script", "div", $html);
    $html = $this->fixNbsp($html);

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

  /**
   * Fix non-breakable spaces which were converted to special character during parsing.
   *
   * @param string $str string to fix
   *
   * @returns string fixed string
   */
  private function fixNbsp($str)
  {
    Preconditions::checkIsString($str);
    // special fix for &nbsp;
    // xml decoder decodes &nbsp; into special utf-8 character
    // TODO(ppershing): nehodili by sa tie &nbsp; niekedy dalej v aplikacii niekedy?
    $nbsp = chr(0xC2).chr(0xA0);
    return str_replace($nbsp, ' ', $str);
  }

  public function closeDialog($dialogUid)
  {
    if ($this->openedDialog != $dialogUid) {
      throw new IllegalStateException("Zatváram zlý dialóg!");
    }
    $this->openedDialog = null;
  }

}
?>
