<?php
/* {{{
Copyright (c) 2010 Martin Králik

 Permission is hereby granted, free of charge, to any person
 obtaining a copy of this software and associated documentation
 files (the "Software"), to deal in the Software without
 restriction, including without limitation the rights to use,
 copy, modify, merge, publish, distribute, sublicense, and/or sell
 copies of the Software, and to permit persons to whom the
 Software is furnished to do so, subject to the following
 conditions:

 The above copyright notice and this permission notice shall be
 included in all copies or substantial portions of the Software.

 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 OTHER DEALINGS IN THE SOFTWARE.
 }}} */

/**
 * Abstraktná trieda reprezentujúca jednu obrazovku v AISe.
 *
 * @author majak
 */
abstract class AIS2AbstractDialog extends AIS2AbstractWindow
{
	protected $parent = null;
	protected $terminated = false;
	protected $openedDialog = false;
	private $compName = null;
	private $embObjName = null;
	private $index = null;

	/**
	 * Konštruktor.
	 *
	 * @param string $appClassName Názov "triedy" obsluhujúcej danú obrazovku v AISe.
	 * @param string $identifiers Konkrétne parametre pre vyvolanie danej obrazovky.
	 */
	public function __construct($parent, $compName, $embObjName, $index)
	{
		$this->parent = $parent;
		$this->compName = $compName;
		$this->embObjName = $embObjName;
		$this->index = $index;
		$this->open();
	}

	/**
	 * Nadviaže spojenie, spustí danú "aplikáciu" v AISe
	 * a natiahne prvotné dáta do atribútu $data.
	 */
	public function open() {
		if ($this->inUse) return;

		if ($this->parent->openedDialog)
		{
			throw new Exception('V nadradenom screene "'.$this->parent->formName.'" už existuje otvorený dialog. Pre otvorenie nového treba pôvodný zatvoriť.');
		}

		$this->inUse = true;

		$response = $this->requestData(array(
			'dlgName' => $this->parent->formName,
			'compName' => $this->compName,
			'embObj' => array(
				'objName' => $this->embObjName,
				'dataView' => array(
					'activeIndex' =>  $this->index,
					'selectedIndexes' => $this->index,
				),
			),
		));

		$formName = match($response, AIS2Utils::DIALOG_NAME_PATTERN);
		if ($formName === false) throw new Exception('Nepodarilo sa nájsť názov dialógu pre triedu '.__CLASS__.'.');

		$this->formName = $formName;
		$this->data = AIS2Utils::request('https://ais2.uniba.sk/ais/servlets/WebUIServlet?appId='.$this->getAppId().'&form='.$this->formName.'&antiCache='.random());

		$this->parent->openedDialog = true;
		$this->terminated = false;
	}

	/**
	 * Zatvorí danú "aplikáciu" v AISe
	 */
	public function close() {
		if (!$this->inUse) return;
		if (!$this->terminated)
		{
			$response = $this->requestData(array(
				'eventClass' => 'avc.ui.event.AVCComponentEvent',
				'command' => 'CLOSE',
			));
		}
		$this->parent->openedDialog = false;
		$this->inUse = false;
	}

	/**
	 * Deštruktor.
	 * Zatvorí danú "aplikáciu" v AISe,
	 * aby sa nevyčerpal limit otvorených aplikácii na session.
	 * Toto správenie nebolo pozorované pri dialógoch, ale pre istotu to tu je.
	 */
	public function  __destruct()
	{
		$this->close();
	}

	/**
	 * Získa nové sériové číslo používané v XML protokole na komunikáciu s AISom od materského screenu.
	 * @return int Nové seriové číslo v poradí.
	 */
	protected function getSerial()
	{
		return $this->parent->getSerial();
	}
	
	protected function getAppId()
	{
		return $this->parent->getAppId();
	}

}
?>
