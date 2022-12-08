<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/02/11                                                            *
	 *		work for  : copy   Item                                                           *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	//ACL begin
	if (QTI_which == 'exam') {
		$sysSession->cur_func='1600100200';
	}
	else if (QTI_which == 'homework') {
		$sysSession->cur_func='1700100200';
	}
	else if (QTI_which == 'questionnaire') {
		$sysSession->cur_func='1800100200';
	}
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
			
	}
	//ACL end

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	// 判斷 ticket 是否正確 (開始)
	$ticket = md5($_POST['gets'] . sysTicketSeed . $course_id . $_COOKIE['idx']);
	if ($ticket != $_POST['ticket']) {
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'Illegal Access!');
	   die('Illegal Access !');
	}
	// 判斷 ticket 是否正確 (結束)
	if (!ereg('^[A-Z0-9_,]+$', $_POST['lists'])) {	// 判斷 ident 序列格式
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], 'ID format error:'.$_POST['lists']);
	   die('ID format error !'); 
	}

    /* 取得夾檔位置 begin */
	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;

	if ($topDir == 'academic')
		$save_uri = sprintf('/base/%05d/%s/Q/',
		  					 $sysSession->school_id,
		  					 QTI_which);
	else
		$save_uri = sprintf('/base/%05d/course/%08d/%s/Q/',
		  					 $sysSession->school_id,
		  					 $sysSession->course_id,
		  					 QTI_which);
	/* 取得夾檔位置 end */

	$t = split('[. ]', microtime());
	$ident = sprintf('WM_ITEM1_%s_%u_%s_', sysSiteUID, $course_id, $t[2]);
	$count = intval(substr($t[1],0,6));

	$RS = dbGetStMr('WM_qti_' . QTI_which . '_item', '*', 'ident in (\'' . str_replace(',', "','", $_POST['lists']) . "')", ADODB_FETCH_ASSOC);

	while(!$RS->EOF){
	    $old_id = $RS->fields['ident'];
		$RS->fields['ident'] = $ident . ($count++);
		$RS->fields['title'] = '[NEW] ' . $RS->fields['title'];
		$RS->fields['content'] = str_replace($old_id, $RS->fields['ident'], $RS->fields['content']);
		$sysConn->Execute($sysConn->GetInsertSQL($RS, $RS->fields, false));
		if ($sysConn->ErrorNo() == 0 && $RS->fields['attach'] && chdir(sysDocumentRoot . $save_uri))
		{
		    @exec("cp -Rf '$old_id' '{$RS->fields['ident']}'");
		}
		$RS->MoveNext();
	}

	header('Location: item_maintain.php?' . $_GET['gets']);
?>
