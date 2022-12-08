<?php
	/**
	 * 學校統計資料 - User log 統計 - 動作代號
	 *
	 * 建立日期：2004/08/16
	 * @author  Amm Lee
	 * @version $Id: pickCode.php,v 1.1 2010/02/24 02:38:43 saly Exp $
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

	if ($_POST['page_num1'] != ''){
		$page_num = intval($_POST['page_num1']);
	}else{
		$page_num = sysPostPerPage;
	}

	if (! empty($_POST['env_scope'])){
		$query_scope = escape_LIKE_query_str(trim($_POST['env_scope']));

		if ($query_scope == 'other'){
			$query_cond = ' scope = ""';

		}else{
			$query_cond = ' scope like "%' . $query_scope . '%"';
		}
	}else{
		$query_scope = '';
		$query_cond  = '1';
	}

	list($all_page) = dbGetStSr('WM_acl_function','count(*)',$query_cond, ADODB_FETCH_NUM);

	$total_page = ceil($all_page / max(1, $page_num));

	if ($_POST['page_no'] == ''){

		if ($total_page > 0){
			$cur_page    = 1;
			$limit_begin = (($cur_page -1)* $page_num);
			$limit_str   = ' limit ' . $limit_begin . ',' . $page_num;

		}else if ($total_page == 0){
			$cur_page = 0;
		}

	}else{

		if (($_POST['page_no'] >  0)){
			$cur_page = intval($_POST['page_no']);
			if ($cur_page < 0 || $cur_page > $total_page) $cur_page = 1;
			$limit_begin = (($cur_page -1)* $page_num);
			$limit_str   = ' limit ' . $limit_begin . ',' . $page_num;
		}else if ($_POST['page_no'] == 0){
			$cur_page = 0;
			$limit_str = '';

		}
	}

	$RS = dbGetStMr('WM_acl_function','function_id,caption',$query_cond . ' order by function_id asc ' .  $limit_str, ADODB_FETCH_ASSOC);

	$js = <<< EOF

	var MSG_SYS_ERROR = "{$MSG['msg_system_error'][$sysSession->lang]}";
	var theme         = "{$sysSession->theme}";
	var ticket        = "{$ticket}";
	var lang          = "{$lang}";
	var cur_page      = {$cur_page};
    var total_page    = {$total_page};
    var page_num      = {$page_num};

	function page(n){
	    var obj = document.getElementById("CodeFm");
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
		obj.submit();
    }

    function page_Row(n){
	    var obj = document.getElementById("CodeFm");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		obj.page_num1.value = parseInt(n);

		obj.submit();
    }

    function scopeQuery(val){
		var obj = document.getElementById("CodeFm");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		obj.submit();
    }

    function back_opener(code_num,code_name){
		var obj = window.opener;

		if (typeof(obj) == "object") {
			obj.showFunction('function_id',code_num);
			obj.showFunction('cond_code',code_name);
			self.close();
	    }
    }

    window.onload = function () {
		var obj = document.getElementById("toolbar1");
		var txt1 = '';

		if ((typeof(obj) == "object") && (obj != null)) txt1 = obj.innerHTML;

		obj = document.getElementById("toolbar2");
		if ((typeof(obj) == "object") && (obj != null)) obj.innerHTML = txt1;
    };
EOF;

	showXHTML_head_B($MSG['title132'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('include', 'sch_statistics.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E('');

	showXHTML_body_B('');

		showXHTML_table_B('width="400" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary[] = array($MSG['title132'][$sysSession->lang], 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');

			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup" ');
					showXHTML_form_B('action="pickCode.php" method="post" enctype="multipart/form-data" target="_self" style="display:inline"', 'CodeFm');
						showXHTML_table_B('id ="mainTable" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
							showXHTML_input('hidden', 'page_no', '', '', '');
							showXHTML_input('hidden', 'page_num1', $page_num, '', '');
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td_B('width="400" colspan="2" ');
									echo $MSG['title136'][$sysSession->lang], $MSG['title174'][$sysSession->lang];
									$scope_array = array('' => '','' => $MSG['all'][$sysSession->lang],'learn' => $MSG['title137'][$sysSession->lang],'teach' => $MSG['title138'][$sysSession->lang] ,'direct' =>  $MSG['title139'][$sysSession->lang] ,'academic' =>  $MSG['title140'][$sysSession->lang],'other' => $MSG['title143'][$sysSession->lang] );
									showXHTML_input('select', 'env_scope', $scope_array,$query_scope, 'class="cssInput" id="env_scope" onchange="scopeQuery(this.value);"');
								showXHTML_td_E('');
							showXHTML_tr_E('');

							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td_B('width="400" colspan="2" id="toolbar1"');
									echo $MSG['page'][$sysSession->lang];
									$P = $total_page > 0 ? array_merge(array($MSG['all'][$sysSession->lang]), range(1,$total_page)) : array($MSG['all'][$sysSession->lang]);
									showXHTML_input('select', '', $P, $cur_page, 'class="cssInput" onchange="page(this.value);"');

	        						echo $MSG['title106'][$sysSession->lang];

									$page_array = array(10 => $MSG['title156'][$sysSession->lang],20 => 20,30 => 30,40 => 40,50 => 50,100 => 100);

									showXHTML_input('select', 'page_num', $page_array,$page_num, 'class="cssInput" id="page_num" onchange="page_Row(this.value);"');

									echo $MSG['title157'][$sysSession->lang];

		            				showXHTML_input('button', 'firstBtn1', $MSG['first1'][$sysSession->lang], '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==1) || ($cur_page==0)) ?          'disabled="true" ' : 'onclick="page(-1);"') . ' title=' . $MSG['switch_page'][$sysSession->lang]);
		            				showXHTML_input('button', 'prevBtn1', $MSG['prev'][$sysSession->lang], '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==1) || ($cur_page==0))?          'disabled="true" ' : 'onclick="page(-2);"') . ' title=' . $MSG['switch_page1'][$sysSession->lang]);
		            				showXHTML_input('button', 'nextBtn1', $MSG['next'][$sysSession->lang], '', 'id="nextBtn1" class="cssBtn" ' . ((($cur_page==0) || ($cur_page==$total_page))?          'disabled="true" ' : 'onclick="page(-3);"') . ' title=' . $MSG['switch_page2'][$sysSession->lang]);
		            				showXHTML_input('button', 'lastBtn1', $MSG['last1'][$sysSession->lang], '', 'id="lastBtn1" class="cssBtn" ' . ((($cur_page==0) || ($cur_page==$total_page))?          'disabled="true" ' : 'onclick="page(-4);"') . ' title=' . $MSG['switch_page3'][$sysSession->lang]);

								showXHTML_td_E('');

							showXHTML_tr_E('');

							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td('width="100" align="center"', $MSG['title134'][$sysSession->lang]);
								showXHTML_td('width="250" align="center"', $MSG['title135'][$sysSession->lang]);
							showXHTML_tr_E('');

							if ($RS->RecordCount() > 0){
								while ($RS1 = $RS->FetchRow()){
									$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
									$function_name = locale_conv($RS1['caption']);
									showXHTML_tr_B($col);
										showXHTML_td('width="100"', '<a href="javascript:;" onclick="back_opener(' . $RS1['function_id'] . ',\'' . $function_name . '\'' . ')">' . $RS1['function_id'] . '</a>');
										showXHTML_td('width="250" nowrap', $function_name);
									showXHTML_td_E('');
								}
							}else{
								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td('colspan=2"', $MSG['title141'][$sysSession->lang]);
								showXHTML_tr_E('');
							}

							// 換頁與動作功能列 (function line)
            				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
            					showXHTML_td('colspan="2" nowrap id="toolbar2"', '&nbsp;');
            				showXHTML_tr_E('');
						showXHTML_table_E('');
					showXHTML_form_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');

	showXHTML_body_E('');
?>
