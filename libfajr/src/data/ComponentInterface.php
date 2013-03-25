<?php
// Copyright (c) 2013 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

namespace libfajr\data;

interface ComponentInterface {
  /**
   * Initialize a component
   *
   * @param DOMDocument $aisResponseHtml AIS2 html parsed reply
   */
  public function initComponentFromResponse($aisResponseHtml);

  /**
   * Update component if there is some change
   *
   * @param DOMDocument $aisResponseHtml AIS2 html parsed reply
   */
  public function updateComponentFromResponse($aisResponseHtml);

  /**
   * Return the changes in component
   *
   * @return return different type of data
   */
  public function getStateChanges();
}
?>
