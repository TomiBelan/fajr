<?php
/**
 * Provider for session storage
 *
 * @copyright  Copyright (c) 2011 The Fajr authors (see AUTHORS).
 *             Use of this source code is governed by a MIT license that can be
 *             found in the LICENSE file in the project root directory.
 *
 * @package    Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @author     Tomi Belan <tomi.belan@gmail.com>
 * @filesource
 */

namespace fajr;

use fajr\config\FajrConfigLoader;
use fajr\config\FajrConfigOptions;
use sfSessionStorage;

/**
 * Provider for sfSessionStorage.
 *
 * We can't modify sfSessionStorage to add getInstance, so we add it here.
 *
 * @package Fajr
 * @author  Peter Perešíni <ppershing+fajr@gmail.com>
 * @author  Tomi Belan <tomi.belan@gmail.com>
 */
class SessionStorageProvider
{
  /** @var sfSessionStorage $instance */
  private static $instance;

  /* TODO document */
  public static function getInstance() {
    if (!isset(self::$instance)) {
      $config = FajrConfigLoader::getConfiguration();

      $lifeTimeSec = 36000;

      $options = array(
        'session_cookie_lifetime' => $lifeTimeSec,
        'session_cookie_path' => '/',
        'session_cookie_domain' => '.' . $_SERVER['HTTP_HOST'],
        'session_cookie_secure' => $config->get(FajrConfigOptions::REQUIRE_SSL),
        'session_cookie_httponly' => true,
        'session_name' => $config->get(FajrConfigOptions::INSTANCE_NAME) . '_session_id',
      );

      // this will render fajr usable when running on localhost
      if ($_SERVER['HTTP_HOST'] == 'localhost') {
        unset($options['session_cookie_domain']);
      }

      // cache expire, server
      ini_set('session.gc_maxlifetime', $lifeTimeSec);
      ini_set('session.cookie_lifetime', $lifeTimeSec);

      // custom cache expire is possible only for custom session directory
      session_save_path($config->getDirectory(FajrConfigOptions::PATH_TO_SESSIONS));

      self::$instance = new sfSessionStorage($options);
    }
    return self::$instance;
  }
}


