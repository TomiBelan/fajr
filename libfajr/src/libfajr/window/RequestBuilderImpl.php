<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 *
 * @package    Libfajr
 * @subpackage Window
 * @author     Peter Perešíni <ppershing+fajr@gmail.com>
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace libfajr\window;

use libfajr\connection\AIS2ServerUrlMap;

class RequestBuilderImpl implements RequestBuilder
{
  private $serial = 0;
  private $server;

  public function __construct(AIS2ServerUrlMap $server)
  {
    $this->server = $server;
  }

  /**
   * Vygeneruje nové sériové číslo používané v XML protokole na komunikáciu s AISom.
   *
   * @returns int Nové seriové číslo v poradí.
   */
  public function newSerial()
  {
    return $this->serial++;
  }

  /**
   * Vytvorí url XML interfacu pre komunikáciu s "aplikáciou" tejto obrazovky.
   *
   * @param string $appId AIS2 id aplikácie.
   * @returns string Url.
   */
  public function getRequestUrl($appId, $formName = null)
  {
    $url = $this->server->getWebUiServletUrl();
    $params = array('appId' => $appId);
    if ($formName !== null) {
      $params['form'] = $formName;
    }
    return $url . '?' . http_build_query($params);
  }

  /**
   * Vytvorí url pre prenos súborov vygenerovaných AISom.
   *
   * @param array(string=>string) $query obsah query stringu.
   * @returns string Url.
   */
  public function getFilesRequestUrl($query)
  {
    $url = $this->server->getFilesUrl();
    if (!empty($query['file'])) {
      $url .= $query['file'];
    }
    return $url . '?' . http_build_query($query);
  }

  public function getAppInitializationUrl(ScreenData $data)
  {
    $url = $this->server->getWebUiServletUrl();
    $params = array(
      'appClassName' => $data->appClassName,
      // tento parameter zaruci, ze nam AIS vrati aj tie tabulkove stlpce, ktore si user rucne skryl
      'fajr' => 'A',
    );
    if ($data->additionalParams !== null) {
      $params += $data->additionalParams;
    }

    return $url .= '?' . http_build_query($params);
  }

  /**
   * Experimentalna funkcia snažiaca sa zovšeobecniť dodatočné requesty jednotlivých AIS aplikácií.
   * Je veľmi pravdepodobné, že sa bude meniť.
   *
   * @param string  $dlgName názov aktuálneho dialógu
   * @param array() $options špeciálne nastavenia, viď kód.
   * @returns array() POST dáta.
   */
  public function buildRequestData($dlgName, array $options)
  {
    $events = true;
    $eventClass = 'avc.ui.event.AVCActionEvent';
    $app = true;
    $command = null;
    $compName = null;
    $appProperties = array();
    $embObj = null;
    $appProperties = array();
    $objProperties = array();
    $changedProperties = null;
    extract($options, EXTR_IF_EXISTS);

    if (!isset($appProperties['activeDlgName'])) {
      $appProperties['activeDlgName'] = $dlgName;
    }

    $xml_spec = '<request><serial>'.$this->newSerial().'</serial>';
    if ($events === true) {
      $xml_spec .= '<events><ev>';
      if ($dlgName !== null) {
        $xml_spec .= '<dlgName>'.$dlgName.'</dlgName>';
      }
      if ($compName !== null) {
        $xml_spec .= '<compName>'.$compName.'</compName>';
      }
      $xml_spec .= '<event class=\''.$eventClass.'\'>';
      if ($command !== null) {
        $xml_spec .= '<command>'.$command.'</command>';
      }
      $xml_spec .= '</event></ev></events>';
    }
    $xml_spec .= '<changedProps>';
    if ($app === true) {
      $xml_spec .= '<changedProperties><objName>app</objName><propertyValues>';
      foreach ($appProperties as $name => $value) {
        $xml_spec .= '<nameValue><name>'.$name.'</name><value>'.$value.'</value></nameValue>';
      }
      $xml_spec .= '</propertyValues></changedProperties>';
    }

    if ($dlgName !== false) {
      $xml_spec .= '<changedProperties><objName>'.$dlgName.'</objName><propertyValues>';
      foreach ($objProperties as $name => $value) {
        $xml_spec .= '<nameValue><name>'.$name.'</name><value>'.$value.'</value></nameValue>';
      }
      $xml_spec .= '</propertyValues><embObjChProps>';

      if ($embObj !== null) {
        foreach ($embObj as $object => $changes) {
          $xml_spec .= '<changedProperties><objName>' . $object .
              '</objName><propertyValues>';
          foreach ($changes as $name => $value) {
            if ($name == 'dataView') {
              $xml_spec .= '<nameValue><name>dataView</name><isXml>true</isXml>' .
                  '<value><![CDATA[<root><selection>';
              if (is_array($value)) {
                foreach ($value as $dataViewName => $dataViewValue) {
                  $xml_spec .= '<'.$dataViewName.'>'.$dataViewValue.'</'.$dataViewName.'>';
                }
              }
              $xml_spec .= '</selection>';
              if (isset($changes['visibleBuffers'])) {
                $xml_spec .= '<visibleBuffers>' . $changes['visibleBuffers'] . '</visibleBuffers>';
              }
              if (isset($changes['loadedBuffers'])) {
                $xml_spec .= '<loadedBuffers>'.$changes['loadedBuffers'].'</loadedBuffers>';
              }
              $xml_spec .= '</root>]]></value></nameValue>';
              if (isset($empObj['editMode'])) {
                $xml_spec .= '<nameValue><name>editMode</name>' .
                    '<isXml>false</isXml><value>'.$changes['editMode'].'</value></nameValue>';
              }
            }
            else if ($name != 'visibleBuffers' && $name != 'loadedBuffers' && $name != 'objName' && $name != 'editMode') {
              $xml_spec .= '<nameValue><name>' . $name . '</name>' .
                  '<value><![CDATA[' . $value . ']]></value></nameValue>';
            }
          }
          $xml_spec .= '</propertyValues><embObjChProps isNull=\'true\'/></changedProperties>';
        }
      }
      $xml_spec .= '</embObjChProps></changedProperties>';
    }
    
    if (is_array($changedProperties)) {
      $xml_spec .= '<changedProperties><propertyValues>';
      foreach ($changedProperties as $name => $value) {
        $xml_spec .= '<nameValue><name>'.$name.'</name><value>'.$value.'</value></nameValue>';
      }
      $xml_spec .= '</propertyValues></changedProperties>';
    }
    
    $xml_spec .= '</changedProps></request>';
    
    return array('xml_spec' => $xml_spec);
  }
}
