<?php
	/**
	 * 儲存討論室設定
	 *
	 * @since   2003/12/30
	 * @author  ShenTing Lin
	 * @version $Id: chat_main_save.php,v 1.1 2010/02/24 02:38:14 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/multi_lang.php');
	require_once(sysDocumentRoot . '/lang/chatroom.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	//$sysSession->cur_func = '2000100300';
	//$sysSession->restore();
	if (!aclVerifyPermission(2000100300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 結束時的動作
	$exitHost = array(
		'none'     => $MSG['exit_act_none'][$sysSession->lang],
		'notebook' => $MSG['exit_act_notebook'][$sysSession->lang],
		'forum'    => $MSG['exit_act_forum'][$sysSession->lang]
	);

	// 聊天室狀態
	$chatStatus = array(
		'disable' => $MSG['status_disable'][$sysSession->lang],
		'open'    => $MSG['status_open'][$sysSession->lang],
		'taonly'  => $MSG['status_taonly'][$sysSession->lang]
	);

	// 必須設定 $env
	if (!isset($env)) die($MSG['access_deny'][$sysSession->lang]);
	$env = trim($env);
	// 不得利用 cookie、post 或 get 方法設定 $env
	$ary = array($_COOKIE['env'], $_POST['env'], $_GET['env']);
	if (in_array($env, $ary)) die($MSG['access_deny'][$sysSession->lang]);

	// 必須設定 $owner_id
	if (!isset($owner_id)) die($MSG['access_deny'][$sysSession->lang]);
	$owner_id = trim($owner_id);
	// 不得利用 cookie、post 或 get 方法設定 $owner_id
	$ary = array($_COOKIE['owner_id'], $_POST['owner_id'], $_GET['owner_id']);
	if (in_array($owner_id, $ary)) die($MSG['access_deny'][$sysSession->lang]);

	$rid = trim($_POST['chat_id']);

	// 檢查 ticket
	$ticket = md5(sysTicketSeed . $_COOKIE['idx'] . $rid);
	if (trim($_POST['ticket']) != $ticket) {
	   wmSysLog($sysSession->cur_func, 1, 'auto', $_SERVER['PHP_SELF'], '拒絕存取!');
	   die($MSG['access_deny'][$sysSession->lang]);
	}

	$isError = false;
	// 檢查日期
	if (($_POST['visibility'] == 'visible') && ($_POST['status'] != 'disable') && isset($_POST['ckopen']) && isset($_POST['ckclose'])) {
		// 比對日期
		if (strcmp($_POST['timeopen'], $_POST['timeclose']) > 0) {
			$msg = $MSG['msg_date_error'][$sysSession->lang];
			$isError = true;
			wmSysLog($sysSession->cur_func, 2, 'auto', $_SERVER['PHP_SELF'], '關閉日期必須大於開始日期');
		}
	}

	if (($_POST['visibility'] == 'hidden') || ($_POST['status'] == 'disable')) {
		if (isset($_POST['ckopen'])) unset($_POST['ckopen']);
		if (isset($_POST['ckclose'])) unset($_POST['ckclose']);
	}

	$open  = preg_replace('/[^0-9: -]/', '', $_POST['timeopen'])  . ':00';
	$close = preg_replace('/[^0-9: -]/', '', $_POST['timeclose']) . ':00';
	$lang['Big5']   = stripslashes(trim($_POST['host_room_name_big5']));
	$lang['GB2312'] = stripslashes(trim($_POST['host_room_name_gb']));
	$lang['en']     = stripslashes(trim($_POST['host_room_name_en']));
	$lang['EUC-JP'] = stripslashes(trim($_POST['host_room_name_jp']));
	$lang['user_define'] = stripslashes(trim($_POST['host_room_name_user']));
	$chat_jump     = isset($_POST['host_change']) ? 'allow' : 'deny';
	$chat_open     = !isset($_POST['ckopen'])  ? '0000-00-00 00:00:00' : $open;
	$chat_close    = !isset($_POST['ckclose']) ? '0000-00-00 00:00:00' : $close;
	$chat_media    = isset($_POST['enable_media']) ? 'enable' : 'disable';
	$chat_protocol = (preg_replace('/\W/', '', $_POST['host_media_protocol']) == 'tcp') ? 'TCP' : 'UDP';
	$chat_login    = (preg_replace('/\W/', '', $_POST['host_login']) == 'no') ? 'N' : 'Y';
	//#chrome
	$dd = array(
		'title'      => addslashes(serialize($lang)),
		'limit'      => intval($_POST['host_user_limit']),
		'exitAct'    => preg_replace('/\W/', '', $_POST['host_exit']),
		'jump'       => $chat_jump,
		'status'     => preg_replace('/\W/', '', $_POST['status']),
		'visibility' => preg_replace('/\W/', '', $_POST['visibility']),
		'media'      => $chat_media,
		'ip'         => preg_replace('/[^0-9.]/', '', $_POST['host_media_ip']),
		'port'       => intval($_POST['host_media_port']),
		'protocol'   => $chat_protocol,
		'host'       => preg_replace('/\W-/', '', $_POST['host_root']),
		'login'      => $chat_login
	);

	if (!$isError) {
		if (empty($rid)) {
			$rid = uniqid('');
			$owner = $owner_id;
			dbNew('WM_chat_setting',
				'`rid`, `owner`, `title`, `host` , `get_host`, ' .
				'`maximum`, `exit_action`, `jump`, `open_time`, `close_time`, ' .
				'`state`, `visibility`, `media`, `ip`, `port`, `protocol`',
				"'{$rid}', '{$owner}', '{$dd['title']}', '{$dd['host']}', '{$dd['login']}', " .
				"{$dd['limit']}, '{$dd['exitAct']}', '{$dd['jump']}', '{$chat_open}', '{$chat_close}', " .
				"'{$dd['status']}', '{$dd['visibility']}', '{$dd['media']}', '{$dd['ip']}', {$dd['port']}, '{$dd['protocol']}'"
			);
			$msg = ($sysConn->Affected_Rows() > 0) ? $MSG['msg_add_success'][$sysSession->lang] : $MSG['msg_add_fail'][$sysSession->lang];
		} else {

		    // 代換學習路徑節點的 <title> (先取得原 title)
			chkSchoolId('WM_chat_setting');
			$old_title = $sysConn->GetOne('select title from WM_chat_setting where rid="' . $rid . '"');

			dbSet('WM_chat_setting',
				"`title`='{$dd['title']}', `host`='{$dd['host']}', `get_host`='{$dd['login']}', " .
				"`maximum`={$dd['limit']}, `exit_action`='{$dd['exitAct']}', `jump`='{$dd['jump']}', " .
				"`open_time`='{$chat_open}', `close_time`='{$chat_close}', `state`='{$dd['status']}', " .
				"`visibility`='{$dd['visibility']}', `media`='{$dd['media']}', " .
				"`ip`='{$dd['ip']}', `port`={$dd['port']}, `protocol`='{$dd['protocol']}'",
				"`rid`='{$rid}'"
			);
			$msg = ($sysConn->Affected_Rows() > 0) ? $MSG['msg_update_success'][$sysSession->lang] : $MSG['msg_update_fail'][$sysSession->lang];

		    // 代換學習路徑節點的 <title> begin
			if ($sysSession->course_id && serialize($lang) != $old_title)
			{
				$manifest = new SyncImsmanifestTitle(); // 本類別定義於 db_initialize.php
				$manifest->replaceTitleForImsmanifest(7, $rid, $manifest->convToNodeTitle($lang));
				$manifest->restoreImsmanifest();
			}
			// 代換學習路徑節點的 <title> end

		}
		wmSysLog($sysSession->cur_func, 0, 'auto', $_SERVER['PHP_SELF'], $msg);
	}

	$js = <<< BOF
	/**
	 * 回到管理列表
	 **/
	function goManage() {
		window.location.replace("chat_manage.php");
	}

	window.onload = function () {
		alert("{$msg}");
	};
BOF;

	showXHTML_head_B($MSG['chat_save_title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    if (defined('SHOW_PHONE_UI') && SHOW_PHONE_UI === 1){
        echo '<style>';
        echo '#outerTable{margin:0 auto;width:96%;margin-top:15px;}';
        echo 'textarea {width:90%}';
        echo '.cssBtn {height:unset}';
        echo '</style>';
    }
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B('');
		$ary = array();
		$ary[] = array($MSG['tabs_save_chat'][$sysSession->lang], 'tabs_host');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, '', 'outerTable');
			// 主持人設定 (Begin)
			if (defined('SHOW_PHONE_UI') && SHOW_PHONE_UI === 1){
			    showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="tabs_host" class="cssTable"');
			} else {
			    showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="tabs_host" class="cssTable"');
			}
				showXHTML_tr_B('class="cssTrHelp"');
					showXHTML_td('colspan="3"', $msg);
				showXHTML_tr_E();
				// 聊天室名稱
				$lang = unserialize(stripslashes($dd['title']));
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('width="180"', $MSG['host_msg_room_name'][$sysSession->lang]);
					showXHTML_td_B('width="570"');
						$multi_lang = new Multi_lang(true, $lang, $col); // 多語系輸入框
						$multi_lang->show(false);
					showXHTML_td_E();
				showXHTML_tr_E();

				// 人數限制
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2"');
						echo $MSG['host_msg_user_limit1'][$sysSession->lang];
						echo $dd['limit'];
						echo $MSG['host_msg_user_limit2'][$sysSession->lang];
					showXHTML_td_E();
				showXHTML_tr_E();
				// 結束聊天後處理聊天內容
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2"');
						echo $MSG['host_msg_exit'][$sysSession->lang];
						echo $exitHost[$dd['exitAct']];
					showXHTML_td_E();
				showXHTML_tr_E();
				// 允許切換聊天室
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2"');
						$chk = ($dd['jump'] == 'allow') ? ' checked="checked" ' : '';
						showXHTML_input('checkbox', 'host_change', '', '', $chk . 'disabled="disabled"');
						echo $MSG['host_msg_allow_chg'][$sysSession->lang];
					showXHTML_td_E();
				showXHTML_tr_E();
				// 顯示或隱藏
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('', $MSG['chat_visibility'][$sysSession->lang]);
					showXHTML_td_B('');
						$ary = array(
							'visible' => $MSG['chat_visible'][$sysSession->lang],
							'hidden'  => $MSG['chat_hidden'][$sysSession->lang]
						);
						echo $ary[$dd['visibility']];
					showXHTML_td_E();
				showXHTML_tr_E();
				if ($dd['visibility'] == 'visible') {
					// 狀態
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td('', $MSG['chat_status'][$sysSession->lang]);
						showXHTML_td('', $chatStatus[$dd['status']]);
					showXHTML_tr_E();
					if ($dd['status'] != 'disable') {
						// 開放日期
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('', $MSG['chat_open_time'][$sysSession->lang]);
							showXHTML_td_B('');
								echo !isset($_POST['ckopen']) ? $MSG['unlimit'][$sysSession->lang] : $open;
							showXHTML_td_E();
						showXHTML_tr_E();
						// 關閉日期
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('', $MSG['chat_close_time'][$sysSession->lang]);
							showXHTML_td_B('');
								echo !isset($_POST['ckclose']) ? $MSG['unlimit'][$sysSession->lang] : $close;
							showXHTML_td_E();
						showXHTML_tr_E();
					}
				}
				// 主持人設定
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('colspan="2"', $MSG['host_msg_host_set'][$sysSession->lang]);
				showXHTML_tr_E();
				// 聊天室管理員
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('colspan="2" style="padding-left: 30px;"', $MSG['host_msg_root'][$sysSession->lang] . $dd['host']);
				showXHTML_tr_E();
				// 登入時是否取回主持權
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2" style="padding-left: 30px;"');
						echo $MSG['host_msg_login'][$sysSession->lang];
						$ary = array(
							'yes' => $MSG['yes'][$sysSession->lang],
							'no'  => $MSG['no'][$sysSession->lang],
						);
						$chk = ($dd['login'] == 'Y') ? 'yes' : 'no';
						echo $ary[$chk];
					showXHTML_td_E();
				showXHTML_tr_E();
				// 離開按鈕
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('colspan="2" align="center"');
						showXHTML_input('button', '', $MSG['btn_return'][$sysSession->lang], '', 'class="cssBtn" onclick="goManage()"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
			// 主持人設定 (End)
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();
?>
