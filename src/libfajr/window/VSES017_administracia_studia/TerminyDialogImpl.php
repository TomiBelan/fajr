<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * TODO
 *
 * PHP version 5.3.0
 *
 * @package    Libfajr
 * @subpackage Libfajr__Window__VSES017_administracia_studia
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace libfajr\window\VSES017_administracia_studia;

use libfajr\pub\window\VSES017_administracia_studia\TerminyDialog;
use libfajr\pub\base\Trace;
use libfajr\window\DialogData;
use libfajr\window\DialogParent;
use libfajr\window\AIS2AbstractDialog;
use libfajr\data_manipulation\AIS2TableParser;
use libfajr\util\StrUtil;
use Exception;
/**
 * Trieda pre dialóg s termínmi skúšok k jednému predmetu.
 *
 * @package    Libfajr
 * @subpackage Libfajr__Window__VSES017_administracia_studia
 * @author     Martin Králik <majak47@gmail.com>
 */
class TerminyDialogImpl extends AIS2AbstractDialog
    implements TerminyDialog
{
  /**
   * @var AIS2TableParser
   */
  private $parser;

  public function __construct(Trace $trace, DialogParent $parent,
      DialogData $data, AIS2TableParser $parser = null)
  {
    parent::__construct($trace, $parent, $data);
    $this->parser = ($parser !== null) ? $parser :  new AIS2TableParser;
  }
  
  public function getZoznamTerminov(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    $response = $this->executor->requestContent($trace);
    return $this->parser->createTableFromHtml($trace->addChild("Parsing table"), $response,
        'zoznamTerminovTable_dataView');
  }
  
  public function prihlasNaTermin(Trace $trace, $terminIndex)
  {
    $this->openIfNotAlready($trace);
    $data = $this->executor->doRequest($trace, array(
      'compName' => 'enterAction',
      'eventClass' => 'avc.ui.event.AVCActionEvent',
      'embObj' => array(
        'objName' => 'zoznamTerminovTable',
        'dataView' => array(
          'activeIndex' => $terminIndex,
          'selectedIndexes' => $terminIndex,
        ),
      ),
    ));
    
    $error = StrUtil::match('@webui\(\)\.messageBox\("([^"]*)",@', $data);
    if ($error) {
      throw new Exception('Nepodarilo sa prihlásiť na zvolený termín.<br/>'.
          'Dôvod: <b>'.$error.'</b>');
    }
    if (!preg_match("@dm\(\)\.closeDialog\("
          ."\"VSES206_VyberTerminuHodnoteniaDlg1\"\);@", $data)) {
      throw new Exception("Problém pri prihlasovaní: ".
          "Neočakávaná odozva od AISu");
    }
    
    $this->closeIfNeeded($trace);
    return true;
  }
  
  public function getZoznamPrihlasenychDialog(Trace $trace, $terminIndex)
  {
    $data = new DialogData();
    $data->compName = 'zobrazitZoznamPrihlasenychAction';
    $data->embObjName = 'zoznamTerminovTable';
    $data->index = $terminIndex;
    return new ZoznamPrihlasenychDialogImpl($trace, $this, $data);
  }
  
}
?>
