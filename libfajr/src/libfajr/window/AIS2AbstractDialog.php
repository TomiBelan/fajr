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

use libfajr\trace\Trace;
use libfajr\window\DialogData;
use libfajr\window\DialogParent;
use libfajr\base\DisableEvilCallsObject;
use libfajr\window\LazyDialog;
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

  const DIALOG_NAME_PATTERN = '@dm\(\)\.openDialog\("(?P<dialogName>[^"]+)",@';

  /**
   * Konštruktor.
   *
   * @param string $appClassName Názov "triedy" obsluhujúcej danú obrazovku v AISe.
   * @param string $identifiers  Konkrétne parametre pre vyvolanie danej obrazovky.
   */
  public function __construct(Trace $trace, DialogParent $parent, DialogData $data)
  {
    $this->trace = $trace;
    $this->parent = $parent;
    $this->data = $data;
    $this->uid = MiscUtil::random();
  }

  /**
   * Nadviaže spojenie, spustí danú "aplikáciu" v AISe
   * a natiahne prvotné dáta do atribútu $data.
   */
  public function openIfNotAlready(Trace $trace)
  {
    if ($this->inUse) {
      return;
    }
    $this->executor = $this->parent->openDialogAndGetExecutor($trace, $this->uid, $this->data);
    $this->formName = $this->executor->requestOpen($trace);
    $this->inUse = true;
    $this->terminated = false;
  }

  /**
   * Zatvorí danú "aplikáciu" v AISe
   */
  public function closeIfNeeded(Trace $trace)
  {
    if (!$this->inUse) {
      return;
    }
    if (!$this->terminated) {
      $this->executor->requestClose($trace);
    }
    $this->inUse = false;
    $this->parent->closeDialog($this->uid);
  }

  /**
   * Deštruktor.
   * Zatvorí danú "aplikáciu" v AISe,
   * aby sa nevyčerpal limit otvorených aplikácii na session.
   * Toto správenie nebolo pozorované pri dialógoch, ale pre istotu to tu je.
   */
  public function  __destruct()
  {
    $this->closeIfNeeded($this->trace);
  }

  public function openDialogAndGetExecutor(Trace $trace, $dialogUid, DialogData $data)
  {
    $this->openIfNotAlready($trace->addChild("opening dialog parent"));
    if ($this->openedDialog !== null) {
      throw new IllegalStateException('V AIS2 screene "' . $this->formName .
          '" už existuje otvorený dialog. Pre otvorenie nového treba pôvodný zatvoriť.');
    }
    $this->openedDialog = $dialogUid;
    $executor = $this->executor->spawnChild($data, $this->formName);
    return $executor;
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
