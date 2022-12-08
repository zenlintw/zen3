<?
    /**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                       *
	*		Creation  : 2003/09/23                                                                    *
	*		work for  : 刪除匯入帳號                                                                   *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	*       $Id: stud_remove_import.php,v 1.1 2010/02/24 02:38:45 saly Exp $                                                                                          *
	**************************************************************************************************/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '1100400600';
	$sysSession->restore();
	if (!aclVerifyPermission(1100400600, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$js = <<< EOF
	/*  檢查是否有上傳檔案  */
    function checkfile(){
        if (document.getElementById('csvfile').value == ''){
            alert("{$MSG['must_select_filename'][$sysSession->lang]}");
            return false;
        }
        document.getElementById('btn_submit').disabled=true;
        return true;
    }
EOF;
	showXHTML_script('inline', $js);
	showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="learn_result" class="cssTable"');
	        showXHTML_tr_B('class="cssTrEvn"');
				showXHTML_td_B('valign="top"');
					$ticket = md5($sysSession->ticket . 'DeleteImport' . $sysSession->school_id . $sysSession->username);
					showXHTML_input('hidden', 'ticket', $ticket, '', '');
					showXHTML_input('file', 'cvsfile', '', '', 'id="csvfile" size="27" class="cssInput"');
				showXHTML_td_E();
				showXHTML_td('', $MSG['del_help04'][$sysSession->lang]);
			showXHTML_tr_E();

		// 讓使用者選擇匯入檔案的編碼 #bug 963 Begin
		showXHTML_tr_B('class="cssTrOdd"');
			showXHTML_td_B();
				$file_type = array('Big5'	=>	$MSG['Big5'][$sysSession->lang],
								   'GB2312'	=>	$MSG['GB2312'][$sysSession->lang],
								   'en'		=>	$MSG['en'][$sysSession->lang],
								   //	先不處理日文 'EUC-JP'	=>	$MSG['EUC-JP'][$sysSession->lang],
								   'UTF-8'	=>	$MSG['UTF-8'][$sysSession->lang]);
				showXHTML_input('select', 'file_format', $file_type, ($sysSession->lang == 'user_define' ? 'UTF-8' : $sysSession->lang), 'class="cssInput" style="width: 158px"');
			showXHTML_td_E();
			showXHTML_td('', $MSG['import_format_help'][$sysSession->lang]);
		showXHTML_tr_E();
		// 讓使用者選擇匯入檔案的編碼 #bug 963 End

			showXHTML_tr_B('class="cssTrEvn"');
				showXHTML_td_B('colspan="2" align="center"');
					showXHTML_input('submit', '', $MSG['delete_account'][$sysSession->lang], '', 'id="btn_submit" class="cssBtn"');
				showXHTML_td_E();
			showXHTML_tr_E();
	showXHTML_table_E();

?>
