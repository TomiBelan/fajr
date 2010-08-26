<?php
namespace fajr\libfajr\window;

use fajr\libfajr\window\DialogData;
use fajr\libfajr\pub\base\Trace;
interface DialogParent {
  /**
   * @returns DialogRequestExecutor
   */
  public function openDialogAndGetExecutor(Trace $trace, $uid, DialogData $data);
  public function closeDialog($uid);
}
