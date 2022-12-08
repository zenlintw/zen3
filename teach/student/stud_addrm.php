<?php
	/**
	 * 新增帳號
	 * $Id: stud_addrm.php,v 1.1 2010/02/24 02:40:30 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/teach_student.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	$sysSession->cur_func = '400300100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	// 設定車票
	setTicket();
	if (isSet($_GET['cIdx']) && $_GET['cIdx'] == 4) {
		$msgtp = 4;
	}
	else
		$msgtp = min(4,max(1, $_SERVER['argv'][0]));
	
	$sysAccountMaxLen1 = sysAccountMaxLen;
    $sysAccountMinLen1 = sysAccountMinLen;

	$js = <<< BOF

	var sysAccountMaxLen ={$sysAccountMaxLen1};
    var sysAccountMinLen ={$sysAccountMinLen1};

	function trim(val) {
		return val.replace(/^\s+|\s+$/g, '');
	}

	function chkData() {
		var obj = document.getElementById("addFm1");
		if (obj == null) return false;

		if (obj.op.value == 5){
			if (! confirm("{$MSG['confirm_delete'][$sysSession->lang]}")) return false;
		}

		try {
			obj.header.value = trim(obj.header.value);
			/*
			 * 檢查有沒有設定前置字元
			 */
			if (obj.header.value.length <= 0) {
				obj.header.focus();
				throw "{$MSG['js_msg01'][$sysSession->lang]}";
			}

		} catch(ex) {
			alert(ex);
			return false;
		}

		return true;
	}

	function chkData2() {
		if (addFm2.op.value == 5){
			if (! confirm("{$MSG['confirm_delete'][$sysSession->lang]}")) return false;
		}

        /*** account begin ***/
        if (addFm2.userlist.value.length == 0){
		    alert("{$MSG['input_username'][$sysSession->lang]}");
		    addFm2.userlist.focus();
		    return false;
		}

	    return true;
	}

	window.onload=function(){
		var toolbar = document.getElementById('toolbar1').innerHTML;
		document.getElementById('toolbar2').innerHTML = toolbar;
		document.getElementById('toolbar3').innerHTML = toolbar;
		chgTarget();	// in stud_chk_add.php
	};

BOF;

	showXHTML_head_B($MSG['create_account'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/teach/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B('');
	$arry = array( array($MSG['create_serial_account'][$sysSession->lang]  , 'addTable1'),
	               array($MSG['create_discrete_account'][$sysSession->lang], 'addTable2'),
	               array($MSG['import_account'][$sysSession->lang]         , 'addTable3'),
	               array($MSG['msg_page_add'][$sysSession->lang]           , 'addTable4')
	             );
		showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" width="1000"');
			showXHTML_tr_B();
				showXHTML_td_B();
					showXHTML_tabs($arry, $msgtp);
				showXHTML_td_E();
			showXHTML_tr_E();
			showXHTML_tr_B();
				showXHTML_td_B('valign="top" ');
					// 新增連續帳號
					showXHTML_form_B('action="stud_addrm1.php?1" method="post" onsubmit="return chkData()" style="display:inline"', 'addFm1');
					showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="addTable1" ' . ($msgtp == 1 ? '' : 'style="display:none"') . ' class="cssTable"');
						showXHTML_tr_B('class="cssTrEvn"');
							$ticket = md5($sysSession->ticket . $sysSession->school_id . $sysSession->username . 'add');
							showXHTML_input('hidden', 'ticket', $ticket);
							showXHTML_td('colspan="4" class="font01" nowrap', $MSG['create_help01'][$sysSession->lang]);
						showXHTML_tr_E();
						showXHTML_tr_B('class="cssTrHead"');
							showXHTML_td('class="font01"', $MSG['header'][$sysSession->lang]);
							showXHTML_td('class="font01"', $MSG['number'][$sysSession->lang]);
							showXHTML_td('class="font01"', $MSG['tail'][$sysSession->lang]);
							showXHTML_td('class="font01"', $MSG['length'][$sysSession->lang]);
						showXHTML_tr_E();
						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td_B('nowrap');
								showXHTML_input('text', 'header', '', '', 'size="15" class="box02"');
							showXHTML_td_E();
							showXHTML_td_B('class="font01" nowrap');
								echo $MSG['first'][$sysSession->lang];
								showXHTML_input('text', 'first', '1', '', 'size="5" class="box02"');
								echo $MSG['last'][$sysSession->lang];
								showXHTML_input('text', 'last', '100', '', 'size="5" class="box02"');
							showXHTML_td_E();
							showXHTML_td_B('nowrap');
								showXHTML_input('text', 'tail', '', '', 'size="15" class="box02"');
							showXHTML_td_E();
							showXHTML_td_B('class="font01" nowrap');
								// showXHTML_input('text', 'len', '3', '', 'size="10" class="box02"');
								showXHTML_input('select', 'len', array_range(1,5), 3, 'class="box02"');
							showXHTML_td_E($MSG['len'][$sysSession->lang]);
						showXHTML_tr_E();
						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td_B('colspan="4" id="toolbar1"');
								showXHTML_input('hidden', 'op');
								showXHTML_input('submit', '', $MSG['add_student'][$sysSession->lang], '', 'class="button01" style="width: 278px;" onclick="this.form.op.value=1;"');
								showXHTML_input('submit', '', $MSG['add_auditor'][$sysSession->lang], '', 'class="button01" style="width: 278px;" onclick="this.form.op.value=2;"'); echo '<br />';
								showXHTML_input('submit', '', $MSG['aud2stu'][$sysSession->lang],     '', 'class="button01" style="width: 278px;" onclick="this.form.op.value=3;"');
								showXHTML_input('submit', '', $MSG['stu2aud'][$sysSession->lang],     '', 'class="button01" style="width: 278px;" onclick="this.form.op.value=4;"'); echo '<br />';
								showXHTML_input('submit', '', $MSG['remove'][$sysSession->lang],      '', 'class="button01" style="width: 278px;" onclick="this.form.op.value=5;"');
								showXHTML_input('reset',  '', $MSG['clean'][$sysSession->lang],       '', 'class="button01" style="width: 278px;"');
							showXHTML_td_E();
						showXHTML_tr_E();
					showXHTML_table_E();
					showXHTML_form_E();

					// 新增不規則帳號
					showXHTML_form_B('action="stud_addrm1.php?2" method="post" onsubmit="return chkData2()" style="display:inline"', 'addFm2');
					showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="addTable2" ' . ($msgtp == 2 ? '' : 'style="display:none"') . '" class="cssTable"');
						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td_B('valign="top" width="20%"');
								$ticket = md5('AddManual' . $sysSession->ticket . $sysSession->school_id . $sysSession->username);
								showXHTML_input('hidden', 'ticket', $ticket, '', '');
								showXHTML_input('textarea', 'userlist', '', '', 'cols="40" rows="20" class="cssInput"');

							showXHTML_td_E();
							showXHTML_td_B('valign="top" width="70%"');
								echo $MSG['addnormal_help'][$sysSession->lang];
							  	showXHTML_input('textarea', '', "userid1\nuserid2", '', 'cols="25" rows="5" disabled class="cssInput"');
							showXHTML_td_E();
						showXHTML_tr_E();
						showXHTML_tr_B('class="cssTrOdd"');
						  showXHTML_td('id="toolbar2" colspan="2"');
						showXHTML_tr_E();
					showXHTML_table_E();
					showXHTML_form_E();

					// 匯入帳號
					showXHTML_form_B('action="stud_addrm2.php" method="post" enctype="multipart/form-data" style="display:inline"', 'addFm3');
					showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="addTable3" ' . ($msgtp == 3 ? '' : 'style="display:none"') . ' class="cssTable"');
						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td_B('valign="top"');
								$ticket = md5($sysSession->ticket . 'AddImport' . $sysSession->school_id . $sysSession->username);
								showXHTML_input('hidden', 'ticket', $ticket, '', '');
								showXHTML_input('file', 'cvsfile', '', '', 'size="27" class="box02"');
							showXHTML_td_E();
							showXHTML_td('class="font01"', $MSG['create_help04'][$sysSession->lang]);
						showXHTML_tr_E();
						
						// 讓使用者選擇匯入檔案的編碼 #bug 963 Begin
						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td_B('');
								$file_type = array('Big5'	=>	$MSG['msgBig5'][$sysSession->lang],
								                   'GB2312'	=>	$MSG['msgGB2312'][$sysSession->lang],
								                   'en'		=>	$MSG['msgen'][$sysSession->lang],
								                   //	先不處理日文 'EUC-JP'	=>	$MSG['msgEUC_JP'][$sysSession->lang],
								                   'UTF-8'	=>	$MSG['msgUTF-8'][$sysSession->lang]);
								showXHTML_input('select', 'file_format', $file_type, ($sysSession->lang == 'user_define' ? 'UTF-8' : $sysSession->lang), 'class="cssInput" style="width: 158px"');
							showXHTML_td_E();
							showXHTML_td('', $MSG['import_format_help'][$sysSession->lang]);
						showXHTML_tr_E();
						// 讓使用者選擇匯入檔案的編碼 #bug 963 End
		
						showXHTML_tr_B('class="cssTrEvn"');
						  showXHTML_td('colspan="2" id="toolbar3"');
						showXHTML_tr_E();
					showXHTML_table_E();
					showXHTML_form_E();

					// 選取帳號
					showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0" id="addTable4" ' . ($msgtp == 4 ? '' : 'style="display:none"'));
						showXHTML_tr_B('');
							showXHTML_td_B('valign="top"');
								require_once(sysDocumentRoot . '/teach/student/stud_chk_add.php');
							showXHTML_td_E();
						showXHTML_tr_E();
					showXHTML_table_E();
				showXHTML_td_E();
			showXHTML_tr_E();

		showXHTML_table_E();
	showXHTML_body_E();
?>
