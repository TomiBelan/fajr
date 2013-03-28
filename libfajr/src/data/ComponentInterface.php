<?php
// Copyright (c) 2013 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

namespace libfajr\data;

interface ComponentInterface {
  /**
   * Initialize a component
   *
   * @param DOMDocument $aisResponse AIS2 html parsed reply
   */
  public function initComponentFromResponse(DOMDocument $aisResponse);

  /**
   * Update component if there is some change
   *
   * @param DOMDocument $aisResponse AIS2 html parsed reply
   */
  public function updateComponentFromResponse(DOMDocument $aisResponse);

  /**
   * Return the changes in component
   *
   * @returns DOMDocument returns what was changed in DOMDocument format.
   */
  public function getStateChanges();
}
?>
