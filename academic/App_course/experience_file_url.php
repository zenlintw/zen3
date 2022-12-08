<?php
	/**
	 * 列出檔案
	 *
	 * @since   2012/08/20
	 * @author  ShenTing Lin
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
//	require_once(sysDocumentRoot . '/lib/interface.php');

	define('ADM_EXPERIENCE', 'video');
	$baseUri = sprintf('/base/%05d/door/APP/wmmedia/%s', $sysSession->school_id, ADM_EXPERIENCE);
	include_once(sysDocumentRoot . '/teach/course/listfiles_app.php');