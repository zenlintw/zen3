<?php
	/**
	 * 審核列表
	 *
	 * @since   2004/03/25
	 * @author  ShenTing Lin
	 * @version $Id: review_review.php,v 1.1 2010/02/24 02:38:41 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '400400100';
	$sysSession->restore();
	if (!aclVerifyPermission(400400100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$rv_kind = 'course';
	// 管理者環境
	// 只需要檢查是不是具備管理者的身分
	$rvEnv = 'academic';
	$RS = dbGetStMr('WM_review_flow', '`idx`, `username`, `create_time`, `discren_id`, `content`', "`kind`='{$rv_kind}' AND `state`='open' order by `discren_id`, `create_time`", ADODB_FETCH_ASSOC);

	require_once(sysDocumentRoot . '/academic/review/review_main.php');
?>
