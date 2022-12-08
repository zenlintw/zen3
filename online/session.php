<?php
	/**
	 * online
	 *
	 * @since   2003/10/28
	 * @author  ShenTing Lin
	 * @version $Id: session.php,v 1.2 2010/02/25 06:23:27 small Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	
#=========== function ============
	function updateSiteAccTime($t)
	{
		global $sysSession;
		dbSet('WM_sch4user', "`total_time`=IFNULL(`total_time`, 0) + {$t}", "school_id={$sysSession->school_id} AND username='{$sysSession->username}'");
	}
#=========== main ================
	// 60 為一小時，系統預設TimeOut時間機制2小時
	// 假如系統已經有設定的話，就沿用系統的
	$sysTimeOutLimit = 120;

	/**
	 * 結束程式
	 **/
	function sysError() {
		echo '<' , '?xml version="1.0" encoding="UTF-8" ?' , '>' , "\n",
		     '<manifest></manifest>';
		exit;
	}

	/**
	 * 取得是否有新的訊息
	 * @param
	 * @return integer : 目前你有幾筆未讀訊息
	 **/
	function hasNewMessage() {
		global $sysSession;
		list($rv) = dbGetStSr('WM_im_setting', '`recive`', "`username`='{$sysSession->username}'", ADODB_FETCH_NUM);
		if ($rv == 'N') return 0;
		list($total) = dbGetStSr('WM_im_message', 'count(*) as total',
			"`username`='{$sysSession->username}' AND `sorder`=0 AND `reciver`='{$sysSession->username}' AND `saw`='N'", ADODB_FETCH_NUM);
		return $total;
	}
	
	header("Content-type: text/xml");
	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			sysError();
		}
		
		//取得上一個touch時間，作為上站累積時間的base
		list($basetime) = dbGetStSr('WM_session','touch',"idx='{$_COOKIE['idx']}'", ADODB_FETCH_NUM);
		$basetime = $sysConn->UnixTimeStamp($basetime);
		$now_time = time();
		if ($now_time > $basetime)
		{
			updateSiteAccTime($now_time-$basetime);
		}
		// 更新自己的 Session
		dbSet('WM_session', 'chance=0', "idx='{$_COOKIE['idx']}'");

		// 取得線上人數
		$csid = intval($sysSession->course_id);
		if (empty($csid)) {
			$RS = dbGetStSr('WM_session', "count(*) as school, sum(course_id IS NULL or course_id = 0) as course", 'chance<3', ADODB_FETCH_ASSOC);
		} else {
			$RS = dbGetStSr('WM_session', "count(*) as school, sum(if(course_id={$csid},1,0)) as course", 'chance<3', ADODB_FETCH_ASSOC);
		}
		
		// 線上訊息
		// 聊天室
		// 學習溫度計 (WM2 有)

		echo '<' , '?xml version="1.0" encoding="UTF-8" ?' , '>' , "\n" , 
		     '<manifest>' ,
			      '<time>'    , date('A h:i')   , '</time>'    ,
			      '<school>'  , $RS['school']   , '</school>'  ,
			      '<course>'  , $RS['course']   , '</course>'  ,
			      '<message>' , hasNewMessage() , '</message>' ,
			      '<datetime>', date('Y/m/d H:i:s'), '</datetime>',
		     '</manifest>';
	} else {
		sysError();
	}

?>
