<?php
/*
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
*/

	/**
	 * Description of Input
	 *
	 * @author majak
	 */
	class Input
	{
		public static $inputParameters = array();

		public static function prepare()
		{
			if (isset($_GET['studium']))
			{
				if (!ctype_digit($_GET['studium'])) throw new Exception('Vstupný parameter "studium" musí byť typu integer.');
				self::$inputParameters['studium'] = $_GET['studium'];
			}

			if (isset($_GET['list']))
			{
				if (!ctype_digit($_GET['list'])) throw new Exception('Vstupný parameter "list" musí byť typu integer.');
				self::$inputParameters['list'] = $_GET['list'];
			}

			if (isset($_POST['login']))
			{
				if (empty($_POST['login'])) throw new Exception('Vstupný parameter "login" nesmie byť prázdny.');
				self::$inputParameters['login'] = $_POST['login'];
			}

			if (isset($_POST['krbpwd']))
			{
				if (empty($_POST['krbpwd'])) throw new Exception('Vstupný parameter "krbpwd" nesmie byť prázdny.');
				self::$inputParameters['krbpwd'] = $_POST['krbpwd'];
			}

			if (isset($_POST['cosignCookie']))
			{
				if (empty($_POST['cosignCookie'])) throw new Exception('Vstupný parameter "cosignCookie" nesmie byť prázdny.');
				self::$inputParameters['cosignCookie'] = $_POST['cosignCookie'];
			}

			if (isset($_GET['logout'])) self::$inputParameters['logout'] = true;
		}

		public static function get($key = null)
		{
			if ($key === null) return self::$inputParameters;
			if (!isset(self::$inputParameters[$key]))
			{
				return null;
			}
			else
			{
				return self::$inputParameters[$key];
			}
		}
	}
?>
