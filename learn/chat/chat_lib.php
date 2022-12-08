<?php
	/**
	 * ��ѫǦ@�Ψ禡
	 *
	 * @since   2003/11/27
	 * @author  ShenTing Lin
	 * @version $Id: chat_lib.php,v 1.1 2010/02/24 02:39:05 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lang/chatroom.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	// �]�w�C���X�����
	$lines = (defined('sysPostPerPage')) ? sysPostPerPage : 10;

	// �]�w Timeout �����ơA�H online 30 ���s�@���p��
	// �w�] 6 ���A�h��ܤT������N�N�ϥΪ̽�X��ѫ�
	$crTimeout = 6;

	// �]�w�s����|
	$crPath = sysTempPath;

	// �y��
	$tones = array(
		0 => array('#8D8D8D', $MSG['chat_tone00'][$sysSession->lang]),
		1 => array('#4E1A9B', $MSG['chat_tone01'][$sysSession->lang]),
		2 => array('#8D8D8D', $MSG['chat_tone02'][$sysSession->lang]),
		3 => array('#126D7B', $MSG['chat_tone03'][$sysSession->lang]),
		4 => array('#0B00A1', $MSG['chat_tone04'][$sysSession->lang]),
		5 => array('#086B00', $MSG['chat_tone05'][$sysSession->lang]),
		6 => array('#2495FF', $MSG['chat_tone06'][$sysSession->lang]),
		7 => array('#D700CF', $MSG['chat_tone07'][$sysSession->lang])
	);

	// �����ɪ��ʧ@
	$exitHost = array(
		'none'     => $MSG['exit_act_none'][$sysSession->lang],
		'notebook' => $MSG['exit_act_notebook'][$sysSession->lang],
		'forum'    => $MSG['exit_act_forum'][$sysSession->lang]
		// 'email'    => $MSG['exit_act_email'][$sysSession->lang]   // �O�d����
	);

	$exitUser = array(
		'none'     => $MSG['exit_act_none'][$sysSession->lang],
		'notebook' => $MSG['exit_act_notebook'][$sysSession->lang],
		// 'email'    => $MSG['exit_act_email'][$sysSession->lang]   // �O�d����
	);

	// ��ѫǪ��A
	$chatStatus = array(
		'disable' => $MSG['status_disable'][$sysSession->lang],
		'open'    => $MSG['status_open'][$sysSession->lang],
		'taonly'  => $MSG['status_taonly'][$sysSession->lang]
	);

	// �y�����L�ҥ�
	$mediaStatus = array(
		'disable' => $MSG['media_disable'][$sysSession->lang],
		'enable'  => $MSG['media_enable'][$sysSession->lang]
	);

	$chatVisible = array(
		'visible' => $MSG['chat_visible'][$sysSession->lang],
		'hidden'  => $MSG['chat_hidden'][$sysSession->lang]
	);
	// ���o��ѫǪ��]�w���
	// dbGetStSr('WM_chat_setting', '', "`rid`={$rid}");

	/**
	 * �]�w��ѫǽs��
	 * @param string $val : ��ѫǽs��
	 **/
	function setRoomId($val) {
		global $_COOKIE;
		dbSet('WM_session', "`room_id`='{$val}'", "idx='{$_COOKIE['idx']}'");
	}

	/**
	 * ���o��Ѥ��e�����ɪ�������|
	 * @param string  $rid : ��ѫǽs��
	 * @return string : ���|
	 **/
	function getRecFullPath($rid='') {
		global $sysSession, $crPath;

		$rid = trim($rid);
		if (empty($rid)) $rid = $sysSession->room_id;

		// �إ߲�Ѥ��e������
		$fname = $crPath . '/wm_cr_' . $rid . '.csv'; // �ɮצW��
		if (!file_exists($fname)) touch($fname);
		return $fname;
	}

	/**
	 * �g�J��Ѥ��e
	 * @param string  $cont         : ���e
	 * @param integer $status       : ���A
	 *     -2�G�D���H�j��U�u
	 *     -1�G�t�αj��U�u
	 *      0�G�U�u����
	 *      1�G�W�u����
	 *      2�G�@�뤺�e
	 *      3�G�ɮ�
	 *      4�G�ШD�o��
	 *      5�G���\�o��
	 *      6�G�T��o��
	 * @param integer $tone         : �y��
	 * @param string  $reciver      : ��H ID
	 * @param string  $reciver_name : ��H�W�r
	 * @param string  $sender       : �ǰe�� ID
	 * @param string  $sender_name  : �ǰe�̦W�r
	 * @return boolean : ���\�Υ���
	 **/
	function setChatCont($cont='', $status=2, $tone=1, $reciver='', $reciver_name='', $sender='', $sender_name='') {
		global $sysSession, $sysConn, $tones, $crPath;

		$rid    = $sysSession->room_id;
		// �ǰe��
		$sender = trim($sender);
		if (empty($sender)) $sender = $sysSession->username;
		$sender_name = trim($sender_name);
		if (empty($sender_name)) $sender_name = $sysSession->realname;
		// ���A
		$status = intval($status);
		// �y��
		$tone   = intval($tone);
		$color  = ($tone > 0) ? $tones[$tone][0] : '';
		$text   = ($tone > 0) ? $tones[$tone][1] : '';
		// �ɶ�
		$date   = date('Y-m-d H:i:s');
		// ���e
		$str = strip_scr(addslashes($cont));
		$str = str_replace("\t", '    '  , $str);
		$str = str_replace("\n", '<br />', $str);
		$str = str_replace("\r", ''      , $str);

		//       ���A       �o��                     �W�r                     ��H        �W�r             �ɶ�     �y��     �C��      ���e
		$csv = "{$status}\t{$sender}\t{$sender_name}\t{$reciver}\t{$reciver_name}\t{$date}\t{$text}\t{$color}\t{$str}\n";

		// �إ߲�Ѥ��e������
		// �g�J��Ʈw

        // MIS#048930 ��h�xweb�D���ɡA�|�o�ͥD�����ɶ����t�A�N�y�����r
        // �ѩ�mysql5.6�~�䴩�L��C�]���L�k�ϥ�mysql���o�L��
        $currSeq = intval(dbGetOne('WM_chat_msg','max(seq)',sprintf("rid='%s'",$rid)));

		$sec_str = microtime();
		list($msec, $sec) = explode(' ',$sec_str);
		$seq = intval($sec.substr($msec,2,4));

        // ���Ƥ�����Ƥj��ثe�ȡA��ܦ��h�xweb�D���b�A�h�H�̤j�ɶ��[1�Ӧs
        if ($currSeq > $seq) {
            chkSchoolId('WM_chat_msg');
            $sysConn->Execute("INSERT INTO WM_chat_msg (rid,seq,msgType,msg) SELECT '{$rid}',max(seq)+1,'{$status}','{$csv}' FROM WM_chat_msg where rid='{$rid}'");
        }else{
            dbNew("WM_chat_msg", "rid,seq,msgType,msg", "'{$rid}','{$seq}','{$status}','{$csv}'");
        }

		/*
			// �g�J�ɮ�
		$fname = getRecFullPath();
		$fp  = fopen($fname, 'a');
		$len = fwrite($fp, $csv);
		fclose($fp);
		*/
		return ($sysConn->ErrorNo() === 0);
	}

	/**
	 * Ū����ѫǤ��e
	 * @param integer $begin : �}�l��ơA�w�]���ɮ׶}�Y
	 * @param integer $end   : ������ơA�w�]���ɮ׵���
	 * @return string : ���e
	 **/
	// �]���Q�׫Ǻ޲z�i�H�j����|ĳ���������ק� by Small 2011/11/16
	function getChatCont($begin=0, $end=0, $room_id='') {
		global $sysSession, $sysConn, $currline;

		$currline = $begin;
		$rid  = (!empty($room_id))? $room_id : $sysSession->room_id;
		$rs = dbGetStMr('WM_chat_msg', 'seq, msg', "rid='{$rid}' and seq>{$begin} order by seq", ADODB_FETCH_ASSOC);
		$rtns = '';
		if ($rs)
		{
			while($fields = $rs->FetchRow())
			{
				$rtns .= $fields['msg'];
				$currline = $fields['seq'];
			}
		}
		return $rtns;

		/*
		global $sysSession, $crPath;

		$rid   = $sysSession->room_id;
		// �إ߲�Ѥ��e������
		$fname = $crPath . '/wm_cr_' . $rid . '.csv'; // �ɮצW��
		if (!file_exists($fname)) touch($fname);

		$fp   = fopen($fname, 'r');
		$i    = 0;
		$cont = '';
		while (!feof($fp)) {
			if ($i >= $begin) break;
			fgets($fp);
			$i++;
		}
		ob_start();
			fpassthru($fp);
			$cont = ob_get_contents();
		ob_end_clean();
		fclose($fp);

		return $cont;
		*/
	}

	/**
	 * �M���w�g���u���H��
	 **/
	function cleanUserLst() {
		global $sysSession, $sysConn, $Sqls, $crTimeout;

		$rid = $sysSession->room_id;
		$sqls = str_replace('%ROOM_ID%', $rid, $Sqls['get_chat_offline_user']);
		chkSchoolId('WM_chat_session');
		$RS = $sysConn->Execute($sqls);
		while (!$RS->EOF) {
			$chance = intval($RS->fields['chance']);
			if (($chance < 0) || ($chance > $crTimeout)) {
				dbDel('WM_chat_session', "`rid`='{$rid}' AND `idx`='{$RS->fields['idx']}' AND `username`='{$RS->fields['username']}'");
				if ($sysConn->Affected_Rows() > 0) {
					setChatCont('', -1, 0, '', '', $RS->fields['username'], $RS->fields['realname']);
				}
			}
			$RS->MoveNext();
		}
	}

	/**
	 * �M���Q�׫Ǥ��e
	 **/
	function clearChatMsg($rid='')
	{
		global $sysSession;
		// �]���Q�׫Ǻ޲z�i�H�j����|ĳ�ӧ@���ק� by Small 2011/11/16
		$rid = (!empty($rid))? $rid : $sysSession->room_id;
		dbDel('WM_chat_msg', "rid='{$rid}'");
	}

	/**
	 * ���o�ϥΪ̦C��
	 * @return
	 **/
	function getChatUserLst() {
		global $sysSession;

		$haveH   = false;
		$host    = '';
		$xmlStrs = '';
		$rid = $sysSession->room_id;
		$RS  = dbGetStMr('WM_chat_session', '`username`, `realname`, `host`, `voice`', "`rid`='{$rid}' order by `host` DESC, `login` ASC", ADODB_FETCH_ASSOC);
		while (!$RS->EOF) {
			$xmlStrs .= '<user>';
			$xmlStrs .= '<username>' . trim($RS->fields['username']) . '</username>';
			$xmlStrs .= '<realname>' . trim(htmlspecialchars($RS->fields['realname'])) . '</realname>';
			$xmlStrs .= '<is_host>' . $RS->fields['host'] . '</is_host>';
			$xmlStrs .= '<say>' . $RS->fields['voice'] . '</say>';
			$xmlStrs .= '</user>';
			if (!$haveH && ($RS->fields['host'] == 'Y')) {
				$host  = '<host>' . trim($RS->fields['username']) . '</host>';
				$haveH = true;
			}
			$RS->MoveNext();
		}
		return $host . $xmlStrs;
	}

	/**
	 * ���o��ѫǦC��
	 * @return
	 **/
	function getChatRoomLst() {
		global $sysSession;

		$xmlStrs = '';
		$RS  = dbGetStMr('WM_chat_session', 'DISTINCT `rid`', '1', ADODB_FETCH_ASSOC);
		if ($RS) {
			while (!$RS->EOF) {
				$rid = $RS->fields['rid'];
				list($title, $jump) = dbGetStSr('WM_chat_setting', '`title`, `jump`', "`rid`='{$rid}'", ADODB_FETCH_NUM);
				$lang = getCaption($title);
				$in_room = ($sysSession->room_id == $rid) ? 'true' : 'false';
				$xmlStrs .= '<rooms>';
				$xmlStrs .= '<room id="' . $rid . '" in="' . $in_room . '" change="' . $jump . '">' . $lang[$sysSession->lang] . '</room>';
				$xmlStrs .= '</rooms>';
				$RS->MoveNext();
			}
		}
		return $xmlStrs;
	}

	/**
	 * ���o��ѫǪ��D���H
	 * @return string : �D���H���b��
	 **/
	function getChatHost() {
		global $sysSession;
		list($host) = dbGetStSr('WM_chat_session', '`username`', "`rid`='{$sysSession->room_id}' AND `host`='Y'", ADODB_FETCH_NUM);
		return $host;
	}

	/**
	 * ���o��ѫǪ��޲z��
	 * @return string : �޲z�����b��
	 **/
	function getChatAdmin() {
		global $sysSession;
		list($host) = dbGetStSr('WM_chat_setting', '`host`', "`rid`='{$sysSession->room_id}'", ADODB_FETCH_NUM);
		return $host;
	}

	/**
	 * ���o��ѫ��ɮצs�񪺸��|
	 * @return string : ���|
	 **/
	function getChatPath() {
		global $sysSession;

		$rid = $sysSession->room_id;
		list($owner) = dbGetStSr('WM_chat_setting', '`owner`', "`rid`='{$rid}'", ADODB_FETCH_NUM);
		$pos = explode('_', $owner);
		$dir = '';
		if (ereg('^[0-9]{8}$', $pos[0])) {
			// �ҵ{
			$dir  = sysDocumentRoot . "/base/{$sysSession->school_id}/course/{$sysSession->course_id}/chat";
			if (!@is_dir($dir)) @mkdir($dir);
			$dir .= '/' . $rid;
			if (!@is_dir($dir)) @mkdir($dir);
			$dir .= '/';
		} else if (ereg('^[0-9]{7}$', $pos[0])) {
			// �Z��
			$dir  = sysDocumentRoot . "/base/{$sysSession->school_id}/class/{$sysSession->class_id}/chat";
			if (!@is_dir($dir)) @mkdir($dir);
			$dir .= '/' . $rid;
			if (!@is_dir($dir)) @mkdir($dir);
			$dir .= '/';
		} else if (ereg('^[0-9]{5}$', $pos[0])) {
			// �Ǯ�
			$dir  = sysDocumentRoot . "/base/{$sysSession->school_id}/chat";
			if (!@is_dir($dir)) @mkdir($dir);
			$dir .= '/' . $rid;
			if (!@is_dir($dir)) @mkdir($dir);
			$dir .= '/';
		// } else if ((checkUsername($pos[0]) == 2) || (checkUsername($pos[0]) == 4)) {
		} else {
			// �ӤH
			$dir = MakeUserDir($pos[0]);
		}
		return $dir;
	}

	function showError($title='', $msg='&nbsp;') {
		global $sysSession, $MSG;
		$js = <<< BOF
	window.onload = function () {
		var sw = 0, sh = 0;
		sw = (parseInt(screen.width) - 300) / 2;
		sh = (parseInt(screen.height) - 250) / 2;
		top.window.moveTo(parseInt(sw), parseInt(sh));
		top.window.resizeTo(300, 250);
	};
BOF;
		showXHTML_head_B($MSG['title_error_win'][$sysSession->lang]);
		showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
		showXHTML_script('inline', $js);
		showXHTML_head_E();
		showXHTML_body_B();
			echo '<div align="center">';
			showXHTML_table_B('width="250" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('', $title);
				showXHTML_tr_E();
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td('', $msg);
				showXHTML_tr_E();
				showXHTML_tr_B('class="cssTrOdd"');
					showXHTML_td_B('align="center"');
						showXHTML_input('button', '', $MSG['btn_ok_close'][$sysSession->lang], '', 'onclick="top.window.close();" class="cssBtn"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
			echo '</div>';
		showXHTML_body_E();
	}

	/**
	 * ���A���P�_(����/�ҥ�/�Юv�B�U�бM��,�H�ΰ_���ɶ�)
	 * @param str $msg   : �T��
	**/
	function showStateError($msg) {
		global $sysSession, $MSG;
		if (!empty($sysSession->room_id)) {
			$js = <<< BOF
		window.onload = function () {
			var obj = document.getElementById("tabs1");
			var xW = 300, xH = 150;
			if (typeof(window.dialogWidth) == "undefined") {
				parent.window.resizeTo(xW, xH);
			} else {
				window.dialogWidth  = xW + "px";
				window.dialogHeight = xH + "px";
			}
		};
BOF;
			showXHTML_head_B($MSG['title_error_win'][$sysSession->lang]);
			showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
			showXHTML_script('inline', $js);
			showXHTML_head_E();
			showXHTML_body_B();
				$ary = array();
				$ary[] = array($MSG['title_error_win'][$sysSession->lang], 'tabs1');
				echo '<div align="center">';
				showXHTML_tabFrame_B($ary, 1); //, form_id, table_id, form_extra, isDragable);
					showXHTML_table_B('width="250" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('', $msg);
						showXHTML_tr_E();
						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td_B('align="center"');
								showXHTML_input('button', '', $MSG['tabs_title_close'][$sysSession->lang], '', 'onclick="top.window.close();" class="cssBtn"');
							showXHTML_td_E();
						showXHTML_tr_E();
					showXHTML_table_E();
				showXHTML_tabFrame_E();
				echo '</div>';
			showXHTML_body_E();
		}
	}
?>
