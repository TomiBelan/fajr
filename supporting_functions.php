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

	function redirect($url = null)
	{
		if ($url === null) $url = 'fajr.php';
		header('Location: '.$url);
		exit();
	}
	
	function pluck($haystack, $pattern)
	{
		$matches = array();
		if (!preg_match($pattern, $haystack, $matches)) return false;
		return $matches[1];
	}
	
	function pluckAll($haystack, $pattern, $singleMatch = false)
	{
		$matches = array();
		if (!preg_match_all($pattern, $haystack, $matches, PREG_SET_ORDER)) return false;
		else
		{
			if ($singleMatch == false) return $matches;
			else return $matches[0];
		}
	}
	
	function dump($s)
	{
		if (is_array($s)) foreach ($s as $value) dump($value);
		else echo '<pre>'.hescape($s).'</pre><hr/>';
	}
	
	function gzdecode($data)
	{
		$g = tempnam(dirname(__FILE__).DIRECTORY_SEPARATOR.'temp', 'gzip');
		@file_put_contents($g, $data);
		ob_start();
		readgzfile($g);
		$d = ob_get_clean();
		unlink($g);
		return $d;
	}
	
	function getCookieFile()
	{
		return dirname(__FILE__).DIRECTORY_SEPARATOR.'cookies'.DIRECTORY_SEPARATOR.session_id();
	}
	
	function download($url, $post = null, $xWwwFormUrlencoded = true)
	{
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_COOKIEFILE, getCookieFile());
		curl_setopt($ch, CURLOPT_COOKIEJAR, getCookieFile());
		curl_setopt ($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; sk; rv:1.9.1.7) Gecko/20091221 Firefox/3.5.7');
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_VERBOSE, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // AIS2 nema koser certifikat

		if (is_array($post))
		{
				curl_setopt($ch, CURLOPT_POST, true);
				if ($xWwwFormUrlencoded === true)
				{
					$newPost = '';
					foreach ($post as $key => $value) $newPost .= urlencode($key).'='.urlencode($value).'&';
					$post = substr($newPost, 0, -1);
				}
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		}

		$output = curl_exec($ch);
		if (curl_errno($ch)) echo curl_error($ch);

		if (strpos($output, "\x1f\x8b\x08\x00\x00\x00\x00\x00") === 0) $output = gzdecode($output); //ak to zacina ako gzip, tak to odzipujeme
		curl_close($ch);
		return $output;
	}
	
	function hescape($string)
	{
		return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
	}

	function random()
	{
		return rand(100000,999999);
	}
	
?>
