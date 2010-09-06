<?php

namespace fajr\libfajr\pub\window\VSES017_administracia_studia;

use fajr\libfajr\pub\window\LazyDialog;
use fajr\libfajr\pub\data_manipulation\SimpleDataTable;
use fajr\libfajr\pub\base\Trace;

interface ZoznamPrihlasenychDialog extends LazyDialog
{

  /**
   * @returns SimpleDataTable
   */
  public function getZoznamPrihlasenych(Trace $trace);
}
