<?php
// Copyright (c) 2010-2011 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * Contains implementation of ais2 table parsing from
 * html response.
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
 */
class AIS2TableParser extends AIS2HTMLParser
{
  public function createTableFromHtml(Trace $trace, $aisResponseHtml, $dataViewName)
  {
    Preconditions::checkIsString($aisResponseHtml);
    Preconditions::checkIsString($dataViewName);
    $html = $this->fixProblematicTags($trace->addChild("Fixing html for better DOM parsing."),
                                                       $aisResponseHtml);
    $domWholeHtml = $this->createDomFromHtml($trace, $html);
    $element = $this->findEnclosingElement($trace, $domWholeHtml, $dataViewName);
    $dom = new DOMDocument();
    $dom->appendChild($dom->importNode($element, true));
    // ok, now we have restricted document
    $headers = $this->getTableDefinition($trace->addChild("Get table definition"), $dom);
    $data = $this->getTableData($trace->addChild("Get table data"), $dom);
    return new DataTableImpl($headers, $data);
  }

  public function getCellContent(DOMElement $element)
  {
    // special fix for checkboxes
    if ($element->hasAttribute('datatype')) {
      switch ($element->getAttribute('datatype')) {
        case 'image':
          assert($element->getAttribute('datatype') == 'image');
          foreach ($element->getElementsByTagName('img') as $img) {
            assert($img->hasAttribute('src'));
            $src = $img->getAttribute('src');
            if (preg_match('@checked\.(?:gif|png)@', $src)) return "TRUE";
            if (preg_match('@removeFlag\.(?:gif|png)@', $src)) return "FALSE";
            throw new \Exception("Neznámy názov obrázku pre logickú hodnotu: " . $src);
          }
          assert(false);
        case 'boolean':
          foreach ($element->getElementsByTagName('div') as $div) {
            assert($div->hasAttribute('class'));
            assert($div->getAttribute('class') === 'booleanCellChecked');
            return 'TRUE';
          }
          foreach ($element->getElementsByTagName('img') as $img) {
            assert($img->hasAttribute('class'));
            assert($img->getAttribute('class') === 'checkedImg');
            return "TRUE";
          }
          assert($this->fixNbsp($element->textContent) === ' ');
          return 'FALSE';
        default:
        assert(false);
      }
    }
    $value = $this->fixNbsp($element->textContent);

    if ($value == ' ') {
      return '';
    }
    assert($value != ''); // probably there is some inner element which we don't know about
    return $value;
  }

  public function getTableData(Trace $trace, DOMDocument $dom)
  {
    $data = array();
    $trace->tlog("finding tbody element");
    $element = $dom->getElementById('dataTabBody0');
    if ($element == null) {
      throw new ParseException("Can't find table data");
    }

    foreach ($element->childNodes as $aisRow) {
      assert($aisRow->tagName == "tr");
      assert($aisRow->hasAttribute("id"));
      assert($aisRow->hasChildNodes());
      // TODO: asserty prerobit na exceptiony
      $row = array();
      $rowId = $aisRow->getAttribute("id");
      $index = StrUtil::match('@^row_([0-9]+)$@', $rowId);
      if ($index === false) {
        throw new ParseException("Unexpected row id format");
      }
      foreach ($aisRow->childNodes as $ais_td) {
        assert($ais_td->tagName == "td");
        $row[] = $this->getCellContent($ais_td);
      }
      $data[$index] = $row;
    }
    $trace->tlogVariable("data", $data);
    return $data;
  }

  public function getTableDefinition(Trace $trace, DOMDocument $dom)
  {
    $child = $trace->addChild("finding table definition element");
    $child->tlogVariable("dom", $dom->saveXML());
    $element = $dom->getElementById('dataTabColGroup');
    if ($element == null) {
      throw new ParseException("Can't find table headers");
    }
    $list = $element->getElementsByTagName('col');
    $columns = array();
    foreach ($list as $node) {
      assert($node->hasAttribute('shortname'));
      $columns[] = $node->getAttribute('shortname');
    }
    $child = $trace->addChild("Parsed columns");
    $child->tlogVariable("Parsed columns:", $columns);
    return $columns;
  }

}
