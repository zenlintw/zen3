<?php
	/**
	 * 我的選課清單
	 *
	 * @since   2003/06/19
	 * @author  ShenTing Lin
	 * @version $Id: elective.php,v 1.1 2009-06-25 09:26:26 edi Exp $
	 * @copyright 2003 SUNNET
	 * @備註 : 此支程式專供/learn/mycourse/index.php中所引用
	 **/

	if (!aclVerifyPermission(700400300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	function getElectiveCourse($nodes) {
		$result = array();
		$cnt = count($nodes->nodeset);
		if ($cnt <= 0) return $result;
		for ($i = 0; $i < $cnt; $i++) {
			$csid = intval($nodes->nodeset[$i]->value);
			$RS = dbGetStSr('WM_term_course', '*', "course_id={$csid}", ADODB_FETCH_ASSOC);
			$result[] = array(
					$RS['course_id'], $RS['caption'],
					$RS['en_begin'],  $RS['en_end'],
					$RS['st_begin'],  $RS['st_end'],
					$RS['teacher']
				);
		}
		return $result;
	}

	$courses  = array();
	$filename = setElevtive();
	if ($xmlvars = domxml_open_file($filename)) {
		$ctx     = xpath_new_context($xmlvars);
		$xpath   = '/manifest/course/@id';
		$nodes   = xpath_eval($ctx, $xpath);
		$courses = getElectiveCourse($nodes);
	}

//	$RS = $sysConn->Execute($sqls);

	$cnt = count($courses);

	// 計算全部的課程數
	$total_course = $cnt;
	// 計算總共分幾頁
	$total_page = ceil($total_course / $lines);
	// 產生下拉換頁選單
	$all_page = range(0, $total_page);
	$all_page[0] = $MSG['page_all'][$sysSession->lang];
	// 設定下拉換頁選單顯示第幾頁
	$setting_no = intval(getSetting('page_no'));
	//$setting_no = empty($setting_no) ? $total_page : $setting_no;
	$page_no = isset($_POST['page']) ? intval($_POST['page']) : $setting_no;
	if (($page_no < 0) || ($page_no > $total_page))
		$page_no = $total_page;
	saveSetting('page_no', $page_no);  // 回存設定

	$js = "var total_page={$total_page};";
	$cols = '7';
	showXHTML_script('inline', $js);
	// 列出課程教室 (Begin)
	showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable" id="tabsCourse"');
		showXHTML_tr_B('class="cssTrHead"');
			showXHTML_td('width="760" colspan="' . $cols . '"', $MSG['msg_help_elective'][$sysSession->lang]);
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
				showXHTML_input('button', 'btnAdd',   $MSG['btn_submit_elective'][$sysSession->lang], '', 'id="btnAdd1" onclick="do_func(\'elective\');" class="cssBtn"');
				showXHTML_input('button', 'btnDel',   $MSG['btn_delete_elective'][$sysSession->lang], '', 'id="btnDel1" onclick="do_func(\'major_del\'); chgTabs(5);" class="cssBtn"');
				showXHTML_input('button', 'btnReset', $MSG['btn_reset_elective'][$sysSession->lang] , '', 'onclick="do_func(\'major_reset\');" class="cssBtn"');
			showXHTML_td_E('');
		showXHTML_tr_E('');

		showXHTML_tr_B('class="cssTrHead"');
			showXHTML_td_B('nowrap="nowrap" align="center"');
				showXHTML_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" onclick="selfunc()" checked="checked"');
				// showXHTML_input('checkbox', 'selBtn', '', '', 'onclick="select_func(this.checked); event.cancelBubble=true;" title="' . $MSG['btn_alt_sel_all_cancel'][$sysSession->lang] . '"');
			showXHTML_td_E('');
			if ($sysSession->env == 'academic') {
				// 除了管理者環境外，一律不顯示課程編號
				showXHTML_td('nowrap="nowrap" align="center"', $MSG['td_course_id'][$sysSession->lang]);
			}
			showXHTML_td('nowrap="nowrap" align="center"', $MSG['td_course_name'][$sysSession->lang]);
			showXHTML_td('nowrap="nowrap" align="center"', $MSG['td_enroll'][$sysSession->lang]);
			showXHTML_td('nowrap="nowrap" align="center"', $MSG['td_study'][$sysSession->lang]);
			showXHTML_td('nowrap="nowrap" align="center"', $MSG['td_teacher'][$sysSession->lang]);
			showXHTML_td('nowrap="nowrap" align="center"', $MSG['td_detail'][$sysSession->lang]);
		showXHTML_tr_E('');

		if ($cnt <= 0) {
			$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
			showXHTML_tr_B($col);
				showXHTML_td_B('nowrap="nowrap" colspan="' . $cols . '"');
					echo $MSG['msg_no_elective_course'][$sysSession->lang];
				showXHTML_td_E('');
			showXHTML_tr_E('');
		}

		if ($page_no == 0) {
			$begin = 0;
			$end   = $cnt;
		} else {
			$begin = intval($page_no - 1) * $lines;
			$end   = intval($page_no) * $lines;
			if ($begin < 0)  $begin = 0;
			if ($end > $cnt) $end   = $cnt;
		}

		for ($i = $begin; $i < $end; $i++) {
			$val = $courses[$i];
			$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
			showXHTML_tr_B($col);
				showXHTML_td_B('nowrap="nowrap" align="center"');
					showXHTML_input('checkbox', 'major_csid[]', $val[0], '', 'onclick="SelCourse(this); event.cancelBubble=true;" checked="checked"');
				showXHTML_td_E('');
				if ($sysSession->env == 'academic') {
					// 除了管理者環境外，一律不顯示課程編號
					showXHTML_td('nowrap="nowrap" align="center"', $val[0]);
				}
				$lang  = getCaption($val[1]);
				$title = $lang[$sysSession->lang];
				showXHTML_td('nowrap="nowrap"', $title);
				$st = $MSG['from2'][$sysSession->lang] . (empty($val[2]) ? $MSG['now'][$sysSession->lang] : $val[2]) . '<br>' .
					  $MSG['to2'][$sysSession->lang] . (empty($val[3]) ? $MSG['forever'][$sysSession->lang] : $val[3]);
				$en = $MSG['from2'][$sysSession->lang] . (empty($val[4]) ? $MSG['now'][$sysSession->lang] : $val[4]) . '<br>' .
					  $MSG['to2'][$sysSession->lang] . (empty($val[5]) ? $MSG['forever'][$sysSession->lang] : $val[5]);
				showXHTML_td('nowrap="nowrap"', $st);
				showXHTML_td('nowrap="nowrap"', $en);
				showXHTML_td('nowrap="nowrap"', $val[6]);
				$icon = '<img src="/theme/' . $sysSession->theme . '/academic/icon_folder.gif" width="16" height="16" border="0" alt="' . $MSG['btn_alt_detail'][$sysSession->lang] . '" title="' . $MSG['btn_alt_detail'][$sysSession->lang] . '">';
				$detail = '<a href="javascript:;" onclick="showDetail(' . $val[0] . '); return false;">' . $icon . '</a>';
				showXHTML_td('nowrap="nowrap" align="center"', $detail);
			showXHTML_tr_E('');
		}

		// 第二排的工具列
		$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
		showXHTML_tr_B($col);
			showXHTML_td_B('nowrap="nowrap" colspan="' . $cols . '" id="tb23"');
				$disable = ($cnt == 0) ? ' disabled="disabled"' : '';
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
				showXHTML_input('button', 'btnAdd',   $MSG['btn_submit_elective'][$sysSession->lang], '', 'id="btnAdd2" onclick="do_func(\'elective\');" class="cssBtn"');
				showXHTML_input('button', 'btnDel',   $MSG['btn_delete_elective'][$sysSession->lang], '', 'id="btnDel2" onclick="do_func(\'major_del\'); chgTabs(5);" class="cssBtn"');
				showXHTML_input('button', 'btnReset', $MSG['btn_reset_elective'][$sysSession->lang] , '', 'onclick="do_func(\'major_reset\');" class="cssBtn"');
			showXHTML_td_E('');
		showXHTML_tr_E('');
	showXHTML_table_E('');
	// 列出課程教室 (End)
?>
