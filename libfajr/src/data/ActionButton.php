<?php
// Copyright (c) 2013 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

namespace libfajr\data;

use DOMElement;
use DOMDocument;
use libfajr\trace\Trace;
use libfajr\util\StrUtil;
use libfajr\base\Preconditions;
use libfajr\data\ComponentInterface;
use libfajr\exceptions\ParseException;


/**
 * Trieda zastrešujúca jednoduche tlacitko v AISe.
 *
 * @package    Libfajr
 */
class ActionButton implements ActionComponentInterface
{
  /**
   * Unique name of the component in aisHTMLCode
   * @var string
   */
  private $componentID = null;


  /**
   * Create a Table and set its dataViewName and definition
   *
   * @param string $dataViewName name of Table which we want to store here
   */
  public function __construct($componentID)
  {
    Preconditions::checkIsString($componentID);
    $this->componentID = $componentID;
  }

/**
   * Returns xml request code just for this actionComponent
   *
   * Sample of xml:
   *
   * <events>
   *   <ev>
   *     <dlgName>VSES017_StudentZapisneListyDlg0</dlgName>
   *     <compName>nacitatDataAction</compName>
   *     <event class='avc.ui.event.AVCActionEvent'>
   *   </ev>
   * </events>
   *
   * @return DOMDocument XML object
   */
 public function getActionXML($dlgName)
  {
    Preconditions::checkIsString($dlgName);

    $xml_spec = new DOMDocument();

    $dlgName = $xml_spec->createElement('dlgName', $dlgName);
    $events = $xml_spec->createElement('events');
    $ev = $xml_spec->createElement('ev');
    $compName = $xml_spec->createElement('compName', $this->componentID);
    $event = $xml_spec->createElement('event');

    $atr = $xml_spec->createAttribute("class");
    $atr->value = 'avc.ui.event.AVCActionEvent';

    $event->appendChild($atr);

    $ev->appendChild($dlgName);
    $ev->appendChild($compName);
    $ev->appendChild($event);

    $events->appendChild($ev);

    $xml_spec->appendChild($events);

    return $xml_spec;
  }
}
