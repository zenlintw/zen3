<?php
	/**
	 * 以其它身份登入
	 *
	 * 建立日期：2003/02/06
	 * @author  ShenTing Lin
	 * @version $Id: relogin.php,v 1.1 2010/02/24 02:38:39 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/stud_account.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	if(isset($_POST['go_where']) == 'direct')
		$cur_function_id = '2400400100';
	else
		$cur_function_id = $cur_function_id;
	$sysSession->cur_func = $cur_function_id;
	$sysSession->restore();
	if (!aclVerifyPermission($cur_function_id, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	/* 安全性檢查 */

	/**
	 * 變換身份的步驟
	 *     1. 檢查是否具備管理者身份，避免越權
	 *     2. 檢查有無此帳號
	 *     3. 檢查有無此班級
	 *     4. 更新個人身份
	 **/
	// 檢查具不具備管理者的身份
	$isAdmin = aclCheckRole($sysSession->username, ($sysRoles['manager']|$sysRoles['administrator']|$sysRoles['root']), $sysSession->school_id);
	if (!$isAdmin) {
		showXHTML_head_B($MSG['title79'][$sysSession->lang]);
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
		showXHTML_head_E();
		showXHTML_body_B('');
			showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" id="ListTable"');
				showXHTML_tr_B('');
					showXHTML_td_B('');
						$ary[] = array($MSG['title81'][$sysSession->lang], 'tabs');
						showXHTML_tabs($ary, 1);
					showXHTML_td_E();
				showXHTML_tr_E();
				showXHTML_tr_B('');
					showXHTML_td_B('valign="top" id="CGroup" ');
						showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="CourseList" class="cssTable"');
							$col = "cssTrEvn";

							showXHTML_tr_B('class=" ' . $col . '"');
								showXHTML_td('class="font01"', $MSG['title84'][$sysSession->lang]);
							showXHTML_tr_E();

							$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';

   						    showXHTML_tr_B('class=" ' . $col . '"');
   							    showXHTML_td_B('');
									if(isset($_POST['go_where']) == 'direct')
										showXHTML_input('button', '', $MSG['go_direct'][$sysSession->lang], '', 'onclick="window.location.replace(\'/academic/class/director_add.php?type=query\');" class="cssBtn"');
									else
										showXHTML_input('button', '', $MSG['title86'][$sysSession->lang], '', 'onclick="window.location.replace(\'/academic/login.php\');" class="cssBtn"');
   							    showXHTML_td_E();
   						    showXHTML_tr_E();
						showXHTML_table_E();
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_body_E();
		wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,1, 'manager', $_SERVER['PHP_SELF'], "{$sysSession->username} 帳號不具備管理者權限!");
		exit();
	}

	// 檢查有無此班級
	if($_POST['go_where'] == 'direct'){
	    list($class_exist) = dbGetStSr('WM_class_main','count(*) as num','class_id="' . trim($_POST['class_id']) . '"',ADODB_FETCH_NUM);
		if($class_exist == 0) {
			showXHTML_head_B($MSG['title81'][$sysSession->lang]);
				showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
			showXHTML_head_E();
			showXHTML_body_B('');
					showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" id="ListTable"');
						showXHTML_tr_B('');
							showXHTML_td_B('');
								$ary[] = array($MSG['title81'][$sysSession->lang], 'tabs');
								showXHTML_tabs($ary, 1);
							showXHTML_td_E();
						showXHTML_tr_E();
						showXHTML_tr_B('');
							showXHTML_td_B('valign="top" id="CGroup" class="bg01"');
								showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="CourseList" class="box01"');
									$col = "cssTrEvn";

									showXHTML_tr_B('class=" ' . $col . '"');
										showXHTML_td('class="font01"', $MSG['none_class'][$sysSession->lang]);
									showXHTML_tr_E();

								$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';

								showXHTML_tr_B('class=" ' . $col . '"');
									showXHTML_td_B('');
										showXHTML_input('button', '', $MSG['go_direct'][$sysSession->lang], '', 'onclick="window.location.replace(\'/academic/class/director_add.php?type=query\');" class="cssBtn"');
									showXHTML_td_E();
								showXHTML_tr_E();
							showXHTML_table_E();
						showXHTML_td_E();
					showXHTML_tr_E();
				showXHTML_table_E();
			showXHTML_body_E();
			wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,2, 'manager', $_SERVER['PHP_SELF'], "{$sysSession->username} 帳號不存在!");
			exit();
		}
	}
	// 2 檢查有沒有這個帳號
	$userinfo = dbGetStSr('WM_user_account', '*', "username='" . trim($_POST['username']) . "'", ADODB_FETCH_ASSOC);
	if ($userinfo == false) {
		showXHTML_head_B($MSG['title81'][$sysSession->lang]);
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
		showXHTML_head_E();
		showXHTML_body_B('');
			showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" id="ListTable"');
				showXHTML_tr_B('');
					showXHTML_td_B('');
						$ary[] = array($MSG['title81'][$sysSession->lang], 'tabs');
						showXHTML_tabs($ary, 1);
					showXHTML_td_E();
				showXHTML_tr_E();
				showXHTML_tr_B('');
					showXHTML_td_B('valign="top" id="CGroup" class="bg01"');
						showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="CourseList" class="box01"');
							$col = "cssTrEvn";

							showXHTML_tr_B('class=" ' . $col . '"');
								showXHTML_td('class="font01"', $MSG['title84'][$sysSession->lang]);
							showXHTML_tr_E();

							$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';

   						    showXHTML_tr_B('class=" ' . $col . '"');
   							    showXHTML_td_B('');
									if(isset($_POST['go_where']) == 'direct')
										showXHTML_input('button', '', $MSG['go_direct'][$sysSession->lang], '', 'onclick="window.location.replace(\'/academic/class/director_add.php?type=query\');" class="cssBtn"');
									else
										showXHTML_input('button', '', $MSG['title86'][$sysSession->lang], '', 'onclick="window.location.replace(\'/academic/login.php\');" class="cssBtn"');
   							    showXHTML_td_E();
   						    showXHTML_tr_E();
						showXHTML_table_E();
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_body_E();
		wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,2, 'manager', $_SERVER['PHP_SELF'], "{$sysSession->username} 帳號不存在!");
		exit();
	}

	// 3. 檢查是否具備管理者身份，避免越權
	$isAnotherAdmin = aclCheckRole($_POST['username'], ($sysRoles['manager']|$sysRoles['administrator']|$sysRoles['root']), $sysSession->school_id);
	if ((!$isAnotherAdmin) || ($sysSession->username == $_POST['username'])) {

	  // 記錄到 WM_log_manager
      // wmSysLog('0300100300',$sysSession->school_id,0,'0','manager',$_SERVER['SCRIPT_FILENAME'],$sysSession->username .' login student environment.');

		// 通過登入檢查
		// 移除舊的 sysSession
		dbDel('WM_session', "idx='{$_COOKIE['idx']}'");
		// 建立新的 sysSession

		$idx = $sysSession->init($userinfo);
		$_COOKIE['idx'] = $idx;

		if(isset($_POST['go_where']) == 'direct'){
			$js_link = '/direct/index.php';
			if($class_exist > 0) {
				$sysSession->class_id = $_POST['class_id'];
				$sysSession->env = 'direct';
			}
		}else
			$js_link = '/learn/index.php';

		$sysSession->restore();

    showXHTML_head_B($MSG['title81'][$sysSession->lang]);
   	showXHTML_script('inline', "top.window.location.replace('{$js_link}');");
   	showXHTML_head_E();
   	showXHTML_body_B('');
   	showXHTML_body_E();

   }else if ($isAnotherAdmin){  // 若是管理者 則不可以使用 另外一位管理者帳號登入
      showXHTML_head_B($MSG['title81'][$sysSession->lang]);
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
		showXHTML_head_E();
		showXHTML_body_B('');
			showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" id="ListTable"');
				showXHTML_tr_B('');
					showXHTML_td_B('');
						$ary[] = array($MSG['title81'][$sysSession->lang], 'tabs');
						showXHTML_tabs($ary, 1);
					showXHTML_td_E();
				showXHTML_tr_E();
				showXHTML_tr_B('');
					showXHTML_td_B('valign="top" id="CGroup" ');
						showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="CourseList" class="cssTable"');
						   $col = "cssTrEvn";
							showXHTML_tr_B('class="font01 ' . $col . '"');
								showXHTML_td('class="font01"', $MSG['title85'][$sysSession->lang]);
							showXHTML_tr_E();

							$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';

   						    showXHTML_tr_B('class=" ' . $col . '"');
   							    showXHTML_td_B('');
									if(isset($_POST['go_where']) == 'direct')
										showXHTML_input('button', '', $MSG['go_direct'][$sysSession->lang], '', 'onclick="window.location.replace(\'/academic/class/director_add.php?type=query\');" class="cssBtn"');
									else
										showXHTML_input('button', '', $MSG['title86'][$sysSession->lang], '', 'onclick="window.location.replace(\'/academic/login.php\');" class="cssBtn"');
   							    showXHTML_td_E();
   						    showXHTML_tr_E();

						showXHTML_table_E();
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_body_E();
		wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,3, 'manager', $_SERVER['PHP_SELF'], "{$sysSession->username} 已是管理者則不可再用其他管理者帳號登入!");
      exit();
   }else{
		// 通過登入檢查
		// 移除舊的 sysSession
		dbDel('WM_session', "idx='{$_COOKIE['idx']}'");
		// 建立新的 sysSession
		$idx = $sysSession->init($userinfo);
		$_COOKIE['idx'] = $idx;

		if(isset($_POST['go_where']) == 'direct'){
			$js_link = '/direct/index.php';
			if($class_exist > 0) {
				$sysSession->class_id = $_POST['class_id'];
				$sysSession->env = 'direct';
			}
		}else
			$js_link = '/learn/index.php';

		$sysSession->restore();

		showXHTML_head_B($MSG['title79'][$sysSession->lang]);
		showXHTML_script('inline', "top.window.location.replace('{$js_link}');");
		showXHTML_head_E();
		showXHTML_body_B('');
		showXHTML_body_E();
	}
?>
