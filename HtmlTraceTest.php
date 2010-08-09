<?php
/**
 * This file contains tests for Validator class
 *
 * @package Fajr
 * @subpackage Tests
 * @author Peter Peresini <ppershing+fajr@gmail.com>
 */

/**
 * @ignore
 */
require_once 'test_include.php';

use \fajr\HtmlTrace;
/**
 * @ignore
 */
class HtmlTraceTest extends PHPUnit_Framework_TestCase
{

  public function testRecursiveOutput()
  {
    $root = new HtmlTrace("root_h");
    $other = new HtmlTrace("other");
    $root->tlog("root_text");
    $c1 = $root->addChild("c1_h");
    $c2 = $root->addChild("c2_h");
    $cc = $c1->addChild("cc_h");
    $cc->tlog("cc_text");

    $root_html = $root->getHtml();
    $c1_html = $c1->getHtml();
    // all texts and headers should appear in the output
    $this->assertRegExp("@root_h@", $root_html);
    $this->assertRegExp("@root_text@", $root_html);
    $this->assertRegExp("@c1_h@", $root_html);
    $this->assertRegExp("@c2_h@", $root_html);
    $this->assertRegExp("@cc_h@", $root_html);
    $this->assertRegExp("@cc_text@", $root_html);

    $this->assertNotRegExp("@other@", $root_html);
    // root nodes shoudn't be shown in child
    $this->assertNotRegExp("@root_h@", $c1_html);
    $this->assertNotRegExp("@root_text@", $c1_html);
    $this->assertRegExp("@c1_h@", $c1_html);
  }

  public function testCallerData() {
    $data = HtmlTrace::getCallerData(0);
    $this->assertEquals(__CLASS__, $data['class']);
    $this->assertEquals(__FUNCTION__, $data['function']);
  }

  public function testVariableOutput() {
    $trace = new HtmlTrace("root");
    $trace->tlogVariable("var1", false);
    $trace->tlogVariable("var2", 4.7);
    $trace->tlogVariable("var3", array('str'));
    $trace->tlogVariable("var4", null);
    $html = $trace->getHtml();
    $this->assertRegExp("@false@", $html);
    $this->assertRegExp("@4.7@", $html);
    $this->assertRegExp("@array.*str@s", $html);
    $this->assertRegExp("@null@i", $html);

    $trace = new HtmlTrace("root");
    $trace->tlogVariable("var", "a'b");
    $html = $trace->getHtml();
    $this->assertRegExp("@a&#039;b@", $html);
  }

  public function testEscaping() {
    $trace = new HtmlTrace();
    $trace->tlog("<&x");
    $trace->tlogData("&x>");
    $trace->tlogVariable("a'a", "a\"a");
    $html = $trace->getHtml();
    $this->assertNotRegExp("@&x@", $html);
    $this->assertNotRegExp("@&x@", $html);
    $this->assertNotRegExp("@a'a@", $html);
    $this->assertNotRegExp("@a\"a@", $html);
    $this->assertRegExp("@&lt;@", $html);
    $this->assertRegExp("@&gt;@", $html);
    $this->assertRegExp("@&amp;@", $html);
    $this->assertRegExp("@a&#039;a@", $html);
    $this->assertRegExp("@a&quot;a@", $html);

  }

}

?>
