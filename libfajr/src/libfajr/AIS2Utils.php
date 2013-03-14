<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Libfajr
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace libfajr;
use libfajr\trace\Trace;
use libfajr\connection\SimpleConnection;
use libfajr\util\StrUtil;
use Exception;

/**
 * Trieda združujúca rôzne základné veci pre prácu s AISom
 *
 * @author Martin Králik <majak47@gmail.com>
 */
class AIS2Utils
{
  /**
   * predpokladame AIS format datumu a casu, t.j.
   * vo formate "11.01.2010 08:30"
   */
  public static function parseAISDateTime($str)
  {
    // Pozn. strptime() nefunguje na windowse, preto pouzijeme regex
    $pattern =
      '@(?P<tm_mday>[0-3][0-9])\.(?P<tm_mon>[0-1][0-9])\.(?P<tm_year>20[0-9][0-9])'.
      ' (?P<tm_hour>[0-2][0-9]):(?P<tm_min>[0-5][0-9]*)@';
    $datum = StrUtil::matchAll($pattern, $str);
    if (!$datum) {
      throw new Exception("Chyba pri parsovaní dátumu a času: " . $str);
    }
    
    return mktime($datum["tm_hour"],$datum["tm_min"],0,
        $datum["tm_mon"],$datum["tm_mday"],$datum["tm_year"]);
  }
  
  /**
   * predpokladame AIS format datumu, t.j.
   * vo formate "11.01.2010"
   */
  public static function parseAISDate($str)
  {
    // Pozn. strptime() nefunguje na windowse, preto pouzijeme regex
    $pattern =
      '@(?P<tm_mday>[0-3][0-9])\.(?P<tm_mon>[0-1][0-9])\.(?P<tm_year>20[0-9][0-9])@';
    $datum = StrUtil::matchAll($pattern, $str);
    if (!$datum) {
      throw new Exception("Chyba pri parsovaní dátumu");
    }
    
    return mktime(0, 0, 0,
        $datum["tm_mon"], $datum["tm_mday"], $datum["tm_year"]);
  }
  
  /**
   *
   * @param str predpokladame range v 2 moznych standardnych ais formatoch
   *    - "do [datum a cas]"
   *    - "[datum a cas] do [datum a cas]"
   * @see parseAISDateTime
   * @returns array('od'=>timestamp, 'do'=>timestamp)
   */
  public static function parseAISDateTimeRange($str)
  {
    $pattern = '@(?P<od>[0-9:. ]*)do (?P<do>[0-9:. ]*)@';
    $data = StrUtil::matchAll($pattern, $str);
    $result = array();
    if ($data['od'] == '') {
      $result['od'] = null;
    } else {
      $result['od'] = self::parseAISDateTime($data['od']);
    }
    $result['do'] = self::parseAISDateTime($data['do']);
    return $result;
  }
  
}

?>
