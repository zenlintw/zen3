<?php
	/**
	 * 儲存審核結果，並且寄送信件
	 *
	 * @since   2004/03/17
	 * @author  ShenTing Lin
	 * @version $Id: review_actmail1.php,v 1.1 2010/02/24 02:38:41 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/academic/review/review_lib.php');
	require_once(sysDocumentRoot . '/message/collect.php');
	require_once(sysDocumentRoot . '/lang/review.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');

	$sysSession->cur_func = '1100500100';
	$sysSession->restore();
	if (!aclVerifyPermission(1100500100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$act = '';
	$_POST['ticket'] = trim($_POST['ticket']);
	// 同意
	$ticket = md5(sysTicketSeed . 'okdoReviews' . $_COOKIE['idx']);
	if ($_POST['ticket'] == $ticket) {
		$single = false;
		$act = 'ok';
	}

	// 不同意
	$ticket = md5(sysTicketSeed . 'denydoReviews' . $_COOKIE['idx']);
	if ($_POST['ticket'] == $ticket) {
		$single = false;
		$act = 'deny';
	}

	// 單一審核
	$did = intval($_POST['did']);
	$ticket = md5(sysTicketSeed . $did . 'singledoReviews' . $_COOKIE['idx']);
	if ($_POST['ticket'] == $ticket) {
		$single = true;
		$act = trim($_POST['pass']);
	}

	// 發生非預期的動作
	if (empty($act)) {
      	wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], '拒絕存取!');
		die($MSG['access_deny'][$sysSession->lang]);
	}

	// 取出審核規則
	$fid = $single ? array($did) : $_POST['fid'];
	if (count($fid) <= 0) {
	    wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], '沒有需要審核的人員!');
		die($MSG['msg_no_people'][$sysSession->lang]);
	}

	// 要被取代的資料
	$sAry = array(
		'%SERVER_HOST%',
		'%SCHOOL_NAME%',
		'%COURSE_NAME%',
		'%ENROLL_BEGIN%',
		'%ENROLL_END%',
		'%STUDY_BEGIN%',
		'%STUDY_END%',
		'%USER_NAME%',
		'%REAL_NAME%'
	);
	// 要取代的資料
	$rAry = array(
		$_SERVER['HTTP_HOST'],
		$sysSession->school_name,
		'',
		'',
		'',
		'',
		'',
		'',
		''
	);

	$mail_title = trim(strip_scr($_POST['caption']));
	$mail_body  = trim(strip_scr($_POST['note']));
	$body_type  = ($_POST['ctype'] == 'text') ? 'text' : 'html';
	$method     = in_array(trim($_POST['method']), array('mail', 'msg', 'both')) ? trim($_POST['method']) : 'mail';

	// 處理審核資料
	// 建立取代陣列
	// 取代標題與內文
	// 處理上傳夾檔
	// 處理附檔
	$orgdir = MakeUserDir($sysSession->username);
	$orgret = trim(save_upload_file($orgdir, 0, 0));

	// ========== 2.從資料庫中取出必要的資訊(每封信件共用資訊) ==========
	list($school_mail) = dbGetStSr('WM_school', 'school_mail', 'school_host="'. $_SERVER['HTTP_HOST'] .'"', ADODB_FETCH_NUM);
	$school_name	   = $sysSession->school_name;					// 學校名稱
	$school_host	   = $_SERVER['HTTP_HOST'];						// 學校網址

	// 寄件者
	if (empty($school_mail)){
		$school_mail = 	'webmaster@'. $school_host;
	}
	$from = mailEncFrom($school_name ,$school_mail);
	// ===================================================================

	$resAry = array();
	foreach ($fid as $key => $val)
		$fid[$key] = intval($val);
	$fids = implode("','", $fid);
	$RS = dbGetStMr('WM_review_flow', '*', "`idx` in ('{$fids}')", ADODB_FETCH_ASSOC);
	while (!$RS->EOF) {
		// 取得課程
		$course  = getCourse($RS->fields['discren_id']);
		$rAry[2] = $course['0'];
		$rAry[3] = $course['5'];
		$rAry[4] = $course['6'];
		$rAry[5] = $course['7'];
		$rAry[6] = $course['8'];

		// 取得姓名
		$rAry[7] = $RS->fields['username'];
		$rAry[8] = getRealname($RS->fields['username']);

		$title = str_replace($sAry, $rAry, $mail_title);
		$body  = str_replace($sAry, $rAry, $mail_body);

		$now = date('Y-m-d H:i:s', time());

		$dom = loadRule($RS->fields['content']);
		$res = false;
		// 紀錄審核的狀態 (Begin)
			// 設定已決定
		$expr = "//activity[@id='WM_START']";
		$node = selectSingleNode($dom, $expr);
		$val = $node->get_attribute('status');
		if ($val == 'none') {
			$node->set_attribute('status', 'decide');
				// 設定決定的結果
			$expr = "//activity[@id='WM_START']/to/feedback";
			$node = selectSingleNode($dom, $expr);
			$node->set_attribute('param', $act);
				// 設定內文
			$expr = "//activity[@id='WM_START']/to/comment";
			$node = selectSingleNode($dom, $expr);
			$node->set_attribute('type', $body_type);
			$child = $dom->create_text_node($body);
			$node->append_child($child);
				// 設定標題
			$expr = "//activity[@id='WM_START']/to/title";
			$node = selectSingleNode($dom, $expr);
			if (is_null($node)) {
				$expr  = "//activity[@id='WM_START']/to";
				$pnode = selectSingleNode($dom, $expr);
				$node  = $dom->create_element('title');
				$pnode->append_child($node);
			}
			$child = $dom->create_text_node($title);
			$node->append_child($child);
				// 設定讀取時間
			$expr = "//activity[@id='WM_START']/to/receive_time";
			$node = selectSingleNode($dom, $expr);
			$child = $dom->create_text_node($now);
			$node->append_child($child);
				// 設定決定時間
			$expr = "//activity[@id='WM_START']/to/decide_time";
			$node = selectSingleNode($dom, $expr);
			$child = $dom->create_text_node($now);
			$node->append_child($child);
			// 紀錄審核的狀態 (End)

			// 取得審核者
			$expr = "//activity[@id='WM_START']/to";
			$node = selectSingleNode($dom, $expr);
			$param = $node->get_attribute('account');

			// 修改後的 XML 資料
			$xmlDocs = $dom->dump_mem(true);
			// 回存資料
			$idx = $RS->fields['idx'];
			dbSet('WM_review_flow', "`state`='close', `param`='{$param}', `result`='{$act}', `content`='" . addslashes($xmlDocs) . "'", "`idx`={$idx}");
			if ($sysConn->Affected_Rows() > 0) {
				// 加入該門課
				if ($act == 'ok') {
					dbNew('WM_term_major', '`username`, `course_id`, `role`, `add_time`', "'{$RS->fields['username']}', '{$RS->fields['discren_id']}', 32, NOW()");
				}
				$res = true;
				// 寄件備份
				collect('sys_sent_backup', $sysSession->username, $sysSession->username, '', $title, $body, $body_type, '', $orgret, 0, 'read', '');

				// 寄發通知信 (Begin)
					// E-mail
					if (($method == 'mail') || ($method == 'both')) {
						$email = getEmail($RS->fields['username']);
						if (!empty($email)) {
							$mail = buildMail($from, $title, $body, $body_type, '', $orgret, $orgdir, 0);
							$mail->to = $email;
							$mail->send();
						}
					}
					// 訊息中心
					if (($method == 'msg') || ($method == 'both')) {
						$ret = cpAttach($RS->fields['username'], $orgdir, $orgret);
						collect('sys_inbox', $school_name, $RS->fields['username'], '', $title, $body, $body_type, '', $ret, 0, 'read', '');
					}
				// 寄發通知信 (End)
			}
		}
		$resAry[] = array($rAry[7], $rAry[8], $rAry[2], $res);
		$RS->MoveNext();
	}

	$js = <<< BOF
	function gotoList() {
		window.location.replace("review_review.php");
	}
BOF;

	showXHTML_head_B($MSG['title_review_mail_result'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		if ($act == 'ok') $ary[] = array($MSG['tabs_review_ok_result'][$sysSession->lang], "tabs1");
		if ($act == 'deny') $ary[] = array($MSG['tabs_review_deny_result'][$sysSession->lang], "tabs1");
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1);
			$cols = 5;
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="dataTb" class="cssTable"');
				// 說明
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('colspan="' . $cols . '"', $MSG['msg_review_result'][$sysSession->lang]);
				showXHTML_tr_E();
				// 標題
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('align="center" nowrap="NoWrap"', $MSG['th_serial'][$sysSession->lang]);
					showXHTML_td('align="center" nowrap="NoWrap"', divMsg(120, $MSG['th_username'][$sysSession->lang]));
					showXHTML_td('align="center" nowrap="NoWrap"', divMsg(120, $MSG['th_realname'][$sysSession->lang]));
					showXHTML_td('align="center" nowrap="NoWrap"', divMsg(120, $MSG['th_sel_course'][$sysSession->lang]));
					showXHTML_td('align="center" nowrap="NoWrap"', $MSG['th_result'][$sysSession->lang]);
				showXHTML_tr_E();
				// 資料
				$idx = 0;
				$log_msg = '';
				foreach ($resAry as $val) {
					$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td('align="center" nowrap="NoWrap"', ++$idx);
						showXHTML_td('nowrap="NoWrap"', divMsg(120, $val[0]));
						showXHTML_td('nowrap="NoWrap"', divMsg(120, $val[1]));
						showXHTML_td('nowrap="NoWrap"', divMsg(120, $val[2]));
						showXHTML_td_B('nowrap="NoWrap"');
							echo $val[3] ? $MSG['msg_rv_success'][$sysSession->lang] : $MSG['msg_rv_fail'][$sysSession->lang];
						showXHTML_td_E();
					showXHTML_tr_E();
					$log_msg .= $val[0] . ' ' . $val[2] . ($val[3] ? $MSG['msg_rv_success'][$sysSession->lang] : $MSG['msg_rv_fail'][$sysSession->lang]) . ',';
				}

				switch ($sysSession->env) {
					case 'academic'	: $dep_id = $sysSession->school_id;	break;
					case 'direct'	: $dep_id = $sysSession->class_id;	break;
					case 'teach'	: $dep_id = $sysSession->course_id;	break;
					default			: $dep_id = '';
				}
				wmSysLog($sysSession->cur_func, $dep_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], ($act == 'ok' ? $MSG['tabs_review_ok_result'][$sysSession->lang] : $MSG['tabs_review_deny_result'][$sysSession->lang]) . $log_msg);
				// 工具列
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('align="center" colspan="' . $cols . '"');
						showXHTML_input('button', 'btnDeny' , $MSG['btn_return_list'][$sysSession->lang] , '', 'onclick="gotoList()" class="cssBtn"');
					showXHTML_td_E();
				showXHTML_tr_E();

			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();
?>
