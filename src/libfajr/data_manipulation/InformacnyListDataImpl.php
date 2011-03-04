<?php
// Copyright (c) 2011 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * @package    Fajr
 * @subpackage Libfajr
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace fajr\libfajr\data_manipulation;

use fajr\libfajr\pub\data_manipulation\InformacnyListData;
use fajr\libfajr\pub\exceptions\NotImplementedException;

/**
 * Class representing infromation sheet for course.
 *
 * @package    Fajr
 * @subpackage Libfajr
 * @author     Martin Králik <majak47@gmail.com>
 */
class InformacnyListDataImpl implements InformacnyListData
{
  /**
   * {@inheritdoc}
   */
  public function getAttribute($attribute) {throw new NotImplementedException();}

  /**
   * {@inheritdoc}
   */
  public function hasAttribute($attribute) {throw new NotImplementedException();}

  /**
   * {@inheritdoc}
   */
  public function getAllAttributes() {throw new NotImplementedException();}

  /**
   * {@inheritdoc}
   */
  public function getListOfAttributes() {throw new NotImplementedException();}
}
