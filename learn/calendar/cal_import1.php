<?php
	/**
	 * 處理匯入行事曆
	 *
	 * 建立日期：2004/04/02
	 * @author  KuoYang Tsao
	 * @version $Id: cal_import1.php,v 1.1 2010/02/24 02:39:04 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/calendar.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/lib_calendar.php');

	switch ($sysSession->cur_func) {
		case '2300300400':
			$calEnv = 'academic';
			$iface  = 'school';
			$sysLog = $sysSession->school_id;
			break;
		case '2300200400':
			$calEnv = 'teach';
			$iface  = 'course';
			$sysLog = $sysSession->course_id;
			break;
		case '2300400400':
			$calEnv = 'direct';
			$iface  = 'class';
			$sysLog = $sysSession->class_id;
			break;
		default:
			$sysSession->cur_func = '2300100400';
			$sysSession->restore();
			$calEnv	= 'learn';
			$iface  = 'person';
			$sysLog = $username;
			break;
	}

	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	//取得上傳資料

	function GetUploadData($upVarName,&$txtdata) {
		global $_FILES;
		if(!is_uploaded_file($_FILES[$upVarName]['tmp_name']) && $_FILES[$upVarName]['size']==0)
			return false;
		$fname = $_FILES[$upVarName]['tmp_name'];
		//echo "<!-- $fname -->\r\n";
		// $txtdata = file_get_contents($fname);
		$txtdata = file($fname);
		return (count($txtdata) > 0);
	}

	/**
	 * 解析一行資料, 轉換成XML String
	 * @param String $str 原始資料
	 * @param String $data XML String
	 * @return 1:該行為註解, 0:資料錯誤, 2:資料解析成功
	 * 備註 : XML資料直接存放在 $data 變數中
	 */
	function ParseLine($str, &$data) {
		global $sysSession;
		
		$str = trim($str);
		if (substr($str, 0, 1) == '#') 
			return 1;
		
		$tmp = explode('%%', $str);
		if (count($tmp) != 9)
			return 0;
		
		foreach($tmp as $k => $v) 
			$tmp[$k] = trim($v);
		
		if (!preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$/", $tmp[0], $c_date) ||
			!preg_match("/^([0-9]{1,2}):([0-9]{1,2})$/", $tmp[1], $begin_time) ||
			!preg_match("/^([0-9]{1,2}):([0-9]{1,2})$/", $tmp[2], $end_time) ||
			strlen($tmp[7]) == 0) return 0;
		$c_date[2]--; // 月份減一
		
		$w_date = array();
		if (strlen($tmp[3]) > 0 && !preg_match("/^([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$/", $tmp[4], $w_date)) return 0;
		if (strlen($tmp[3]) === 0) $tmp[3] = 'none';// Chrome 沒有值時應寫入none
		
		$tmp[7] = iconv($sysSession->lang, 'UTF-8', $tmp[7]);
		$tmp[8] = str_replace('\n', "\n", iconv($sysSession->lang, 'UTF-8', $tmp[8]));
		
		
		$data = <<< BOF
<manifest>
	<year>$c_date[1]</year>
	<month>$c_date[2]</month>
	<day>$c_date[3]</day>
	<idx></idx>
	<time_begin>$tmp[1]</time_begin>
	<time_end>$tmp[2]</time_end>
	<repeat>$tmp[3]</repeat>
	<repeat_endY>$w_date[1]</repeat_endY>
	<repeat_endM>$w_date[2]</repeat_endM>
	<repeat_endD>$w_date[3]</repeat_endD>
	<subject>$tmp[7]</subject>
	<alert_type>$tmp[5]</alert_type>
	<alert_before>$tmp[6]</alert_before>
	<content type="text">$tmp[8]</content>
</manifest>
BOF;
		return 2;
	}

	function ShowErrorLine($index, $s , $title='') {
		global $MSG,$sysSession;

		$error_msg = str_replace('%index%',$index,$MSG['error_msg'][$sysSession->lang]);

		$error_msg = str_replace('%title%',$title,$error_msg);

		$error_msg = str_replace('%s%',$s,$error_msg);

		showXHTML_tr_B('class="font01 cssTrEvn"');
			showXHTML_td('height="20"',$error_msg);
		showXHTML_tr_E('');
	}

	$js = <<< BOF
	var msgSetSaved = "{$MSG['setting_saved'][$sysSession->lang]}";
	var theme = "/theme/{$sysSession->theme}/{$sysSession->env}/";
	window.onload = function () {
		self.resizeTo(400,300);
		if (opener)
		    opener.CalendarImported();
		else
			window.dialogArguments.CalendarImported();
	}
BOF;

	showXHTML_head_B($MSG['import'][$sysSession->lang].$MSG['heml_title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B('');

	showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" id="mt" ');
		showXHTML_tr_B('');
			showXHTML_td_B('');
				$arry[] = array($MSG['import_result'][$sysSession->lang], 'importTable');
                showXHTML_tabs($arry, 1);
			showXHTML_td_E('');
		showXHTML_tr_E('');
		showXHTML_tr_B('');
			showXHTML_td_B('valign="top"');
				showXHTML_table_B('id="stud_list" width="100%" border="0" cellspacing="1" cellpadding="3" id="delTable2" style="display:block" class="cssTable"');
// begin
				$txtdata = '';
				$ErrorOccured = false;
				$LineIndex = 0;
				if(GetUploadData('filename', $TxtLines) && is_array($TxtLines)) {
					foreach($TxtLines as $k => $v) {
						$LineIndex++;
						$strv = trim(str_replace("\r", '', $v));
						$data = Array();
						$i = ParseLine($strv, $data);
						if($i==2) {
							if ( (!$dom = @domxml_open_mem($data)) || strpos(saveMemo($dom, $iface), '<status>1</status>') === false) {
								$ErrorOccured = true;
								ShowErrorLine($LineIndex, $v, $MSG['save_file'][$sysSession->lang]);
							}
						} else if($i==0) {
							ShowErrorLine($LineIndex, $v, $MSG['parsedata'][$sysSession->lang]);
						}
					}
					if(!$ErrorOccured) {

						$col = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';

						showXHTML_tr_B($col);
							showXHTML_td('height="20"',$MSG['data_all_import'][$sysSession->lang]);
						showXHTML_tr_E('');
					}
				} else {
					$col = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';

					showXHTML_tr_B($col);
						showXHTML_td('height="20"',$MSG['data_read_failure'][$sysSession->lang]);
					showXHTML_tr_E('');
				}
				$col = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';

				showXHTML_tr_B($col);
					showXHTML_td_B('height="20"');
						showXHTML_input('button','btnClose',$MSG['window_close'][$sysSession->lang],'','class="cssBtn" onClick="self.close();"');
					showXHTML_td_E('');
				showXHTML_tr_E('');
// end
				showXHTML_table_E('');
			showXHTML_td_E('');
		showXHTML_tr_E('');
	showXHTML_table_E('');

	showXHTML_body_E('');
?>
