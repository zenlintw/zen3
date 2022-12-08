<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *      Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	 *                                                                                                *
	 *      Programmer: Wiseguy Liang                                                                 *
	 *      Creation  : 2003/08/10                                                                    *
	 *      work for  :                                                                               *
	 *      work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	 *                                                                                                *
	 **************************************************************************************************/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '2000200100';
	$sysSession->restore();
	if (!aclVerifyPermission(2000200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$tid = intval($_SERVER['argv'][0]);
	$gid = intval($_SERVER['argv'][1]);
	$owners = $sysSession->course_id.'_'.$tid.'_'.$gid;
	list($rid) = dbGetStSr('WM_chat_setting', 'rid', "owner='{$owners}'", ADODB_FETCH_NUM);
	if(empty($rid)){
		// 取得小組名稱
		list($captions) = dbGetStSr('WM_student_group', 'caption', "course_id={$sysSession->course_id} and group_id=$gid and team_id=$tid", ADODB_FETCH_NUM);
		// 取得rid
		$newrid = uniqid('');

		dbNew('WM_chat_setting','rid,owner,title', "'$newrid','$owners','$captions'");
		$rid = $newrid;
		wmSysLog($sysSession->cur_func, $sysSession->course_id , $rid , 0, 'auto', $_SERVER['PHP_SELF'], 'new chat setting');
	}
	// 若有$rid，則將$rid存進session中，若無則顯示「Page Not Found !」
	if (!empty($rid))
	{
		dbSet('WM_session', "`room_id`='{$rid}'", "idx='{$_COOKIE['idx']}'");
		echo '<script>';
		echo 'window.location.replace("/learn/chat/index.php");';
		echo '</script>';
	}
	else
	{
		echo 'Page Not Found !';
	}
?>
