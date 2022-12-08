<?php
	/**
	 * 批次同意修課
	 *
	 * @since   2004/03/16
	 * @author  ShenTing Lin
	 * @version $Id: review_review.php,v 1.1 2010/02/24 02:38:58 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$sysSession->cur_func = '700200100';
	$sysSession->restore();
	if (!aclVerifyPermission(700200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 取得所有成員
	$caid = checkClassID($sysSession->class_id);
	if ($caid === false) die();

	$classMember = dbGetCol('WM_class_member', '`username`', "`class_id`={$caid}");

	$rv_kind = 'course';
	// 導師環境
	// 需要有課程編號
	$rvEnv = 'direct';
	// $discren = $sysSession->class_id;
	$RS = dbGetStMr('WM_review_flow', '`idx`, `username`, `create_time`, `discren_id`, `content`', "`kind`='{$rv_kind}' AND `state`='open' order by `create_time`", ADODB_FETCH_ASSOC);

	require_once(sysDocumentRoot . '/academic/review/review_main.php');
?>
