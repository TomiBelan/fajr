<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Fajr
 * @subpackage Libfajr__Window
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\window;
use fajr\libfajr\pub\base\Trace;
use Exception;
use fajr\libfajr\base\DisableEvilCallsObject;
use fajr\libfajr\pub\connection\SimpleConnection;

class FakeRequestExecutor extends DisableEvilCallsObject
{
  private $rootPath;
  private sfStorage $session;

  /**
   * Konštruktor.
   *
   */
  public function __construct($rootPath, sfStorage $session)
  {
    Preconditions::checkIsString($rootPath);
    $this->$rootPath = $rootPath;
    $this->session = $session;
  }

  public function spawnChild(DialogData $data, $parentFormName)
  {
    return new AIS2DialogRequestExecutor($this->requestBuilder, $this->connection, $data, $this->parentAppId, $parentFormName);
  }

  public function readTable($file)
  {
    $fullPath = $rootPath.'/'.$file
    if (!(file_exists($fullPath))) {
      throw new Exception("Cannot find file.");
    }
    return include "$fullPath";
  }

}
?>
