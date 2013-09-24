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
 * @subpackage Window__Studium
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace libfajr\window\studium;

use libfajr\window\studium\TerminyDialog;
use libfajr\trace\Trace;
use libfajr\data\DataTable;
use libfajr\data\ActionButton;
use libfajr\window\DialogData;
use libfajr\window\DialogParent;
use libfajr\window\AIS2AbstractDialog;
use libfajr\data\AIS2TableParser;
use libfajr\util\StrUtil;
use Exception;
/**
 * Trieda pre dialóg s termínmi skúšok k jednému predmetu.
 *
 * @package    Libfajr
 * @subpackage Window__Studium
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
    $components['dataComponents']['zoznamTerminovTable_dataView'] = new DataTable("zoznamTerminovTable_dataView");
    $components['actionComponents']['enterAction'] = new ActionButton("enterAction");
    parent::__construct($trace, $parent, $data, $components);
    $this->parser = ($parser !== null) ? $parser :  new AIS2TableParser;
  }
  
  public function getZoznamTerminov(Trace $trace)
  {
    return $this->components['zoznamTerminovTable_dataView'];
  }
  
  public function prihlasNaTermin(Trace $trace, $terminIndex)
  {
    $table = $this->components['zoznamTerminovTable_dataView'];
    $table->selectSingleRow((integer)$terminIndex);
    $table->setActiveRow((integer)$terminIndex);
    $data = $this->doAction('enterAction');

    $data = $data->getElementById("init-data");
    $data = $data->textContent;
    
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
    
    $this->closeWindow();
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
