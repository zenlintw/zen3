<?php
	/**
	 * 學校統計資料 - 教材閱讀統計 (course)
	 *
	 * 建立日期：2004/08/16
	 * @author  Amm Lee
	 * @version $Id: cour_material_statistics1.php,v 1.1 2010/02/24 02:38:43 saly Exp $
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

	$ticket = md5(sysTicketSeed . $sysSession->username . 'cour_material_stat' . $sysSession->ticket);

	$icon_up = '<img src="/theme/default/academic/dude07232001up.gif" border="0" align="absmiddl">';
	$icon_dn = '<img src="/theme/default/academic/dude07232001down.gif" border="0" align="absmiddl">';

	$ta = array('', 'course_id', 'caption');
	$sortby = min(2, max(1, $_POST['sortby']));
	$sortby = $ta[$sortby];

	$order = ($_POST['order']== 'asc') ? 'asc' : 'desc';
	$query = 'kind = "course" and status != 9 ';

	if (empty($_POST['page_num'])){
		$page_num = sysPostPerPage;
	}else{
		$page_num = intval($_POST['page_num']);
	}

	if (! empty($_POST['tea_query']) && $_POST['tea_query'] != $MSG['title105'][$sysSession->lang]){
		$tea_query = escape_LIKE_query_str(trim($_POST['tea_query']));

		$ta_rs = dbGetStMr('WM_term_major','course_id','role&' . ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']) . ' and username like "%' . $tea_query . '%"', ADODB_FETCH_ASSOC);

		$cour_arr = array();

		if ($ta_rs->RecordCount() > 0){

			while ($fields = $ta_rs->FetchRow()){
				if (! in_array($fields['course_id'],$cour_arr)){
					$cour_arr[] = $fields['course_id'];
				}
			}

			if (count($cour_arr) > 0)
				$query .= ' and course_id in (' . implode(',', $cour_arr) . ') ';
			else
				$query .= ' and course_id = null';

		}else
			$query .= ' and course_id = null';
	}

	if (! empty($_POST['cour_query']) && $_POST['cour_query'] != $MSG['title105'][$sysSession->lang]){
		$temp = escape_LIKE_query_str(addslashes(trim($_POST['cour_query'])));

    	$query .= " and caption like '%" . $temp . "%'";
	}

	list($all_page) = dbGetStSr('WM_term_course','count(*)',$query, ADODB_FETCH_NUM);

	$total_page = ceil($all_page / max(1, $page_num));

	if ($_POST['page_no'] == ''){
		if ($total_page > 0){
			$cur_page = 1;

			$limit_begin = (($cur_page -1)* $page_num);
			$limit_str = ' limit ' . $limit_begin . ',' . $page_num;

		}else if ($total_page == 0){
			$cur_page = 0;
		}

	}else{
		if (($_POST['page_no'] >  0)){
	        $cur_page = intval($_POST['page_no']);
			if ($cur_page < 0 || $cur_page > $total_page) $cur_page = 1;
		    $limit_begin = (($cur_page -1)* $page_num);
			$limit_str = ' limit ' . $limit_begin . ',' . $page_num;
	}else if ($_POST['page_no'] == 0){
			$cur_page = 0;
			$limit_str = '';
		}
	}

	$query .= " order by $sortby  $order";

    $RS = dbGetStMr('WM_term_course','course_id,caption',$query . $limit_str, ADODB_FETCH_ASSOC);

	if ($_POST['ticket'] != $ticket) die ($MSG['illegal_access'][$sysSession->lang]);

	$js = <<< EOF
	var theme          = "{$sysSession->theme}";
	var ticket         = "{$ticket}";
	var lang           = "{$lang}";
	var cur_page       = {$cur_page};
    var total_page     = {$total_page};

	function page(n){
	    var obj = document.getElementById("selfQuery");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		obj.page_no.value = n;
		switch(n){
    		case -1:
    			obj.page_no.value = 1;
    			break;
    		case -2:
    			obj.page_no.value = (cur_page-1);
    			break;
    		case -3:
    			obj.page_no.value = (cur_page+1);
    			break;
    		case -4:
    			obj.page_no.value = (total_page);
    			break;
    		default:
    			var page_no = parseInt(n);
		}
		obj.action = 'cour_material_statistics1.php';

		window.onunload = function () {};

		obj.submit();
    }


   /*
    * 標題排序
   */
   function chgPageSort(val) {
        var obj = document.getElementById("selfQuery");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		obj.order.value  = obj.order.value == 'asc' ? 'desc' : 'asc';
		obj.sortby.value = val;
		window.onunload  = function () {};
        obj.submit();
	}
	
	function cur_material(){

		var inputs = document.getElementsByTagName('input');

		var cour_id = 0;

		for(var i = 0; i < inputs.length; i++){
			if ((inputs[i].type.toLowerCase() == 'radio') && (inputs[i].checked == true)){
				cour_id = inputs[i].value;
			}
		}

		if (cour_id == 0){
			alert("{$MSG['title119'][$sysSession->lang]}");
			return false;
		}

	    var obj = document.getElementById("selfQuery");

		obj.course_id.value = cour_id;
		obj.page_no.value = cur_page;
		obj.action = 'cour_material_statistics2.php';

		window.onunload = function () {};

		obj.submit();
	}


	var orgload = window.onload;
	window.onload = function () {
		orgload();

		var txt1 = '';

		obj = document.getElementById("toolbar1");

		if ((typeof(obj) == "object") && (obj != null)) txt1 = obj.innerHTML;

		obj = document.getElementById("toolbar2");
		if ((typeof(obj) == "object") && (obj != null)) obj.innerHTML = txt1;

	};

EOF;

	showXHTML_head_B($MSG['title4'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('include', 'sch_statistics.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E('');

	showXHTML_body_B('');

		showXHTML_table_B('width="650" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary[] = array($MSG['title101'][$sysSession->lang], 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');

			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup" ');

					showXHTML_table_B('id ="mainTable" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						showXHTML_input('hidden', 'sortby', $_POST['sortby'], '', '');
	        			showXHTML_input('hidden', 'order', $order, '', '');

						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td_B('colspan="4" id="toolbar1"');

							$ary = array($MSG['all'][$sysSession->lang]);
            				echo $MSG['page'][$sysSession->lang];

                            for($j=0; $j<=$total_page; $j++){
                                if ($j == 0){
                                    $P[$j]=$MSG['all'][$sysSession->lang];
                                }else{
                                    $P[$j]=$j;
                                }
                			}

            			    showXHTML_input('select', '', $P, $cur_page, 'size="1" onchange="page(this.value);"');
            				echo '&nbsp;&nbsp;';
            				showXHTML_input('button', 'firstBtn1', $MSG['first1'][$sysSession->lang]  , '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==1) || ($cur_page==0)) ?          'disabled="true" ' : 'onclick="page(-1);"') . ' title=' . $MSG['switch_page'][$sysSession->lang]);
            				showXHTML_input('button', 'prevBtn1' , $MSG['prev'][$sysSession->lang]    , '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==1) || ($cur_page==0))?           'disabled="true" ' : 'onclick="page(-2);"') . ' title=' . $MSG['switch_page1'][$sysSession->lang]);
            				showXHTML_input('button', 'nextBtn1' , $MSG['next'][$sysSession->lang]    , '', 'id="nextBtn1" class="cssBtn" '  . ((($cur_page==0) || ($cur_page==$total_page))? 'disabled="true" ' : 'onclick="page(-3);"') . ' title=' . $MSG['switch_page2'][$sysSession->lang]);
            				showXHTML_input('button', 'lastBtn1' , $MSG['last1'][$sysSession->lang]   , '', 'id="lastBtn1" class="cssBtn" '  . ((($cur_page==0) || ($cur_page==$total_page))? 'disabled="true" ' : 'onclick="page(-4);"') . ' title=' . $MSG['switch_page3'][$sysSession->lang]);
            				showXHTML_input('button', 'cour_mat' , $MSG['title111'][$sysSession->lang], '', 'id="lastBtn1" class="cssBtn" '  . 'onclick="cur_material();"');
							showXHTML_input('button', 'go_back'  , $MSG['title109'][$sysSession->lang], '', 'id="go_back" class="cssBtn" '   . 'onclick="do_fun(5);"' . ' title=' . $MSG['title109'][$sysSession->lang]);
							showXHTML_td_E('');
						showXHTML_tr_E('');

						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('nowrap align="center"', $MSG['title110'][$sysSession->lang]);
							showXHTML_td('nowrap align="center"', $MSG['title121'][$sysSession->lang]);
							showXHTML_td_B(' align="center" nowrap="noWrap" onclick="chgPageSort(1);" title="' . $MSG['title108'][$sysSession->lang] . '"');
								echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">',
                                     $MSG['title108'][$sysSession->lang],
                                    (($sortby == 'course_id') ? ($order == 'desc' ? $icon_dn : $icon_up) : ''),
                                    '</a>';
							showXHTML_td_E('');

							showXHTML_td_B(' align="center" nowrap="noWrap" onclick="chgPageSort(2);" title="' . $MSG['title107'][$sysSession->lang] . '"');
								echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">',
                                     $MSG['title107'][$sysSession->lang],
                                    (($sortby == 'caption') ? ($order == 'desc' ? $icon_dn : $icon_up) : ''),
                                    '</a>';
							showXHTML_td_E('');
						showXHTML_tr_E('');

						// 序號
						$ser_no = $cur_page > 0 ? ($page_num * ($cur_page-1) + 1) :  1;

						if ($RS->RecordCount() > 0){
							while ($fields = $RS->FetchRow()){
								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td('align="center"', '<input type="radio" name="cour_id" value="' . $fields['course_id'] . '">');
									showXHTML_td('align="center"', $ser_no++);
									showXHTML_td('nowrap', $fields['course_id']);
									$lang = unserialize($fields['caption']);
									showXHTML_td('nowrap', $lang[$sysSession->lang]);
								showXHTML_tr_E('');
							}
						}else{
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td('align="cetner" colspan="4"', $MSG['title112'][$sysSession->lang]);
							showXHTML_tr_E('');
						}

						// 換頁與動作功能列 (function line)
            			$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
            				showXHTML_td('colspan="4" nowrap id="toolbar2"', '&nbsp;');
            			showXHTML_tr_E('');

					showXHTML_table_E('');

				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');

		showXHTML_form_B('action="" method="post" enctype="multipart/form-data" style="display:none" target="main"', 'selfQuery');
			showXHTML_input('hidden', 'ticket', $ticket, '', '');
			showXHTML_input('hidden', 'cour_query', htmlspecialchars(stripslashes(trim($_POST['cour_query']))), '', '');
			showXHTML_input('hidden', 'tea_query', trim($_POST['tea_query']), '', '');
			showXHTML_input('hidden', 'page_num', $page_num, '', '');
			showXHTML_input('hidden', 'page_no', '', '', '');
			showXHTML_input('hidden', 'course_id', '', '', '');
			showXHTML_input('hidden', 'sortby', $_POST['sortby'], '', '');
   			showXHTML_input('hidden', 'order', $order, '', '');
		showXHTML_form_E('');

	showXHTML_body_E('');

?>
