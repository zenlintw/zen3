<?php
   /**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Saly Lin                                                                         *
	*		Creation  : 2004/05/7                                                                      *
	*		work for  : 修改教師                                                                        *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*                                                                                                 *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lang/teacher_settutor.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func='300100200';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	// 修改教師
	$ticket = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Edit' . $sysSession->username);

	if (trim($_POST['ticket']) == $ticket) {
		$_POST['username'] = trim($_POST['username']);
		$res = checkUsername($_POST['username']);
		if (($res != 2) && ($res != 4))
		{
			wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'User not found!');
			die($MSG['illege_access'][$sysSession->lang]);
		}
        // 抓取教師的姓名
		$user = getUserDetailData($_POST['username']);
		$teacher_name = $user['realname'];
	}
	else {
	   wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'Illegal Access!');
	   die($MSG['illege_access'][$sysSession->lang]);
	}

	// 開始呈現 HTML
	showXHTML_head_B($MSG['title2'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/teach/wm.css");
	showXHTML_head_E();
	showXHTML_body_B();

		showXHTML_table_B('border="0" width="760" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B();
				showXHTML_td_B();
					$ary[] = array($MSG['title2'][$sysSession->lang], 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E();
			showXHTML_tr_E();
			showXHTML_tr_B();
				showXHTML_td_B('valign="top" width="760" id="CGroup" ');

					showXHTML_form_B('method="post" action="teacher_save.php" style="display:inline;"', 'actForm');
					$ticket = md5($sysSession->ticket . 'Edit' . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
					showXHTML_input('hidden', 'ticket'   , $ticket           );
					showXHTML_input('hidden', 'username' , $_POST['username']);
					showXHTML_input('hidden', 'old_role' , $_POST['role']    );
					showXHTML_input('hidden', 'ticket2'  , $_POST['ticket']  );
					showXHTML_input('hidden', 'course_id', ''                );
					showXHTML_input('hidden', 'state'    , 'M'               );
					showXHTML_input('hidden', 'page_no'  , intval($_POST['page_no']));

					showXHTML_table_B('id ="mainTable" width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						$col = 'cssTrEvn';
						showXHTML_tr_B('class=" ' . $col . '"');
							showXHTML_td('align="center" nowrap ', $MSG['user_account'][$sysSession->lang]);
							showXHTML_td('', $_POST['username'] . '&nbsp;');
						showXHTML_tr_E('');

						$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
	                    showXHTML_tr_B('class=" ' . $col . '"');
							showXHTML_td('align="center" nowrap ', $MSG['real_name'][$sysSession->lang]);
							showXHTML_td('', $teacher_name);
						showXHTML_tr_E();

						$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
						showXHTML_tr_B('class=" ' . $col . '"');
							showXHTML_td('align="center" nowrap ', $MSG['status'][$sysSession->lang]);
							showXHTML_td_B();
								showXHTML_input('select', 'role', array('assistant' => $MSG['assistant'][$sysSession->lang],
								                                        'instructor'=>$MSG['instructor'][$sysSession->lang]),
								                strtolower($_POST['role']), 'class="cssInput"');
							showXHTML_td_E();
						showXHTML_tr_E();

						$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
                  		showXHTML_tr_B('class=" ' . $col . '"');
							showXHTML_td_B('colspan="2" align="left"');
							    showXHTML_input('submit', '', $MSG['modify'][$sysSession->lang], '', 'class="cssBtn"');
								showXHTML_input('button', '', $MSG['title6'][$sysSession->lang], '', 'onclick="document.actList.submit();" class="cssBtn"');
							showXHTML_td_E();
						showXHTML_tr_E();

					showXHTML_table_E();
					showXHTML_form_E();

				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E();

	  showXHTML_form_B('action="teacher_list.php" method="post"', 'actList');
		showXHTML_input('hidden', 'page_no', intval($_POST['page_no']), '', '');
	  showXHTML_form_E();

	showXHTML_body_E();
?>
