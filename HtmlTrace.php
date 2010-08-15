<?php

namespace fajr;
use \fajr\libfajr\base\Trace;
use \Renderable;
use \Label;
use \Collapsible;
use \fajr\libfajr\base\Timer;

// TODO(ppershing): documentation

class HtmlTrace implements Trace, Renderable{
  private $header;
  private $children = array();
  private $constructTime = null;
  private $timer = null;

  public function __construct(Timer $timer, $header = "", $escape = true){
    $this->header = $escape ? hescape($header) : $header;
    $this->constructTime = microtime(true);
    $this->timer = $timer;
  }

  public function setHeader($header) {
    $this->header = $header;
  }

  public function tlog($text) {
    $this->children[] = new Label("<div class='trace'>" .
        $this->getStatusString() . hescape($text)."</div>");
  }

  public function tlogData($text) {
    $this->children[] = new Label("<div class='trace'>" .
        $this->getStatusString() . "<pre class='trace'>" .
        hescape($text)."</pre></div>");
  }

  public function tlogVariable($name, $variable) {
    $this->children[] = new Label("<div class='trace'>" .
        $this->getStatusString() . "\$".hescape($name).":= <pre class='trace'>" .
        hescape(preg_replace("@\\\\'@", "'", var_export($variable, true))) . "</pre></div>");
  }

  public function addChild($header = "") {
    $child = new HtmlTrace($this->timer, $this->getStatusString().hescape($header), false);
    $this->children[] = $child;
    return $child;
  }

  /**
   * Finds appropriate caller data associated with stacktrace.
   *
   * @param int $depth How much back in stack we should go.
   *                   Zero defaults to caller of this function.
   *
   * @returns array @see debug_backtrace for details
   */
  public static function getCallerData($depth) {
    $data = debug_backtrace();
    for ($i = 0; $i < $depth + 1; $i++) {
      array_shift($data);
    }
    $caller = array_shift($data);
    return $caller;
    return $caller['class']."::".$caller['function'].":";
  }

  private function getStatusString() {
    $caller = $this->getCallerData(2);
    $class = isset($caller['class']) ? $caller['class'] : "";
    $class = preg_replace("@.*\\\\@", "", $class);
    $function = $caller['function'];
    return sprintf("<span class='trace_s'> %+0.2fs %s::%s(): </span>",
                   $this->timer->getElapsedTime(), $class, $function);
  }

  public function getHtml() {
    $html = "";
    foreach ($this->children as $child) {
      assert($child instanceof Renderable);
      $html .= $child->getHtml()."\n";
    }

    $collapsible = new Collapsible(new Label("<div>" . $this->header . "</div>"),
                                   new Label($html), true);
    return $collapsible->getHtml();
  }
}
