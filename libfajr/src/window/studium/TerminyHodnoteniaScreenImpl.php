<?php
// Copyright (c) 2010 The Fajr authors (see AUTHORS).
// Use of this source code is governed by a MIT license that can be
// found in the LICENSE file in the project root directory.

/**
 * PHP version 5.3.0
 *
 * @package    Libfajr
 * @subpackage Window__Studium
 * @author     Martin Králik <majak47@gmail.com>
 * @filesource
 */
namespace libfajr\window\studium;

use libfajr\window\studium\TerminyHodnoteniaScreen;
use libfajr\trace\Trace;
use libfajr\connection\SimpleConnection;
use libfajr\window\DialogData;
use libfajr\window\ScreenData;
use libfajr\data\DataTable;
use libfajr\data\ComboBox;
use libfajr\data\ActionButton;
use libfajr\window\RequestBuilderImpl;
use libfajr\window\ScreenRequestExecutor;
use libfajr\window\AIS2AbstractScreen;
use libfajr\data\AIS2TableParser;
use libfajr\util\StrUtil;
use Exception;

/**
 * Trieda reprezentujúca jednu obrazovku so zoznamom predmetov zápisného listu
 * a termínov hodnotenia.
 *
 * @package    Libfajr
 * @subpackage Window__Studium
 * @author     Martin Králik <majak47@gmail.com>
 */
class TerminyHodnoteniaScreenImpl extends AIS2AbstractScreen
    implements TerminyHodnoteniaScreen
{
  /**
   * @var AIS2TableParser
   */
  private $parser;

  public function __construct(Trace $trace, ScreenRequestExecutor $executor,
      AIS2TableParser $parser, $paramName)
  {
    $data = new ScreenData();
    $data->appClassName = 'ais.gui.vs.es.VSES007App';
    $data->additionalParams = array('kodAplikacie' => 'VSES007',
        'paramName' => $paramName);
    $components['dataComponents']['terminyTable_dataView'] = new DataTable("terminyTable_dataView");
    $components['dataComponents']['predmetyTable_dataView'] = new DataTable("predmetyTable_dataView");
    $components['dataComponents']['semesterComboBox'] = new ComboBox("semesterComboBox");

    $components['actionComponents']['filterAction'] = new ActionButton("filterAction");
    $components['actionComponents']['zobrazitTerminyAction'] = new ActionButton("zobrazitTerminyAction");
    parent::__construct($trace, $executor, $data, $components);
    $this->parser = $parser;
  }

  public function getPredmetyZapisnehoListu(Trace $trace)
  {
    // zatial to tu je zbytocne dokym sa nemenia moznosti v comboBoxe
    $this->components['semesterComboBox']->selectOption(0);
    $this->doAction('filterAction');
    return $this->components['predmetyTable_dataView'];
  }

  public function getTerminyHodnotenia(Trace $trace)
  {
    // zatial to tu je zbytocne dokym sa nemenia moznosti v comboBoxe
    $this->components['semesterComboBox']->selectOption(0);
    $this->doAction('zobrazitTerminyAction');

    return $this->components['terminyTable_dataView'];
  }

  public function getZoznamTerminovDialog(Trace $trace, $predmetIndex)
  {
    $data = new DialogData();
    $data->compName = 'pridatTerminAction';
    $data->embObjName = 'predmetyTable';
    $data->index = $predmetIndex;

    return new TerminyDialogImpl($trace, $this, $data);
  }

  public function getZoznamPrihlasenychDialog(Trace $trace, $terminIndex)
  {
    $data = new DialogData();
    $data->compName = 'zoznamPrihlasenychStudentovAction';
    $data->embObjName = 'terminyTable';
    $data->index = $terminIndex;
    return new ZoznamPrihlasenychDialogImpl($trace, $this, $data);
  }

  public function odhlasZTerminu(Trace $trace, $terminIndex)
  {
    // Posleme request ze sa chceme odhlasit.
    $data = $this->executor->doRequest($trace, array(
      'compName' => 'odstranitTerminAction',
      'eventClass' => 'avc.ui.event.AVCActionEvent',
      'embObj' => array('terminyTable' => array(
          'dataView' => array(
            'activeIndex' => $terminIndex,
            'selectedIndexes' => $terminIndex,
          ),
          'editMode' => 'false',
        ),
      ),
    ));
    if (!preg_match("@Skutočne chcete odobrať vybraný riadok?@", $data)) {
      throw new Exception("Problém pri odhlasovaní - neočakávaná odozva AISu");
    }
    
    // Odklikneme konfirmacne okno ze naozaj.
    $data = $this->executor->doRequest($trace, array(
      'events' => false,
      'app' => false,
      'dlgName' => false,
      'changedProperties' => array(
        'confirmResult' => 2,
      ),
    ));
    
    $message = StrUtil::match('@webui\.messageBox\("([^"]*)"@', $data);
    if (($message !== false) && ($message != 'Činnosť úspešne dokončená.')) {
      throw new Exception("Z termínu sa (pravdepodobne) nepodarilo odhlásiť." .
                          "Dôvod:<br/><b>".$message.'</b>');
    }

    if (!preg_match("@dm\(\).setActiveDialogName\(".
          "'VSES007_StudentZoznamPrihlaseniNaSkuskuDlg0'\);@", $data)) {
      throw new Exception("Problém pri odhlasovaní z termínu - neočakávaná odpoveď od AISu");
    }
    
    return true;
  }
}
