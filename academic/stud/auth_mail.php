<?php
	/**
	 * 審核通知信
	 * $Id: auth_mail.php,v 1.1 2010/02/24 02:38:44 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/verify_mail.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '2400300500';
	$sysSession->restore();
	if (!aclVerifyPermission(2400300500, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$msgtp = $_POST['msgtp'] ? $_POST['msgtp'] : ($_GET['msgtp'] ? $_GET['msgtp'] : 1);
	$msgtp = min(2, max(1, $msgtp));

	$js = <<< BOF
	/*
	* 核可 及 不核可通知信
	*/
	function chgHistory(val) {
		var obj = document.getElementById("actFm");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		obj.msgtp.value = val;
		obj.submit();
	}

	var col = '';
	function add_att(){
		var obj= document.getElementById("att_file");
		// var IH = obj.innerHTML;
		var cnt = 1;	// 計算有幾個 (number)

		col = (col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';

		var IH = '<span><br><input type="file" name="uploads[]" id="uploads[]" class="cssInput"> '+
				'<input type="button" class="cssBtn" value="{$MSG['del_att_file'][$sysSession->lang]}"'+
				' onclick="delMe(this);"><br></span>';

		obj.insertAdjacentHTML('beforeend', IH);
	}

	function delMe(obj){
			obj.parentNode.parentNode.removeChild(obj.parentNode);
	}

	function delFile(file_name){
		document.addFm.mode.value = 'delfile';
		document.addFm.file_name.value = file_name;
		document.addFm.submit();
	}

	function chk_this(){
		document.addFm.mode.value = 'save';
		document.addFm.submit();
	}
BOF;

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();

		$arry = array();
		$arry[] = array($MSG['edit_allow'][$sysSession->lang] , 'addTable1', 'chgHistory(1);');
		$arry[] = array($MSG['edit_forbid'][$sysSession->lang], 'addTable2', 'chgHistory(2);');

		switch ($msgtp){
			case 1:
				$form_extra = 'action="verify_mail1.php" method="post" enctype="multipart/form-data" style="display:inline"';
				break;
			case 2:
				$form_extra = 'action="fail_mail1.php" method="post" enctype="multipart/form-data" style="display:inline"';
				break;
		}

		showXHTML_tabFrame_B($arry, $msgtp, 'addFm', '', $form_extra, '');
			// 個人 (begin)
			switch ($msgtp){
				case 1:     // 核可通知信
					include_once(sysDocumentRoot . '/academic/stud/verify_mail.php');
					break;
				case 2:     // 不核可通知信
					include_once(sysDocumentRoot . '/academic/stud/fail_mail.php');
					break;
			}
			// 個人 (end)
		showXHTML_tabFrame_E();

		showXHTML_form_B('action="'.$_SERVER['PHP_SELF'].'" method="post" enctype="multipart/form-data" style="display:none"', 'actFm');
			showXHTML_input('hidden', 'msgtp', $msgtp, '', '');
		showXHTML_form_E();

	showXHTML_body_E();
?>
