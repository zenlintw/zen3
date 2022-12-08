<?php

require_once(BREEZE_PHP_DIR . '/Actions/URL.php');

function getISO8601_datetime($now=null){
	return (is_null($now))?date('Y-m-d\TH:i:s').".000":date('Y-m-d\TH:i:s', $now).".000";
}

function parseValue($pattern, $str)
{
	$rtns = '';
	if (ereg($pattern,$str, $args))
	{
		$rtns = $args[1];
	}
	return $rtns;
}

function getBetweenString($str, $start, $end, &$offset)
{
	if ( ($pos=strpos($str, $start, $offset)) === FALSE) return '';
	if ( ($pos1=strpos($str, $end, $pos+strlen($start))) === FALSE) return '';
	$rtns = substr($str, $pos, $pos1-$pos+1);
	$offset = $pos1+strlen($end);
	return $rtns;
}

/**
	取得介於$start, $end之間的字串，但不含$start, $end
*/
function getBetweenInnerString($str, $start, $end, &$offset)
{
	if ( ($pos=strpos($str, $start, $offset)) === FALSE) return '';
	$pos += strlen($start);
	if ( ($pos1=strpos($str, $end, $pos)) === FALSE) return '';
	$rtns = substr($str, $pos, $pos1-$pos);
	$pos1 += strlen($end);
	$offset = $pos1;
	return $rtns;
}


//Action Base Class
class action
{
	var $name;		//action name
	var $paras;		//paras
	var $sessionId;	//breeze session
	var $conn;	//socket to breeze server
	function action($na, $sess='')
	{
		$this->name = $na;
		$this->action = $na;
		$this->sessionId = $sess;
		$this->initParameters();
	}
	
	//$args is named array.
	function setParameters($args)
	{
		if (count($args)==0) return false;
		foreach($args as $key=>$val)
		{
			$this->paras[$key] = $val;
		}
		return true;
	}
	
	function addParameters($key, $val)
	{
		$this->paras[$key] = $val;
	}
	
	function initParameters()
	{
		$this->addParameters('accesskey',BREEZE_ACCESSKEY);
		$this->addParameters('action', $this->action);
	}
	
	function run()
	{
		$this->conn = new URL(BREEZE_SERVER_ADDR, 'POST');
		$this->conn->setPosts($this->paras);
		if (!empty($this->sessionId)) 
			$this->conn->setHTTP_REQUEST_COOKIE($this->sessionId);
		$this->conn->Connection();
	}
}

?>