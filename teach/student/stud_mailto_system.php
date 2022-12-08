<?php
	/**
     * 目  的 : 自動點名規則設定
     *
     * @since   2005/09/28
     * @author  Edi Chen
     * @version $Id: stud_mailto_system.php,v 1.3 2009/08/11 02:03:50 edi Exp $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lstable.php');
	require_once(sysDocumentRoot . '/lang/teach_student.php');
	require_once(sysDocumentRoot . '/lib/common.php');


	function divMsg($width=100, $caption='&nbsp;', $title='', $without_title=false) {
		if (empty($title)) $title = $caption;
		return $without_title ? ('<div style="width: ' . $width . 'px; overflow:hidden;">' . $caption . '</div>') : ('<div style="width: ' . $width . 'px; overflow:hidden;" title="' . $title . '">' . $caption . '</div>');
	}
	
	function showCheckBox($sid, $enable, $begin_time, $end_time, $frequence)
	{
		if ($enable == 'disable' && $frequence != 'once' && ($begin_time == '' || $begin_time == '0000-00-00 00:00:00' || $end_time == '' || $end_time == '0000-00-00 00:00:00'))
			return '';
		return '<input type="checkbox" name="node_id[]" value="' . $sid . '" onclick="chgCheckbox(\'tabsSystem\'); event.cancelBubble=true;" >';
	}
	
	/**
	 * 顯示是否啟用
	 */
	function showEnable($enable) {
		global $MSG, $sysSession;
		return divMsg('30', $MSG['roll_call_'.$enable][$sysSession->lang]);
	}

	/**
	 * 顯示點名對象
	 */
	function showRoles($role) {
		global $MSG, $sysSession;
		return divMsg('50', $MSG[$role][$sysSession->lang]);
	}

	/**
	 * 顯示分組次
	 */
	function showGroup($team_id, $group_id) {
		global $MSG, $sysSession, $teams, $group;
		if ($team_id == 0)
			$msg = $MSG['all'][$sysSession->lang];
		else if ($group_id == 0)
			$msg = $teams[$team_id][$sysSession->lang] . $MSG['all'][$sysSession->lang];
		else
			$msg = $teams[$team_id][$sysSession->lang] . $group[$team_id][$group_id][$sysSession->lang];
		return divMsg('150', $msg);
	}

	/**
	 * 顯示點名條件
	 */
	function showCondition($mtType, $mtFilter, $mtOP, $mtVal) {
		global $sysSession, $MSG, $qti_list;
		$msg = '';
		switch ($mtType) {
			case 'login' :
			case 'lesson' :
			case 'progress':
			case 'chat':
			case 'post':
				if ($mtFilter == 'total')
					$msg = $MSG[$mtType . '_total'][$sysSession->lang] . $MSG[$mtOP][$sysSession->lang] . $mtVal . ($mtType == 'progress' ? $MSG['msg_val2'][$sysSession->lang] : $MSG['msg_val1'][$sysSession->lang]) ;
				else if ($mtFilter == 'off')
					$msg = $MSG[$mtType . '_off'][$sysSession->lang] . $MSG[$mtOP][$sysSession->lang] . $mtVal . $MSG['msg_val3'][$sysSession->lang];
				else if ($mtFilter == 'last')
					$msg = $MSG[$mtType . '_last_login'][$sysSession->lang] . $MSG[$mtOP][$sysSession->lang] . $mtVal;
				else if ($mtType == 'progress' && $mtFilter == 'total')
					$msg = $MSG['progress_total'][$sysSession->lang] . $MSG[$mtOP][$sysSession->lang] . $mtVal;
				else if ($mtType == 'progress' && $mtFilter == 'page')
					$msg = $MSG['progress_page'][$sysSession->lang] . $MSG[$mtOP][$sysSession->lang] . $mtVal;
				break;
			case 'homework':
			case 'exam':
			case 'questionnaire':
				if ($mtFilter == 'no')
					$msg = $MSG[$mtType . '_not_do'][$sysSession->lang] . $MSG[$mtOP][$sysSession->lang] . $mtVal . $MSG['msg_val1'][$sysSession->lang];
				else if ($mtFilter == 'yes')
					$msg = $MSG[$mtType . '_do'][$sysSession->lang] . $MSG[$mtOP][$sysSession->lang] . $mtVal . $MSG['msg_val1'][$sysSession->lang];
				else if ($mtFilter == 'some')
					$msg = $MSG[$mtType . '_do_' . $mtOP][$sysSession->lang] . $qti_list[$mtType][$mtVal][$sysSession->lang];
				break;
		}
		return divMsg('220', $msg);
	}

	/**
	 * 顯示點名期間
	 */
	function showDuration($begin_time, $end_time, $frequence, $freq_extra) {
		global $MSG, $sysSession;
		
		if ($frequence == 'once') return divMsg('138', $freq_extra, $freq_extra);
		
		$begin_time = ($begin_time == '' || $begin_time == '0000-00-00 00:00:00') ? "<font color='red'>{$MSG['msg_no_begin_date'][$sysSession->lang]}</font>"  : date('Y-m-d', strtotime($begin_time));
		$end_time 	= ($end_time   == '' || $end_time   == '0000-00-00 00:00:00') ? "<font color='red'>{$MSG['msg_no_end_date'][$sysSession->lang]}</font>" : date('Y-m-d', strtotime($end_time));
		$caption    = $MSG['from'][$sysSession->lang] . $begin_time . '<br>' . $MSG['to'][$sysSession->lang] . $end_time;
		return divMsg('170', $caption, strip_tags($caption));
	}

	/**
	 * 顯示點名頻率
	 */
	function showFrequence($freq) {
		global $MSG, $sysSession;
		return divMsg('50', $MSG['roll_call_freq_'.$freq][$sysSession->lang]);
	}

	// 取得所有的組次列表
	$RS = dbGetStMr('WM_student_separate', '`team_id`, `team_name`', "`course_id`={$sysSession->course_id} order by `permute` ASC", ADODB_FETCH_ASSOC);
	$teams = array();
	if ($RS) {
		while (!$RS->EOF) {
			$teams[$RS->fields['team_id']] = getCaption($RS->fields['team_name']);
			$RS->MoveNext();
		}
	}

	$RS = dbGetStMr('WM_student_group', '`group_id`, `team_id`, `caption`', "`course_id`={$sysSession->course_id} order by `team_id` ASC, `permute` ASC", ADODB_FETCH_ASSOC);
	if ($RS) {
		while (!$RS->EOF) {
			$group[$RS->fields['team_id']][$RS->fields['group_id']] = getCaption($RS->fields['caption']);
			$RS->MoveNext();
		}
	}

	// 取得作業 測驗 問卷的名稱
	$qti_list = array();
	foreach (array('exam', 'homework', 'questionnaire') as $type) {
		$RS = dbGetStMr('WM_roll_call as A left join WM_qti_' . $type . '_test as B on A.mtVal = B.exam_id', 'B.exam_id, B.title', "A.course_id = {$sysSession->course_id} and A.mtType = '{$type}' and A.mtFilter = 'some'", ADODB_FETCH_ASSOC);
		if ($RS) {
			while (!$RS->EOF) {
				$qti_list[$type][$RS->fields['exam_id']] = unserialize($RS->fields['title']) ;
				$RS->MoveNext();
			}
		}
	}

	$js = <<< BOF

	/**
	 * 取得勾選的點名規則
	 * @return array nid
	 **/
	function getCkVal() {
		var nid = new Array();
		var obj = document.getElementById('dataTabs');
		var nodes = obj.getElementsByTagName("input");
		if ((nodes == null) || (nodes.length <= 0)) return nid;
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			if (nodes[i].checked) {
				nid[nid.length] = nodes[i].value;
			}
		}
		return nid;
	}

	/**
	 * 新增或者修改點名規則
	 * @param val int 點名規則編號
	 */
	function setRollCall(val) {
		var obj = document.getElementById("modifyFm");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		obj.nid.value = val;
		obj.submit();
	}

	/**
	 * 啟用 停用 刪除 點名規則
	 * @param func string 功能
	 * rm      : 刪除
	 * enable  : 啟用
	 * disable : 停用
	 */
	function setStatus(func) {
		var nid = getCkVal();
		if (nid.length <= 0) {
			alert("{$MSG['roll_call_select_empty'][$sysSession->lang]}");
			return false;
		}

		if (func == 'rm' && !confirm("{$MSG['roll_call_confirm_del'][$sysSession->lang]}")) return false;
		obj = document.getElementById("chgStatusFm");
		val = nid.toString();
		if ((obj != null) && (val != "")) {
			obj.nids.value = val;
			obj.func.value = func;
			obj.submit();
		}
	}
	
	function chgPage()
	{
		return '&tabs=2';
	}

BOF;

	showXHTML_script('inline', $js);

	showXHTML_table_B('width="1000" align="center" border="0" cellspacing="0" cellpadding="0" class="cssTable" id="tabsSystem"');
		showXHTML_tr_B();
			showXHTML_td_B();
				$myTable = new table();
				$myTable->add_help($MSG['mail_roll_call_system_readme'][$sysSession->lang]);
				$myTable->extra = 'width="1000" border="0" cellspacing="1" cellpadding="3" id="dataTabs" ';
				// 工具列
				$toolbar = new toolbar();
				$toolbar->add_caption('&nbsp;&nbsp;');
				$toolbar->add_input('button', '', $MSG['roll_call_add'][$sysSession->lang] 		, '', 'class="cssBtn" onclick="setRollCall(\'\')"');
				$toolbar->add_input('button', '', $MSG['roll_call_rm'][$sysSession->lang]  		, '', 'class="cssBtn" onclick="setStatus(\'rm\')"');
				$toolbar->add_input('button', '', $MSG['roll_call_enable'][$sysSession->lang] 	, '', 'class="cssBtn" onclick="setStatus(\'enable\')"');
				$toolbar->add_input('button', '', $MSG['roll_call_disable'][$sysSession->lang] 	, '', 'class="cssBtn" onclick="setStatus(\'disable\')"');
				$toolbar->add_caption('&nbsp;&nbsp;');
				$myTable->set_def_toolbar($toolbar);
				// 全選全消的按鈕
				$myTable->set_select_btn(true, 'btnSel', $MSG['select_all'][$sysSession->lang], 'onclick="selfunc(\'tabsSystem\')"');

				// 資料
				$ck1 = new toolbar();
				$ck1->add_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" onclick="selfunc(\'tabsSystem\')"');

				// $ck2 = new toolbar();
				// $ck2->add_input('checkbox', 'node_id[]', '%serial_id', '', 'onclick="chgCheckbox(\'tabsSystem\'); event.cancelBubble=true;"');

				$btns = new toolbar();
				$btns->add_input('button', '', $MSG['roll_call_modify'][$sysSession->lang], '', 'class="cssBtn" onclick="setRollCall(\'%serial_id\')"');

				// $myTable->add_field($ck1                                       	  	, $MSG['select_all_msg'][$sysSession->lang], '', $ck2, ''             , 'width="20" align="center"');
				$myTable->add_field($ck1, $MSG['select_all_msg'][$sysSession->lang], '', '%serial_id%enable%begin_time%end_time%frequence', 'showCheckBox', 'width="20" align="center"');
				$myTable->add_field($MSG['roll_call_enable'][$sysSession->lang]   	, '', '', '%enable' , 'showEnable'  , 'nowrap="noWrap"');
				$myTable->add_field($MSG['roll_call_roles'][$sysSession->lang] 		, '', '', '%role'    , 'showRoles' , 'nowrap="noWrap"');
				$myTable->add_field($MSG['roll_call_group'][$sysSession->lang]		, '', '', '%team_id%group_id'    , 'showGroup' , 'nowrap="noWrap"');
				$myTable->add_field($MSG['roll_call_cond'][$sysSession->lang]		, '', '', '%mtType%mtFilter%mtOP%mtVal'    , 'showCondition' , 'nowrap="noWrap"');
				$myTable->add_field($MSG['roll_call_duration'][$sysSession->lang]	, '', '', '%begin_time%end_time%frequence%freq_extra'    , 'showDuration'   , 'align="center" nowrap="noWrap"');
				$myTable->add_field($MSG['roll_call_freq'][$sysSession->lang]   	, '', '', '%frequence'    , 'showFrequence', 'align="center"' );
				$myTable->add_field($MSG['roll_call_modify'][$sysSession->lang]    	, '', '', $btns   , ''             , 'align="center"' );

				$myTable->set_sqls('WM_roll_call', '`serial_id`, `team_id`, `group_id`, `enable`, `role`, `mtType`, `mtFilter`, `mtOP`, `mtVal`, `frequence`, `freq_extra`, `begin_time`, `end_time`', '`course_id`=' . $sysSession->course_id . ' order by `serial_id`');
				$myTable->set_page(true, 1, sysPostPerPage, 'chgPage();');
				
				foreach($MSG['no_roll_data'] as $msg_key => $msg_val){
					$MSG['no_data'][$msg_key] = $msg_val;
				}

				$myTable->show();
			showXHTML_td_E();
		showXHTML_tr_E();
	showXHTML_table_E();
?>
