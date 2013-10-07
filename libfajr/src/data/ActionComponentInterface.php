<?php
// Copyright (c) 2013 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

namespace libfajr\data;

use libfajr\trace\Trace;
use DOMDocument;

interface ActionComponentInterface {
  /**
   * Return the xml of action component
   *
   * @returns DOMDocument XML object
   */
  public function getActionXML($dlgName);
}
?>
