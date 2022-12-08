<?php
	/**
	 * 下載聊天室中的檔案
	 *
	 * @since   2004/03/07
	 * @author  ShenTing Lin
	 * @version $Id: chat_file.php,v 1.1 2010/02/24 02:39:05 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/learn/chat/chat_lib.php');
	require_once(sysDocumentRoot . '/lang/chatroom.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	$sysSession->cur_func='2000200100';
	$sysSession->restore();
	if (!aclVerifyPermission(2000200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	// 檢查有無參與聊天
	list($cnt) = dbGetStSr('WM_chat_session', 'count(*)', "`rid`='{$sysSession->room_id}' AND `username`='{$sysSession->username}'", ADODB_FETCH_NUM);
	if ($cnt <= 0) {
	   wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], '拒絕存取!');
	   die($MSG['access_deny'][$sysSession->lang]);
	}

	// 去掉不該有的字串
	$sary = array('\\', '/');
	$rary = array('', '');
	$name = str_replace($sary, $rary, trim($_GET['real']));

	// 取得檔案存放的目錄
	$dir  = getChatPath();
	$filename = realpath($dir . $name);
	$realpath = str_replace('\\', '/', dirname($filename)) . '/';
	$dir = ereg_replace('/+', '/', $dir);

    /*
	if ($dir != $realpath) {
	    wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], '檔案路徑錯誤');
		echo $MSG['msg_path_error'][$sysSession->lang];
		exit;
	}
    */
	if (file_exists($filename)) {
		$leng = filesize($filename);
		if (empty($leng)) {
		    wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 3, 'auto', $_SERVER['PHP_SELF'], '檔案不存在:'.$filename);
			echo $MSG['msg_file_not_exist'][$sysSession->lang];
			exit;
		}

		$name = trim($_GET['name']);
		$code = mb_detect_encoding($name, "auto");
		if (empty($code)) {
			// $name = rawurldecode($name);
			$name = mb_convert_encoding($name, 'UTF-8', $sysSession->lang);
			$name = str_replace('\\', '', $name); // 為了去掉 Big5 中「功」後面多出來的「\」
			$name = mb_convert_encoding($name, $sysSession->lang, 'UTF-8');
		} else {
			$name = rawurlencode($name);
		}
		// $name = iconv('UTF-8', $sysSession->lang, $name);
		// $name = mb_convert_encoding($name, 'UTF-8', $sysSession->lang);

        while (@ob_end_clean());
		
        header('Cache-control: no-cache, no-store, private, must-revalidate, post-check=0, pre-check=0');
		header('pragma:no-cache');
		header('expires:0');
		header('Content-transfer-encoding: binary');
		header("Content-Disposition: attachment; filename={$name}\n");
		header("Content-Type: application/octet-stream; name={$name}\n");
		header('Accept-Ranges: bytes');
        
		readfile($filename);
	} else {
	    wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 3, 'auto', $_SERVER['PHP_SELF'], '檔案不存在:'.$filename);
		echo $MSG['msg_file_not_exist'][$sysSession->lang];
	}
?>
