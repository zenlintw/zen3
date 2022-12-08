<?php
	/**
	 * 導師環境 - 成員管理 - 到課統計
	 *
	 * @since   2004/06/30
	 * @author  ShenTing Lin
	 * @version $Id: member_stud_stat.php,v 1.1 2010/02/24 02:38:58 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/member_stud_stat.php');

	$sysSession->cur_func = '1500500100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}

	$icon_up = '<img src="/theme/default/direct/dude07232001up.gif" border="0" align="absmiddl">';
	$icon_dn = '<img src="/theme/default/direct/dude07232001down.gif" border="0" align="absmiddl">';

	$page_num   = empty($_POST['page_num1']) ? sysPostPerPage : intval($_POST['page_num1']);
	$sort_key   = $_POST['sort_key']   == 2 ? 'caption' : 'course_id';
	$sort_state = $_POST['sort_state'] == 'desc' ? 'desc' : 'asc';

	$sqls = ' select B.course_id,C.caption ' .
			' from WM_class_member as A ' .
			' left join WM_term_major as B ' .
			' on A.username = B.username ' .
			' left join WM_term_course as C ' .
			' on B.course_id = C.course_id ' .
			' where A.class_id=' . $sysSession->class_id .
			' and C.status != 9 ' .
			' group by course_id ';

    chkSchoolId('WM_class_member');
	$Row_RS = $sysConn->Execute($sqls);

	if ($Row_RS){
		$all_row    = $Row_RS->RecordCount();
		$total_page = ceil($all_row / $page_num);
	}else{
		$total_page = 0;
	}

	if ($_POST['page_no'] == ''){
		$page_no = 1;
	}else{
		$page_no = intval($_POST['page_no']);
	}

	if ($_POST['page_no'] == ''){
		if ($total_page > 0){
			$cur_page = 1;
			$limit_begin = (($cur_page -1)* $page_num);
		}else if ($total_page == 0){
			$cur_page = 0;
		}
	}else{
		if (($_POST['page_no'] >  0)){
			$cur_page = intval($_POST['page_no']);
			if ($cur_page < 0 || $cur_page > $total_page) $cur_page = 1;
			$limit_begin = (($cur_page -1)* $page_num);
		}else if ($_POST['page_no'] == 0){
			$cur_page = 0;
			$limit_str = '';
		}
	}

	$sqls .= " order by {$sort_key} {$sort_state}";

    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	if ($page_no > 0){
		$RS = $sysConn->SelectLimit($sqls,$page_num,$limit_begin);
	}else{
		$RS = $sysConn->Execute($sqls);
	}


	$js = <<< BOF

	var cur_page = {$cur_page};
    var total_page = {$total_page};
    var page_num = {$page_num};

	function page(n){
	    var obj = document.getElementById("CourList");
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
		obj.page_num1.value = page_num;

		obj.action = 'member_stud_stat.php';
		obj.submit();
    }

	function page_Row(row){
		var obj = null;

		obj = document.getElementById("page_num");

		if ((typeof(obj) != "object") || (obj == null)) return false;

	    obj = document.getElementById("CourList");
		if ((typeof(obj) != "object") || (obj == null)) return false;

		obj.page_num1.value = row;

		obj.action = 'member_stud_stat.php';

		obj.submit();
    }

	function chgPageSort(n){
		var obj = null;

		obj = document.getElementById("CourList");
		if ((typeof(obj) != "object") || (obj == null)) return false;

		obj.sort_key.value   = n;
		obj.sort_state.value = obj.sort_state.value == 'asc' ? 'desc' : 'asc';
		obj.submit();
	}

	function course_radio(){
		var obj = null,nodes = null,course_id = 0;

		nodes = document.getElementsByTagName("input");

		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type == "radio") && (nodes[i].checked == true)){
				course_id = nodes[i].value;
			}
		}

		if (course_id == 0){
			alert("{$MSG['put_radio'][$sysSession->lang]}");
			return false;
		}

		obj = document.getElementById('StatFm');
		obj.course_id.value = parseInt(course_id);

		obj.submit();
	}

	function course_link(course_id){
		var obj = null;

		obj = document.getElementById('StatFm');
		obj.course_id.value = parseInt(course_id);

		obj.submit();
	}

	window.onload = function () {
		var txt1 = '';

		var obj = document.getElementById("toolbar1");
		if ((typeof(obj) == "object") && (obj != null)) txt1 = obj.innerHTML;

		obj = document.getElementById("toolbar2");
		if ((typeof(obj) == "object") && (obj != null)) obj.innerHTML = txt1;
    };

BOF;

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['title'][$sysSession->lang], 'tabs1');

		showXHTML_tabFrame_B($ary, 1, 'CourList', '', 'action="" method="post" enctype="multipart/form-data" style="display: inline;"'); //, isDragable);
			showXHTML_table_B('width="600" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_input('hidden', 'page_no'   , ''         );
				showXHTML_input('hidden', 'page_num1' , ''         );
				showXHTML_input('hidden', 'sort_key'  , $sort_key  );
				showXHTML_input('hidden', 'sort_state', $sort_state);

				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('colspan="3"', $MSG['title_statement'][$sysSession->lang]);
				showXHTML_tr_E();

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('width="600" colspan="3" id="toolbar1"');
						echo $MSG['page'][$sysSession->lang];

    					$P    = range(0, $total_page);
    					$P[0] = $MSG['all'][$sysSession->lang];
						showXHTML_input('select', '', $P, $cur_page, 'class="cssInput" onchange="page(this.value);"');

						echo $MSG['title106'][$sysSession->lang], $MSG['title_page'][$sysSession->lang];
						$page_array = array(10 => $MSG['default_row'][$sysSession->lang],20 => 20,30 => 30,40 => 40,50 => 50,100 => 100);
						showXHTML_input('select', 'page_num', $page_array,$page_num, 'class="cssInput" id="page_num" onchange="page_Row(this.value);"');
						echo '&nbsp;', $MSG['title_row'][$sysSession->lang], $MSG['title157'][$sysSession->lang];
        				showXHTML_input('button', 'firstBtn1', $MSG['first1'][$sysSession->lang]   , '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==1) || ($cur_page==0))          ? 'disabled="true" ' : 'onclick="page(-1);"') . ' title=' . $MSG['switch_page'][$sysSession->lang] );
        				showXHTML_input('button', 'prevBtn1' , $MSG['prev'][$sysSession->lang]     , '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==1) || ($cur_page==0))          ? 'disabled="true" ' : 'onclick="page(-2);"') . ' title=' . $MSG['switch_page1'][$sysSession->lang]);
        				showXHTML_input('button', 'nextBtn1' , $MSG['next'][$sysSession->lang]     , '', 'id="nextBtn1"  class="cssBtn" ' . ((($cur_page==0) || ($cur_page==$total_page))? 'disabled="true" ' : 'onclick="page(-3);"') . ' title=' . $MSG['switch_page2'][$sysSession->lang]);
        				showXHTML_input('button', 'lastBtn1' , $MSG['last1'][$sysSession->lang]    , '', 'id="lastBtn1"  class="cssBtn" ' . ((($cur_page==0) || ($cur_page==$total_page))? 'disabled="true" ' : 'onclick="page(-4);"') . ' title=' . $MSG['switch_page3'][$sysSession->lang]);
						showXHTML_input('button', ''         , $MSG['title_btn'][$sysSession->lang], '', 'style="24"     class="cssBtn" onclick="course_radio()"');
					showXHTML_td_E();
				showXHTML_tr_E();

                showXHTML_tr_B('class="cssTrHead"');
                	showXHTML_td('algin="center" width="70"', $MSG['title_radio'][$sysSession->lang]);
					showXHTML_td_B('algin="center" width="100" title="' . $MSG['title_td'][$sysSession->lang] . '"');
						echo '<a class="cssAnchor" href="javascript:chgPageSort(1);" >',
                			 $MSG['title_td'][$sysSession->lang],
                			 ($sort_key == 'course_id' ? ($sort_state == 'desc' ? $icon_dn : $icon_up) : ''),
                		     '</a>';
					showXHTML_td_E();

					showXHTML_td_B('algin="center" width="430" title="' . $MSG['title_td1'][$sysSession->lang] . '"');
						echo '<a class="cssAnchor" href="javascript:chgPageSort(2);" >',
                			 $MSG['title_td1'][$sysSession->lang],
                			 ($sort_key == 'caption' ? ($sort_state == 'desc' ? $icon_dn : $icon_up) : ''),
                		    '</a>';
					showXHTML_td_E();
				showXHTML_tr_E();

				if ($RS){
					while ($RS1 = $RS->FetchRow()){
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td_B('width="70"');
								echo '<input type="radio" name="cour_id" value="' . $RS1['course_id'] . '">';
							showXHTML_td_E();
            				showXHTML_td('nowrap width="100"', $RS1['course_id']);
            				showXHTML_td_B('nowrap width="430"');
								$cour_lang = unserialize($RS1['caption']);
								echo '<a class="cssAnchor" href="javascript:void(null)" onclick="course_link(' , $RS1['course_id'] , '); return false;" >' ,
									htmlspecialchars($cour_lang[$sysSession->lang]) ,
									'</a>';
            				showXHTML_td_E();
            			showXHTML_tr_E();

					}
				}else{
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
           				showXHTML_td('colspan="3" nowrap id="toolbar2"', $MSG['no_data'][$sysSession->lang]);
            		showXHTML_tr_E('');
				}

				// 換頁與動作功能列 (function line)
            	$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
            		showXHTML_td('colspan="3" nowrap id="toolbar2"', '&nbsp;');
            	showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();

		//  課程統計
    	showXHTML_form_B('action="member_stud_stat1.php" method="post" enctype="multipart/form-data" style="display:none"', 'StatFm');
	    	showXHTML_input('hidden', 'course_id', ''       );
	    	showXHTML_input('hidden', 'page_no'  , $page_no );
	    	showXHTML_input('hidden', 'page_num1', $page_num);
    	showXHTML_form_E();

	showXHTML_body_E();
?>
