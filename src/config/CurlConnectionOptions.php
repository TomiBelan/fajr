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

namespace fajr\config;

use fajr\config\FajrConfig;
use fajr\config\FajrConfigOptions;
use fajr\config\FajrConfigLoader;

/**
 * Curl connection options
 *
 * @package    Fajr
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @author     Tomi Belan <tomi.belan@gmail.com>
 */
class CurlConnectionOptions
{
  public static function getOptions()
  {
    $config = FajrConfigLoader::getConfiguration();
    $options = array(
        CURLOPT_FORBID_REUSE => false, // Keepalive konekcie
        CURLOPT_FOLLOWLOCATION => true, // Redirecty pri prihlasovani/odhlasovani
        CURLOPT_VERBOSE => false,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => true,
        CURLOPT_USERAGENT => $config->get(FajrConfigOptions::USER_AGENT),
        CURLOPT_ENCODING => 'gzip',
        );
    // overridnutie adresara pre certifikaty
    if ($config->get(FajrConfigOptions::PATH_TO_SSL_CERTIFICATES)) {
      $options[CURLOPT_CAPATH] = $config->getDirectory(FajrConfigOptions::PATH_TO_SSL_CERTIFICATES);
    }
    return $options;
  }
}

