<?php
	/**
	 * 變更環境
	 *
	 * @since   2003/11/04
	 * @author  ShenTing Lin
	 * @version $Id: chgenv.php,v 1.1 2010/02/24 02:40:18 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	/**
	 * 結束程式
	 **/
	function sysError() {
		header("Content-type: text/xml");
		echo '<' , '?xml version="1.0" encoding="UTF-8" ?' , '>' , "\n",
		     '<manifest></manifest>';
		exit;
	}

	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			sysError();
		}

		$envs = getNodeValue($dom, 'env');
		switch ($envs) {
			// 教室
			case 'classroom': $env = 'learn';   break;
			// 老師
			case 'teacher'  : $env = 'teach';   break;
			// 導師
			case 'director' : $env = 'direct';  break;
			// 管理者
			case 'manager'  : $env = 'acadmic'; break;
				break;
			default:
				$env = 'learn';
		}
		$sysSession->env = $env;
		$sysSession->restore();
		
		header("Content-type: text/xml");
		echo '<' , '?xml version="1.0" encoding="UTF-8" ?' , '>' , "\n" ,
		     '<manifest></manifest>';
		
		echo $xmlStrs;
	} else {
		sysError();
	}

?>
