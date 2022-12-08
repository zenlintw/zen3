<?php
	/**
	 * 課程群組列表
	 *
	 * @since   2004/07/16
	 * @author  ShenTing Lin
	 * @version $Id: enroll_group_list.php,v 1.1 2010/02/24 02:38:57 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/direct/enroll/course_lib.php');
	require_once(sysDocumentRoot . '/direct/enroll/enroll_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '700400300';
	$sysSession->restore();
	if (!aclVerifyPermission(700300400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$objAssoc->restore();
	$wiseguy = trim($objAssoc->getValues('course_other', 'wiseguy'));
	if ($wiseguy == 'back') {
		// 回復原值
		$benc = $objAssoc->getValues('course_other', 'ticket');
		$csid = sysDecode($benc);
		$res = getParents($csid, true);
		$ary = array();
		if (is_array($res)) {
			foreach ($res as $key => $val) {
				if (intval($val) == 10000000) continue;
				$val = sysEncode($val);
				$ary[] = '"' . $val . '"';
			}
		}
		$plst = implode(', ', $ary);
		$plst = 'var plst = [' . $plst . '];';
	} else {
		$benc = sysEncode('10000000');
		$plst = 'var plst = new Array();';
	}
	$objAssoc->store();

	$ticket = md5(sysTicketSeed . $sysSession->username . 'MySchool' . $sysSession->school_id . $_COOKIE['idx']);

	$js = <<< BOF
	var MSG_LOADING   = "{$MSG['msg_loading'][$sysSession->lang]}";
	var MSG_EXPAND    = "{$MSG['tree_expand'][$sysSession->lang]}";
	var MSG_COLLECT   = "{$MSG['tree_collect'][$sysSession->lang]}";
	var MSG_SYS_ERROR = "{$MSG['msg_sys_error'][$sysSession->lang]}";

	var theme = "{$sysSession->theme}";
	var ticket = "{$ticket}";
	var lang = "{$lang}";
	var ids = "{$benc}";
	{$plst}

	// 關閉 Mozilla 的 scrollbar (Begin)
	var obj = window.scrollbars;
	if ((typeof(obj) == "object") && (obj.visible == true)) {
		obj.visible = false;
	}
	// 關閉 Mozilla 的 scrollbar (End)
BOF;
	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('include', './enroll_group_list.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
	showXHTML_body_B('class="cssTbBodyBg" topmargin="0" leftmargin="0" marginwidth="0" marginheight="0"');
		showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" align="right"');
			showXHTML_tr_B('');
				showXHTML_td_B('class="cssTbBtn"');
					echo '<a href="javascript:;" onclick="return winExpand(true)" id="IconExpand" style="display:none"><img src="/theme/' . $sysSession->theme . '/academic/icon_expand.gif" border="0" alt="' . $MSG['msg_expend'][$sysSession->lang] . '" title="' . $MSG['msg_expend'][$sysSession->lang] . '"></a>';
					echo '<a href="javascript:;" onclick="return winExpand(false)" id="IconCollection" style="display:block"><img src="/theme/' . $sysSession->theme . '/academic/icon_collection.gif" border="0" alt="' . $MSG['msg_collect'][$sysSession->lang] . '" title="' . $MSG['msg_collect'][$sysSession->lang] . '"></a>';
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');

		echo '<div id="ToolBar" class="cssToolbar" style="width: 190px; height: 200px; overflow: auto; z-index: 10;">';
		showXHTML_table_B('width="190" border="0" cellspacing="0" cellpadding="0"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0"');
						showXHTML_tr_B('class="cssTrEvn"');
							// 版面問題，所以自己輸出
							echo '<td width="3" valign="top" nowrap><img src="/theme/' . $sysSession->theme . '/academic/cl2.gif" width="3" height="3" border="0"></td>';
							echo '<td align="right" valign="top" nowrap><img src="/theme/' . $sysSession->theme . '/academic/cl3.gif" width="3" height="3" border="0"></td>';
						showXHTML_tr_E('');
						$benc = sysEncode('10000000');
						showXHTML_tr_B('class="cssTrEvn" Mimura="true" MyAttr="' . $benc . '"');
							showXHTML_td_B('colspan="2" nowrap="nowrap" id="allCSTitle"');
								echo '&nbsp;<img src="/theme/' . $sysSession->theme . '/direct/icon_book.gif" width="22" height="12" border="0" align="absmiddle">&nbsp;';
								echo '<a href="javascript:void(null)" class="cssTbHead">' . $sysSession->school_name . '</a>';
								echo '<span id="editDir">&nbsp;</span>';
							showXHTML_td_E('');
						showXHTML_tr_E('');
					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0" class="cssTbTable"');
						showXHTML_tr_B('class="cssTbTr"');
							showXHTML_td_B('colspan="2" class="cssTbTd" nowrap id="CGroup"');
							showXHTML_td_E('');
						showXHTML_tr_E('');
					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
		echo '</div>';
		echo '<div id="ToolBar" class="cssTbBugIE5">&nbsp;</div>';
	showXHTML_body_E();
?>
