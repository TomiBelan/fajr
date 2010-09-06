<?
namespace fajr\libfajr\pub\window;

use fajr\libfajr\pub\base\Trace;

interface LazyDialog {
  /**
   * Opens the ais screen/dialog. Note that this will be called
   * automatically on first object request.
   */
  public function openIfNotAlready(Trace $trace);

  /**
   * Close screen/dialog. This will be automatically called at the destructor,
   * but you may find it handy to terminate dialog earlier. Note hovewer, that
   * you must close child dialog first!.
   */
  public function closeIfNeeded(Trace $trace);
}
