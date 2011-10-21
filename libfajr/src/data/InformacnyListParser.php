<?php

/**
 * Contains implementation of parsing of information lists.
 *
 * @copyright  Copyright (c) 2010, 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Libfajr
 * @subpackage Data
 * @author     Jakub Marek <jakub.marek@gmail.com>
 * @filesource
 */

namespace libfajr\data;

use DOMDocument;
use DOMElement;
use DOMXPath;
use libfajr\data\DataTableImpl;
use libfajr\data\InformacnyListAttributeEnum;
use libfajr\trace\Trace;
use libfajr\base\Preconditions;
use libfajr\exceptions\ParseException;
use libfajr\data\ParserUtils;
use libfajr\util\StrUtil;

/**
 * Parses AIS2 information list and retrieves data.
 *
 * @package    Libfajr
 * @subpackage Data
 * @author     Jakub Marek <jakub.marek@gmail.com>
 *
 */
class InformacnyListParser
{
  const IGNORE_ATTRIBUTE = '_ignore';

  private $list;
  private $attr_def = array(
    'Názov vysokej školy, názov fakulty' => InformacnyListAttributeEnum::SKOLA_FAKULTA,
    'Kód' => InformacnyListAttributeEnum::KOD,
    'Názov' => InformacnyListAttributeEnum::NAZOV,
    'Študijný program' => InformacnyListAttributeEnum::STUDIJNY_PROGRAM,
    'Garantuje' => InformacnyListAttributeEnum::GARANTUJE,
    'Zabezpečuje' => InformacnyListAttributeEnum::ZABEZPECUJE,
    'Obdobie štúdia predmetu' => InformacnyListAttributeEnum::OBDOBIE_STUDIA_PREDMETU,
    'Forma výučby' => InformacnyListAttributeEnum::FORMA_VYUCBY,
    'Odporúčaný rozsah výučby ( v hodinách )' => self::IGNORE_ATTRIBUTE,
    'Týždenný' => InformacnyListAttributeEnum::VYUCBA_TYZDENNE,
    'Za obdobie štúdia' => InformacnyListAttributeEnum::VYUCBA_SPOLU,
    'Počet kreditov' => InformacnyListAttributeEnum::POCET_KREDITOV,
    'Podmieňujúce predmety' => InformacnyListAttributeEnum::PODMIENUJUCE_PREDMETY,
    'Obsahová prerekvizita' => InformacnyListAttributeEnum::OBSAHOVA_PREREKVIZITA,
    'Spôsob hodnotenia a skončenia štúdia predmetu' => InformacnyListAttributeEnum::SPOSOB_HODNOTENIA_A_SKONCENIA,
    'Priebežné hodnotenie (napr. test, samostatná práca...)' => InformacnyListAttributeEnum::PRIEBEZNE_HODNOTENIE,
    'Záverečné hodnotenie (napr. skúška, záverečná práca...)' => InformacnyListAttributeEnum::ZAVERECNE_HODNOTENIE,
    'Cieľ predmetu' => InformacnyListAttributeEnum::CIEL_PREDMETU,
    'Stručná osnova predmetu' => InformacnyListAttributeEnum::OSNOVA_PREDMETU,
    'Literatúra' => InformacnyListAttributeEnum::LITERATURA,
    'Jazyk, v ktorom sa predmet vyučuje' => InformacnyListAttributeEnum::VYUCOVACI_JAZYK,
    'Podpis garanta a dátum poslednej úpravy listu' => InformacnyListAttributeEnum::DATUM_POSLEDNEJ_UPRAVY
  );

  /**
   * Parses html document into object InformationListDataImpl which
   * handles all further manipulation with information list.
   *
   * @param string $html
   *
   * @return new instance of class InformacnyListDataImpl
   *
   */
  public function parse(Trace $trace, $html)
  {
    $trace->tlog("Called method parse(), creating table with parsed elements");
    $trace = $trace->addChild("Parsing informacny list");
    $this->parseHtml($trace, $html);
    $trace->tlogVariable("parsed list", $this->list);
    return new InformacnyListDataImpl($this->list);
  }

  /**
   * Sets value for attribute in associative array $list.
   *
   * @param string $attribute
   * @param array $values
   *
   */
  private function setAttribute(Trace $trace, $attribute, $values)
  {
    $new_values = array();
    foreach ($values as $value) {
      if ($value != '') $new_values[] = $value;
    }
    $values = $new_values;
    if (count($values) == 0) {
      $trace->tlog("Ignoring empty attribute '" . $attribute . "'");
      return;
    }
    $attribute = trim($attribute);
    if (StrUtil::endsWith($attribute, ':')) {
      $attribute = substr($attribute, 0, strlen($attribute) - 1);
    }
    $id = null;
    $name = $attribute;
    if (isset($this->attr_def[$attribute])) {
      $id = $this->attr_def[$attribute];
      $name = InformacnyListAttributeEnum::getUnicodeName($id);
    }
    if ($id == self::IGNORE_ATTRIBUTE) {
      $trace->tlog("Ignoring attribute '" . $attribute . "'");
      return;
    }
    $this->list[] = array(
      'id' => $id,
      'name' => $name,
      'rawLabel' => $attribute,
      'values' => $values);
  }

  /**
   * Fixes nbsp, deletes breaklines and trims string.
   *
   * @param string $string
   *
   * @return fixed attribute value
   *
   */
  static function fixAttributeValue($string)
  {
    Preconditions::checkIsString($string);
    $string = ParserUtils::fixNbsp($string);
    return trim(str_replace(array("\r", "\r\n", "\n"), '', $string));
  }

  /**
   * Parses <b> element, as after <b> elements occur data, that needs to
   * be extracted.
   *
   * @param DOMElement $final
   * @param array $pole
   *
   * @returns array with parsed data
   */
  private function spracujB(Trace $trace, DOMElement $final)
  {
    //do attribue_names pridam element, podla ktoreho parsujem
    $attributeName = ParserUtils::fixNbsp($final->nodeValue);
    $this->attribute_names[] = $attributeName;
    $child = $trace->addChild("Parsing attribute '$attributeName'");

    $sused = $final->nextSibling;
    if ($sused == NULL) {
      $child->tlog("No value to parse");
      return;
    }
    if ($sused->nextSibling == NULL) {
      // je textNode
      $child->tlog("Attribute is text node");
      $child->tlogVariable("Parsed attribute:", $sused->nodeValue);
      $values = array(self::fixAttributeValue($sused->nodeValue));
      $this->setAttribute($trace, $attributeName, $values);
      return;
    }
    $textSused = $sused->nextSibling;
    if ($textSused->nodeType != \XML_ELEMENT_NODE) {
      $child->tlog("Nothing to parse here");
      return;
    }
    if ($textSused->tagName == 'p') {
      $child->tlog("Parsing <p> tags");
      $values = array();
      for (; $textSused != null; $textSused = $textSused->nextSibling) {
        if ($textSused->nodeType != \XML_ELEMENT_NODE ||
            $textSused->tagName != 'p') {
          continue;
        }
        $values[] = self::fixAttributeValue($textSused->nodeValue);
      }
      $child->tlogVariable("Parsed attribute:", $values);
      $this->setAttribute($trace, $attributeName, $values);
    } else {
      $child->tlog("Parsing other tags");
      $child->tlogVariable("Parsed attribute:", $sused->nodeValue);
      $values = array(self::fixAttributeValue($sused->nodeValue));
      $this->setAttribute($trace, $attributeName, $values);
    }
  }

  /**
   * Replaces <br> tags in html document, so they wont complicate
   * further parsing.
   *
   * @param string $html html code to fix
   *
   * @returns string fixed html code ready for DOM parsing.
   */
  public static function fixBr(Trace $trace, $html)
  {
    Preconditions::checkIsString($html);
    $html = str_replace("<br>", " ", $html);
    return $html;
  }

  /**
   * Creates array with elements parsed from html containing information list.
   *
   * @param string $aisResponseHtml
   *
   * @returns complete array with parsed data from html
   * @throws ParseException on failure of creating DOM from html
   */
  public function parseHtml(Trace $trace, $aisResponseHtml)
  {
    Preconditions::checkIsString($aisResponseHtml);
    $html = self::fixBr($trace, $aisResponseHtml);
    $domWholeHtml = ParserUtils::createDomFromHtml($trace, $html);
    $domWholeHtml->preserveWhiteSpace = false;

    //ziskanie nazvu skoly, jedina vec co chcem ziskat co sa nenachadza v tabulke
    $b = $domWholeHtml->getElementsByTagName("b");
    $trace->tlog("Finding first element with tag name 'b'");
    $this->spracujB($trace, $b->item(0));

    $trNodes = $domWholeHtml->getElementsByTagName("tr");
    $trace->tlog("Getting all elements with tag name 'tr'");
    // prechadzam vsetkymi <tr> tagmi
    $firstTr = true;
    foreach ($trNodes as $tr) {
      // nechcem uplne prvy tag co je v tr, za <b> je iba nazov: informacny list
      if ($firstTr) {
        $firstTr = false;
        continue;
      }
      $trace->tlog("Getting all elements with tag name 'td'");
      $tdNodes = $tr->getElementsByTagName("td");
      // prechadzam <td> tagmi
      foreach ($tdNodes as $td) {
        if (!$td->hasChildNodes()) {
          continue;
        }
        $trace->tlog("Getting all child nodes of element 'td'");
        foreach ($td->childNodes as $final) {
          if ($final->nodeType != \XML_ELEMENT_NODE) {
            continue;
          }
          if ($final->tagName == 'b') {
            $trace->tlog("Parsing node with tag name 'b'");
            $this->spracujB($trace, $final);
          } else if ($final->tagName == 'div') {
            $trace->tlog("Parsing node with tag name 'div'");
            $this->parseDiv($trace, $final);
          }
        }
      }
    }
  }

  /**
   * Parses div tag. If it contains <b> element, it calls method spracujB,
   * which parses element <b>.
   *
   * @param domNode $final
   *
   * @returns array
   */
  public function parseDiv(Trace $trace, $final)
  {
    $final2 = $final->childNodes;
    foreach ($final2 as $key) {
      if ($key->nodeType != \XML_ELEMENT_NODE) {
        continue;
      }
      if ($key->tagName == 'b') {
        $trace->tlog("Parsing node with tag name 'b' inside 'div' tag");
        $this->spracujB($trace, $key);
      }
    }
  }

}
