<?php
	/**
	 * 新增帳號
	 * $Id: stud_account.php,v 1.1 2010/02/24 02:38:44 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '400300100';
	$sysSession->restore();
	if (!aclVerifyPermission(400300100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 日期
	$date = getdate();

	$msgtp = $_POST['msgtp'] ? $_POST['msgtp'] : ($_GET['msgtp'] ? $_GET['msgtp'] : 1);
	$msgtp = min(4, max(1, $msgtp));

	if ($msgtp == 4)
		include_once(sysDocumentRoot . '/lang/verify_mail.php');
	else
		include_once(sysDocumentRoot . '/lang/stud_account.php');


	/**
	 * 安全性檢查
	 *     1. 身份的檢查
	 *     2. 權限的檢查
	 *     3. .....
	 **/

	// 設定車票
	setTicket();

	$js = <<< BOF
    /*
    * 新增連續帳號 &　新增不規則帳號　&　新增匯入帳號 & 編輯註冊通知信
    */
    function chgHistory(val) {
		var obj = document.getElementById("actFm");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		obj.msgtp.value = val;
		obj.submit();
	}
BOF;

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B($load_date);
	$arry[] = array($MSG['create_serial_account'][$sysSession->lang]  , 'addTable1', 'chgHistory(1);');
	$arry[] = array($MSG['create_discrete_account'][$sysSession->lang], 'addTable2', 'chgHistory(2);');
	$arry[] = array($MSG['import_account'][$sysSession->lang]         , 'addTable3', 'chgHistory(3);');
	$arry[] = array($MSG['edit_register_mail'][$sysSession->lang]     , 'addTable4', 'chgHistory(4);');

	switch ($msgtp){
	    case 1:
	        $form_id = 'addFm1';
	        $form_extra = 'action="stud_account1.php" method="post" onsubmit="return chkData()" style="display:inline"';
	        break;
        case 2:
            $form_id = 'addFm2';
	        $form_extra = 'action="stud_account2.php" method="post" onsubmit="return chkData2()" style="display:inline"';
	        break;
	    case 3:
	        $form_id = 'addFm3';
	        $form_extra = 'action="stud_import.php" method="post" enctype="multipart/form-data" onsubmit="return checkfile();" style="display:inline"';
	        break;
        case 4:
	        $form_id = 'f1';
	        $form_extra = 'action="' . $_SERVER['PHP_SELF'] .'" method="post" enctype="multipart/form-data"  style="display:inline"';
	        break;
	}

        showXHTML_tabFrame_B($arry, $msgtp, $form_id, '', $form_extra, '');
            // 個人 (begin)
            switch ($msgtp){
                case 1:     // 新增連續帳號
                    include_once(sysDocumentRoot . '/academic/stud/stud_addserial.php');
                    break;
                case 2:     // 新增不規則帳號
                    include_once(sysDocumentRoot . '/academic/stud/stud_addabnormal.php');
                    break;
                case 3:     // 新增匯入帳號
                    include_once(sysDocumentRoot . '/academic/stud/stud_addimport.php');
                    break;
                case 4:     // 編輯新增帳號通知信
                    include_once(sysDocumentRoot . '/academic/stud/stud_account_mail.php');
                    break;
            }
            // 個人 (end)
        showXHTML_tabFrame_E();

        showXHTML_form_B('action="' . $_SERVER['PHP_SELF']. '" method="post" enctype="multipart/form-data" style="display:none"', 'actFm');
	        showXHTML_input('hidden', 'msgtp', $msgtp, '', '');
	        showXHTML_input('hidden', 'deal_pages', 'import', '', '');
    	showXHTML_form_E();

	showXHTML_body_E();
?>
