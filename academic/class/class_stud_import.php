<?php
	/**
	 * 功能名稱 匯入班級成員
	 * @since   2005/02/21
	 * @author  Amm Lee
 	 * @version $Id: class_stud_import.php,v 1.1 2010/02/24 02:38:14 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/class_stud_import.php');

	$sysSession->cur_func='2400500100';
	$sysSession->restore();
	if (!aclVerifyPermission(2400500100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}


$js = <<< BOF
	function add_audit(){
		var obj = document.studImportForm;

		if (obj.csvfile.value.length == 0){
			alert("{$MSG['must_select_filename'][$sysSession->lang]}");
			return false;
		}
		obj.submit();
	}

BOF;
	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");

	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B('leftmargin="7" topmargin="7"');
		$ary = array();
		$ary[] = array($MSG['title'][$sysSession->lang], 'tabs');
		showXHTML_tabFrame_B($ary, 1, 'studImportForm', '', 'action="class_stud_import1.php" method="post" enctype="multipart/form-data" style="display:inline" onsubmit="return add_audit()"', false);
			showXHTML_table_B('id ="mainTable" width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="left" nowrap="nowrap"', $MSG['td_title'][$sysSession->lang]);

					showXHTML_td_B('align="left" nowrap="nowrap"');
						$ticket = md5($sysSession->ticket . 'AddImport' . $sysSession->school_id . $sysSession->username);
						showXHTML_input('hidden', 'ticket', $ticket, '', '');
						showXHTML_input('file', 'cvsfile', '', '', 'id="csvfile" size="27" class="cssInput"');
					showXHTML_td_E();

					showXHTML_td('align="left" nowrap="nowrap"', $MSG['cvs_file_comment'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 讓使用者選擇匯入檔案的編碼 #bug 963 Begin
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['import_format_title'][$sysSession->lang]);
					showXHTML_td_B('');
						$file_type = array(
											'Big5'	=>	$MSG['Big5'][$sysSession->lang],
											'GB2312'	=>	$MSG['GB2312'][$sysSession->lang],
											'en'		=>	$MSG['en'][$sysSession->lang],
											//	先不處理日文 'EUC-JP'	=>	$MSG['EUC-JP'][$sysSession->lang],
											'UTF-8'	=>	$MSG['UTF-8'][$sysSession->lang],
											);
						showXHTML_input('select', 'file_format', $file_type, ($sysSession->lang == 'user_define' ? 'UTF-8' : $sysSession->lang), 'class="cssInput" style="width: 158px"');
					showXHTML_td_E();
					showXHTML_td('', $MSG['import_format_help'][$sysSession->lang]);
				showXHTML_tr_E('');
				// 讓使用者選擇匯入檔案的編碼 #bug 963 End

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="left" nowrap="nowrap"', $MSG['td_title2'][$sysSession->lang]);

					showXHTML_td_B('valign="top" align="left" nowrap="nowrap"');
						$class_RS = dbGetStMr('WM_class_main','class_id,caption','class_id > 1000000', ADODB_FETCH_ASSOC);
						if ($class_RS)
						{
							$class_array = array();
							while ($class_RS1 = $class_RS->FetchRow()){
								$lang = unserialize($class_RS1['caption']);
								$class_array[$class_RS1['class_id']] = $lang[$sysSession->lang];
							}
							showXHTML_input('radio', 'class_id', $class_array, '', '', '<br />');
						}
					showXHTML_td_E();

					showXHTML_td('align="left" nowrap="nowrap"', $MSG['td_dep_comment'][$sysSession->lang]);
				showXHTML_tr_E('');

				// 匯入
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('align="center" colspan="4" nowrap="nowrap"');

						// 判斷目前WM_class_main 有無班級
						list($class_num) = dbGetStSr('WM_class_main','count(*) as num','class_id > 1000000', ADODB_FETCH_NUM);
						$btn_hidden = ($class_num == 0) ? 'disabled="disabled"' : '';
						showXHTML_input('button', '', $MSG['btn_import'][$sysSession->lang] , '', 'class="cssBtn" onclick="add_audit()" ' . $btn_hidden);
						showXHTML_input('reset', '', $MSG['btn_cancel'][$sysSession->lang] , '', 'class="cssBtn" ');
					showXHTML_td_E();
				showXHTML_tr_E('');

			showXHTML_table_E();
		showXHTML_tabFrame_E();
	showXHTML_body_E('');

?>
