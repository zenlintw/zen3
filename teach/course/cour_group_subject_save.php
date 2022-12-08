<?php
	/**
	 * �x�s�s�հQ�ת��]�w
	 *
	 * @since   2003/12/30
	 * @author  ShenTing Lin
	 * @version $Id: cour_group_subject_save.php,v 1.1 2010/02/24 02:40:23 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
	require_once(sysDocumentRoot . '/lib/multi_lang.php');
	require_once(sysDocumentRoot . '/lang/teach_course.php');
	require_once(sysDocumentRoot . '/teach/course/cour_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	$sysSession->cur_func = '900100500';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}

	$bid = trim($_POST['bid']);
	// �ˬd ticket
	$ticket = md5(sysTicketSeed . 'setBorad' . $_COOKIE['idx'] . $bid);
	if (trim($_POST['ticket']) != $ticket) {
	   wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], '�ڵ��s��!');
	   die($MSG['access_deny'][$sysSession->lang]);
	}

	$lang['Big5']        = stripslashes(trim($_POST['subject_name_big5']));
	$lang['GB2312']      = stripslashes(trim($_POST['subject_name_gb']  ));
	$lang['en']          = stripslashes(trim($_POST['subject_name_en']  ));
	$lang['EUC-JP']      = stripslashes(trim($_POST['subject_name_jp']  ));
	$lang['user_define'] = stripslashes(trim($_POST['subject_name_user']));

	$switch     = (trim($_POST['mailfollow']) == 'yes') ? 'mailfollow' : '';
	$withattach = (empty($_POST['withattach']) ? 'no' : trim($_POST['withattach']));
	$dd = array(
		'title'      => addslashes(serialize($lang)),
		'help'       => strip_scr($_POST['help']),
		'mailfollow' => trim($_POST['mailfollow']),
		'withattach' => $withattach,
		'sort'       => trim($_POST['defsort'])
	);

    chkSchoolId('WM_bbs_boards');
    // �N���ǲ߸��|�`�I�� <title> (�����o�� title)
	$old_title = $sysConn->GetOne('select bname from WM_bbs_boards where board_id=' . $bid);

	dbSet('WM_bbs_boards',
		  "`bname`='{$dd['title']}', `title`='{$dd['help']}', " .
		  "`switch`='{$switch}', `with_attach`='{$dd['withattach']}', `vpost`='{$_POST['vpost']}', `default_order`='{$dd['sort']}'",
		  "`board_id`={$bid}");
	$suc2 = $sysConn->Affected_Rows();
	
    // �N���ǲ߸��|�`�I�� <title> begin
	if ($suc2 && ($new_title = stripslashes($dd['title'])) != $old_title)
	{
		$manifest = new SyncImsmanifestTitle(); // �����O�w�q�� db_initialize.php
		$manifest->replaceTitleForImsmanifest(6, $bid, $manifest->convToNodeTitle($lang));
		$manifest->restoreImsmanifest();
	}
	// �N���ǲ߸��|�`�I�� <title> end

	$msg = ($suc2 > 0) ? $MSG['msg_update_success'][$sysSession->lang] : $MSG['msg_update_fail'][$sysSession->lang];
	wmSysLog($sysSession->cur_func, $sysSession->course_id , $bid , ($suc2 > 0 ? 0 : 2), 'auto', $_SERVER['PHP_SELF'], $msg);

	$js = <<< BOF
	/**
	 * �^��޲z�C��
	 **/
	function goManage() {
		window.location.replace("cour_group_subject.php");
	}

	window.onload = function () {
		alert('{$msg}');
	};
BOF;

	showXHTML_head_B($MSG['save_group_subject'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B('');
		$ary = array();
		$ary[] = array($MSG['save_group_subject'][$sysSession->lang], 'tabs_host');
		showXHTML_tabFrame_B($ary, 1);
			// �D���H�]�w (Begin)
			$col = 'class="font01 cssTrOdd"';
			showXHTML_table_B('width="500" border="0" cellspacing="1" cellpadding="3" id="tabs_host" class="cssTable"');
				showXHTML_tr_B('class="font01 cssTrHead"');
					showXHTML_td('colspan="2"', $msg);
				showXHTML_tr_E();
				// ��ѫǦW��
				$lang = unserialize(stripslashes($dd['title']));
				$col = ($col == 'class="font01 cssTrEvn"') ? 'class="font01 cssTrOdd"' : 'class="font01 cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['title_subject'][$sysSession->lang]);
					showXHTML_td_B();
						$multi_lang = new Multi_lang(true, $lang, $col); // �h�y�t��J��
						$multi_lang->show(false);
					showXHTML_td_E();
				showXHTML_tr_E();

				// ����
				$col = ($col == 'class="font01 cssTrEvn"') ? 'class="font01 cssTrOdd"' : 'class="font01 cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['title_help'][$sysSession->lang]);
					showXHTML_td('', nl2br($dd['help']));
				showXHTML_tr_E();

				// �۰���H
				$col = ($col == 'class="font01 cssTrEvn"') ? 'class="font01 cssTrOdd"' : 'class="font01 cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['title_mailfollow'][$sysSession->lang]);
					showXHTML_td_B();
						echo ($dd['mailfollow'] == 'yes' ? $MSG['title_yes'][$sysSession->lang] : $MSG['title_no'][$sysSession->lang]) , 
						     ($dd['withattach'] == 'yes' ? "&nbsp;({$MSG['with_attach'][$sysSession->lang]})" : '');
					showXHTML_td_E();
				showXHTML_tr_E();
				// �y���Q�תO
				if (Voice_Board == 'Y')
				{
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td('', $MSG['vpost'][$sysSession->lang]);
						$tmpdesc = ( (intval($_POST['vpost'])&1) == 1) ? $MSG['title_yes'][$sysSession->lang]:$MSG['title_no'][$sysSession->lang];
						showXHTML_td('',$tmpdesc);
					showXHTML_tr_E();
				}
				
				// �ժO�Q��
				if (White_Board == 'Y')
				{
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td('', $MSG['whiteboard'][$sysSession->lang]);
						$tmpdesc = ( (intval($_POST['vpost'])&2) == 2) ? $MSG['title_yes'][$sysSession->lang]:$MSG['title_no'][$sysSession->lang];
						showXHTML_td('',$tmpdesc);
					showXHTML_tr_E();
				}
				// �w�]�ƧǪ����
				$col = ($col == 'class="font01 cssTrEvn"') ? 'class="font01 cssTrOdd"' : 'class="font01 cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['title_sort'][$sysSession->lang]);
					showXHTML_td('', $titleSort[$dd['sort']]);
				showXHTML_tr_E();
				// ���}���s
				$col = ($col == 'class="font01 cssTrEvn"') ? 'class="font01 cssTrOdd"' : 'class="font01 cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2" align="center"');
						showXHTML_input('button', '', $MSG['btn_return'][$sysSession->lang], '', 'class="button01" onclick="goManage()"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
			// �D���H�]�w (End)
		showXHTML_tabFrame_E();
	showXHTML_body_E();
?>
