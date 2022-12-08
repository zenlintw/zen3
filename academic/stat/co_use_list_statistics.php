<?php
	/**
	 * 學校統計資料 - 硬碟空間使用率
	 * 建立日期：2004/08/16
	 * @author  Amm Lee
	 * @version $Id: sch_quota1.php,v 1.1 2010/02/24 02:38:43 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/sch_statistics2.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

    set_time_limit(0);

	$sysSession->cur_func = '1500200100';
	$sysSession->restore();
	if (!aclVerifyPermission(1500200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$ticket = md5(sysTicketSeed . $sysSession->username . 'QuotaQuery' . $sysSession->ticket);
	// if ($_POST['ticket'] != $ticket) die ($MSG['illegal_access'][$sysSession->lang]);

	$icon_up = '<img src="/theme/' . $sysSession-> theme . '/academic/dude07232001up.gif" border="0" align="absmiddl">';
	$icon_dn = '<img src="/theme/' . $sysSession-> theme . '/academic/dude07232001down.gif" border="0" align="absmiddl">';

	$page_num = isset($_POST['page_num']) ? intval($_POST['page_num']) : sysPostPerPage;
	
	$group_key  = Array('', 'WM_user_account.username', 'first_name', 'use_times', 'use_people');
	
	$gp_key    = intval($_POST['gp_sortby']);
	if (empty($group_key[$gp_key])) $gp_key = 1;
			$gp_sortby = $group_key[$gp_key];
			
			
			$gp_order  = (trim($_POST['gp_order']) == 'asc') ? 'asc' : 'desc';

			$all_page=dbGetOne('WM_term_major','count(distinct(username))','role&512');

			
	$total_page = ceil($all_page / $page_num);
	
	if (!isset($_POST['page_no']))
	{
		if ($total_page > 0)
		{
			$cur_page = 1;

			$limit_begin = (($cur_page -1)* $page_num);
			$limit_str = ' limit ' . $limit_begin . ',' . $page_num;
		}
		else if ($total_page == 0)
		{
			$cur_page = 0;
		}
	}
	else
	{
		if ($_POST['page_no'] >  0)
		{
	        $cur_page = intval($_POST['page_no']);
			if ($cur_page < 0 || $cur_page > $total_page) $cur_page = 1;
		    $limit_begin = (($cur_page -1)* $page_num);
			$limit_str = ' limit ' . $limit_begin . ',' . $page_num;
		}
		else if ($_POST['page_no'] == 0)
		{
			$cur_page = 0;
			$limit_str = '';
		}
	}



   $js = <<< EOF

	var cur_page = {$cur_page};
    var total_page = {$total_page};
   /*
   * 標題排序
   */
   function schoolSort(val) {
		var obj = document.getElementById("schoolForm");
		if ((typeof(obj) != "object") || (obj == null)) return false;

		if (obj.sortby.value == val)
			obj.order.value   = (obj.order.value == 'asc') ? 'desc' : 'asc';
		obj.sortby.value  = val;
		obj.page_no.value = cur_page;
		window.onunload   = null;
		obj.submit();
	}

	/*
	 * 標題排序
	 */
	function GroupSort(val) {
		var obj = document.getElementById("schoolForm");
		if ((typeof(obj) != "object") || (obj == null)) return false;

		if (obj.gp_sortby.value == val)
			obj.gp_order.value  = (obj.gp_order.value == 'asc') ? 'desc' : 'asc';
		obj.gp_sortby.value = val;
		obj.page_no.value   = cur_page;
		window.onunload     = null;
		obj.submit();
	}

	function page(n){
		var obj = document.getElementById("schoolForm");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		obj.page_no.value = n;
		switch (n)
		{
			case -1:
				obj.page_no.value = 1;
				break;
			case -2:
				obj.page_no.value = (cur_page - 1);
				break;
			case -3:
				obj.page_no.value = (cur_page + 1);
				break;
			case -4:
				obj.page_no.value = (total_page);
				break;
			default:
				var page_no = parseInt(n);
		}

		window.onunload = function () {};
		obj.submit();
	}

	function quota_init() {
		var txt1 = '';

		obj = document.getElementById("toolbar1");
		if ((typeof(obj) == "object") && (obj != null)) txt1 = obj.innerHTML;

		obj = document.getElementById("toolbar2");
		if ((typeof(obj) == "object") && (obj != null)) obj.innerHTML = txt1;
	};

	if (window.attachEvent)
		window.attachEvent("onload", quota_init);
	else
		window.addEventListener("load", quota_init, false);
EOF;

	showXHTML_head_B($MSG['title5'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('include', 'sch_statistics.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E('');

	showXHTML_body_B('');

		showXHTML_table_B('width="600" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary[] = array($MSG['title159_1'][$sysSession->lang], 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');

			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup" ');
					showXHTML_table_B('id ="mainTable" width="600" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td_B('colspan="6" id="toolbar1"');
								echo $MSG['page'][$sysSession->lang];

								$P = $total_page > 0 ? array_merge(array($MSG['all'][$sysSession->lang]), range(1,$total_page)) : array($MSG['all'][$sysSession->lang]);
								showXHTML_input('select', '', $P, $cur_page, 'class="cssInput" onchange="page(this.value);"');

								echo '&nbsp;&nbsp;';

								showXHTML_input('button', 'firstBtn1', $MSG['first1'][$sysSession->lang], '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==1) || ($cur_page==0)) ?          'disabled="true" ' : 'onclick="page(-1);"') . ' title=' . $MSG['switch_page'][$sysSession->lang]);
								showXHTML_input('button', 'prevBtn1', $MSG['prev'][$sysSession->lang], '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==1) || ($cur_page==0))?          'disabled="true" ' : 'onclick="page(-2);"') . ' title=' . $MSG['switch_page1'][$sysSession->lang]);
								showXHTML_input('button', 'nextBtn1', $MSG['next'][$sysSession->lang], '', 'id="nextBtn1" class="cssBtn" ' . ((($cur_page==0) || ($cur_page==$total_page))?          'disabled="true" ' : 'onclick="page(-3);"') . ' title=' . $MSG['switch_page2'][$sysSession->lang]);
								showXHTML_input('button', 'lastBtn1', $MSG['last1'][$sysSession->lang], '', 'id="lastBtn1" class="cssBtn" ' . ((($cur_page==0) || ($cur_page==$total_page))?          'disabled="true" ' : 'onclick="page(-4);"') . ' title=' . $MSG['switch_page3'][$sysSession->lang]);
								// showXHTML_input('button', 'go_back', $MSG['title168'][$sysSession->lang], '', 'id="go_back" class="cssBtn" ' .  'onclick="do_fun(9);" ' . ' title=' . $MSG['title149'][$sysSession->lang]);

							showXHTML_td_E('');

						showXHTML_tr_E('');


						
						
									$sort_cond = " order by {$gp_sortby} {$gp_order}";

									showXHTML_tr_B('class="cssTrHead"');
										showXHTML_td_B(' align="center" nowrap="noWrap" onclick="GroupSort(1);" title="' . $MSG['number'][$sysSession->lang] . '"');
											echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
											echo $MSG['number'][$sysSession->lang];
											echo ($gp_sortby == 'WM_user_account.username') ? ($gp_order == 'desc' ? $icon_dn : $icon_up) : '';
											echo '</a>';
										showXHTML_td_E('');

										showXHTML_td_B(' align="center" nowrap="noWrap" onclick="GroupSort(2);" title="' . $MSG['name'][$sysSession->lang] . '"');
											echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
											echo $MSG['name'][$sysSession->lang];
											echo ($gp_sortby == 'first_name') ? ($gp_order == 'desc' ? $icon_dn : $icon_up) : '';
											echo '</a>';
										showXHTML_td_E('');

										showXHTML_td_B(' align="center" nowrap="noWrap" onclick="GroupSort(3);" title="' . $MSG['Interactive_use_times'][$sysSession->lang] . '"');
											echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
											echo $MSG['Interactive_use_times'][$sysSession->lang];
											echo ($gp_sortby == 'use_times') ? ($gp_order == 'desc' ? $icon_dn : $icon_up) : '';
											echo '</a>';
										showXHTML_td_E('');
										
										showXHTML_td_B(' align="center" nowrap="noWrap" onclick="GroupSort(4);" title="' . $MSG['interactive_participants'][$sysSession->lang] . '"');
											echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
											echo $MSG['interactive_participants'][$sysSession->lang];
											echo ($gp_sortby == 'use_people') ? ($gp_order == 'desc' ? $icon_dn : $icon_up) : '';
											echo '</a>';
										showXHTML_td_E('');
										

									showXHTML_tr_E('');
									// $sysConn->debug=true;
									$cour_str = dbGetOne('WM_term_major','count(*)','role&512' , ADODB_FETCH_ASSOC);

									if ($cour_str > 0) {
										$GP_RS = dbGetStMr('WM_term_major left join WM_user_account on WM_user_account.username=WM_term_major.username','*,WM_term_major.username as username,
										((select count(*) from WM_qti_exam_test left join WM_term_course 
										ON WM_term_course.course_id=WM_qti_exam_test.course_id and WM_term_course.status!=9 
										where type=5 and WM_qti_exam_test.course_id=WM_term_major.course_id)+
										(select count(*) from WM_qti_questionnaire_test left join WM_term_course 
										ON WM_term_course.course_id=WM_qti_questionnaire_test.course_id and WM_term_course.status!=9 
										where type=5 and WM_qti_questionnaire_test.course_id=WM_term_major.course_id) ) as use_times,
										((select count(*) from  WM_qti_exam_result left join WM_qti_exam_test 
										ON WM_qti_exam_test.exam_id=WM_qti_exam_result.exam_id 
										where WM_qti_exam_test.type=5 and WM_qti_exam_test.course_id=WM_term_major.course_id)+
										(select count(*) from   WM_qti_questionnaire_result  left join WM_qti_questionnaire_test  
										ON WM_qti_questionnaire_result.exam_id=WM_qti_questionnaire_test .exam_id 
										where WM_qti_questionnaire_test.type=5 and WM_qti_questionnaire_test.course_id=WM_term_major.course_id)) as use_people','role&512 group by WM_term_major.username' . $sort_cond . $limit_str, ADODB_FETCH_ASSOC);
										if ($GP_RS->RecordCount() > 0) {
											while ($GP_RS1 = $GP_RS->FetchRow()) {
												$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
												showXHTML_tr_B($col);
													showXHTML_td('align="left"', $GP_RS1['username']);
													showXHTML_td('align="left"', $GP_RS1['first_name']);
													showXHTML_td('align="left"', $GP_RS1['use_times']);
													showXHTML_td('align="left"', $GP_RS1['use_people']);
												showXHTML_tr_E('');
											}
										}
									}else{
										$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
										showXHTML_tr_B($col);
											showXHTML_td('align="left" colspan="6"', $MSG['title66'][$sysSession->lang]);
										showXHTML_tr_E('');
									}
									
									
						// 換頁與動作功能列 (function line)
            			$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
            				showXHTML_td('colspan="6" nowrap id="toolbar2"', '&nbsp;');
            			showXHTML_tr_E('');

					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');

		// 學校 & 群組 & 排序 & 翻頁
		showXHTML_form_B('action="' . $_SERVER['PHP_SELF']. '" method="post" enctype="multipart/form-data" style="display:none" target="_self"', 'schoolForm');
			showXHTML_input('hidden', 'ticket', $ticket, '', '');
			showXHTML_input('hidden', 'target_member', $_POST['target_member'], '', '');
			showXHTML_input('hidden', 'sortby', $key, '', '');
   			showXHTML_input('hidden', 'order', $order, '', '');
			showXHTML_input('hidden', 'single_all', $_POST['single_all'], '', '');
			showXHTML_input('hidden', 'single_group_id', $_POST['single_group_id'], '', '');
			showXHTML_input('hidden', 'single_course_id', $_POST['single_course_id'], '', '');
			showXHTML_input('hidden', 'gp_sortby', $gp_key, '', '');
   			showXHTML_input('hidden', 'gp_order', $gp_order, '', '');
			showXHTML_input('hidden', 'page_num', $page_num, '', '');
			showXHTML_input('hidden', 'page_no', '', '', '');
		showXHTML_form_E('');

	showXHTML_body_E('');
	
?>
