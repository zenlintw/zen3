<?php
	/**
	 * 我的選課清單
	 *
	 * @since   2003/06/19
	 * @author  ShenTing Lin
	 * @version $Id: ev_result.php,v 1.1 2009-06-25 09:26:26 edi Exp $
	 * @copyright 2003 SUNNET
	 * @備註 : 此支程式專供/learn/mycourse/index.php中所引用
	 **/
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');

	if (!aclVerifyPermission(700400300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	// 計算全部的審核資料
	list($total_course) = dbGetStSr('WM_review_flow', 'count(*)', "`username`='{$sysSession->username}' AND `kind`='course'", ADODB_FETCH_NUM);
	// $total_course = count($courses);
	// 計算總共分幾頁
	$total_page = ceil($total_course / $lines);
	// 產生下拉換頁選單
	$all_page = range(0, $total_page);
	$all_page[0] = $MSG['page_all'][$sysSession->lang];
	// 設定下拉換頁選單顯示第幾頁
	$page_no = isset($_POST['page']) ? intval($_POST['page']) : 1;
	if (($page_no < 0) || ($page_no > $total_page))
		$page_no = $total_page;


	$js = <<< BOF
	var total_page={$total_page};
	var MSG_RULE_SEL_DEL     = "{$MSG['msg_rule_select_del'][$sysSession->lang]}";
	var MSG_RULE_DEL_SUCCESS = "{$MSG['msg_rule_del_success'][$sysSession->lang]}";
	var MSG_RULE_DEL_FAIL    = "{$MSG['msg_rule_del_fail'][$sysSession->lang]}";

	function del_rule() {
		var xmlHttp = null, xmlVars = null;
		var obj = null, ary = null, node = null, nodes = null, tnode = null;
		var txt = "";
		var res = 0;

		ary = new Array();
		obj = document.getElementById("tabsCourse");
		nodes = obj.getElementsByTagName("input");
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (!nodes[i].checked)) continue;
			ary[ary.length] = nodes[i].value;
		}
		if (ary.length <= 0) {
			alert(MSG_RULE_SEL_DEL);
			return false;
		}
		if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
		if ((typeof(xmlVars) != "object") || (xmlVars == null)) xmlVars = XmlDocument.create();

		txt  = "<manifest>";
		txt += "<ticket>" + ticket + "</ticket>";
		txt += "<action>ev_delete</action>";
		txt += "</manifest>";
		res = xmlVars.loadXML(txt);
		if (!res) return false;
		for (var i = 0; i < ary.length; i++) {
			node = xmlVars.createElement('rid');
			tnode = xmlVars.createTextNode(ary[i]);
			node.appendChild(tnode);
			xmlVars.documentElement.appendChild(node);
		}
		xmlHttp.open("POST", "do_function.php", false);
		xmlHttp.send(xmlVars);
		// alert(xmlHttp.responseText);
		txt = xmlHttp.responseText;
		ary = txt.split(',');
		txt = MSG_RULE_DEL_SUCCESS.replace("%s", ary[0]) + "\\n";
		txt += MSG_RULE_DEL_FAIL.replace("%s", ary[1]);
		alert(txt);
		// window.location.reload();
		location.replace('index.php');
	}
BOF;

	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('inline', $js);

	$cols = '6';
	// 選課結果 (Begin)
	showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable" id="tabsCourse"');
		showXHTML_tr_B('class="cssTrHead"');
			showXHTML_td('width="760" colspan="' . $cols . '"', $MSG['msg_help_elective_result'][$sysSession->lang]);
		showXHTML_tr_E('');
		showXHTML_tr_B('class="cssTrEvn"');
			showXHTML_td_B('nowrap="nowrap" colspan="' . $cols . '" id="tb1"');
				showXHTML_input('button', 'btnSel1', $MSG['msg_select_all'][$sysSession->lang], '', 'id="btnSel1" onclick="selfunc()"');
				echo '&nbsp;' . $MSG['page1'][$sysSession->lang];
				showXHTML_input('select', 'ap', $all_page, $page_no, 'class="cssInput" onchange="go_page(this.value);"');
				echo $MSG['page2'][$sysSession->lang];
				showXHTML_input('button', 'fp', $MSG['btn_page_first'][$sysSession->lang], '', 'onclick="go_page(-1)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''));
				showXHTML_input('button', 'pp', $MSG['btn_page_prev'][$sysSession->lang] , '', 'onclick="go_page(-2)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''));
				showXHTML_input('button', 'np', $MSG['btn_page_next'][$sysSession->lang] , '', 'onclick="go_page(-3)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''));
				showXHTML_input('button', 'lp', $MSG['btn_page_last'][$sysSession->lang] , '', 'onclick="go_page(-4)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''));
				echo '&nbsp;&nbsp;';
				showXHTML_input('button', 'btnReturn', $MSG['btn_goto_list'][$sysSession->lang], '', 'onclick="chgTabs(3);" class="cssBtn"');
				echo '&nbsp;&nbsp;';
				showXHTML_input('button', 'btnDel1', $MSG['btn_del_rule'][$sysSession->lang], '', 'id="btnDel1" onclick="del_rule()" class="cssBtn"');
			showXHTML_td_E('');
		showXHTML_tr_E('');

		showXHTML_tr_B('class="cssTrHead"');
			showXHTML_td_B('nowrap="nowrap" align="center"');
				showXHTML_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" onclick="selfunc()"');
			showXHTML_td_E('');
			if ($sysSession->env == 'academic') {
				// 除了管理者環境外，一律不顯示課程編號
				showXHTML_td('nowrap="nowrap" align="center"', $MSG['td_course_id'][$sysSession->lang]);
			}
			showXHTML_td('nowrap="nowrap" align="center"', $MSG['td_course_name'][$sysSession->lang]);
			showXHTML_td('nowrap="nowrap" align="center"', $MSG['td_enroll'][$sysSession->lang]);
			showXHTML_td('nowrap="nowrap" align="center"', $MSG['td_study'][$sysSession->lang]);
			showXHTML_td('nowrap="nowrap" align="center"', $MSG['td_result'][$sysSession->lang]);
		showXHTML_tr_E('');

		if ($page_no > 0) {
			$begin = intval($page_no - 1) * $lines;
			$end   = intval($page_no) * $lines;
			if ($begin < 0) $begin = 0;
			$sqls = " limit {$begin}, {$lines}";
		}
		$csary = array();
		$RS = dbGetStMr('WM_review_flow', '`idx`, `create_time`, `discren_id`, `state`, `param`, `result`', "`username`='{$sysSession->username}' AND `kind`='course' order by `create_time` DESC {$sqls}", ADODB_FETCH_ASSOC);
		if (empty($RS)) {
			$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
			showXHTML_tr_B($col);
				showXHTML_td_B('nowrap="nowrap" colspan="' . $cols . '"');
					echo $MSG['msg_no_elective_result'][$sysSession->lang];
				showXHTML_td_E('');
			showXHTML_tr_E('');
		} else {
			while (!$RS->EOF) {
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					$csid = intval($RS->fields['discren_id']);
					if (!isset($csary[$csid])) {
						$RSS = dbGetStSr('WM_term_course', '`caption`, `en_begin`, `en_end`, `st_begin`, `st_end`', "`course_id`={$csid}", ADODB_FETCH_ASSOC);
						$lang = getCaption($RSS['caption']);
						$title = $lang[$sysSession->lang];

						$en = $MSG['from2'][$sysSession->lang] . (empty($RSS['en_begin']) ? $MSG['now'][$sysSession->lang]     : $RSS['en_begin']) . '<br>' .
					  		  $MSG['to2'][$sysSession->lang]   . (empty($RSS['en_end'])   ? $MSG['forever'][$sysSession->lang] : $RSS['en_end']);
						$st = $MSG['from2'][$sysSession->lang] . (empty($RSS['st_begin']) ? $MSG['now'][$sysSession->lang]     : $RSS['st_begin']) . '<br>' .
					  		  $MSG['to2'][$sysSession->lang]   . (empty($RSS['st_end'])   ? $MSG['forever'][$sysSession->lang] : $RSS['st_end']);

						$csary[$csid] = array($title, $en, $st);
					}
					if ($RS->fields['state'] == 'open') {
						$result = $MSG['msg_open'][$sysSession->lang];
						$result .= "<input type='button' value = '".$MSG['btn_drop_elective'][$sysSession->lang]."' class='cssBtn' onclick=\"confirm('".$MSG['msg_drop_unelective'][$sysSession->lang]."')?drop_elective('drop_unelective','".$csid."'):'';\">";
					} else {
						$result = '';
						switch (trim($RS->fields['param'])) {
							case '%cs_status'     : $result = $MSG['msg_cs_state'][$sysSession->lang];           break;
							case '%cs_study'      : $result = $MSG['msg_basic_setting'][$sysSession->lang];      break;
							case '#none'          : $result = $MSG['msg_rule_none'][$sysSession->lang];          break;
							case '#assistant'     : $result = $MSG['msg_rule_assistant'][$sysSession->lang];     break;
							case '#instructor'    : $result = $MSG['msg_rule_instructor'][$sysSession->lang];    break;
							case '#teacher'       : $result = $MSG['msg_rule_teacher'][$sysSession->lang];       break;
							case '#director'      : $result = $MSG['msg_rule_director'][$sysSession->lang];      break;
							case '#manager'       : $result = $MSG['msg_rule_manager'][$sysSession->lang];       break;
							case '#administrator' : $result = $MSG['msg_rule_administrator'][$sysSession->lang]; break;
							case '%cs_delete'     : $result = $MSG['msg_cs_delete'][$sysSession->lang];          break;
							case '%student_full'  : $result = $MSG['msg_n_limit_greater'][$sysSession->lang];    break;
							default               :
						}

						switch (trim($RS->fields['result'])) {
							case 'yes'  : $result .= $MSG['msg_cs_selected'][$sysSession->lang];        break;
							case 'deny' : $result .= $MSG['msg_cs_study_deny'][$sysSession->lang];      break;
							case 'ok'   : $result .= $MSG['msg_cs_study_ok'][$sysSession->lang];        break;
							case '0'    : $result .= $MSG['msg_cs_state_close'][$sysSession->lang];     break;
							case '5'    : $result .= $MSG['msg_cs_state_not_ready'][$sysSession->lang]; break;
							case '9'    : $result .= $MSG['msg_cs_state_delete'][$sysSession->lang];    break;
							case 'auditor' : $result .= $MSG['msg_accept_auditor'][$sysSession->lang];  break;
							default     : $result .= '&nbsp;';
						}
					}
					showXHTML_td_B('nowrap="nowrap" align="center"');
					if ($RS->fields['state'] == 'close') {
						showXHTML_input('checkbox', 'rid[]', sysEncode($RS->fields['idx']), '', 'onclick="chgCheckbox()"');
					} else {
						echo '&nbsp;';
					}
					showXHTML_td_E('');
					if ($sysSession->env == 'academic') {
						// 除了管理者環境外，一律不顯示課程編號
						showXHTML_td('nowrap="nowrap" align="center"', $csid);
					}
					showXHTML_td('nowrap="nowrap"', $csary[$csid][0]);
					showXHTML_td('nowrap="nowrap" align="center"', $csary[$csid][1]);
					showXHTML_td('nowrap="nowrap" align="center"', $csary[$csid][2]);
					showXHTML_td('nowrap="nowrap"', $result);
				showXHTML_tr_E('');
				$RS->MoveNext();
			}
		}


		showXHTML_tr_B('class="cssTrEvn"');
			showXHTML_td_B('nowrap="nowrap" colspan="' . $cols . '" id="tb1"');
				showXHTML_input('button', 'btnSel2', $MSG['msg_select_all'][$sysSession->lang], '', 'id="btnSel2" onclick="selfunc()"');
				echo '&nbsp;' . $MSG['page1'][$sysSession->lang];
				showXHTML_input('select', 'ap', $all_page, $page_no, 'class="cssInput" onchange="go_page(this.value);"');
				echo $MSG['page2'][$sysSession->lang];
				showXHTML_input('button', 'fp', $MSG['btn_page_first'][$sysSession->lang], '', 'onclick="go_page(-1)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''));
				showXHTML_input('button', 'pp', $MSG['btn_page_prev'][$sysSession->lang] , '', 'onclick="go_page(-2)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''));
				showXHTML_input('button', 'np', $MSG['btn_page_next'][$sysSession->lang] , '', 'onclick="go_page(-3)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''));
				showXHTML_input('button', 'lp', $MSG['btn_page_last'][$sysSession->lang] , '', 'onclick="go_page(-4)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''));
				echo '&nbsp;&nbsp;';
				showXHTML_input('button', 'btnReturn', $MSG['btn_goto_list'][$sysSession->lang], '', 'onclick="chgTabs(3);" class="cssBtn"');
				echo '&nbsp;&nbsp;';
				showXHTML_input('button', 'btnDel2', $MSG['btn_del_rule'][$sysSession->lang], '', 'id="btnDel2" onclick="del_rule()" class="cssBtn"');
			showXHTML_td_E('');
		showXHTML_tr_E('');
	showXHTML_table_E('');
	// 選課結果 (End)
?>
