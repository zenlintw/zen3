<?php
	/**
	 * 成員列表
	 *
	 * @since   2004/06/30
	 * @author  ShenTing Lin
	 * @version $Id: member_list.php,v 1.1 2010/02/24 02:38:58 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/direct/enroll/enroll_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '300100600';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 儲存設定
	$objAssoc->restore();
	$objAssoc->setValues('function', 'member', 1);
	if (isset($_POST['courses'])) {
		// 儲存課程資料
		storeCourseData();
		// 註記
		$objAssoc->setValues('course_other', 'wiseguy', 'back');
	}
	$objAssoc->store();

	// 回復之前的設定
	if (isset($_POST['wiseguy']) && (trim($_POST['wiseguy']) == 'back')) {
		if (count($objAssoc->assoc_ary['member_list']) > 0) {
			$_POST['lsList']  = implode(',', $objAssoc->assoc_ary['member_list']);
		}

		// $_POST['user']   = implode(',', $objAssoc->assoc_ary['member']);
		$_POST['msgtp']   = $objAssoc->getValues('member_other', 'msgtp');
		$_POST['page']    = $objAssoc->getValues('member_other', 'page');
		$_POST['roles']   = $objAssoc->getValues('member_other', 'roles');
		$_POST['kind']    = $objAssoc->getValues('member_other', 'kind');
		$_POST['keyword'] = $objAssoc->getValues('member_other', 'keyword');
		$_POST['wiseguy'] = '';
	}

	$ASSIGN_COURSE = true;
	$enroll_js = <<< BOF
	var MSG_NO_USER = "{$MSG['msg_no_user'][$sysSession->lang]}";
	function assign(tURL) {
		var ary = new Array();
		var obj = document.getElementById("fmAction");
		if (obj == null) return false;
		var nodes = document.getElementsByTagName("input");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		for (var i = 0, j = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			if (nodes[i].checked) ary[ary.length] = nodes[i].value;
		}
		obj.user.value = ary.toString();
		/*
		// 假如設定只有本頁有勾選才行，打啟本段檢查，關閉下面的的檢查，並且修改 enroll_confirm.php
		if (ary.length <= 0) {
			alert(MSG_NO_USER);
			return false;
		}
		*/
		// if (ary.length <= 0) return false;
		// 同步 checkbox (Begin)
		ary = new Array();
		for (var i in lsObj) {
			if (lsObj[i]) ary[ary.length] = i;
		}
		// 另一個檢查的地方
		if (ary.length <= 0) {
			alert(MSG_NO_USER);
			return false;
		}
		obj.lsList.value = ary.toString();
		// 同步 checkbox (End)
		if ((typeof(tURL) == "undefined") || (tURL == "")) {
			obj.action = "enroll_course_list.php";
		} else {
			obj.action = tURL;
		}
		obj.submit();
	}

	function goHelp() {
		window.location.replace("enroll_help.php");
	}

	function goReview() {
		assign("enroll_confirm.php");
	}
BOF;

	require_once(sysDocumentRoot . '/direct/member/member_list.php');
?>
