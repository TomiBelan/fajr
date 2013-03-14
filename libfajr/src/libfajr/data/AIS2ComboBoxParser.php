<?php
// Copyright (c) 2013 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains implementation of ais2 combo box parsing from
 * html response.
 *
 * @package    Libfajr
 * @subpackage Data
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 * @filesource
 */
namespace libfajr\data;

use DOMDocument;
use DOMElement;
use DOMXPath;
use libfajr\data\DataTableImpl;
use libfajr\trace\Trace;
use libfajr\base\Preconditions;
use libfajr\exceptions\ParseException;
use libfajr\util\StrUtil;

/**
 * Parses AIS2 html response and retrieve data for specific combo box.
 * 
 * @package    Libfajr
 * @subpackage Data
 * @author     Martin Sucha <anty.sk+fajr@gmail.com>
 *
 * TODO(ppershing): document methods
 * TODO(anty): refactor into separate widget classes
 */
class AIS2ComboBoxParser extends AIS2HTMLParser
{
  public function getOptionsFromHtml(Trace $trace, $aisResponseHtml, $elementId)
  {
    Preconditions::checkIsString($aisResponseHtml);
    Preconditions::checkIsString($elementId);
    $html = $this->fixProblematicTags($trace->addChild("Fixing html for better DOM parsing."),
                                                       $aisResponseHtml);
    $domWholeHtml = $this->createDomFromHtml($trace, $html);
    $element = $this->findEnclosingElement($trace, $domWholeHtml, $elementId);
    // ok, now we have restricted document
    $options = $this->getOptions($trace->addChild("Get options"), $element);
    return $options;
  }

  public function getOptions(Trace $trace, DOMElement $element)
  {
    $options = array();
    
    $list = $element->getElementsByTagName('option');
    foreach ($list as $node) {
      $value = $this->fixNbsp($node->textContent);
      $options[] = $value;
    }

    $trace->tlogVariable("options", $options);
    return $options;
  }

}
