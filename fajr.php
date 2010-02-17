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
	
	require_once 'displayManager.php';
	require_once 'AIS2.php';
        require_once 'changelog.php';
 
	try
	{
		AIS2::prepareInputParameters();
		
		if (AIS2::getInputParameter('logout') !== null) AIS2::cosignLogout();
		
		if (AIS2::connect())
		{
			DisplayManager::addContent(AIS2::getStudentZoznamStudii());
			if (AIS2::getInputParameter('studium') !== null)
			{
				DisplayManager::addContent(AIS2::getStudentZapisneListy());
				if (AIS2::getInputParameter('list') !== null)
				{
					DisplayManager::addContent(AIS2::getPredmetyZapisnehoListu());
				}
			}
			
			DisplayManager::addContent(AIS2::$identity.' | <a href="?logout">odhlásiť</a><hr/>');
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
	AIS2::disconnect(); //zavrieme aj poslednu "aplikaciu"
	
	echo DisplayManager::display();
	
	session_write_close();
?>
