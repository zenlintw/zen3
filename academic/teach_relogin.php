<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2003/09/022                                                                     *
	*		work for  : �Юv�����n�J                                                                    *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       $Id: teach_relogin.php,v 1.1 2010/02/24 02:38:39 saly Exp $                                                                                          *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/teacher_login.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '600200100';
	$sysSession->restore();
	if (!aclVerifyPermission(600200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	/* �w�����ˬd */

	/**
	 * �Юv�����n�J���B�J
	 *     1. �ˬd���L���b��
	 *     2. �ˬd�O�_ �� �Ѯv������
	 *     3. �ˬd�O�_��ƺ޲z�̨����A�קK�V�v
	 **/

    if (empty($_POST['username'])) {
    	wmSysLog($sysSession->cur_func, $sysSession->course_id ,0 ,1, 'teacher', $_SERVER['PHP_SELF'], '�D�k�s��!');
    	die($MSG['illege_access'][$sysSession->lang]);
    }

    if (empty($_POST['ticket'])) {
    	wmSysLog($sysSession->cur_func, $sysSession->course_id ,0 ,1, 'teacher', $_SERVER['PHP_SELF'], '�D�k�s��!');
    	die($MSG['illege_access'][$sysSession->lang]);
    }

    $ticket = md5($sysSession->ticket . $_COOKIE['idx'] . $sysSession->school_id . $sysSession->school_name);

    /*
        �P�_ $_POST['ticket'] �P $ticket �O�_�@��
    */
    if (trim($_POST['ticket']) != $ticket) {
    	wmSysLog($sysSession->cur_func, $sysSession->course_id ,0 ,1, 'teacher', $_SERVER['PHP_SELF'], '�D�k�s��!');
    	die($MSG['illege_access'][$sysSession->lang]);
    }


	// 1. �ˬd���S���o�ӱb��
	$userinfo = dbGetStSr('WM_user_account', '*', "username='" . trim($_POST['username']) . "'", ADODB_FETCH_ASSOC);

   if ($userinfo == false){
   showXHTML_head_B($MSG['teacher_login_title'][$sysSession->lang]);
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
		showXHTML_head_E('');
		showXHTML_body_B('');
			showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" id="ListTable"');
				showXHTML_tr_B('');
					showXHTML_td_B('');
						$ary[] = array($MSG['teacher_login_title'][$sysSession->lang], 'tabs');
						showXHTML_tabs($ary, 1);
					showXHTML_td_E('');
				showXHTML_tr_E('');
				showXHTML_tr_B('');
					showXHTML_td_B('valign="top" id="CGroup" ');
						showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="CourseList" class="cssTable"');
						   $col = "cssTrEvn";
							showXHTML_tr_B('class=" ' . $col . '"');
								showXHTML_td('class="font01"', $MSG['title6'][$sysSession->lang]);
							showXHTML_tr_E('');

							$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';

   						showXHTML_tr_B('class=" ' . $col . '"');
   							showXHTML_td_B('');
                           showXHTML_input('button', '', $MSG['title8'][$sysSession->lang], '', 'onclick="window.location.replace(\'/academic/teacher_login.php\');" class="cssBtn"');
   							showXHTML_td_E('');
   						showXHTML_tr_E('');

						showXHTML_table_E('');
					showXHTML_td_E('');
				showXHTML_tr_E('');
			showXHTML_table_E('');
		showXHTML_body_E('');
		wmSysLog($sysSession->cur_func, $sysSession->course_id ,0 ,2, 'teacher', $_SERVER['PHP_SELF'], '�L���b��!');
      exit();
   }
   // 2. �ˬd�O�_ �� �Ѯv������
   if (!aclCheckRole(trim($_POST['username']), $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'])){
      showXHTML_head_B($MSG['teacher_login_title'][$sysSession->lang]);
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
		showXHTML_head_E('');
		showXHTML_body_B('');
			showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" id="ListTable"');
				showXHTML_tr_B('');
					showXHTML_td_B('');
						$ary[] = array($MSG['teacher_login_title'][$sysSession->lang], 'tabs');
						showXHTML_tabs($ary, 1);
					showXHTML_td_E('');
				showXHTML_tr_E('');
				showXHTML_tr_B('');
					showXHTML_td_B('valign="top" id="CGroup" ');
						showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="CourseList" class="cssTable"');
						   $col = "cssTrEvn";
							showXHTML_tr_B('class=" ' . $col . '"');
								showXHTML_td('class="font01"', $MSG['title6'][$sysSession->lang]);
							showXHTML_tr_E('');

							$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';

   						showXHTML_tr_B('class=" ' . $col . '"');
   							showXHTML_td_B('');
                           showXHTML_input('button', '', $MSG['title8'][$sysSession->lang], '', 'onclick="window.location.replace(\'/academic/teacher_login.php\');" class="cssBtn"');
   							showXHTML_td_E('');
   						showXHTML_tr_E('');

						showXHTML_table_E('');
					showXHTML_td_E('');
				showXHTML_tr_E('');
			showXHTML_table_E('');
		showXHTML_body_E('');
		wmSysLog($sysSession->cur_func, $sysSession->course_id ,0 ,3, 'teacher', $_SERVER['PHP_SELF'], '�L�Ѯv����!');
      exit();
   }

   // 3. �ˬd�O�_��ƺ޲z�̨����A�קK�V�v
	$isAnotherAdmin = aclCheckRole($_POST['username'], ($sysRoles['manager']|$sysRoles['administrator']|$sysRoles['root']), $sysSession->school_id);
	if ((!$isAnotherAdmin) || ($sysSession->username == $_POST['username'])) {

		// �O���� WM_log_manager
		wmSysLog('0300100300',$sysSession->school_id,0,'0','manager',$_SERVER['SCRIPT_FILENAME'],$sysSession->username .' login student environment.');

		// �q�L�n�J�ˬd
		// �����ª� sysSession
		dbDel('WM_session', "idx='{$_COOKIE['idx']}'");
		// �إ߷s�� sysSession
		$idx = $sysSession->init($userinfo);
		$_COOKIE['idx'] = $idx;
		$sysSession->cur_func=10010102;
		$sysSession->restore();

      showXHTML_head_B($MSG['teacher_login_title'][$sysSession->lang]);
	  showXHTML_script('inline', 'top.window.location.replace("/learn/index.php");');
	  showXHTML_head_E('');
	  showXHTML_body_B('');
	  showXHTML_body_E('');
   }else if ($isAnotherAdmin){  // �Y�O�޲z�� �h���i�H�ϥ� �t�~�@��޲z�̱b���n�J
      showXHTML_head_B($MSG['teacher_login_title'][$sysSession->lang]);
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
		showXHTML_head_E('');
		showXHTML_body_B('');
			showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" id="ListTable"');
				showXHTML_tr_B('');
					showXHTML_td_B('');
						$ary[] = array($MSG['teacher_login_title'][$sysSession->lang], 'tabs');
						showXHTML_tabs($ary, 1);
					showXHTML_td_E('');
				showXHTML_tr_E('');
				showXHTML_tr_B('');
					showXHTML_td_B('valign="top" id="CGroup" ');
						showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="CourseList" class="cssTable"');
						   $col = "cssTrEvn";
							showXHTML_tr_B('class="font01 ' . $col . '"');
								showXHTML_td('class="font01"', $MSG['title7'][$sysSession->lang]);
							showXHTML_tr_E('');

							$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';

   						showXHTML_tr_B('class=" ' . $col . '"');
   							showXHTML_td_B('');
                           showXHTML_input('button', '', $MSG['title8'][$sysSession->lang], '', 'onclick="window.location.replace(\'/academic/teacher_login.php\');" class="cssBtn"');
   							showXHTML_td_E('');
   						showXHTML_tr_E('');

						showXHTML_table_E('');
					showXHTML_td_E('');
				showXHTML_tr_E('');
			showXHTML_table_E('');
		showXHTML_body_E('');
      wmSysLog('0300100300',$sysSession->school_id,0,'4','manager',$_SERVER['SCRIPT_FILENAME'],$sysSession->username .' �޲z�̤��i�A�Ψ�L�޲z�̨����b���n�J.');
      exit();
   }

?>
