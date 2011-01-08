/**
 * Contains tests of latinise.js
 *
 * @copyright  Copyright (c) 2010 The Fajr authors.
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @subpackage Web__Javascripts
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 */

LatiniseTest = TestCase("LatiniseTest");

LatiniseTest.prototype.testLatinise = function() {
  assertSame("aecl", "äéěľ");
}
