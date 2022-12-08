<?php
	/**
	 * 管理者的環境
	 * $Id: sysbar.php,v 1.1 2010/02/24 02:38:20 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/index.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	if (!aclVerifyPermission(700400300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}

	// 檢查權限
	$level = getAdminLevel($sysSession->username);
	if ($level <= 0) {
	    wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], 'not_admin!');
		die($MSG['not_admin'][$sysSession->lang]);
	}

	/**
	 * 檢查課程的編號是否符合規定
	 **/
	$temp_csid = sysDecode(trim($_POST['csid']));
	if (($csid = checkCourseID($temp_csid)) === false)
		die($MSG['tabs_deny_ip'][$sysSession->lang]);
	
	list($caption) = dbGetStSr('WM_term_course', 'caption', "course_id={$csid}", ADODB_FETCH_NUM);
	$lang          = unserialize($caption);
	$csname        = $lang[$sysSession->lang];

	$sysSession->course_id = $csid;
	$sysSession->course_name = $csname;
	$sysSession->restore();

	/**
	 * 展開或隱藏 Frame
	 * FrameExpand(val, resize, extra);
	 * @pram val : 展開或隱藏
	 *         0 : 隱藏
	 *         1 : 展開
	 *         2 : 自訂，自己決定要顯示多大
	 * @pram resize : 要不要捲動軸
	 *         true  : 可以變動 frame 的大小
	 *         false : 不可變動 frame 的大小
	 *
	 **/
	$js = <<< BOF
	function FrameExpand(val, resize, extra) {
		var obj = null;
		obj = document.getElementById("catalog");
		if (obj != null) {
			if (obj.noResize == resize) obj.noResize = !resize;
		}

		obj = document.getElementById("workarea");
		if (obj != null) {
			switch (val) {
				case 0 : obj.cols = "0,*"; break;
				case 2 : obj.cols = extra + ",*"; break;
				default:
					obj.cols = "200,*";
			}
		}
	}

	window.onload = function () {
		chkBrowser();
		window.focus();
	};
BOF;

	showXHTML_head_B($sysSession->school_name . ' - ' . $sysSession->course_name);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline' , $js);
	showXHTML_head_E('');
	echo <<< BOF
<frameset cols="0,200" framespacing="3" frameborder="Yes" border="3" id="workarea" bordercolor="#FFFFFF">
	<frame src="sysbar_tools.php" name="catalog" id="catalog" frameborder="No" scrolling="Yes">
	<frame src="sysbar_edit_teach.php" name="main" id="main" frameborder="No" scrolling="Auto">
	<noframes>
		<body>
			<p>{$MSG['not_support'][$sysSession->lang]}</p>
		</body>
	</noframes>
</frameset>
</html>
BOF;
?>
