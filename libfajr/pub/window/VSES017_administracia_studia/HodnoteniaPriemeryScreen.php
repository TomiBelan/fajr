<?php

namespace fajr\libfajr\pub\window\VSES017_administracia_studia;

use fajr\libfajr\pub\window\LazyDialog;
use fajr\libfajr\pub\base\Trace;

interface HodnoteniaPriemeryScreen extends LazyDialog
{
  /**
   * @returns SimpleDataTable tabulka hodnoteni
   */
  public function getHodnotenia(Trace $trace);

  /**
   * @returns SimpleDataTable tabulka priemerov
   */
  public function getPriemery(Trace $trace);

}
