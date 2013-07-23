<?php
/**
 * Curl connection options
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

use fajr\config\FajrConfig;
use fajr\config\FajrConfigOptions;
use fajr\config\FajrConfigLoader;
use fajr\util\FajrUtils;
use fajr\Statistics;
use libfajr\connection\CurlConnection;
use libfajr\connection\AIS2ErrorCheckingConnection;

/**
 * Curl connection provider
 *
 * @package    Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @author     Tomi Belan <tomi.belan@gmail.com>
 */
class ConnectionProvider
{
  public static function getOptions()
  {
    $config = FajrConfigLoader::getConfiguration();
    $options = array(
        CURLOPT_FORBID_REUSE => false, // Keepalive konekcie
        CURLOPT_FOLLOWLOCATION => true, // Redirecty pri prihlasovani/odhlasovani
        CURLOPT_VERBOSE => false,
        CURLOPT_USERAGENT => $config->get(FajrConfigOptions::USER_AGENT),
        CURLOPT_ENCODING => 'gzip',
        );
    // overridnutie adresara pre certifikaty
    if ($config->get(FajrConfigOptions::PATH_TO_SSL_CERTIFICATES)) {
      $options[CURLOPT_CAPATH] = $config->getDirectory(FajrConfigOptions::PATH_TO_SSL_CERTIFICATES);
    }
    return $options;
  }

  private static function provideCookieFile()
  {
    $config = FajrConfigLoader::getConfiguration();
    return FajrUtils::joinPath($config->getDirectory('Path.Temporary.Cookies'),
                               'cookie_'.session_id());

  }

  public static function getInstance()
  {
    $statistics = Statistics::getInstance();
    $curlOptions = self::getOptions();
    $connection = new CurlConnection($curlOptions, self::provideCookieFile());

    $statistics->setRawStatistics($connection->getStats());

    $connection = new AIS2ErrorCheckingConnection($connection);

    return $statistics->hookFinalConnection($connection);
  }
}

