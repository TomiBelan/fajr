<?php


namespace fajr\libfajr\window;
use fajr\libfajr\pub\base\Trace;
use Exception;
use fajr\libfajr\base\DisableEvilCallsObject;
use fajr\libfajr\pub\connection\SimpleConnection;

interface ScreenRequestExecutor
{
  public function spawnChild(DialogData $data, $parentFormName);

  /**
   * Nadviaže spojenie, spustí danú "aplikáciu" v AISe
   * a natiahne prvotné dáta do atribútu $data.
   */
  public function requestOpen(Trace $trace, ScreenData $data);

  public function requestContent(Trace $trace);

  /**
   * Zatvorí danú "aplikáciu" v AISe
   */
  public function requestClose(Trace $trace);

  public function getRequestUrl();

  public function doRequest(Trace $trace, $options);

  public function spawnDialogExecutor(DialogData $data);
}
?>
