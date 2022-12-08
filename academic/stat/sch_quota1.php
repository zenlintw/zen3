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
	require_once(sysDocumentRoot . '/lang/sch_statistics.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '1500200100';
	$sysSession->restore();
	if (!aclVerifyPermission(1500200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$ticket = md5(sysTicketSeed . $sysSession->username . 'QuotaQuery' . $sysSession->ticket);
	if ($_POST['ticket'] != $ticket) die ($MSG['illegal_access'][$sysSession->lang]);

	$icon_up = '<img src="/theme/' . $sysSession-> theme . '/academic/dude07232001up.gif" border="0" align="absmiddl">';
	$icon_dn = '<img src="/theme/' . $sysSession-> theme . '/academic/dude07232001down.gif" border="0" align="absmiddl">';

	$school_key = array('', 'school_name', 'school_host', 'quota_limit', 'quota_used');
	$group_key  = Array('', 'course_id', 'caption', 'quota_limit', 'quota_used');

	// 抓取所有的課程群組
	$csGpTree = array();
	$RS = dbGetStMr('`WM_term_group`', '*', '1 order by `parent`, `permute`', ADODB_FETCH_ASSOC);

	if ($RS && $RS->RecordCount() > 0)
	{
		while ($RS1 = $RS->FetchRow()) {
			$csGpTree[$RS1['parent']][$RS1['permute']] = $RS1['child'];
		}
	}

	$page_num = isset($_POST['page_num']) ? intval($_POST['page_num']) : sysPostPerPage;

	switch ($_POST['target_member']) {
		case 1:		// 整所學校
			$key    = intval($_POST['sortby']);
			if (empty($school_key[$key])) $key = 1;
			$sortby = $school_key[$key];
			$order  = (trim($_POST['order']) == 'asc') ? 'asc' : 'desc';

			$sort_cond = '  order by ' . " $sortby  $order ";
			list($all_page) = dbGetStSr('WM_school', 'count(*)', 'school_id=' . $sysSession->school_id . $sort_cond, ADODB_FETCH_NUM);

			break;
		case 2:		// 某個課程群組
			$gp_key    = intval($_POST['gp_sortby']);
			if (empty($group_key[$gp_key])) $gp_key = 1;
			$gp_sortby = $group_key[$gp_key];
			$gp_order  = (trim($_POST['gp_order']) == 'asc') ? 'asc' : 'desc';

			if($_POST['single_all'] == 1) {
				//  課程群組
				// *****	begin
				if ($_POST['single_group_id'] == 10000000){	// 未分組課程
					chkSchoolId('WM_term_course');
					$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
					$data = $sysConn->GetCol('select B.course_id ' .
					                         'from WM_term_course as B ' .
					                         'left join WM_term_group as G ' .
					                         'on B.course_id=G.child ' .
					                         'where G.child is NULL and B.kind="course" and B.status != 9');
					$cour_str = is_array($data) && count($data) ? implode(',', $data) : '';
					$all_page = count($data);
				}else{		//  某群組 底下的所有課程
					$group_id = intval($_POST['single_group_id']);
					$data     = array_keys(getAllCourseInGroup($group_id));
					$cour_str = is_array($data) && count($data) ? implode(',', $data) : '';
					$all_page = count($data);
				}
			}else{
				//  課程
				$cour_str = intval($_POST['single_course_id']);
				$all_page = 1;
			}
			break;
	}

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
					$ary[] = array($MSG['title158'][$sysSession->lang], 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');

			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup" ');
					showXHTML_table_B('id ="mainTable" width="600" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td_B('colspan="5" id="toolbar1"');
								echo $MSG['page'][$sysSession->lang];

								$P = $total_page > 0 ? array_merge(array($MSG['all'][$sysSession->lang]), range(1,$total_page)) : array($MSG['all'][$sysSession->lang]);
								showXHTML_input('select', '', $P, $cur_page, 'class="cssInput" onchange="page(this.value);"');

								echo '&nbsp;&nbsp;';

								showXHTML_input('button', 'firstBtn1', $MSG['first1'][$sysSession->lang], '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==1) || ($cur_page==0)) ?          'disabled="true" ' : 'onclick="page(-1);"') . ' title=' . $MSG['switch_page'][$sysSession->lang]);
								showXHTML_input('button', 'prevBtn1', $MSG['prev'][$sysSession->lang], '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==1) || ($cur_page==0))?          'disabled="true" ' : 'onclick="page(-2);"') . ' title=' . $MSG['switch_page1'][$sysSession->lang]);
								showXHTML_input('button', 'nextBtn1', $MSG['next'][$sysSession->lang], '', 'id="nextBtn1" class="cssBtn" ' . ((($cur_page==0) || ($cur_page==$total_page))?          'disabled="true" ' : 'onclick="page(-3);"') . ' title=' . $MSG['switch_page2'][$sysSession->lang]);
								showXHTML_input('button', 'lastBtn1', $MSG['last1'][$sysSession->lang], '', 'id="lastBtn1" class="cssBtn" ' . ((($cur_page==0) || ($cur_page==$total_page))?          'disabled="true" ' : 'onclick="page(-4);"') . ' title=' . $MSG['switch_page3'][$sysSession->lang]);
								showXHTML_input('button', 'go_back', $MSG['title168'][$sysSession->lang], '', 'id="go_back" class="cssBtn" ' .  'onclick="do_fun(7);" ' . ' title=' . $MSG['title149'][$sysSession->lang]);

							showXHTML_td_E('');

						showXHTML_tr_E('');


						switch ($_POST['target_member']){
								case 1:		// 整所學校
									$sort_cond = " order by $sortby $order";
									$RS = dbGetStMr('WM_school','school_name,school_host,quota_limit,quota_used','school_id=' . $sysSession->school_id . $sort_cond . $limit_str, ADODB_FETCH_ASSOC);

									showXHTML_tr_B('class="cssTrHead"');
										showXHTML_td_B(' align="center" nowrap="noWrap" onclick="schoolSort(1);" title="' . $MSG['title160'][$sysSession->lang] . '"');
											echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
											echo $MSG['title160'][$sysSession->lang];
											echo ($sortby == 'school_name') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
											echo '</a>';
										showXHTML_td_E('');

										showXHTML_td_B(' align="center" nowrap="noWrap" onclick="schoolSort(2);" title="Domain Name"');
											echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
											echo 'Domain Name';
											echo ($sortby == 'school_host') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
											echo '</a>';
										showXHTML_td_E('');

										showXHTML_td_B(' align="center" nowrap="noWrap" onclick="schoolSort(3);" title="' . $MSG['title162'][$sysSession->lang] . '"');
											echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
											echo $MSG['title162'][$sysSession->lang];
											echo ($sortby == 'quota_limit') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
											echo '</a>';
										showXHTML_td_E('');

										showXHTML_td_B(' align="center" nowrap="noWrap" onclick="schoolSort(4);" title="' . $MSG['title163'][$sysSession->lang] . '"');
											echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
											echo $MSG['title163'][$sysSession->lang];
											echo ($sortby == 'quota_used') ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
											echo '</a>';
										showXHTML_td_E('');

										showXHTML_td_B(' align="center" nowrap="noWrap" ');
											echo $MSG['title164'][$sysSession->lang];
										showXHTML_td_E('');

									showXHTML_tr_E('');

									if ($RS && $RS->RecordCount() > 0) {
										while ($RS1 = $RS->FetchRow()) {
											$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
											showXHTML_tr_B($col);
												showXHTML_td('align="left"', $RS1['school_name']);
												showXHTML_td('align="left"', $RS1['school_host']);
												showXHTML_td('align="left"', $RS1['quota_limit']);
												showXHTML_td('align="left"', $RS1['quota_used']);
												$used_rate = $RS1['quota_limit'] > 0 ? (round($RS1['quota_used']/$RS1['quota_limit'],4) * 100) : 0;
												showXHTML_td('align="left" nowrap', $used_rate . ' %');
											showXHTML_tr_E('');
										}
									} else {
										$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
										showXHTML_tr_B($col);
											showXHTML_td('align="left" colspan="5"', $MSG['title66'][$sysSession->lang]);
										showXHTML_tr_E('');
									}

									break;
								case 2:		// 某個課程群組
									$sort_cond = " order by {$gp_sortby} {$gp_order}";

									showXHTML_tr_B('class="cssTrHead"');
										showXHTML_td_B(' align="center" nowrap="noWrap" onclick="GroupSort(1);" title="' . $MSG['title166'][$sysSession->lang] . '"');
											echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
											echo $MSG['title166'][$sysSession->lang];
											echo ($gp_sortby == 'course_id') ? ($gp_order == 'desc' ? $icon_dn : $icon_up) : '';
											echo '</a>';
										showXHTML_td_E('');

										showXHTML_td_B(' align="center" nowrap="noWrap" onclick="GroupSort(2);" title="' . $MSG['title107'][$sysSession->lang] . '"');
											echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
											echo $MSG['title107'][$sysSession->lang];
											echo ($gp_sortby == 'caption') ? ($gp_order == 'desc' ? $icon_dn : $icon_up) : '';
											echo '</a>';
										showXHTML_td_E('');

										showXHTML_td_B(' align="center" nowrap="noWrap" onclick="GroupSort(3);" title="' . $MSG['title162'][$sysSession->lang] . '"');
											echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
											echo $MSG['title162'][$sysSession->lang];
											echo ($gp_sortby == 'quota_limit') ? ($gp_order == 'desc' ? $icon_dn : $icon_up) : '';
											echo '</a>';
										showXHTML_td_E('');

										showXHTML_td_B(' align="center" nowrap="noWrap" onclick="GroupSort(4);" title="' . $MSG['title163'][$sysSession->lang] . '"');
											echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
											echo $MSG['title163'][$sysSession->lang];
											echo ($gp_sortby == 'quota_used') ? ($gp_order == 'desc' ? $icon_dn : $icon_up) : '';
											echo '</a>';
										showXHTML_td_E('');

										showXHTML_td_B(' align="center" nowrap="noWrap" ');
											echo $MSG['title164'][$sysSession->lang];
										showXHTML_td_E('');

									showXHTML_tr_E('');
									if ($cour_str > 0) {
										$GP_RS = dbGetStMr('WM_term_course','course_id,caption,quota_limit,quota_used','course_id in (' . $cour_str . ') and kind="course" and status!=9' . $sort_cond . $limit_str, ADODB_FETCH_ASSOC);
										if ($GP_RS->RecordCount() > 0) {
											while ($GP_RS1 = $GP_RS->FetchRow()) {
												$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
												showXHTML_tr_B($col);
													showXHTML_td('align="left"', $GP_RS1['course_id']);
													$cn_lang = unserialize($GP_RS1['caption']);
													showXHTML_td('align="left"', $cn_lang[$sysSession->lang]);
													showXHTML_td('align="left"', $GP_RS1['quota_limit']);
													showXHTML_td('align="left"', $GP_RS1['quota_used']);
													$used_rate = $GP_RS1['quota_limit'] > 0 ? (round($GP_RS1['quota_used']/$GP_RS1['quota_limit'],4) * 100) : 0;
													showXHTML_td('align="left" nowrap', $used_rate . ' %');
												showXHTML_tr_E('');
											}
										}
									}else{
										$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
										showXHTML_tr_B($col);
											showXHTML_td('align="left" colspan="5"', $MSG['title66'][$sysSession->lang]);
										showXHTML_tr_E('');
									}
									break;
						}
						// 換頁與動作功能列 (function line)
            			$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
            				showXHTML_td('colspan="5" nowrap id="toolbar2"', '&nbsp;');
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
