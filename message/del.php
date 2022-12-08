<?php
	/**
	 * 刪除訊息
	 *
	 * 建立日期：2003/05/15
	 * @author  ShenTing Lin
	 * @version $Id: del.php,v 1.1 2010/02/24 02:40:17 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/msg_center.php');
	require_once(sysDocumentRoot . '/message/lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	// $sysSession->cur_func = '2200200400';
	// $sysSession->restore();
	if (!aclVerifyPermission(2200200400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if ($sysSession->cur_func == $msgFuncID['notebook']) {
		$target = 'notebook.php';
		$trash  = 'sys_notebook_trash';
		$isNB   = true;
	} else {
		$target = 'index.php';
		$trash  = 'sys_trash';
		$isNB   = false;
	}
	
	$error = 0;
	$result = array();
	if (!isset($_POST['fid'])) {
		$error = 1;
	} else {
		$fid = $_POST['fid'];
		if (!is_array($fid) || count($fid) <= 0) {
			$error = 2;
		} else {
			// 取得目前所在的資料夾 ID
			$folder_id = getFolderId();
			$ary = array('sys_trash', 'sys_notebook_trash');
			if (!in_array($folder_id, $ary)) {
				// 將訊息移至備份資料夾中
                $moveTime = date('Y-m-d H:i:s', time());
				for ($i = 0; $i < count($fid); $i++) {
					$val = intval($fid[$i]);
					dbSet('WM_msg_message', "`folder_id`='{$trash}', `submit_time` = '{$moveTime}', `receive_time` = '{$moveTime}'", "`msg_serial`='{$val}' AND `receiver`='{$sysSession->username}'");

                    if ($sysSession->cur_func == $msgFuncID['notebook']) {
                        // 如果是筆記本的功能，要處理雲端筆記的log - begin
                        $logTime = strtotime($moveTime);
                        dbNew('APP_note_action_history',
                            '`username`, `log_time`, `action`, `folder_id`, `msg_serial`, `from`',
                            "'{$sysSession->username}', {$logTime}, 'D', '{$trash}', {$val}, 'server'");
                        // 如果是筆記本的功能，要處理雲端筆記的log - end
                    }
				}

				header('Location: ' . $target);
				die();
			} else {
				// 訊息已經在備份資料夾中則直接刪除
				$filepath = MakeUserDir($sysSession->username);   // 取得使用者的目錄
				for ($i = 0; $i < count($fid); $i++) {
					$val = intval($fid[$i]);
					// 取得標題與夾檔
					$RS = dbGetStSr('WM_msg_message', '`subject`, `attachment`', "`msg_serial`='{$val}' AND `receiver`='{$sysSession->username}'", ADODB_FETCH_ASSOC);

					// 刪除附件
					if (!empty($RS['attachment'])) {
						$f = explode(chr(9), trim($RS['attachment']));
						for($j = 0; $j < count($f); $j += 2) {
							@unlink($filepath . DIRECTORY_SEPARATOR . $f[$j + 1]);
						}
					}

					// 刪除訊息
					dbDel('WM_msg_message', "`msg_serial`='{$val}' AND `receiver`='{$sysSession->username}'");
					if ($sysConn->Affected_Rows() > 0) {
						$result[$fid[$i]] = array($RS['subject'], 1); // 成功
					} else {
						$result[$fid[$i]] = array($RS['subject'], 0); // 失敗
					}

				}
			}
		}
		// 順便清除多餘檔案
		cleanRedundancyAttachments();
	}

	$js = <<< BOF

	function getTarget() {
		var obj = null;
		switch (this.name) {
			case "s_main"   : obj = parent.s_catalog; break;
			case "c_main"   : obj = parent.c_catalog; break;
			case "main"     : obj = parent.catalog  ; break;
			case "s_catalog": obj = parent.s_main   ; break;
			case "c_catalog": obj = parent.c_main   ; break;
			case "catalog"  : obj = parent.main     ; break;
		}
		return obj;
	}

	/**
	 * return message list
	 * @param
	 * @return
	 **/
	function goList() {
		remove_unload();
		window.location.replace("{$target}");
	}

	function remove_unload() {
		window.onunload = function () {};
	}

	function winColse() {
		var obj = null;
		obj = getTarget();
		if (obj != null) obj.location.replace("about:blank");
	}

	window.onunload = winColse;

BOF;
	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);

	showXHTML_head_E('');
	showXHTML_body_B('');
		showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary = array();
					$ary[] = array($MSG['tabs_del_msg'][$sysSession->lang], 'tabs1');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" class="bg01"');
					showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						if ($error > 0) {
							showXHTML_tr_B('class="cssTrHead"');
								showXHTML_td('', $MSG['del_empty_msg'][$sysSession->lang]);
							showXHTML_tr_E('');
						} else {
							showXHTML_tr_B('class="cssTrHead"');
								$msg = $isNB ? $MSG['nb_subject'][$sysSession->lang] : $MSG['subject'][$sysSession->lang];
								showXHTML_td('nowrap="nowrap" align="center"', $msg);
								showXHTML_td('nowrap="nowrap" align="center"', $MSG['result'][$sysSession->lang]);
							showXHTML_tr_E('');

							foreach($result as $val) {
								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td('nowrap="nowrap"', $val[0]);
									showXHTML_td('nowrap="nowrap"', $val[1] ? $MSG['del_success'][$sysSession->lang] : $MSG['del_fail'][$sysSession->lang]);
								showXHTML_tr_E('');
							}
						}
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td_B('colspan="2" nowrap="nowrap" align="center"');
								showXHTML_input('button', '', $MSG['goto_list'][$sysSession->lang], '', 'class="cssBtn" onclick="goList();"');
							showXHTML_td_E('');
						showXHTML_tr_E('');
					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
	showXHTML_body_E('');
?>
