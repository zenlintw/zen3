<?php
	/**
	 * 成員資料查詢
	 *
	 * @since   2004/07/01
	 * @author  ShenTing Lin
	 * @version $Id: member_detail.php,v 1.1 2010/02/24 02:38:58 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/direct_member_list.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');

	$sysSession->cur_func = '300100600';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$DIRECT_MEMBER = true;
	$uri_target = 'member_list.php';
	$direct_js = <<< BOF
	/**
    * 回 刪除不規則帳號
    **/
    function go_list() {
		var obj = document.getElementById("fmAction");
		if (obj == null) {
			window.location.replace("{$uri_target}");
		} else {
			obj.action = "{$uri_target}";
			obj.submit();
		}
	}
BOF;

	$dt_page_no = isset($_POST['page']) ? intval($_POST['page']) : 1;
	$dt_roles   = isset($_POST['roles']) ? trim($_POST['roles']) : 'all';
	$dt_kind    = isset($_POST['kind']) ? trim($_POST['kind']) : '';
	$dt_keyword = Filter_Spec_char(stripslashes(trim($_POST['keyword'])), 'search');
	$dt_lsList  = trim($_POST['lsList']);
 	require_once(sysDocumentRoot . '/academic/stud/stud_query1.php');
?>
