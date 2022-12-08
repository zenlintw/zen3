<?php
if (!class_exists('URL'))
{
class URL
{
	var $server = '';
	var $port = 80;
	var $target ='';
	var $method = '';			//requestion method: post or get
	var $posts = array();		//form posts array
	var $gets = array();		//form gets array
	var $host = '';
	var $HTTP_REQUEST_COOKIE = '';			// HTTP_Requestion_Cookie
	var $HTTP_REQUEST_STRING = '';	//HTTP Requestion String
	var $HTTP_RESPONSE_STRING = '';	//HTTP Response String
	var $HTTP_RESPONSE_HEADER = ''; //HTTP Response header string
	var $HTTP_RESPONSE_BODY = ''; //HTTP Response header string

	function URL($server, $method='POST')
	{
		$this->server = $server;
		$this->method = $method;
		if ($method == 'GET')
		{
			$this->target = '/api/xml?';
		}else if ($method == 'POST'){
			$this->target = '/api/xml';
		}
	}

	function setPosts($arg)
	{
		$this->posts = $arg;
	}

	function setGets($arg)
	{
		$this->gets = $arg;
	}

	function getPostValues()
	{
		$tmpArr = array();
		foreach($this->posts as $key=>$val)
		{
			$tmpArr[] = $key .'='. urlencode($val);
		}
		return implode('&',$tmpArr);
	}

	function getGetValues()
	{
		$tmpArr = array();
		foreach($this->gets as $key=>$val)
		{
			$tmpArr[] = $key .'='. urlencode($val);
		}
		return implode('&',$tmpArr);
	}

	function setHTTP_REQUEST_COOKIE($val)
	{
		$this->HTTP_REQUEST_COOKIE = $val;
	}

	function getHTTP_REQUEST_STRING()
	{
		$getsValue = $this->getGetValues();

		$request  = "{$this->method} {$this->target}{$getValue} HTTP/1.0\r\n";
		$request .= "User-Agent: Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.2.1)\r\n";
		$request .= "Content-Type: application/x-www-form-urlencoded\r\n";
		$request .= "Connection: Close\r\n";

		if (!empty($this->HTTP_REQUEST_COOKIE))
		{
			$request .= "Cookie: BREEZESESSION={$this->HTTP_REQUEST_COOKIE}\r\n";
		}

		if ( $this->method == "POST" )
		{
			$postValue = $this->getPostValues();
			$length = strlen( $postValue);
 	    	$request .= "Content-Type: application/x-www-form-urlencoded\r\n";
			$request .= "Content-Length: $length\r\n";
			$request .= "Connection: close\r\n";
			$request .= "\r\n";
		 	$request .= $postValue;
		}
		$this->HTTP_REQUEST_STRING = $request;
		return $request;
	}

	function setHTTP_RESPONSE_STRING($str)
	{
		if (strlen($str) == 0) return;
		$this->HTTP_RESPONSE_STRING = $str;

		if (($pos=strpos($this->HTTP_RESPONSE_STRING,"\r\n\r\n")) !== false)
		{
			$this->HTTP_RESPONSE_HEADER = substr($this->HTTP_RESPONSE_STRING,0,$pos);
			$this->HTTP_RESPONSE_BODY = substr($this->HTTP_RESPONSE_STRING, $pos+strlen("\r\n\r\n"));
		}
	}

	function Connection()
	{
		$request = $this->getHTTP_REQUEST_STRING();
		$socket = fsockopen($this->server, $this->port);

		if ($socket)
		{
			fputs( $socket, $request);
			$ret = '';
        	while(!feof($socket))
	        {
	    	    $buffer = fgets($socket, 4096);
				$ret .= $buffer;
			}
			$this->setHTTP_RESPONSE_STRING($ret);
		}
		echo $errstr;
	}
}
}
?>
