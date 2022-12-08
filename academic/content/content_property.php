<?php
	/**
	 * 建立或修改教材
	 *
	 * 建立日期：2002/08/23
	 * @author  ShenTing Lin
	 * @version $Id: content_property.php,v 1.1 2010/02/24 02:38:18 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/multi_lang.php');
	require_once(sysDocumentRoot . '/lang/content_manage.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/quota.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$sysSession->cur_func='700500500';
	$sysSession->restore();

	if (!aclVerifyPermission(700500500, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}

	$actType = '';
	$title   = '';
	$usage   = '';
	$limit   = '';
	$status  = '';

	$gid = '100000';
	$ticket = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Edit' . $sysSession->username);
	if (empty($_POST['ticket'])) {	// 新增教材
		if (!empty($_GET['gid'])) $gid = intval($_GET['gid']);
		$actType = 'Create';
		$title = $MSG['title_add'][$sysSession->lang];

		$limit = getDefaultQuota();
		$status = 'readonly';
		$lang['Big5']        = '';
		$lang['GB2312']      = '';
		$lang['en']          = '';
		$lang['EUC-JP']      = '';
		$lang['user_define'] = '';
	}
	else if (trim($_POST['ticket']) == $ticket) {	// 修改教材
		$actType = 'Edit';
		$title = $MSG['title_edit'][$sysSession->lang];

		$RS     = dbGetStSr('WM_content', '*', 'content_id=' . intval($_POST['content_id']), ADODB_FETCH_ASSOC);
		$lang   = old_getCaption($RS['caption']);
		$usage  = $RS['quota_used'];
		$limit  = $RS['quota_limit'];
		$status = $RS['status'];
	}
	else {
		wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,1, 'manager', $_SERVER['PHP_SELF'], '拒絕存取!');
		die($MSG['access_deny'][$sysSession->lang]);
	}

	$js = <<< BOF

	var MSG_TITLE_ERROR = "{$MSG['msg_title_error'][$sysSession->lang]}";
	var content_type    = "{$RS['content_type']}";

	function checkData() {

		var node = document.getElementById("actForm");
		if (node == null) return false;

		var tmp_content_sn = node.content_sn.value;

		if(tmp_content_sn.length == 0) {
			alert("{$MSG['msg_content_sn_empty'][$sysSession->lang]}");
			return false;
		}

		if(tmp_content_sn.length > 32) {
			alert("{$MSG['msg_content_sn_leng'][$sysSession->lang]}");
			return false;
		}

		if (! Filter_Spec_char(tmp_content_sn)){
			alert("{$MSG['msg_content_sn_error'][$sysSession->lang]}");
			return false;
		}

		return chk_multi_lang_input(1, true, "{$MSG['msg_input_title'][$sysSession->lang]}", un_htmlspecialchars(MSG_TITLE_ERROR));
	}

	function goList() {
	    if (location.pathname.indexOf('/course/') > 0)
	        window.location.replace("content_list.php");
	    else
			window.location.replace("content_package_manager.php");
	}

	function ContentFormVisible(val)
	{
		var obj = document.getElementById("td_content_form");
		var status_obj = document.getElementById("tr_status_list");
		var quota_obj = document.getElementById("tr_quota_limit");
		if (val == 'traditional')
		{
			obj.style.display = 'block';

			status_obj.style.display = 'none';
			quota_obj.style.display = 'none';
		}else{
			obj.style.display = 'none';

			status_obj.style.display = 'block';
			quota_obj.style.display = 'block';
		}
		// 設定版面
		obj.previousSibling.colSpan = val == 'digitization' ? 2 : 1;
		var tmp = quota_obj;
		var css = quota_obj.className;
		do {
			if (tmp.style.display != 'none') {
				tmp.className = css;
				css = css == 'cssTrEvn' ? 'cssTrOdd' : 'cssTrEvn';
			}
		} while (tmp = tmp.nextSibling);
	}

	window.onload = function(){
		if(content_type == 'traditional'){
			ContentFormVisible('traditional');
		}else{
			ContentFormVisible('digitization');
		}
	};
BOF;
	// 開始呈現 HTML
	showXHTML_head_B($title);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('include', '/lib/filter_spec_char.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B('');

		$ary[] = array($title, 'tabs');

		showXHTML_tabFrame_B($ary, 1, 'actForm', 'ListTable', 'method="post" action="content_save.php" style="display:inline;" onsubmit="return checkData()"');
			$ticket = md5($sysSession->ticket . $actType . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
			showXHTML_input('hidden', 'ticket', $ticket, '', '');
			showXHTML_input('hidden', 'gid', $gid);
			showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top"', $MSG['td_title_no'][$sysSession->lang]);
					showXHTML_td_B('colspan="2"');
						showXHTML_input('text', 'content_sn', $RS['content_sn'], '', 'size="10" maxlength="32" class="cssInput"');
					showXHTML_td_E();
					showXHTML_td('valign="top"',$MSG['th_alt_content_no'][$sysSession->lang] . '<br><font color=red>' . $MSG['msg_content_sn'][$sysSession->lang] . '</font>');
				showXHTML_tr_E('');

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top"', $MSG['td_title_caption'][$sysSession->lang]);
					showXHTML_td_B('colspan="2"');
						$multi_lang = new Multi_lang(false, $lang, 'class="cssTrOdd"'); // 多語系輸入框
						$multi_lang->show();
					showXHTML_td_E();
					showXHTML_td('valign="top"',$MSG['th_alt_content_name'][$sysSession->lang]);
				showXHTML_tr_E('');

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col . ' id="tr_quota_limit"');
					showXHTML_td('align="right" valign="top"', $MSG['td_title_quota_mb'][$sysSession->lang]);
					showXHTML_td_B('colspan="3"');
						showXHTML_input('text', 'quota_limit', $limit, '', 'size="10" maxlength="10" class="cssInput"');
					showXHTML_td_E(' KB');
				showXHTML_tr_E('');

				if ($actType == 'Edit') {
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td('align="right" valign="top"', $MSG['td_title_usage_mb'][$sysSession->lang]);
						showXHTML_td_B('colspan="2"');
							showXHTML_input('hidden', 'content_id', intval($_POST['content_id']), '', '');
							showXHTML_input('hidden', 'quota_used', $usage, '', '');
						showXHTML_td_E($usage . ' KB');
						showXHTML_td('','');
					showXHTML_tr_E('');
				}

				$type_list = array(
					'digitization' => $MSG['state_digital'][$sysSession->lang],
					'traditional' => $MSG['state_traditional'][$sysSession->lang]
				);

				$type_list1 = array(
					'1' => $MSG['Option_Content_Type1'][$sysSession->lang],
					'2' => $MSG['Option_Content_Type2'][$sysSession->lang],
					'3' => $MSG['Option_Content_Type3'][$sysSession->lang],
					'4' => $MSG['Option_Content_Type4'][$sysSession->lang],
					'5' => $MSG['Option_Content_Type5'][$sysSession->lang],
					'6' => $MSG['Option_Content_Type6'][$sysSession->lang],
					'7' => $MSG['Option_Content_Type7'][$sysSession->lang],
					'8' => $MSG['Option_Content_Type8'][$sysSession->lang]
				);

				//教材形式
				$content_form_value = 1;
				for($i=1; $i<=8; $i++)
				{
					if (strcmp($RS['content_form'],$MSG['Option_Content_Type'.$i][$sysSession->lang]) == 0)
						$content_form_value = $i;
				}

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top"', $MSG['td_title_state'][$sysSession->lang]);
					showXHTML_td_B();
						showXHTML_input('select', 'content_type', $type_list, $RS['content_type'], 'class="cssInput" onChange="ContentFormVisible(this.value);"');
					showXHTML_td_E('');
					showXHTML_td_B(' id="td_content_form"');
						echo $MSG['td_title_state1'][$sysSession->lang];
						showXHTML_input('select', 'content_form', $type_list1, $content_form_value, 'class="cssInput"');
					showXHTML_td_E('');
					showXHTML_td('');
				showXHTML_tr_E('');

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top"', $MSG['td_title_note'][$sysSession->lang]);
					showXHTML_td_B('colspan="3"');
						showXHTML_input('textarea', 'content_note', $RS['content_note'], '', 'rows="3" cols="60" class="cssInput"');
					showXHTML_td_E();
				showXHTML_tr_E('');

				$status_list = array(
					'readonly' => $MSG['state_read_only'][$sysSession->lang],
					'disable' => $MSG['state_close'][$sysSession->lang],
				//  #item 1238 電子檔狀態需隱藏「讀寫」的選項。
				//	'modifiable' => $MSG['state_read_write'][$sysSession->lang]
				);
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col . ' id="tr_status_list"');
					showXHTML_td('align="right" valign="top"', $MSG['td_title_state0'][$sysSession->lang]);
					showXHTML_td_B('colspan="2"');
						showXHTML_input('select', 'status', $status_list, $status, 'class="cssInput"');
					showXHTML_td_E('');
					showXHTML_td('colspan="2"', '<font color="red">' . $MSG['msg_status'][$sysSession->lang] . '</font>');
				showXHTML_tr_E('');

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="4" align="center"');
						showXHTML_input('button', '', $MSG['btn_reutrn'][$sysSession->lang], '', 'onclick="goList();" class="cssBtn"');
						showXHTML_input('submit', '', $MSG['btn_save'][$sysSession->lang], '', 'class="cssBtn"');
						showXHTML_input('reset', '', $MSG['btn_reset'][$sysSession->lang], '', 'class="cssBtn"');
					showXHTML_td_E('');
				showXHTML_tr_E('');

			showXHTML_table_E('');
		showXHTML_tabFrame_E();
	showXHTML_body_E('');
?>
