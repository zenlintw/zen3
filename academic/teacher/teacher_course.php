<?php
	/**************************************************************************************************
	*                                                                                                 *
	*               Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	*                                                                                                 *
	*               Programmer: Amm Lee                                                                       *
	*               Creation  : 2003/09/23                                                                    *
	*               work for  :  基本資料 & 修課記錄 &　學習成果                                                                      *
	*               work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	*       @version:$Id: teacher_course.php,v 1.1 2010/02/24 02:38:48 saly Exp $                                                                                          *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot .'/lib/acl_api.php');

	$sysSession->cur_func = '1500400100';
	$sysSession->restore();
	if (!aclVerifyPermission(1500400100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$ACADEMIC_TEACHER = true;
	$uri_target = 'teacher_show_list.php';
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
	require_once(sysDocumentRoot . '/academic/stud/stud_query1.php');
?>
