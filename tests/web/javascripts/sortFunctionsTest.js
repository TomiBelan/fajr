/**
 * Contains tests of sortFunctions.js
 *
 * @copyright  Copyright (c) 2010 The Fajr authors.
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Web__Javascripts
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */

SortFunctionsTest = TestCase("SortFunctionsTest");


/**
 * Test basic padding.
 */
SortFunctionsTest.prototype.testPad0Basic = function() {
  assertEquals("12", pad0("12", 1));
  assertEquals("12", pad0("12", 2));
  assertEquals("012", pad0("12", 3));
  assertEquals("0012", pad0("12", 4));
}

/**
 * Test some tricky cases.
 */
SortFunctionsTest.prototype.testPad0Tricky = function() {
  assertEquals("0000", pad0("", 4));
  assertEquals("00 0", pad0(" 0", 4));
  assertEquals("00  ", pad0("  ", 4));
  assertEquals("  ", pad0("  ", -4));
}
