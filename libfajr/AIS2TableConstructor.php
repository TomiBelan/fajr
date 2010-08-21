<?php
use fajr\libfajr\base\Trace;
use \Exception;
use \DOMDocument;

class AIS2TableConstructor {
  public function fixProblematicTags(Trace $trace, $html) {
    $html = str_replace("<!--", "", $html);
    $html = str_replace("-->", "", $html);
    $html = str_replace("script", "div", $html);
    $trace->tlogVariable("Fixed html", $html);
    return $html;
  }

  public function createDomFromHtml(Trace $trace, $html) {
    $dom = new DOMDocument();
    $trace->tlog("Loading html to DOM");
    $loaded = @$dom->loadHTML($html);
    if (!$loaded) {
      throw new Exception("Problem parsing ais2 response html");
    }
    return $dom;
  }

  public function findEnclosingElement(Trace $trace, DOMDocument $dom, $elementId) {
    $trace->tlog("Finding emenent with id '$elementId'");
    $element = $dom->getElementById($elementId);
    if ($element === null) {
      throw new Exception("Problem parsing ais2 response: Element not found");
    }
    $trace->tlog("Element found");
    $child = $trace->addChild("Element xml content (pretty formatted)");
    $child->tlogVariable("content", $this->prettyFormatXml($dom, $element));
    return $element;
  }

  public function prettyFormatXml($dom, $element) {
    $outXML = '<?xml version="1.0" encoding="UTF-8"?>'.$dom->saveXML($element);
    $tmp = new DOMDocument();
    $tmp->encoding='UTF-8';
    $tmp->preserveWhiteSpace = false;
    $tmp->formatOutput = true;
    $tmp->loadXML($outXML);
    return $tmp->saveXML(); 
  }

  public function createTableFromHtml(Trace $trace, $aisResponseHtml, $dataViewName) {
    $html = $this->fixProblematicTags($trace->addChild("Fixing html for better DOM parsing."),
        $aisResponseHtml);
    $domWholeHtml = $this->createDomFromHtml($trace, $html);
    $element = $this->findEnclosingElement($trace, $domWholeHtml, $dataViewName);
    $dom = new DOMDocument();
    $dom->appendChild($dom->importNode($element, true));
    // ok, now we have restricted document
    $headers = $this->getTableDefinition($trace->addChild("Get table definition"), $dom);
    $data = $this->getTableData($trace->addChild("Get table data"), $dom);
    return new AIS2Table($headers, $data);
  }

  public function getCellContent(DOMElement $element) {
    // special fix for checkboxes
    if ($element->hasAttribute('datatype')) {
      assert($element->getAttribute('datatype')=='boolean');
      foreach ($element->getElementsByTagName('img') as $img) {
        assert($img->hasAttribute('class'));
        assert($img->getAttribute('class')=='checkedImg');
        return "TRUE";
      }
      assert($element->textContent == "\302\240");
      return "FALSE";
    }
    $value = $element->textContent;

    // special fix for &nbsp;
    if ($value == ' ') return '';
    assert($value != ''); // probably the is some inner element which we don't know about
    return $value;
  }

  public function getTableData(Trace $trace, $dom) {
    $data = array();
    $trace->tlog("finding tbody element");
    $element = $dom->getElementById('dataTabBody0');
    if ($element == null) {
      throw new Exception("Can't find table data");
    }

    foreach ($element->childNodes as $ais_row) {
      assert($ais_row->tagName == "tr");
      assert($ais_row->hasAttribute("rid"));
      assert($ais_row->hasChildNodes());
      $row = array();
      $index = $ais_row->getAttribute("rid");
      foreach ($ais_row->childNodes as $ais_td) {
        assert($ais_td->tagName == "td");
        $row[] = $this->getCellContent($ais_td);
      }
      $data[$index] = $row;
    }
    $trace->tlogVariable("data", $data);
    return $data;
  }

  public function getTableDefinition(Trace $trace, DOMDocument $dom) {
    $trace->tlog("finding table definition element");
    $trace->tlogVariable("", $dom->saveXML());
    $element = $dom->getElementById('dataTabColGroup');
    if ($element == null) {
      throw new Exception("Can't find table headers");
    }
    $list = $element->getElementsByTagName('col');
    $columns = array();
    foreach ($list as $node) {
      assert($node->hasAttribute('shortname'));
      $columns[] = $node->getAttribute('shortname');
    }
    $trace->tlogVariable("Parsed columns:", $columns);
    return $columns;
  }

}
