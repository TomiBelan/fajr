<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Libfajr
 * @subpackage Regression__Fake_data
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @filesource
 */

namespace libfajr\regression\fake_data;

use PHPUnit_Framework_TestCase;
use RecursiveDirectoryIterator;
use RegexIterator;
use RecursiveIteratorIterator;
use libfajr\storage\FileStorage;
use libfajr\util\StrUtil;

/**
 * @ignore
 */
require_once 'test_include.php';
/**
 * @ignore
 */
class FakeDataTest extends PHPUnit_Framework_TestCase
{

  /**
   * Ensure that all fake data we use are valid FileStorage
   * data files. This should ensure, that fake backend and
   * other services depending on fake data will not encounter
   * broken data.
   */
  public function testAllData()
  {
    $dir = FakeData::getDirectory();
    $storage = new FileStorage(array("root_path" => $dir . '/'));
    
    $it = new RecursiveDirectoryIterator($dir);
    $nonrecursiveIt = new RecursiveIteratorIterator($it);
    $regexIt = new RegexIterator($nonrecursiveIt, '@\.dat$@');
    foreach ($regexIt as $file) {
      $this->assertTrue($file->isFile());
      $key = $file->getPathname();
      assert(StrUtil::startsWith($key, $dir));
      // remove $dir from beginning of $key
      $key = substr($key, strlen($dir));
      $key = preg_replace("@\.dat$@", "", $key);

      $data = $storage->read($key);
      $this->assertTrue(null !== $data);
    }
  }


}
