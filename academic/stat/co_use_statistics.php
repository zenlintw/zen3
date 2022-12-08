<?php
	/**
	 * 學校統計資料 - 硬碟空間使用率
	 * 建立日期：2004/08/16
	 * @author  Amm Lee
	 * @version $Id: sch_quota.php,v 1.1 2010/02/24 02:38:43 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/sch_statistics.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '1500200100';
	$sysSession->restore();
	if (!aclVerifyPermission(1500200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$js = <<< EOF
	function check_data(){
		var obj = document.getElementById('queryForm');
		if (obj.single_all[0].checked){
			if (obj.single_group_id.value == ''){
				alert("{$MSG['title151'][$sysSession->lang]}");
				obj.single_group.focus();
				return false;
			}
		}
		if (obj.single_all[1].checked){
			if (obj.single_course_id.value == ''){
				alert("{$MSG['title152'][$sysSession->lang]}");
				obj.single_course.focus();
				return false;
			}
		}
		obj.action = 'sch_quota2.php';
		window.onunload = function () {};
		obj.submit();
	}

	function select_cgroup(){
		var ret = showDialog('pickCGroup.php',true,window,true,0,0,'250px','250px','scrollbars=0');
	}

	function select_class(){
		var obj = document.getElementById('single_cgroup_id');
		var nodes = document.getElementsByTagName('input');

		if (obj.value.length == 0) {
			alert("{$MSG['title93'][$sysSession->lang]}");

			obj = document.getElementById('single_cgroup_id');
			obj.focus();
			return false;
		}

		var gd = obj.value;

		var ret = showDialog('pickClass.php?gd='+gd,true,window,true,0,0,'250px','250px','scrollbars=0');
	}

	// 顯示課程名稱於 repost_course 中 (course_name)
	function showCourseCaption(idx,caption) {

		var field = document.getElementById(idx);
		if(!field) return;

		field.value = caption;
	}

	function select_group(){
		var ret = showDialog('pickGroup.php',true,window,true,0,0,'250px','250px','scrollbars=0');
	}

	function select_course(){
		var obj = document.getElementById('single_group_id');

		if (obj.value.length == 0) {
			alert("{$MSG['title64'][$sysSession->lang]}");
			obj = document.getElementById('single_group');
			obj.focus();
			return false;
		}

		var gd = obj.value;
		var ret = showDialog('pickCourse.php?gd='+gd,true,window,true,0,0,'250px','250px','scrollbars=0');
	}
EOF;

	showXHTML_head_B($MSG['title5'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('include', 'sch_statistics.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E('');

	showXHTML_body_B('');
		$ary[] = array($MSG['title159_1'][$sysSession->lang], 'tabs');
		showXHTML_tabFrame_B($ary, 1, 'queryForm', 'ListTable', 'action="" method="POST" style="display: inline;"', false);
				showXHTML_table_B('id ="mainTable" width="680" border="0" cellspacing="1" cellpadding="3" class="cssTable" ');
					$ticket = md5(sysTicketSeed . $sysSession->username . 'QuotaQuery' . $sysSession->ticket);

					showXHTML_input('hidden', 'ticket', $ticket, '', '');

					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';

					showXHTML_tr_B($col);
						showXHTML_td('nowrap', $MSG['title167'][$sysSession->lang]);
						showXHTML_td_B('nowrap colspan="2"');
							showXHTML_input('radio', 'target_member', array(2 => $MSG['title59'][$sysSession->lang]), 2);
							echo '&nbsp;&nbsp;';
							showXHTML_input('text'  , 'single_group'   , '', '', 'id="single_group" class="cssInput" size="20"');
							showXHTML_input('hidden', 'single_group_id', '', '', 'id="single_group_id" class="cssInput" size="20"');
							showXHTML_input('button','btnImp',$MSG['title60'][$sysSession->lang],'','class="cssBtn" onclick="select_group()"');
							echo '<br>&nbsp;&nbsp;';
							showXHTML_input('radio', 'single_all', array(1 => $MSG['title61'][$sysSession->lang]), 1);
							echo '<br>&nbsp;&nbsp;';
							showXHTML_input('radio', 'single_all', array(2 => $MSG['title62'][$sysSession->lang]));
							showXHTML_input('text'  , 'single_course'   , '', '', 'id="single_course" class="cssInput" size="20"');
							showXHTML_input('hidden', 'single_course_id', '', '', 'id="single_course_id" class="cssInput" size="20"');
							showXHTML_input('button','btnImp',$MSG['title63'][$sysSession->lang],'','class="cssBtn" onclick="select_course()"');
						showXHTML_td_E();
					showXHTML_tr_E();

					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td('', $MSG['title106'][$sysSession->lang]);
						showXHTML_td_B('');
							$page_array = array(10 => $MSG['title156'][$sysSession->lang],20 => 20,30 => 30,40 => 40,50 => 50,100 => 100);
							showXHTML_input('select', 'page_num', $page_array,$page_num, 'class="cssInput" id="page_num" onchange="page(this.value);"');
							echo $MSG['title157'][$sysSession->lang];
						showXHTML_td_E('');
					showXHTML_tr_E('');

					showXHTML_tr_B('class="cssTrEvn"');
						showXHTML_td_B('colspan="3" nowrap align="center"');
						  	showXHTML_input('button', '', $MSG['title11'][$sysSession->lang], '', 'class="cssBtn" onclick="check_data();"');
							showXHTML_input('reset','btnImp',$MSG['title22'][$sysSession->lang],'','');
						showXHTML_td_E();
					showXHTML_tr_E();

				showXHTML_table_E();
		showXHTML_tabFrame_E();
	showXHTML_body_E('');

?>