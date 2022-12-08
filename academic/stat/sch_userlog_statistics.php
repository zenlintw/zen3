<?php
	/**
	 * 學校統計資料 - User log 統計
	 *
	 * 建立日期：2004/08/16
	 * @author  Amm Lee
	 * @version $Id: sch_userlog_statistics.php,v 1.1 2010/02/24 02:38:43 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/jscalendar/calendar.php');
	require_once(sysDocumentRoot . '/lang/sch_statistics.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '1500200100';
	$sysSession->restore();
	if (!aclVerifyPermission(1500200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$ticket = md5(sysTicketSeed . $sysSession->username . 'login_stat' . $sysSession->ticket);

	$js = <<< EOF

	var MSG_SYS_ERROR     = "{$MSG['msg_system_error'][$sysSession->lang]}";
	var theme = "{$sysSession->theme}";
	var ticket = "{$ticket}";
	var lang = "{$lang}";

	var msg1 = "{$MSG['msg_date_error1'][$sysSession->lang]}";
    var msg2 = "{$MSG['msg_date_error2'][$sysSession->lang]}";
    var msg3 = "{$MSG['msg_date_error3'][$sysSession->lang]}";
    var msg4 = "{$MSG['msg_date_error4'][$sysSession->lang]}";
    var msg5 = "{$MSG['msg_date_error5'][$sysSession->lang]}";
    var msg6 = "{$MSG['msg_date_error6'][$sysSession->lang]}";
    var msg7 = "{$MSG['title142'][$sysSession->lang]}";

	// 秀日曆的函數
	function Calendar_setup(ifd, fmt, btn, shtime) {
		Calendar.setup({
			inputField  : ifd,
			ifFormat    : fmt,
			showsTime   : shtime,
			time24      : true,
			button      : btn,
			singleClick : true,
			weekNumbers : false,
			step        : 1
		});
	}

	// 顯示功能 (function)
	function showFunction(idx,caption) {

		var field = document.getElementById(idx);
		if(!field) {
			return;
		}

		if (idx == 'cond_code'){
			field.innerHTML = '&nbsp;&nbsp;&nbsp;<br>'+msg7 + caption;
		}else{
			field.value = caption;
		}
	}

	function open_page(){
		window.open("pickCode.php", "", "width=450,height=250,toolbar=0,location=0,status=1,menubar=0,directories=0,resizable=0,scrollbars=1");
	}

	function check_data(){
		var obj = document.getElementById('queryFm');
		var temp = '';
		var function_reg = /^[0-9]{1,}$/;

		for(var i=0; i< document.queryFm.elements['cond[]'].length;i++){
			if(document.queryFm.elements['cond[]'][i].checked){
				val = parseInt(document.queryFm.elements['cond[]'][i].value);
				switch (val){
					case 1:
						if (obj.cond_username.value.length == 0){
							alert("{$MSG['title131'][$sysSession->lang]}");
							obj.cond_username.focus();
							return false;
						}
						break;
					case 2:
							var date_from = obj.cond_from.value.replace(/[\D]/ig, '');
							var date_to = obj.cond_to.value.replace(/[\D]/ig, '');

							if ((date_from.length==0) || (date_to.length==0)) {
								alert("{$MSG['title171'][$sysSession->lang]}");
								return false;
							}
							if (parseInt(date_from) >= parseInt(date_to)) {
								alert("{$MSG['title170'][$sysSession->lang]}");
								obj.cond_from.focus();
								return false;
							}
						break;
					case 3:
						if (obj.cond_ip.value.length == 0){
							alert("{$MSG['title42'][$sysSession->lang]}");
							obj.cond_ip.focus();
							return false;
						}
						break;
					case 4:
						var function_temp = obj.function_id.value;

						if (function_temp.length == 0){
							alert("{$MSG['title133'][$sysSession->lang]}");
							obj.function_id.focus();
							return false;
						}

						if (function_temp.search(function_reg) == -1){
							alert("{$MSG['title148'][$sysSession->lang]}");
							obj.function_id.focus();
							return false;
						}
						break;
				}
			}
		}
		obj.action = 'sch_userlog_statistics1.php';

		window.onunload = function () {};

		obj.submit();

	}

	var orgload = window.onload;
	window.onload = function () {
		orgload();
		// java script 的 date
		Calendar_setup('cond_from', '%Y-%m-%d', 'cond_from', false);
		Calendar_setup('cond_to', '%Y-%m-%d', 'cond_to', false);
	};
EOF;

	showXHTML_head_B($MSG['title4'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	$calendar = new DHTML_Calendar('/lib/jscalendar/', $sysSession->lang, 'calendar-system');
	$calendar->load_files();
	showXHTML_script('include', 'sch_statistics.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E('');

	showXHTML_body_B('');

		showXHTML_table_B('width="600" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary[] = array($MSG['title102'][$sysSession->lang], 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');

			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup" ');
				showXHTML_form_B('action="" method="post" enctype="multipart/form-data" target="_self" style="display:inline"', 'queryFm');
					showXHTML_table_B('id ="mainTable" border="0" cellspacing="1" cellpadding="3" class="cssTable"');

					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('width="80"', $MSG['title136'][$sysSession->lang]);
							showXHTML_td_B('width="520"');
								$array_env = array(
									$MSG['title137'][$sysSession->lang], $MSG['title139'][$sysSession->lang],
									$MSG['title140'][$sysSession->lang], $MSG['title138'][$sysSession->lang],
									$MSG['title143'][$sysSession->lang]
								);
                                showXHTML_input('radio', 'query_table', $array_env, '0', '','<br>');
							showXHTML_td_E('');
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('width="80"', $MSG['title129'][$sysSession->lang]);
							showXHTML_td_B('width="520"');
								showXHTML_input('checkbox', 'cond[]', 1 ,1,'');
								echo $MSG['title130'][$sysSession->lang], $MSG['title174'][$sysSession->lang];
								showXHTML_input('text', 'cond_username', '', '', 'id="cond_username" class="cssInput" size="20"');
								echo '<br>';
								showXHTML_input('checkbox', 'cond[]', 2 ,2,'');
								echo $MSG['title155'][$sysSession->lang], $MSG['from'][$sysSession->lang], $MSG['title174'][$sysSession->lang];
								showXHTML_input('text', 'cond_from', '', '', 'id="cond_from" class="cssInput" size="20" readonly="readonly"');
								echo $MSG['title155'][$sysSession->lang], $MSG['to'][$sysSession->lang], $MSG['title174'][$sysSession->lang];
								showXHTML_input('text', 'cond_to', '', '', 'id="cond_to" class="cssInput" size="20" readonly="readonly"');
								echo '<br>';
								showXHTML_input('checkbox', 'cond[]', 3 ,3,'');
								echo $MSG['title34'][$sysSession->lang];
								showXHTML_input('text', 'cond_ip', '', '', 'id="cond_ip" class="cssInput" size="20"');
								echo '<br>';
								showXHTML_input('checkbox', 'cond[]', 4 ,4,'');
								echo $MSG['title134'][$sysSession->lang], $MSG['title174'][$sysSession->lang];
								showXHTML_input('text', 'function_id', '', '', 'id="function_id" class="cssInput" size="20"');
								showXHTML_input('button','btnImp',$MSG['title132'][$sysSession->lang],'','onclick="open_page()"');
								echo '<span id="cond_code"></span>';
							showXHTML_td_E('');
						showXHTML_tr_E('');

						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('', $MSG['title106'][$sysSession->lang]);
							showXHTML_td_B('');
								$page_array = array(10 => $MSG['title156'][$sysSession->lang],20 => 20,30 => 30,40 => 40,50 => 50,100 => 100);
								showXHTML_input('select', 'page_num', $page_array,$page_num, 'class="cssInput" id="page_num" onchange="page(this.value);"');
								echo $MSG['title157'][$sysSession->lang];
							showXHTML_td_E('');
						showXHTML_tr_E('');
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td_B('colspan="2" align="center"');
								showXHTML_input('button','btnImp',$MSG['title11'][$sysSession->lang],'','onclick="check_data()"');
								showXHTML_input('reset','btnImp',$MSG['title22'][$sysSession->lang],'','');
							showXHTML_td_E('');
						showXHTML_tr_E('');
					showXHTML_table_E('');
					showXHTML_form_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
	showXHTML_body_E('');

?>