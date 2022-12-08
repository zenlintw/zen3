<?php
	/**
	 * 審核列表
	 *
	 * @since   2004/03/10
	 * @author  ShenTing Lin
	 * @version $Id: review_main.php,v 1.1 2010/02/24 02:38:41 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/academic/review/review_lib.php');
	require_once(sysDocumentRoot . '/lang/review.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lib/username.php');

	if (!aclVerifyPermission(400300700, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if (!isset($rv_kind) || !isset($rvEnv)) {
	    wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], '拒絕存取!');
		die($MSG['access_deny'][$sysSession->lang]);
	}

	/**
	 * 檢查該帳號所有的導師的身份
	 * @param string  $username : 帳號
	 * @param integer $cid      : 課程編號 (預設為 $sysSession->class_id)
	 * @return array : 身份列表
	 **/
	function getDirectRole($username, $cid='') {
		global $sysSession, $sysRoles;

		$ary  = array();
		$caid = (empty($cid)) ? $sysSession->class_id : intval($cid);
		$lst  = array('director', 'assistant');
		foreach ($lst as $val)
		{
			$res = aclCheckRole($username, $sysRoles[$val], $caid);
			if ($res) $ary[] = '#' . $val;
		}
		return $ary;
	}

	/**
	 * 檢查該帳號所有的老師的身份
	 * @param string  $username : 帳號
	 * @param integer $cid      : 課程編號 (預設為 $sysSession->course_id)
	 * @return array : 身份列表
	 **/
	function getTeachRole($username, $cid='') {
		global $sysSession, $sysRoles;

		$ary = array();
		$csid = (empty($cid)) ? $sysSession->course_id : intval($cid);
		$lst  = array('teacher', 'instructor', 'assistant');
		foreach ($lst as $val)
		{
			$res = aclCheckRole($username, $sysRoles[$val], $csid);
			if ($res) $ary[] = '#' . $val;
		}
		return $ary;
	}

	/**
	 * 檢查該帳號的管理者權限
	 * @param string  $username : 帳號
	 * @param integer $role     : 何種身份
	 * @return array : 身份列表
	 **/
	function getAdminRole($username, $sid='') {
		global $sysSession, $sysRoles;

		$ary = array();
		$sid = trim($sid);
		if (empty($sid)) $sid = $sysSession->school_id;
		if (empty($username) || empty($sid)) return false;
		$RS = dbGetStMr('WM_manager', 'DISTINCT `level`', "`username`='{$username}' AND `school_id`='$sid'", ADODB_FETCH_ASSOC);
		if ($RS) {
			while (!$RS->EOF) {
				$key = array_search($RS->fields['level'], $sysRoles);
				$ary[] = '#' . $key;
				$RS->MoveNext();
			}
		}
		return $ary;
	}

	$js = <<< BOF
	var MSG_SELECT_ALL    = "{$MSG['btn_select_all'][$sysSession->lang]}";
	var MSG_SELECT_CANCEL = "{$MSG['btn_cancel_select'][$sysSession->lang]}";
	var MSG_NEED_OK       = "{$MSG['msg_need_sel_ok'][$sysSession->lang]}";
	var MSG_NEED_DENY     = "{$MSG['msg_need_sel_deny'][$sysSession->lang]}";

	function doReview(val) {
		var obj = document.getElementById("actFm");
		if (obj == null) return false;
		obj.did.value = val;
		obj.submit();
	}

	/**
	 * 切換全選或全消的 checkbox
	 **/
	function chgCheckbox() {
		var bol = true;
		var nodes = document.getElementsByTagName("input");
		var obj  = document.getElementById("ck");
		var btn1 = document.getElementById("btnSel1");
		var btn2 = document.getElementById("btnSel2");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
			if (nodes[i].checked == false) bol = false;
		}
		nowSel = bol;
		if (obj  != null) obj.checked = bol;
		if (btn1 != null) btn1.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
		if (btn2 != null) btn2.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
	}

	var nowSel = false;
	function selfunc() {
		var obj  = document.getElementById("ck");
		var btn1 = document.getElementById("btnSel1");
		var btn2 = document.getElementById("btnSel2");
		if (obj != null) {
			nowSel = !nowSel;
			obj.checked = nowSel;
		}
		if (btn1 != null) btn1.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
		if (btn2 != null) btn2.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;

		select_func('', obj.checked);
	}

	/**
	 * 取得所有點選的規則
	 * @param
	 * @return
	 **/
	function getSelCk() {
		var obj = null, nodes = null, attr = null;
		var ary = new Array();
		obj = document.getElementById("dataTb");
		if (obj == null) return ary;
		nodes = obj.getElementsByTagName("input");
		if (nodes == null) return ary;
		for (var i = 0; i < nodes.length; i++) {
			if ((nodes[i].type != "checkbox") || (!nodes[i].checked)) continue;
			attr = nodes[i].getAttribute("exclude");
			if ((attr != null) || (attr == "true")) continue;
			ary[ary.length] = nodes[i].value;
		}
		return ary;
	}

	function rvAction(val) {
		var ary = new Array();
		var obj = null;
		var msg = "", oid = "";
		msg = val ? MSG_NEED_OK : MSG_NEED_DENY;
		oid = val ? "okFm" : "denyFm";
		ary = getSelCk();
		if (ary.length <= 0) {
			alert(msg);
			return false;
		}
		obj = document.getElementById(oid);
		if (obj != null) {
			obj.dids.value = ary.toString();
			obj.submit();
		}
	}
BOF;

    // SHOW_PHONE_UI 常數定義於 /mooc/teach/review/review_review.php
    if (defined('SHOW_PHONE_UI') && SHOW_PHONE_UI === 1) {

        $smarty->assign('inlineJS', $js);

        $datas = array();
        while (!$RS->EOF) {
            // 取得課程名稱
            $csid = $RS->fields['discren_id'];
            $course[$csid] = getCourse($csid);
            // 取得姓名
            $userDetail = getUserDetailData($RS->fields['username']);
            $userDetail['course_name'] = $course[$csid][0];
            $userDetail['idx'] = $RS->fields['idx'];
            $userDetail['create_time'] = showDatetime($RS->fields['create_time']);
            $datas[] = $userDetail;
            $RS->MoveNext();
        }
        $smarty->assign('datas', $datas);

        $smarty->display('common/tiny_header.tpl');
        if ($rvEnv == 'teach') {
            $smarty->display('common/course_header.tpl');
        }else if ($rvEnv == 'academic') {
            $smarty->display('common/site_header.tpl');
        }
        $smarty->display('phone/teach/review/review_review.tpl');
        $smarty->display('common/tiny_footer.tpl');

        exit;
    }
	showXHTML_head_B($MSG['tabs_teach_main'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['tabs_teach_main'][$sysSession->lang], 'tabs1');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1); //, form_id, table_id, form_extra, isDragable);
			$cols = 9;
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="dataTb" class="cssTable"');
				// 說明
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('colspan="' . $cols . '"', $MSG['msg_help_main'][$sysSession->lang]);
				showXHTML_tr_E();
				// 工具列
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('colspan="' . $cols . '"');
						showXHTML_table_B('width="730" border="0" cellspacing="0" cellpadding="0"');
							showXHTML_tr_B();
								showXHTML_td_B('align="left"');
									showXHTML_input('button', 'btnSel1', $MSG['btn_select_all'][$sysSession->lang], '', 'id="btnSel1" onclick="selfunc()" class="cssBtn"');
								showXHTML_td_E();
								showXHTML_td_B('align="center"');
									showXHTML_input('button', 'btnAgree', $MSG['btn_agree'][$sysSession->lang], '', 'onclick="rvAction(true)" class="cssBtn"');
									showXHTML_input('button', 'btnDeny' , $MSG['btn_deny'][$sysSession->lang] , '', 'onclick="rvAction(false)" class="cssBtn"');
								showXHTML_td_E();
							showXHTML_tr_E();
						showXHTML_table_E();
					showXHTML_td_E();
				showXHTML_tr_E();
				// 標題
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td_B('width="25" align="center" title="' . $MSG['th_select_title'][$sysSession->lang] . '"');
						showXHTML_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" onclick="selfunc()"');
					showXHTML_td_E();
					showXHTML_td('align="center" nowrap="NoWrap"', $MSG['th_serial'][$sysSession->lang]);
					showXHTML_td('align="center" nowrap="NoWrap"', divMsg(120, $MSG['th_username'][$sysSession->lang], '', true));
					showXHTML_td('align="center" nowrap="NoWrap"', divMsg(120, $MSG['th_realname'][$sysSession->lang], '', true));
					showXHTML_td('align="center" nowrap="NoWrap"', divMsg(120, $MSG['th_sel_course'][$sysSession->lang], '', true));
					showXHTML_td('align="center" nowrap="NoWrap"', divMsg(130 , $MSG['th_n_limit'][$sysSession->lang], '', true));
					showXHTML_td('align="center" nowrap="NoWrap"', divMsg(130 , $MSG['th_a_limit'][$sysSession->lang], '', true));
					showXHTML_td('align="center" nowrap="NoWrap"', divMsg(160, $MSG['th_create_time'][$sysSession->lang], '', true));
					showXHTML_td('align="center" nowrap="NoWrap"', $MSG['th_action'][$sysSession->lang]);
				showXHTML_tr_E();
				// 資料列表
				$user = array();
				$course = array();
				$idx = 0;

				$roles = array();
				$is_manager = false;
				switch ($rvEnv) {
					case 'academic':
						$roles = getAdminRole($sysSession->username, $sysSession->school_id);
						if (count($roles)>0) $is_manager = true;
						break;

					case 'personal':
						$roles[] = trim($sysSession->username);
						break;

					case 'teach':
						$roles = getTeachRole($sysSession->username, $sysSession->course_id);
						break;

					case 'direct':
						$roles = getDirectRole($sysSession->username, $sysSession->class_id);
						break;
					default:
				}

				while (!$RS->EOF) {
					// 取得姓名
					$username = $RS->fields['username'];
					if (!isset($user[$username])) {
						list($fn, $ln) = dbGetStSr('WM_user_account', '`first_name`, `last_name`', "`username`='{$username}'", ADODB_FETCH_NUM);
                        // Bug#1263 真實姓名的顯示不按照個人語系，而按照個人姓名的設定 by Small 2006/12/28
                        $user[$username] = checkRealname($fn,$ln);
					}
					// 取得課程名稱
					$csid = $RS->fields['discren_id'];
					$course[$csid] = getCourse($csid);

					// 檢查身份
					$xmlDocs = loadRule($RS->fields['content']);
					
					$role = getChecker($xmlDocs);
					$res = in_array($role, $roles);
					if ($res || $is_manager) {
					
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td_B('align="center"');
								showXHTML_input('checkbox', 'fid[]', $RS->fields['idx'], '', 'onclick="chgCheckbox(); event.cancelBubble=true;"');
							showXHTML_td_E();
							showXHTML_td('align="center" nowrap="NoWrap"', ++$idx);
							// #47104 Chrome 移除寬度限制以避免文字重疊
                            showXHTML_td('nowrap="NoWrap"', divMsg('', $username));
						    showXHTML_td('nowrap="NoWrap"', divMsg(120, $user[$username],$user[$username]));
							// #47104 Chrome 移除寬度限制以避免文字重疊
                            showXHTML_td('nowrap="NoWrap"', divMsg('', $course[$csid][0]));
							showXHTML_td('align="center" nowrap="NoWrap"', $course[$csid][3] . '/' . $course[$csid][1]);
							showXHTML_td('align="center" nowrap="NoWrap"', $course[$csid][4] . '/' . $course[$csid][2]);
							showXHTML_td_B('nowrap="NoWrap"');
								echo divMsg(160, showDatetime($RS->fields['create_time']));
							showXHTML_td_E();
							showXHTML_td_B('align="center" nowrap="NoWrap"');
								showXHTML_input('button', 'btnAct', $MSG['btn_review'][$sysSession->lang], '', 'onclick="doReview(' . $RS->fields['idx'] . ')" class="cssBtn"');
							showXHTML_td_E();
						showXHTML_tr_E();
					}
					$RS->MoveNext();
				}
				if (empty($idx)) {
						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('align="center" colspan="' . $cols . '"', $MSG['msg_no_data'][$sysSession->lang]);
						showXHTML_tr_E();
				}
				// 工具列
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('colspan="' . $cols . '"');
						showXHTML_table_B('width="730" border="0" cellspacing="0" cellpadding="0"');
							showXHTML_tr_B();
								showXHTML_td_B('align="left"');
									showXHTML_input('button', 'btnSel2', $MSG['btn_select_all'][$sysSession->lang], '', 'id="btnSel2" onclick="selfunc()" class="cssBtn"');
								showXHTML_td_E();
								showXHTML_td_B('align="center"');
									showXHTML_input('button', 'btnAgree', $MSG['btn_agree'][$sysSession->lang], '', 'onclick="rvAction(true)" class="cssBtn"');
									showXHTML_input('button', 'btnDeny' , $MSG['btn_deny'][$sysSession->lang] , '', 'onclick="rvAction(false)" class="cssBtn"');
								showXHTML_td_E();
							showXHTML_tr_E();
						showXHTML_table_E();
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
		showXHTML_form_B('action="review_action.php" method="post" enctype="multipart/form-data" style="display: none;"', 'actFm');
			showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . 'doReviews' . $_COOKIE['idx']), '', '');
			showXHTML_input('hidden', 'did', '', '', '');
		showXHTML_form_E();

		showXHTML_form_B('action="review_actmail.php" method="post" enctype="multipart/form-data" style="display: none;"', 'okFm');
			showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . 'doOKReviews' . $_COOKIE['idx']), '', '');
			showXHTML_input('hidden', 'dids', '', '', '');
		showXHTML_form_E();

		showXHTML_form_B('action="review_actmail.php" method="post" enctype="multipart/form-data" style="display: none;"', 'denyFm');
			showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . 'doDenyReviews' . $_COOKIE['idx']), '', '');
			showXHTML_input('hidden', 'dids', '', '', '');
		showXHTML_form_E();
	showXHTML_body_E();
?>
