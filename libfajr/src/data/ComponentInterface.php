<?php
// Copyright (c) 2013 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

namespace libfajr\data;

interface ComponentInterface {
  /**
   * Update component if there is some change or initialize component
   *
   * @param Trace $trace for creating logs, tracking activity
   * @param DOMDocument $aisResponse AIS2 html parsed reply
   */
  public function updateComponentFromResponse(Trace $trace, DOMDocument $aisResponse);

  /**
   * Return the changes in component
   *
   * @returns DOMDocument XML object
   */
  public function getStateChanges();
}
?>
