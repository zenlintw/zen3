<?php
   /**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Saly Lin                                                                         *
	*		Creation  : 2004/05/7                                                                      *
	*		work for  : 新增授課教師 的列表                                                                       *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*                                                                                                 *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/teacher_settutor.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	$sysSession->cur_func = '300100100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$js = <<< BOF
	var isIE = (navigator.userAgent.indexOf(' MSIE ') > -1) ? true : false;

	function checkData() {
	    var obj = document.getElementById("actForm");

		if (obj.username.value == ''){
	      alert("{$MSG['title12'][$sysSession->lang]}");
	      obj.username.focus();
	      return false;
	    }

       /*
        disable submit button
       */
       var obj2 = document.getElementById("btn_submit");
       obj2.disabled = true;
	}

	window.onload = function () {
		var obj = document.getElementById("actForm");
		obj.username.focus();
	};

BOF;
	// 開始呈現 HTML
	showXHTML_head_B($MSG['add_teacher'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/teach/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();

		showXHTML_table_B('border="0" width="760" cellspacing="0" cellpadding="0"  id="ListTable"');
			showXHTML_tr_B();
				showXHTML_td_B();
					$ary[] = array($MSG['add_teacher'][$sysSession->lang], 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E();
			showXHTML_tr_E();

			showXHTML_tr_B();
				showXHTML_td_B('valign="top" id="CGroup" ');
					showXHTML_form_B('method="post" action="teacher_save.php" style="display:inline;" onsubmit="return checkData()"', 'actForm');
					$ticket = md5($sysSession->ticket . 'Create' . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
					showXHTML_input('hidden', 'ticket', $ticket, '', '');
					showXHTML_table_B('id ="mainTable" width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						
						$col = 'cssTrEvn';
						showXHTML_tr_B('class="'. $col . '"');
							showXHTML_td_B();
								echo $MSG['user_account'][$sysSession->lang] . '&nbsp;&nbsp;';
								showXHTML_input('text', 'username', trim(stripslashes($_POST['username'])), '', 'size="20" width="30" class="cssInput"');
								showXHTML_input('select', 'level', array('assistant' => $MSG['assistant'][$sysSession->lang], 
								                                         'instructor'=>$MSG['instructor'][$sysSession->lang]), '', 'class="cssInput"');
							showXHTML_td_E();
						showXHTML_tr_E();

						$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
						showXHTML_tr_B('class="'. $col . '"');
						   showXHTML_td('align="center" nowrap', $MSG['title37'][$sysSession->lang]);
						showXHTML_tr_E();
						
						$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
						showXHTML_tr_B('class="'. $col . '"');
							showXHTML_td_B('align="left"');
								showXHTML_input('submit', '', $MSG['store'][$sysSession->lang] , '', 'id="btn_submit" class="cssBtn"');
								showXHTML_input('reset' , '', $MSG['reset'][$sysSession->lang] , '', 'class="cssBtn"');
								showXHTML_input('button', '', $MSG['title6'][$sysSession->lang], '', 'onclick="location.replace(\'teacher_list.php\')" class="cssBtn"');
							showXHTML_td_E();
						showXHTML_tr_E();

					showXHTML_table_E();
					showXHTML_form_E();
				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E();

	showXHTML_body_E();
?>
