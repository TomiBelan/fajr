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
 * @subpackage Window__Predmety
 * @author     Tomi Belan <tomi.belan@gmail.com>
 * @filesource
 */
namespace libfajr\window\predmety;

use libfajr\window\predmety\RegisterPredmetovScreen;
use libfajr\trace\Trace;
use libfajr\connection\SimpleConnection;
use libfajr\window\AIS2AbstractScreen;
use libfajr\window\RequestBuilderImpl;
use libfajr\window\ScreenRequestExecutor;
use libfajr\window\DialogData;
use libfajr\window\ScreenData;
use libfajr\data\AIS2TableParser;
use libfajr\data\AIS2ComboBoxParser;
use libfajr\util\StrUtil;
use libfajr\util\MiscUtil;
use Exception;
use libfajr\exceptions\ParseException;
// TODO checknut ci vsetky hentie use stale treba (su skopcene z VSES017)

/**
 * Trieda reprezentujúca jednu obrazovku s registrom predmetov.
 *
 * @package    Libfajr
 * @subpackage Window__Studium
 * @author     Martin Kralik <majak47@gmail.com>
 */
class RegisterPredmetovScreenImpl extends AIS2AbstractScreen
    implements RegisterPredmetovScreen
{
  /**
   * @var AIS2TableParser
   */
  private $parser;
  
  /**
   * @var AIS2ComboBoxParser
   */
  private $cbParser;

  public function __construct(Trace $trace, ScreenRequestExecutor $executor,
      AIS2TableParser $parser, AIS2ComboBoxParser $cbParser)
  {
    $data = new ScreenData();
    $data->appClassName = 'ais.gui.vs.st.VSST060App';
    $data->additionalParams = array('kodAplikacie' => 'VSST060');
    parent::__construct($trace, $executor, $data);
    $this->parser = $parser;
    $this->cbParser = $cbParser;
  }

  public function getInformacnyList(Trace $trace, $kodPredmetu, $akRok=null)
  {
    $this->openIfNotAlready($trace);
    // TODO(anty): moze sa tu robit aj tento request? (t.j. ten pri otvoreni okna???)
    $response = $this->executor->requestContent($trace->addChild("get content"));
    $options = $this->cbParser->getOptionsFromHtml($trace, $response, 'akRokComboBox');
    
    // zistime, ktory je aktualny akademicky rok (nemusi byt nutne najnovsi)
    // default je prvy v tabulke
    $akRokIndex = 0;
    if ($akRok !== null) {
      foreach ($options as $k => $v) {
        $akRokIndex = array_search($akRok, $options);
        if ($akRokIndex === false) {
          throw new Exception("Zadaný akad. rok sa v zozname nenachádza");
        }
      }
    }
    
    $data = $this->executor->doRequest(
        $trace->addChild("Requesting data:"),
        array('compName' => 'zobrazitPredmetyAction',
              'embObj' => array(
                'skratkaPredmetuTextField' => array(
                  'text' => $kodPredmetu,
                ),
                'akRokComboBox' => array(
                  'dataView' => array(
                    'selectedIndexes' => $akRokIndex,
                  ),
                ),
              ),
            ));
    $table = $this->parser->createTableFromHtml($trace->addChild("Parsing table"),
        $data, 'VSST060_RegisterPredmetovDlg0_zoznamPredmetovTable_dataView');
    if(count($table->getData()) == 0) {
      throw new ParseException("Daný kód predmetu sa v zozname nenachádza.");
      return;
    }

    // TODO: nikde nevyberame, ktoreho vrateneho riadku informacny list chceme...
    // asi sa defaultne pouzije prvy - je to OK?
    
    $data = $this->executor->doRequest(
        $trace->addChild("Requesting data:"),
        array('compName' => 'informacnyListAction')
    );
    if (!preg_match("@dm\(\)\.openDialog\("
          ."\"CM017_SpravaSablonDlg1\"@", $data)) {
      throw new Exception("Problém pri sťahovaní: ".
          "Neočakávaná odozva od AISu");
    }

    $data = $this->executor->doRequest(
        $trace->addChild("Requesting data:"),
        array('compName' => 'enterAction',
              'dlgName' => 'CM017_SpravaSablonDlg1',
              'embObj' => array('typZostavyComboBox' => array(
                  'dataView' => array(
                    'selectedIndexes' => 1,
                  ),                  
                  'editMode' => 'false',
                ),
              ),
            ));
    if (!preg_match("@dm\(\)\.closeDialog\("
          ."\"CM017_SpravaSablonDlg1\"\);@", $data)) {
      throw new Exception("Problém pri sťahovaní: ".
          "Neočakávaná odozva od AISu");
    }

    $data = $this->executor->doRequest(
        $trace->addChild("Requesting data:"),
        array('events' => false,
              'app' => false,
              'dlgName' => false,
              'changedProperties' => array('confirmResult' => '-1')
            ));
    if(!preg_match('@shellExec@', $data)) {
      throw new Exception("Problém pri sťahovaní: ".
          "Neočakávaná odozva od AISu");
    }

    return $this->executor->doFilesRequest($trace, array('file' => '', 'contentType' => ''));
  }

}
?>
