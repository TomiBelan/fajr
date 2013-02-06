<?php
// Copyright (c) 2010-2011 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Base class for HTML parsing classes.
 *
 * @package    Libfajr
 * @subpackage Data
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
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
 * Parses AIS2 html response and retrieve data for specific table.
 *
 * @package    Libfajr
 * @subpackage Data
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 *
 * TODO(ppershing): document methods
 * TODO(anty): Refactor into AIS2Dom class which will be used as instance instead
 */
class AIS2HTMLParser
{
  /**
   * Fix problems PHP DOM parser have with ais-generated html.
   *
   * The major problem is <script> tag inside which are the table data.
   * Here, we fix these <script> tags replacing them as <div> tags.
   *
   * @param Trace $trace
   * @param string $html html code to fix
   *
   * @returns string fixed html code ready for DOM parsing.
   */
  public function fixProblematicTags(Trace $trace, $html)
  {
    Preconditions::checkIsString($html);
    $html = str_replace("<!--", "", $html);
    $html = str_replace("-->", "", $html);
    $html = str_replace("script", "div", $html);
    $trace->tlogVariable("Fixed html", $html);
    return $html;
  }

  /**
   * Fix problem with PHP DOM "id" attribute parsing.
   *
   * Sometimes "id" attribute is not recognized as id attribute during parsing.
   * This method will fix the problem.
   *
   * @param Trace $trace
   * @param DOMDocument $dom DOM document to be fixed
   *
   * @returns void
   */
  public function fixIdAttributes(Trace $trace, DOMDocument $dom)
  {
    $xpath = new DOMXPath($dom);
    $nodes = $xpath->query("//*[@id]");
    foreach ($nodes as $node) {
      // Note: do not erase next line. @see
      // http://www.navioo.com/php/docs/function.dom-domelement-setidattribute.php
      // for explanation!
      $node->setIdAttribute('id', false);
      $node->setIdAttribute('id', true);
    }
  }

  /**
   * Parses ais html into DOM.
   *
   * @param Trace $trace
   * @param string $html
   *
   * @returns DOMDocument parsed DOM
   * @throws ParseException on failure
   */
  public function createDomFromHtml(Trace $trace, $html)
  {
    Preconditions::checkIsString($html);
    $dom = new DOMDocument();
    $trace->tlog("Loading html to DOM");
    $loaded = @$dom->loadHTML($html);
    if (!$loaded) {
      throw new ParseException("Problem parsing html to DOM.");
    }
    $trace->tlog('Fixing id attributes in the DOM');
    $this->fixIdAttributes($trace, $dom);
    return $dom;
  }

  /**
   * Find an element with specified id in $dom.
   *
   * @param Trace $trace
   * @param DOMDocument $dom document to search
   * @param string $elementId id of element to find
   *
   * @returns DOMElement element
   * @throws ParseException if not found
   */
  public function findEnclosingElement(Trace $trace, DOMDocument $dom, $elementId)
  {
    Preconditions::checkIsString($elementId);
    $trace->tlog("Finding element with id '$elementId'");
    $element = $dom->getElementById($elementId);
    if ($element === null) {
      throw new ParseException("Problem parsing ais2 response: Element not found");
    }
    $trace->tlog("Element found");
    $child = $trace->addChild("Element xml content (pretty formatted)");
    $child->tlogVariable("content", $this->prettyFormatXml($element));
    return $element;
  }

  /**
   * Returns xml representation of DOM rooted at $element.
   * Returned xml will be nicely formatted (nested indentation).
   *
   * @param DOMElement $element node with subnodes which souble be formatted.
   *
   * @returns string formatted xml
   */
  public function prettyFormatXml(DOMElement $element)
  {
    $outXML = '<?xml version="1.0" encoding="UTF-8"?>' .
        $element->ownerDocument->saveXML($element);
    $tmp = new DOMDocument();
    $tmp->encoding='UTF-8';
    $tmp->preserveWhiteSpace = false;
    $tmp->formatOutput = true;
    $tmp->loadXML($outXML);
    return $tmp->saveXML(); 
  }

  /**
   * Fix non-breakable spaces which were converted to special character furing parsing.
   *
   * @param string $str string to fix
   * 
   * @returns string fixed string
   */
  public function fixNbsp($str)
  {
    Preconditions::checkIsString($str);
    // special fix for &nbsp;
    // xml decoder decodes &nbsp; into special utf-8 character
    // TODO(ppershing): nehodili by sa tie &nbsp; niekedy dalej v aplikacii niekedy?
    $nbsp = chr(0xC2).chr(0xA0);
    return str_replace($nbsp, ' ', $str);
  }

}
