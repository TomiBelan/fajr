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

	session_start(); 

	require_once 'Input.php';
	require_once 'displayManager.php';
	require_once 'AIS2Utils.php';
  require_once 'changelog.php';
	require_once 'AIS2AdministraciaStudiaScreen.php';
	require_once 'AIS2TerminyHodnoteniaScreen.php';
 
	try
	{
		Input::prepare();
		
		if (Input::get('logout') !== null) AIS2Utils::cosignLogout();
		
		if (AIS2Utils::connect())
		{
			$adminStudia = new AIS2AdministraciaStudiaScreen();
			DisplayManager::addContent($adminStudia->getZoznamStudii()->getHtml());
			if (Input::get('studium') !== null)
			{
				DisplayManager::addContent($adminStudia->getZapisneListy(Input::get('studium'))->getHtml());
				if (Input::get('list') !== null)
				{
					$list = Input::get('list');
					$skusky = new AIS2TerminyHodnoteniaScreen($adminStudia->getIdZapisnyList($list), $adminStudia->getIdStudium($list));

					$terminyHodnotenia = $skusky->getTerminyHodnotenia();
					$terminyHodnotenia->setParams(array('studium' => Input::get('studium'), 'list' => $list));
					DisplayManager::addContent($terminyHodnotenia->getHtml());

					$predmetyZapisnehoListu = $skusky->getPredmetyZapisnehoListu();
					$predmetyZapisnehoListu->setParams(array('studium' => Input::get('studium'), 'list' => $list));
					DisplayManager::addContent($predmetyZapisnehoListu->getHtml());
				}
			}
			
			DisplayManager::addContent('<a href="?logout">odhlásiť</a><hr/>');
		}
		else
		{
			DisplayManager::addContent(Changelog::getChangelog(), false);
			DisplayManager::addContent('credits', true);
			DisplayManager::addContent('warnings', true);
			DisplayManager::addContent('loginBox', true);
		}
		
	}
	catch (Exception $e)
	{
		DisplayManager::addContent('<h2>ERROR!</h2><div class="error">'.$e->getMessage().'<br/>'.$e->getTraceAsString().'</div>');
	}
	
	echo DisplayManager::display();
	
	session_write_close();
?>
