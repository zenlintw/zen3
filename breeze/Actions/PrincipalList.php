<?php
//Action : PrincipalList

require_once(BREEZE_PHP_DIR . '/Actions/Action.php');

class PrincipalList extends Action
{
	function PrincipalList($sess)
	{
		parent::action('principal-list', $sess);
	}
	
	function isUserIncluded($user)
	{
		if (strpos($this->conn->HTTP_RESPONSE_BODY, '<login>'.$user) === FALSE)
			return false;
		return ture;
	}
	
	function getSomeonePid($user)
	{
		$pid = 0;
		$arr = explode("<principal",$this->conn->HTTP_RESPONSE_BODY);
		for($i=0, $size=count($arr); $i<$size; $i++)
		{
			if ( ($pos=strpos($arr[$i],'<login>'.$user)) === false) continue;
			if (ereg("principal-id=\"([0-9]{1,})\"",$arr[$i], $args))
			{
				$pid = $args[1];
			}
		}
		return $pid;
	}
}

?>