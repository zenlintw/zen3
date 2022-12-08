<?
    /**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                       *
	*		Creation  : 2003/09/23                                                                    *
	*		work for  : 匯入帳號                                                                     *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	*       version   :  $Id: stud_addimport.php,v 1.1 2010/02/24 02:38:44 saly Exp $
	*                                                                                                 *
	**************************************************************************************************/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '400300500';
	$sysSession->restore();
	if (!aclVerifyPermission(400300500, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if ($_POST['delimport'] != ''){
		if (file_exists($_POST['impfile'])){
			unlink($_POST['impfile']);
		}
	}
	$js = <<< BOE
	/*
	 * 檢查是否有上傳檔案
	 */
	function checkfile(){
		if (document.getElementById('csvfile').value == '') {
			alert("{$MSG['must_select_filename'][$sysSession->lang]}");
			return false;
		}
		document.getElementById('btn_submit3').disabled=true;
		return true;
	}
BOE;
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline', $js);

    showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="learn_result" class="cssTable"');

      showXHTML_tr_B('class="cssTrEvn"');
			showXHTML_td_B('valign="top"');
				$ticket = md5($sysSession->ticket . 'AddImport' . $sysSession->school_id . $sysSession->username);
				showXHTML_input('hidden', 'ticket', $ticket, '', '');
				showXHTML_input('file', 'cvsfile', '', '', 'id="csvfile" size="27" class="cssInput"');
			showXHTML_td_E();
			showXHTML_td('', $MSG['create_help04'][$sysSession->lang]);
			showXHTML_td_B();
				showXHTML_input('button', '', $MSG['btn_csv_example'][$sysSession->lang], '', 'id="btn_csv_example" class="cssBtn" onclick="OpenNamedWin(\'stud_import_csvhelp.php\',\'csvhelpwin\',780,520)"');
			showXHTML_td_E();
		showXHTML_tr_E();

		// 讓使用者選擇匯入檔案的編碼 #bug 963 Begin
		showXHTML_tr_B('class="cssTrOdd"');
			showXHTML_td_B();
				$file_type = array('Big5'	=>	$MSG['Big5'][$sysSession->lang],
								   'GB2312'=>	$MSG['GB2312'][$sysSession->lang],
								   'en'	=>	$MSG['en'][$sysSession->lang],
								   //	先不處理日文 'EUC-JP'	=>	$MSG['EUC-JP'][$sysSession->lang],
								   'UTF-8'	=>	$MSG['UTF-8'][$sysSession->lang]);
				showXHTML_input('select', 'file_format', $file_type, ($sysSession->lang == 'user_define' ? 'UTF-8' : $sysSession->lang), 'class="cssInput" style="width: 158px"');
			showXHTML_td_E();
			showXHTML_td('', $MSG['import_format_help'][$sysSession->lang]);
			showXHTML_td('', '&nbsp');
		showXHTML_tr_E();
		// 讓使用者選擇匯入檔案的編碼 #bug 963 End

		showXHTML_tr_B('class="cssTrEvn"');
			showXHTML_td_B('colspan="3" align="center"');
				showXHTML_input('submit', '', $MSG['create_account'][$sysSession->lang], '', 'id="btn_submit3" class="cssBtn"');
			    showXHTML_input('reset' , '', $MSG['cancel'][$sysSession->lang]     , '', 'class="cssBtn"');
			showXHTML_td_E();
		showXHTML_tr_E();
    showXHTML_table_E();
?>