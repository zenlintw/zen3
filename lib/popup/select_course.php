<?php
	/**
	 * 選擇課程 (列表)
	 *
	 * @since   2005/11/30
	 * @author  Hubert
	 * @version $Id: select_course.php,v 1.1 2009-06-25 09:27:28 edi Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/select_course.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$lines = sysPostPerPage;
	// 計算總共有幾筆資料

	$where = '';
	$sWord = trim($_POST['keyword']);
	if ($sWord != '' && strcmp($sWord, $MSG['msg_title05'][$sysSession->lang]) != 0){
		$where = 'and caption like "%' . escape_LIKE_query_str(addslashes($sWord)) . '%" ';
	}

	list($total_msg) = dbGetStSr('WM_term_course', 'count(*) AS total', "kind='course' and status < 9 {$where}", ADODB_FETCH_NUM);

	// 計算總共分幾頁
	$total_page = ceil($total_msg / $lines);

	// 產生下拉換頁選單
	$all_page    = range(0, $total_page);
	$all_page[0] = $MSG['all'][$sysSession->lang];

	// 設定下拉換頁選單顯示第幾頁
	$page_no = isset($_POST['page']) ? intval($_POST['page']) : 1;
	if (($page_no < 0) || ($page_no > $total_page))
		$page_no = $total_page;

	if ($page_no > 0)
	{
		$limit = ' limit ' . (($page_no-1)*$lines) . ',' . $lines;
	}

	$RS = dbGetStMr('WM_term_course', 'course_id, caption, quota_used', "kind='course' and status < 9 {$where} order by course_id asc {$limit}", ADODB_FETCH_ASSOC);

	$js = <<< BOF
	var MSG_SELECT_ALL    = "{$MSG['msg_select'][$sysSession->lang]}";
	var MSG_SELECT_CANCEL = "{$MSG['msg_cancel'][$sysSession->lang]}";
	var PLZ_INPUT         = "{$MSG['input_keyword'][$sysSession->lang]}";
	var KEY_WD 		      = "{$MSG['msg_title05'][$sysSession->lang]}";
	var PLZ_CHECK         = "{$MSG['msg_title07'][$sysSession->lang]}";
	var selTotal          = 0;

	var total_page = "{$total_page}";
	var cnt        = "{$total_msg}";
	function go_page(n){
		var obj = document.getElementById("actFm");
		if ((typeof(obj) != "object") || (obj == null)) return '';
		switch(n){
			case -1:	// 第一頁
				obj.page.value = 1;
				break;
			case -2:	// 前一頁
				obj.page.value = parseInt(obj.page.value) - 1;
				if (parseInt(obj.page.value) == 0) obj.page.value = 1;
				break;
			case -3:	// 後一頁
				obj.page.value = parseInt(obj.page.value) + 1;
				break;
			case -4:	// 最末頁
				obj.page.value = parseInt(total_page);
				break;
			default:	// 指定某頁
				obj.page.value = parseInt(n);
				break;
		}
		obj.submit();
	}

	function ReturnWork(){
		var obj = document.getElementsByTagName('input');

		if (obj == null) return false;

		var i = 0;
		var total_len = obj.length;
		var cid = '';

		for (i = 0;i < total_len;i++){
			if ((obj[i].type == 'radio') && obj[i].checked){
				if (obj[i].value.length > 0){
					cid = obj[i].value;
				}
			}
		}


		if (cid != ''){
			var hwnd = opener.getHwnd("WinCourseSelect");
			if (hwnd != null)
			{
				hwnd.callback(cid, document.getElementById('CourseName_'+cid).innerText);
			}

			window.close();
		}else{
			alert(PLZ_CHECK);
		}
	}

	function chkForm(fm){
		if (typeof(fm) == 'object'){
			var kwd = fm.keyword.value;
			if (kwd == '' || kwd == KEY_WD)
			{
				alert(PLZ_INPUT);
				fm.keyword.focus();
				return false;
			}
			return true;
		}
		return false;
	}

	window.onload = function(){
		if (cnt > 0){
		  var obj = document.getElementById('Instr_List');
		  if (typeof(obj) == 'object')
		  	obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[2].cells[0].innerHTML;
	  }
  };

BOF;
// 開始呈現 HTML
	showXHTML_head_B($MSG['msg_title06'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
		echo "<style>
					.cssTrSel {
					font-size: 12px;
					line-height: 16px;
					text-decoration: none;
					letter-spacing: 2px;
					color: #000000;
					background-color: #F0EBF0;
					font-family: \"Tahoma\", \"PMingliu\", \"MingLiU\", \"Times New Roman\", \"Times\", \"serif\";
					}
					</style>";

	showXHTML_script('inline', $js, false);
	showXHTML_head_E();
	showXHTML_body_B();
		echo "<center>\n";
		showXHTML_table_B('width="450" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B();
				showXHTML_td_B();
					$ary[] = array($MSG['msg_title06'][$sysSession->lang], 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E();
			showXHTML_tr_E();
			showXHTML_tr_B();
			showXHTML_td_B('valign="top"');

					showXHTML_form_B('style="display:inline;" action="' . $_SERVER['PHP_SELF'] . '" method="post" onSubmit="return chkForm(this);"', 'actFm');
						showXHTML_input('hidden', 'page', $page_no, '', '');
						showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="Instr_List" class="cssTable"');

							showXHTML_tr_B('class="cssTrEvn"');
								showXHTML_td('colspan="4"', $MSG['msg_title07'][$sysSession->lang]);
							showXHTML_tr_E('');

							// 查詢搜尋
							showXHTML_tr_B('class="cssTrOdd"');
								showXHTML_td_B('colspan="4"');
									echo $MSG['msg_title02'][$sysSession->lang],$MSG['msg_title01'][$sysSession->lang];
	                				showXHTML_input('text', 'keyword', ($sWord!=''?$sWord:$MSG['msg_title05'][$sysSession->lang]), '', 'id="keyword" size="20" maxlength="30" class="cssInput" onclick="this.value=\'\'"');
									showXHTML_input('submit', '', $MSG['btn_query'][$sysSession->lang], '', 'style="24" class="cssBtn"');
								showXHTML_td_E('');
							showXHTML_tr_E('');

						if ($RS->RecordCount() > 0){
							showXHTML_tr_B('class="cssTrEvn"');
								showXHTML_td_B('colspan="4"');
									echo $MSG['page'][$sysSession->lang];
									showXHTML_input('select', 'ap', $all_page, $page_no, 'class="cssInput" onchange="go_page(this.value);"');
									showXHTML_input('button', 'fp', $MSG['btn_page_first'][$sysSession->lang],    '', 'onclick="go_page(-1)" class="cssBtn"'. (($page_no == 1) || ($page_no == 0) ? ' disabled="disabled"' : ''));
									showXHTML_input('button', 'pp', $MSG['btn_page_prev'][$sysSession->lang], '', 'onclick="go_page(-2)" class="cssBtn"'. (($page_no == 1) || ($page_no == 0) ? ' disabled="disabled"' : ''));
									showXHTML_input('button', 'np', $MSG['btn_page_next'][$sysSession->lang],     '', 'onclick="go_page(-3)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''));
									showXHTML_input('button', 'lp', $MSG['btn_page_last'][$sysSession->lang],     '', 'onclick="go_page(-4)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''));

									// 確定 & 關閉視窗
									echo '&nbsp;&nbsp;';
									showXHTML_input('button', '', $MSG['btn_confirm'][$sysSession->lang], '', 'class="cssBtn" onclick="ReturnWork();"');
									showXHTML_input('button', '', $MSG['btn_close'][$sysSession->lang], '', 'class="cssBtn" onclick="window.close();"');
								showXHTML_td_E();
							showXHTML_tr_E();
						}

							// html 標題
							showXHTML_tr_B('class="font01 cssTrHead"');
								showXHTML_td_B('width="20"');
								showXHTML_td_E('');
								showXHTML_td('noWrap="noWrap" align="center"', $MSG['course_id'][$sysSession->lang]);
								showXHTML_td('noWrap="noWrap" align="center"', $MSG['name'][$sysSession->lang]);
								showXHTML_td('noWrap="noWrap" align="center"', $MSG['course_size'][$sysSession->lang]);
							showXHTML_tr_E();

							// 產生資料
							if ($RS){
								if ($RS->RecordCount() == 0){
									$col = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
									showXHTML_tr_B($col);
										showXHTML_td('class="font01" colspan="4"', $MSG['no_data'][$sysSession->lang]);
									showXHTML_tr_E();
								}else{
									while ($RS1 = $RS->FetchRow()){

										$col = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
										showXHTML_tr_B($col);

											showXHTML_td_B(' class="font01" ');
												echo '<input type="radio" name="rdo_course" value="'.$RS1['course_id'].'">';
											showXHTML_td_E();

											showXHTML_td_B(' align="center" class="font01" nowrap');
												echo '<div style="width: 100px;" title="' . $RS1['course_id'] . '">' . $RS1['course_id'] . '</div>';
											showXHTML_td_E();

											showXHTML_td_B('class="font01" nowrap');
												$course_name = getCaption($RS1['caption']);
												echo '<div id="CourseName_'.$RS1['course_id'].'" style="width: 200px;overflow:hidden" title="' . htmlspecialchars($course_name[$sysSession->lang]) . '">' . $course_name[$sysSession->lang] . '</div>';
											showXHTML_td_E();

											showXHTML_td_B(' align="center" class="font01" nowrap');
												echo '<div style="width: 100px;">' . $RS1['quota_used'] . '</div>';
											showXHTML_td_E();

										showXHTML_tr_E();
									}
									$col = $col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
									showXHTML_tr_B($col);
										showXHTML_td('colspan="4"', '&nbsp;');
									showXHTML_tr_E();
								}
							}

						showXHTML_table_E();

					showXHTML_form_E();
				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E();
		echo "</center>\n";

	showXHTML_body_E();
?>
