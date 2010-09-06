<?php

namespace fajr\libfajr\pub\window\VSES017_administracia_studia;

use fajr\libfajr\pub\window\LazyDialog;
use fajr\libfajr\pub\data_manipulation\SimpleDataTable;
use fajr\libfajr\pub\base\Trace;

interface TerminyDialog extends LazyDialog
{

  /**
   * @returns SimpleDataTable
   */
  public function getZoznamTerminov(Trace $trace);

  /**
   * @returns ZoznamPrihlasenychDialog
   */
  public function getZoznamPrihlasenychDialog(Trace $trace, $terminIndex);

  public function prihlasNaTermin(Trace $trace, $terminIndex);
}
