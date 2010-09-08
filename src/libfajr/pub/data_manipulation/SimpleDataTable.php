<?php
// Copyright (c) 2010 The Fajr authors.
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

// TODO(??): missing author

namespace fajr\libfajr\pub\data_manipulation;

interface SimpleDataTable {
  public function getData();
  public function getTableDefinition();
}
