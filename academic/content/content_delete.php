<?php
	/**
	 * 刪除教材
	 *
	 * 建立日期：2002/08/30
	 * @author  ShenTing Lin
	 * @version $Id: content_delete.php,v 1.1 2010/02/24 02:38:16 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot  . '/lib/interface.php');
	require_once(PEAR_INSTALL_DIR . '/System.php');
	require_once(sysDocumentRoot . '/lang/content_manage.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	$sysSession->cur_func='700500300';
	$sysSession->restore();

	if (!aclVerifyPermission(700500300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}

	$title = '';
	// 刪除教材
	$ticket = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Delete' . $sysSession->username);
	if (trim($_POST['ticket']) == $ticket) {
		$title = $MSG['title_delete'][$sysSession->lang];
	} else {
		wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,1, 'manager', $_SERVER['PHP_SELF'], '拒絕存取!');
		die($MSG['access_deny'][$sysSession->lang]);
	}

	// 設定車票
	//setTicket();

	// 開始呈現 HTML
	showXHTML_head_B($MSG['title_delete'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_head_E('');
	showXHTML_body_B('');
		$ary[] = array($MSG['tabs_delete'][$sysSession->lang], 'tabs');
		showXHTML_tabFrame_B($ary, 1, 'fmact', 'ListTable');
			showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" class="box01"');
				showXHTML_tr_B('class="font01 bg02"');
					showXHTML_td('', $MSG['td_title_id'][$sysSession->lang]);
					showXHTML_td('', $MSG['td_title_name'][$sysSession->lang]);
					showXHTML_td('', $MSG['td_title_result'][$sysSession->lang]);
				showXHTML_tr_E('');

                $_POST['cont_id'] = array_unique(preg_split('/\D+/', implode(',', $_POST['cont_id']), -1, PREG_SPLIT_NO_EMPTY));
				$content_ids = implode(',', $_POST['cont_id']);
				if (!empty($content_ids)) {
					//$sysConn->debug = true;
					$inUses = array();
					$delPath = array();
					// 取得每份教材被課程使用的數量
					$RS = dbGetStMr('WM_term_course', 'content_id, count(*) AS cnt', "content_id in ({$content_ids}) group by content_id", ADODB_FETCH_ASSOC);
					while (!$RS->EOF) {
						$inUses[$RS->fields['content_id']] = $RS->fields['cnt'];
						$RS->MoveNext();
					}
					// 從資料庫中取得舊有的資料
					$RS = dbGetStMr('WM_content', 'content_id, caption,content_sn, path', "content_id in ({$content_ids})", ADODB_FETCH_ASSOC);
					while (!$RS->EOF) {
						$delPath[$RS->fields['content_id']] = array($RS->fields['caption'], $RS->fields['path'],$RS->fields['content_sn']);
						$RS->MoveNext();
					}
					// 刪除沒有被課程使用的教材
					while(list($key, $val) = each($_POST['cont_id'])) {
						$lang = unserialize($delPath[$val][0]);
						$col = ($col == 'bg03') ? 'bg04' : 'bg03';
						// 使用中
						if ($inUses[$val] > 0) {
							showXHTML_tr_B('class="font01 ' . $col . '"');
								showXHTML_td('', $delPath[$val][2]);
								showXHTML_td('', $lang[$sysSession->lang]);
								showXHTML_td('', $MSG['msg_cant_del'][$sysSession->lang]);
							showXHTML_tr_E('');
							wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,2, 'manager', $_SERVER['PHP_SELF'], $MSG['msg_cant_del'][$sysSession->lang].$val);
							continue;
						}
						// 未使用
						showXHTML_tr_B('class="font01 ' . $col . '"');
							if (empty($delPath[$val])) {
								showXHTML_td('', $delPath[$val][2]);
								showXHTML_td('', '&nbsp; ');
								showXHTML_td('', $MSG['msg_not_exist'][$sysSession->lang]);
								wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,3, 'manager', $_SERVER['PHP_SELF'], $MSG['msg_not_exist'][$sysSession->lang].$val);
							} else {
								// 將資料從資料庫中刪除
								$RS = dbDel('WM_content', "content_id={$val}");
								// 刪除教材檔案
								$path = sysDocumentRoot . $delPath[$val][1];
								if (preg_match('!^/base/' . $sysSession->school_id . '/content/[0-9]{6}$!', $delPath[$val][1]) &&
									is_dir($path)
								   ) {
									@System::rm("-rf {$path}");
								}
								wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,0, 'manager', $_SERVER['PHP_SELF'], $MSG['msg_del_success'][$sysSession->lang].$val);
								showXHTML_td('', $delPath[$val][2]);
								showXHTML_td('', $lang[$sysSession->lang]);
								showXHTML_td('', $MSG['msg_del_success'][$sysSession->lang]);
							}
						showXHTML_tr_E('');
					}
				}
				$col = ($col == 'bg03') ? 'bg04' : 'bg03';
				showXHTML_tr_B('class="font01 ' . $col . '"');
					showXHTML_td_B('colspan="3" align="center"');
						if (strpos($_SERVER['PHP_SELF'], '/course/') === false)
							showXHTML_input('button', '', $MSG['btn_reutrn'][$sysSession->lang], '', 'class="button01" onclick="window.location.replace(\'content_package_manager.php\')"');
						else
							showXHTML_input('button', '', $MSG['btn_reutrn'][$sysSession->lang], '', 'class="button01" onclick="window.location.replace(\'content_list.php\')"');
					showXHTML_td_E('');
				showXHTML_tr_E('');
			showXHTML_table_E('');
		showXHTML_tabFrame_E();
	showXHTML_body_E('');
?>
