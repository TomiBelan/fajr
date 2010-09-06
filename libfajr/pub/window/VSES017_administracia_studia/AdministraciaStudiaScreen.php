<?php

namespace fajr\libfajr\pub\window\VSES017_administracia_studia;

use fajr\libfajr\pub\window\LazyDialog;
use fajr\libfajr\pub\base\Trace;

interface AdministraciaStudiaScreen extends LazyDialog
{
  /**
   * @returns SimpleDataTable
   */
  public function getZoznamStudii(Trace $trace);

  /**
   * @returns SimpleDataTable
   */
  public function getZapisneListy(Trace $trace, $studiumIndex);

  public function getZapisnyListIdFromZapisnyListIndex(Trace $trace, $zapisnyListIndex);

  public function getStudiumIdFromZapisnyListIndex(Trace $trace, $zapisnyListIndex);
}
