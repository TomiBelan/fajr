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
    parent::__construct($trace, $executor, $data);
    $this->parser = $parser;
  }

  public function getPredmetyZapisnehoListu(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    $data = $this->executor->doRequest($trace,
      array('eventClass' => 'avc.ui.event.AVCActionEvent',
        'compName' => 'filterAction',
        'embObj' => array('semesterComboBox' => array(
            'dataView' => array(
              'selectedIndexes' => 0,
            ),
            'editMode' => 'false',
          ),
        ),
    ));

    return $this->parser->createTableFromHtml($trace->addChild("Parsing table"), $data,
        'VSES007_StudentZoznamPrihlaseniNaSkuskuDlg0_predmetyTable_dataView');
  }

  public function getTerminyHodnotenia(Trace $trace)
  {
    $this->openIfNotAlready($trace);
    $data = $this->executor->doRequest($trace,
      array('eventClass' => 'avc.ui.event.AVCActionEvent',
        'compName' => 'zobrazitTerminyAction',
        'embObj' => array('zobrazitTerminyComboBox' => array(
            'dataView' => array(
              'selectedIndexes' => 0,
            ),
            'editMode' => 'false',
          ),
        ),
    ));

    return $this->parser->createTableFromHtml($trace->addChild("Parsing table"),
                $data, 'VSES007_StudentZoznamPrihlaseniNaSkuskuDlg0_terminyTable_dataView');
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
    $this->openIfNotAlready($trace);
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
