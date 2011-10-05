<?php
// Copyright (c) 2010-2011 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * TODO
 *
 * PHP version 5.3.0
 *
 * @package    Libfajr
 * @subpackage Window__VSES017_administracia_studia
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace libfajr\window\VSES017_administracia_studia;

use libfajr\pub\window\VSES017_administracia_studia\AdministraciaStudiaScreen;
use libfajr\pub\base\Trace;
use libfajr\pub\connection\SimpleConnection;
use libfajr\window\AIS2AbstractScreen;
use libfajr\window\RequestBuilderImpl;
use libfajr\window\ScreenRequestExecutor;
use libfajr\window\DialogData;
use libfajr\window\ScreenData;
use libfajr\data_manipulation\AIS2TableParser;
use libfajr\util\StrUtil;
use libfajr\util\MiscUtil;
use libfajr\pub\exceptions\ParseException;

/**
 * Trieda reprezentujúca jednu obrazovku so zoznamom štúdií a zápisných listov.
 *
 * @package    Libfajr
 * @subpackage Window__VSES017_administracia_studia
 * @author     Martin Kralik <majak47@gmail.com>
 */
class AdministraciaStudiaScreenImpl extends AIS2AbstractScreen
    implements AdministraciaStudiaScreen
{
  // TODO(ppershing): use named pattern here
  const APP_LOCATION_PATTERN = '@webui\(\)\.startApp\("([^"]+)","([^"]+)"\);@';
  const ID_PATTERN = '@(?:&idZapisnyList\=(?P<idZapisnyList>[0-9]*))(?:&idStudium\=(?P<idStudium>[0-9]*))?@';
  
  protected $idCache = array();

  /**
   * @var AIS2TableParser
   */
  private $parser;

  public function __construct(Trace $trace, ScreenRequestExecutor $executor, AIS2TableParser $parser)
  {
    $data = new ScreenData();
    $data->appClassName = 'ais.gui.vs.es.VSES017App';
    $data->additionalParams = array('kodAplikacie' => 'VSES017');
    parent::__construct($trace, $executor, $data);
    $this->parser = $parser;
  }

  public function getZoznamStudii(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    $response = $this->executor->requestContent($trace->addChild("get content"));
    return $this->parser->createTableFromHtml($trace->addChild("Parsing table"), $response, 'studiaTable_dataView');
  }

  public function getZapisneListy(Trace $trace, $studiumIndex)
  {
    $this->openIfNotAlready($trace);
    $data = $this->executor->doRequest(
        $trace->addChild("Requesting data:"),
        array('compName' => 'nacitatDataAction',
              'embObj' => array(
                'objName' => 'studiaTable',
                'dataView' => array(
                  'activeIndex' => $studiumIndex,
                  'selectedIndexes' => $studiumIndex,
                ),
              ),
            ));
    return $this->parser->createTableFromHtml($trace->addChild("Parsing table"),
        $data, 'VSES017_StudentZapisneListyDlg0_zapisneListyTable_dataView');
  }

  public function getZapisnyListIdFromZapisnyListIndex(Trace $trace, $zapisnyListIndex, $action)
  {
    return $this->getIdFromZapisnyListIndex($trace, $zapisnyListIndex, 'idZapisnyList', $action);
  }

  public function getStudiumIdFromZapisnyListIndex(Trace $trace, $zapisnyListIndex, $action)
  {
    return $this->getIdFromZapisnyListIndex($trace, $zapisnyListIndex, 'idStudium', $action);
  }

  protected function getIdFromZapisnyListIndex(Trace $trace, $zapisnyListIndex, $idType, $action)
  {
    $this->openIfNotAlready($trace);
    if (empty($this->idCache[$zapisnyListIndex]) || empty($this->idCache[$zapisnyListIndex][$idType]))
    {
      $response = $this->executor->doRequest(
          $trace->addChild("Requesting data:"),
          array('compName' => $action,
                'embObj' => array(
                  'objName' => 'zapisneListyTable',
                  'dataView' => array(
                    'activeIndex' => $zapisnyListIndex,
                    'selectedIndexes' => $zapisnyListIndex,
                  ),
                ),
              ));

      try {
        $data = $this->parseIdFromZapisnyListIndexFromResponse($response);
      }
      catch (ParseException $ex) {
        throw new ParseException("Nepodarilo sa zistiť $idType pre akciu $action: " . $ex->getMessage(), null, $ex);
      }
      if (empty($this->idCache[$zapisnyListIndex])) {
        $this->idCache[$zapisnyListIndex] = array();
      }
      $this->idCache[$zapisnyListIndex] = array_merge($this->idCache[$zapisnyListIndex], $data);
    } else {
      $trace->tlogVariable("data from cache", $this->idCache[$zapisnyListIndex][$idType]);
    }
    return $this->idCache[$zapisnyListIndex][$idType];
  }

  public function parseIdFromZapisnyListIndexFromResponse($response)
  {
      $data = StrUtil::matchAll(self::APP_LOCATION_PATTERN, $response);
      if ($data === false) {
        throw new ParseException("Location of APP_PATTERN failed.");
      };
      $data = StrUtil::matchAll(self::ID_PATTERN, $data[2]);
      if ($data === false) {
        throw new ParseException("Parsing of ids from zapisnyListIndex failed.");
      }
      return MiscUtil::removeIntegerIndexesFromArray($data);
  }
  
  public function getPrehladKreditovDialog(Trace $trace, $studiumIndex)
  {
    $data = new DialogData();
    $data->compName = 'ziskaneKredityAction';
    $data->embObjName = 'studiaTable';
    $data->index = $studiumIndex;
    return new PrehladKreditovDialogImpl($trace, $this, $data);
  }

}
?>
