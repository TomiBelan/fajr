<?php

namespace fajr;
use \fajr\libfajr\Trace;
use \Renderable;
use \Label;
use \Collapsible;

// TODO(ppershing): documentation

class HtmlTrace implements Trace, Renderable{
  private $header;
  private $children = array();

  public function __construct($header = ""){
    $this->header = $header;
  }

  public function setHeader($header) {
    $this->header = $header;
  }

  public function tlog($text) {
    $this->children[] = new Label("<div class='trace'>".hescape($text)."</div>");
  }

  public function tlogData($text) {
    $this->children[] = new Label("<pre class='trace'>".hescape($text)."</pre>");
  }

  public function addChild($header = "") {
    $child = new HtmlTrace($header);
    $this->children[] = $child;
    return $child;
  }

  public function getHtml() {
    $html = "";
    foreach ($this->children as $child) {
      assert($child instanceof Renderable);
      $html .= $child->getHtml()."\n";
    }

    $collapsible = new Collapsible($this->header, new Label($html), true);
    return $collapsible->getHtml();
  }
}
