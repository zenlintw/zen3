<?php
	/**
	 * 儲存審核流程
	 *
	 * @since   2004/02/26
	 * @author  ShenTing Lin
	 * @version $Id: review_save.php,v 1.1 2010/02/24 02:38:41 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/multi_lang.php');
	require_once(sysDocumentRoot . '/academic/review/review_init.php');
	require_once(sysDocumentRoot . '/lang/review.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '400300700';
	$sysSession->restore();
	if (!aclVerifyPermission(400300700, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	/**
	 * 產生審核流程
	 * @param string $role : 身份
	 * @param string $user : 指定某一人
	 * @return string : 產生的 XML
	 **/
	function genFlowXML($role, $user) {
		$role = trim($role);
		switch ($role) {
			case 'none'  : $to = ''; break;
			case 'other' : $to = trim($user); break;
			default:
				$to = '#' . $role;
		}
		$xmlDocs = <<< BOF
	<flow>
		<activity id="WM_START" type="to" status="none">
			<description></description>
			<to account="{$to}" email="">
				<agent account="" email=""></agent>
				<feedback param="">
					<param value="ok" activity=""></param>
					<param value="deny" activity=""></param>
				</feedback>
				<comment type=""></comment>
				<arrive_time></arrive_time>
				<receive_time></receive_time>
				<decide_time></decide_time>
			</to>
		</activity>
	</flow>
BOF;
		return $xmlDocs;
	}

	$nid = intval($_POST['nid']);

	// 檢查 ticket
	$ticket = md5(sysTicketSeed . 'saveRule' . $_COOKIE['idx'] . $nid);
	if (trim($_POST['ticket']) != $ticket) {
	   wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], $MSG['access_deny'][$sysSession->lang]);
	   die($MSG['access_deny'][$sysSession->lang]);
	}

	$lang['Big5']        = stripslashes(trim($_POST['rvCaption_big5']));
	$lang['GB2312']      = stripslashes(trim($_POST['rvCaption_gb']));
	$lang['en']          = stripslashes(trim($_POST['rvCaption_en']));
	$lang['EUC-JP']      = stripslashes(trim($_POST['rvCaption_jp']));
	$lang['user_define'] = stripslashes(trim($_POST['rvCaption_user']));


	$role   = trim($_POST['rvRole']);
	$other  = trim($_POST['rvRoleOther']);
	$temp   = trim($_POST['rvCSGP']);
	$assign = explode(',', $temp);

	$dd = array(
		'title'  => addslashes(serialize($lang)),   // 標題
		'desc'   => '',                     		// 描述
		'role'   => $role,                  		// 由誰審核
		'other'  => $other,                 		// 指定誰審核
		'assign' => $assign,                		// 指派給哪些課程
	);

	// 產生 XML
	// 儲存到資料庫
	// 更新關聯

	$assign = array_unique($assign);
	$isFail = false;
	$xmlStrs = genFlowXML($role, $other);
	if (empty($nid)) {
		// 新增
		$fields = '`kind`, `start`, `title`, `content`';
		$value  = "'course', '', '{$dd['title']}', '{$xmlStrs}'";
		dbNew('WM_review_syscont', $fields, $value);

		if ($sysConn->Affected_Rows() > 0) {
			$nid = $sysConn->Insert_ID();
			dbSet('WM_review_syscont', "`permute`={$nid}", "`flow_serial`={$nid}");
			$msg = $MSG['msg_add_success'][$sysSession->lang];
		} else {
			$isFail = true;
			$msg = $MSG['msg_add_fail'][$sysSession->lang];
		}
		if (!$isFail)
		   wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'manager', $_SERVER['PHP_SELF'], '新增審核流程:' . $msg);
		else
		   wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 2, 'manager', $_SERVER['PHP_SELF'], '新增審核流程:' . $msg);
	} else {
		// 修改
		$isFail = true;
		$msg = $MSG['msg_add_fail'][$sysSession->lang];
		dbSet('WM_review_syscont', "`title`='{$dd['title']}', `content`='{$xmlStrs}'", "`flow_serial`={$nid}");
		if ($sysConn->Affected_Rows() > 0) {
			$isFail = false;
			$msg = $MSG['msg_update_success'][$sysSession->lang];
		}else if ($sysConn->ErrorNo() == 0){
			$isFail = false;
			$msg = $MSG['msg_update_fail'][$sysSession->lang];
		}

		if (!$isFail)
		   wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'manager', $_SERVER['PHP_SELF'], '修改審核流程:' . $msg);
		else
		   wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 3, 'manager', $_SERVER['PHP_SELF'], '修改審核流程:' . $msg);
	}

	// 取得此規則屬於那些群組與課程
	if (count($assign) <= 1)  $assign[] = 0;
	$csSelCsID = $assign;

	$js = <<< BOF
	var lang = "{$sysSession->lang}";
	var selGpIDs = new Array({$selgpids});

	function okSelGroup(val) {
		layerAction("tbGpCs", false);
	}

	function gotoEdit() {
		var obj = document.getElementById("dataFm");
		if (obj != null) obj.submit();
	}

	function gotoList() {
		window.location.replace("review_list.php");
	}

	window.onload = function () {
		alert("{$msg}");
	};
BOF;

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['tabs_review_save'][$sysSession->lang], 'tabs1');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'dataFm', '', 'action="review_property.php" method="post" enctype="multipart/form-data" style="display: inline;"');
			showXHTML_input('hidden', 'rvCSGP', $_POST['rvCSGP'], '', '');
			showXHTML_input('hidden', 'nid'   , $nid, '', '');
			$ticket = md5(sysTicketSeed . 'saveRule' . $_COOKIE['idx'] . $nid);
			showXHTML_input('hidden', 'ticket', $ticket, '', '');

			showXHTML_table_B('width="760" aling="center" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				// 新增或修改的結果
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('colspan="2"', $msg);
				showXHTML_tr_E();

				// 標題
			    $lang = unserialize(stripslashes($dd['title']));

				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top" class="cssTrHead" width="80"', $MSG['td_title'][$sysSession->lang]);
					showXHTML_td_B('nowrap="nowrap" width="680"');
						$multi_lang = new Multi_lang(true, $lang, $col); // 多語系輸入框
						$multi_lang->show(false);
					showXHTML_td_E();
				showXHTML_tr_E();

				// 由誰審核
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="right" valign="top" class="cssTrHead"', $MSG['td_review'][$sysSession->lang]);
					showXHTML_td_B('');
						showXHTML_input('hidden', 'rvRole', $dd['role'], '', '');
						showXHTML_input('radio', '', $roles, $dd['role'], 'disabled', '<br />');
						if ($dd['role'] == 'other') {
							echo '<span id="spanRole" style="padding: 0px 0px 0px 20px;">';
							showXHTML_input('text', '', $dd['other'], '', 'size="32" maxlength="32" id="rvRoleOther" class="cssInput" disabled');
							showXHTML_input('hidden', 'rvRoleOther', $dd['other'], '', '');
							echo '</span>';
						}
					showXHTML_td_E();
				showXHTML_tr_E();

				// 按鈕
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('align="center" colspan="2"');
						if ($isFail) {
							showXHTML_input('button', 'btnReEdit', $MSG['btn_re_edit'][$sysSession->lang], '', 'class="cssBtn" onclick="gotoEdit()"');
						}
						showXHTML_input('button', 'btnReturn', $MSG['btn_return'][$sysSession->lang], '', 'class="cssBtn" onclick="gotoList()"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E('');

	showXHTML_body_E();
?>
