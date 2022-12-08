<?php
	/**
	 * 批次同意修課
	 *
	 * @since   2004/03/16
	 * @author  ShenTing Lin
	 * @version $Id: review_review.php,v 1.1 2010/02/24 02:39:11 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

	$rv_kind = 'course';
	// 個人
	$rvEnv   = 'personal';
	$RS      = dbGetStMr('WM_review_flow',
						 '`idx`, `username`, `create_time`, `discren_id`, `content`',
						 "`kind`='{$rv_kind}' AND `state`='open' order by `discren_id`, `create_time`",
						 ADODB_FETCH_ASSOC);

	require_once(sysDocumentRoot . '/academic/review/review_main.php');
?>
