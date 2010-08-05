<?php
/* {{{
Copyright (c) 2010 Martin Sucha

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

use \fajr\libfajr\Trace;
use \fajr\libfajr\NullTrace;

/**
 * Zbiera základné štatistické informácie o vykonaných spojeniach
 */
class AIS2DebugConnection implements AIS2Connection {

	private $requests = null;
	private $delegate = null;

	function __construct($delegate) {
		$this->delegate = $delegate;
		$this->clear();
	}

	public function clear() {
		$this->requests = array();
	}

	public function get($url, Trace $trace = null) {
    $trace || $trace = new NullTrace();
		$requestInfo = array();
		$requestInfo['url'] = $url;
		$requestInfo['method'] = 'GET';
		$startTime = 0;
		try {
			$startTime = microtime(true);
			$result = $this->delegate->get($url);
			$endTime = microtime(true);

			$requestInfo['startTime'] = $startTime;
			$requestInfo['endTime'] = $endTime;
			$requestInfo['responseData'] = $result;
			$this->requests[] = $requestInfo;
      $trace->tlog("DebugConnection:");
      $trace->tlogData("GET: $url");
      $child = $trace->addChild("Response:");
      $child->tlogData($result);
			
			return $result;
		}
		catch (Exception $e) {
			$requestInfo['endTime'] = $endTime;
			$requestInfo['exception'] = $e;
			$this->requests[] = $requestInfo;
			throw $e;
		}
	}

	public function post($url, $data, Trace $trace = null) {
    $trace || $trace = new NullTrace();
		$requestInfo = array();
		$requestInfo['url'] = $url;
		$requestInfo['method'] = 'POST';
		$requestInfo['requestData'] = $data;
		$startTime = 0;
		try {
			$startTime = microtime(true);
			$result = $this->delegate->post($url, $data);
			$endTime = microtime(true);

			$requestInfo['startTime'] = $startTime;
			$requestInfo['endTime'] = $endTime;
			$requestInfo['responseData'] = $result;
			$this->requests[] = $requestInfo;
      $trace->tlog("DebugConnection:");
      $trace->tlogData("POST: $url");
      $child = $trace->addChild("Request:");
      $child->tlogData(var_export($data, true));
      $child = $trace->addChild("Response:");
      $child->tlogData($result);
			return $result;
		}
		catch (Exception $e) {
			$this->errorCount++;
			$requestInfo['endTime'] = $endTime;
			$requestInfo['exception'] = $e;
			$this->requests[] = $requestInfo;
			throw $e;
		}
	}

	public function addCookie($name, $value, $expire, $path, $domain, $secure = true, $tailmatch = false) {
		return $this->delegate->addCookie($name, $value, $expire, $path, $domain, $secure, $tailmatch);
	}

	public function clearCookies() {
		return $this->delegate->clearCookies();
	}

	public function getRequests() {
		return $this->requests;
	}

}
