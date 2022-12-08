<?php
	/**
	 * 前往各功能的捷徑
	 *
	 * @since   2004//
	 * @author  ShenTing Lin
	 * @version $Id: mod_short_link.php,v 1.1 2010/02/24 02:39:09 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	if (!defined('MYCOURSE_MODULE') || MYCOURSE_MODULE === false) {
		include_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
		include_once(sysDocumentRoot . '/lib/interface.php');
		include_once(sysDocumentRoot . '/lib/acl_api.php');
	}
	require_once(sysDocumentRoot . '/learn/mycourse/modules/mod_short_link_lib.php');
	
	$sysSession->cur_func = '700700100';
	$sysSession->restore();
	if (!aclVerifyPermission(700700100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$isEdit = ($sysSession->username != 'guest');
	$lines = 3;
	$id = 'ShortLink';
	$wd = $defSize;   // 主要視窗大小的設定
	$theme = (empty($sysSession->theme)) ? '/theme/default/learn/' : "/theme/{$sysSession->theme}/{$sysSession->env}/";
	$icon = array(
		'<img src="' . $theme . 'my_item1.gif" width="15" height="18" border="0" align="absmiddle">',
		'<img src="' . $theme . 'my_item2.gif" width="17" height="20" border="0" align="absmiddle">'
	);
	// $wd = '195';
	// $id = showXHTML_mytitle_B($id, $MSG['tabs_mycourse'][$sysSession->lang], $wd, $isEdit);
	echo '<div id="' . $id . '">';
		showXHTML_table_B('width="' . $wd . '" border="0" cellspacing="1" cellpadding="3" class="cssTable" id="tab_' . $id . '"');
			showXHTML_tr_B('class="cssMyHead"');
				showXHTML_td('align="center" nowrap="nowrap"', divMsg(40, $MSG['th_homework'][$sysSession->lang]));
				showXHTML_td('align="center" nowrap="nowrap"', divMsg(40, $MSG['th_exam'][$sysSession->lang])    );
				showXHTML_td('align="center" nowrap="nowrap"', divMsg(40, $MSG['th_message'][$sysSession->lang]) );
				showXHTML_td('align="center" nowrap="nowrap"', divMsg(40, $MSG['th_post'][$sysSession->lang])   );
			showXHTML_tr_E();
			showXHTML_tr_B('class="cssTrEvn"');
				// 作業
				showXHTML_td_B('align="center"');
					$not_do = getQTIUndoCount($sysSession->username, 'homework');
					if ($not_do > 0) {
						$title = sprintf($MSG['msg_not_do_homework'][$sysSession->lang], $not_do);
						echo '<a href="/learn/my_homework.php" title="' . $title . '">' . $icon[1] . '<a>';
					} else {
						echo str_replace('>', ' title="' . $MSG['msg_no_hw'][$sysSession->lang] . '">', $icon[0]);
					}
				showXHTML_td_E();
				// 測驗
				showXHTML_td_B('align="center"');
					$not_do = getQTIUndoCount($sysSession->username, 'exam');
					if ($not_do > 0) {
						$title = sprintf($MSG['msg_not_do_exam'][$sysSession->lang], $not_do);
						echo '<a href="/learn/my_exam.php" title="' . $title . '">' . $icon[1] . '<a>';
					} else {
						echo str_replace('>', ' title="' . $MSG['msg_no_exam'][$sysSession->lang] . '">', $icon[0]);
					}
				showXHTML_td_E();
				// 訊息
				showXHTML_td_B('align="center"');
					// 線上傳訊
					$cnt = checkIM($sysSession->username);
					if (intval($cnt) > 0) {
						$title = sprintf($MSG['msg_new_im'][$sysSession->lang], intval($cnt));
						echo '<a href="javascript:;" onclick="parent.frames[0].showUserList(\'/online/msg_history.php\');" title="' . $title . '">' . $icon[1] . '<a>';
					} else {
						echo str_replace('>', ' title="' . $MSG['msg_no_im'][$sysSession->lang] . '">', $icon[0]);
					}
					/**
					// 訊息中心 (Begin)
					$ary = checkMessage($sysSession->username);
					if (intval($ary[1]) > 0) {
						$title = sprintf($MSG['msg_new_message'][$sysSession->lang], $ary[0], intval($ary[1]));
						echo '<a href="javascript:;" title="' . $title . '">' . $icon[1] . '<a>';
					} else {
						echo str_replace('>', ' title="' . $MSG['msg_no_message'][$sysSession->lang] . '"', $icon[0]);
					}
					// 訊息中心 (End)
					**/
				showXHTML_td_E();
				// 文章
				showXHTML_td_B('align="center"');
					$cnt = checkPost($sysSession->username);
					if (intval($cnt) > 0) {
						$title = sprintf($MSG['msg_new_forum'][$sysSession->lang], intval($cnt));
						// echo '<a href="javascript:;" title="' . $title . '">' . $icon[1] . '<a>';
						echo '<a href="/learn/my_forum.php" title="' . $title . '">' . $icon[1] . '<a>';
					} else {
						echo str_replace('>', ' title="' . $MSG['msg_no_post'][$sysSession->lang] . '">', $icon[0]);
					}
				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E();
		$msg = ($isEdit) ? $MSG['msg_reposition_here'][$sysSession->lang] : '&nbsp;';
		showXHTML_mytitle_postit($id, $msg, ' width: ' . $wd . 'px;');
	echo '</div>';
	// showXHTML_mytitle_E();
?>
