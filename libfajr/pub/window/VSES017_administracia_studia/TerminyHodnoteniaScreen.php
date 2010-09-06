<?php

namespace fajr\libfajr\pub\window\VSES017_administracia_studia;

use fajr\libfajr\pub\window\LazyDialog;
use fajr\libfajr\pub\base\Trace;

interface TerminyHodnoteniaScreen extends LazyDialog
{
  /**
   * @returns SimpleDataTable
   */
  public function getPredmetyZapisnehoListu(Trace $trace);

  /**
   * @returns SimpleDataTable
   */
  public function getTerminyHodnotenia(Trace $trace);

  /**
   * @returns ZoznamTerminovDialog
   */
  public function getZoznamTerminovDialog(Trace $trace, $predmetIndex);

  /**
   * @returns ZoznamPrihlasenychDialog
   */
  public function getZoznamPrihlasenychDialog(Trace $trace, $terminIndex);

  public function odhlasZTerminu(Trace $trace, $terminIndex);
}
