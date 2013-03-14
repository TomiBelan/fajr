<?php

/**
 * @copyright  Copyright (c) 2010-2012 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 *
 * @package    Libfajr
 * @author     Martin Králik <majak47@gmail.com>
 * @author     Jakub Marek <jakub.marek@gmail.com>
 * @filesource
 */

namespace libfajr\data;

use libfajr\data\InformacnyListData;
use libfajr\exceptions\NotImplementedException;

/**
 * Class representing infromation sheet for course.
 *
 * @package    Libfajr
 * @author     Martin Králik <majak47@gmail.com>
 * @author     Jakub Marek <jakub.marek@gmail.com>
 */
class InformacnyListDataImpl implements InformacnyListData
{

  private $list;

  public function __construct($list = null)
  {
    $this->list = $list;
  }

  /**
   * {@inheritdoc}
   */
  public function getAttribute($id)
  {
    if ($this->list === null) {
      return false;
    }
    foreach ($this->list as $attribute) {
      if ($attribute['id'] == $id) {
        return $attribute;
      }
    }
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function hasAttribute($id)
  {
    return $this->getAttribute($id) !== false;
  }

  /**
   * {@inheritdoc}
   */
  public function getAllAttributes()
  {
    return $this->list;
  }

  /**
   * {@inheritdoc}
   */
  public function getListOfAttributes()
  {
    $ids = array();
    foreach ($this->list as $attribute) {
      if ($attribute['id'] !== null) {
        $ids[] = $attribute['id'];
      }
    }
    return $ids;
  }

}
