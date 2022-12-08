<?php
	/**
	 * 批次同意修課
	 *
	 * @since   2004/03/16
	 * @author  ShenTing Lin
	 * @version $Id: review_review.php,v 1.1 2010/02/24 02:38:59 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '700200100';
	$sysSession->restore();
	if (!aclVerifyPermission(700200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$rv_kind = 'course';
	// 導師環境
	$rvEnv   = 'direct';
	$discren = $sysSession->class_id;
	$RS = dbGetStMr('WM_class_member as M, WM_review_flow as F',
					'F.`idx`, F.`username`, F.`create_time`, F.`discren_id`, F.`content`',
					"M.`class_id`={$discren} and M.`username`=F.`username` and F.`kind`='{$rv_kind}' AND F.`state`='open' order by F.`create_time`",
					ADODB_FETCH_ASSOC);

	require_once(sysDocumentRoot . '/academic/review/review_main.php');
?>
