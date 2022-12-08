<?php
	/**
	 * 匯入行事曆
	 *
	 * 建立日期：2004/04/02
	 * @author  KuoYang Tsao
	 * @version $Id: cal_import.php,v 1.1 2010/02/24 02:39:04 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/calendar.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	switch ($sysSession->cur_func) {
		case '2300300400':
			$calEnv = 'academic';
			break;
		case '2300200400':
			$calEnv = 'teach';
			break;
		case '2300400400':
			$calEnv = 'direct';
			break;
		default:
			$sysSession->cur_func = '2300100400';
			$sysSession->restore();
			$calEnv	= 'learn';
			break;
	}

	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	switch ($calEnv){
			case 'academic':
				$js = <<< BOF
				var msgSetSaved = "{$MSG['setting_saved'][$sysSession->lang]}";
				var theme = "/theme/{$sysSession->theme}/{$sysSession->env}/";
				function chkForm(f) {
					if(f.filename.value == '') {
						alert("{$MSG['input_file'][$sysSession->lang]}");
						f.filename.focus();
						return;
					}
					f.submit();
				}
BOF;
			break;

			default:
				$ticket = md5($sysSession->username . 'Calendar' . $sysSession->ticket . $sysSession->school_id);
				$str = date('Y-n-j', time());
				$date = explode('-', $str);
				$date[1]--;
				$js = <<< BOF
				var msgSetSaved = "{$MSG['setting_saved'][$sysSession->lang]}";
				var theme = "/theme/{$sysSession->theme}/{$sysSession->env}/";
				var ticket = "{$ticket}";
				var orgYear = {$date[0]}, orgMonth = {$date[1]}, orgDay = {$date[2]};
				var theYear = {$date[0]}, theMonth = {$date[1]}, theDay = {$date[2]};

				function chkForm(f) {
					if(f.filename.value == '') {
						alert("{$MSG['input_file'][$sysSession->lang]}");
						f.filename.focus();
						return;
					}
					f.submit();
				}
BOF;
			break;
}

	showXHTML_head_B($MSG['import'][$sysSession->lang].$MSG['heml_title'][$sysSession->lang]);
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
		showXHTML_script('inline', $js);
		echo '<base target="_self">';
	showXHTML_head_E('');
	showXHTML_body_B('');
	
	$ary = array(array($MSG['import'][$sysSession->lang] . $MSG['heml_title'][$sysSession->lang]));
	showXHTML_tabFrame_B($ary, 1, 'form_import', 'calImport', 'action="cal_import1.php?calEnv='.$calEnv.'" method="POST" enctype="multipart/form-data" style="display: inline"', false, false);
		showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="tabs2" class="cssTable"');
			showXHTML_tr_B('class="cssTrHead"');
				showXHTML_td('id="helpMsg"', $MSG['csv_note'][$sysSession->lang]);
			showXHTML_tr_E('');
			
			showXHTML_tr_B('class="cssTrOdd"');
				showXHTML_td_B('');
					showXHTML_input('file','filename','','','size="50"');
				showXHTML_td_E('');
			showXHTML_tr_E('');

			showXHTML_tr_B('class="cssTrEvn"');
				showXHTML_td_B('');
					showXHTML_input('button','btnImp',$MSG['import'][$sysSession->lang],'','onclick="chkForm(this.form);"');
					showXHTML_input('button','btnImp',$MSG['window_close'][$sysSession->lang],'','onclick="window.close();"');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
	showXHTML_tabFrame_E();

	showXHTML_body_E('');
?>
