<?php
	/**
	 * 學校列表
	 *
	 * 建立日期：2002
	 * @author  ShenTing Lin
	 * @version $Id: sch_list.php,v 1.1 2010/02/24 02:38:41 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/sch_manage.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$sysSession->cur_func = '100300300';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}

	$level = getAdminLevel($sysSession->username);

	// 檢查具不具備進階管理者的身份
	if (!isset($isSingle)) {
		if (!($level & $sysRoles['administrator'] || $level & $sysRoles['root'])) {
			$isSingle = 'Single';
		}
	}

	$mutilJS = <<< BOF
	/**
	 * schCreate()
	 *     建立一所全新的學校
	 **/
	function schCreate(idx) {
		var obj = document.getElementById("actForm");
		if (obj != null) {
			obj.action = "sch_priority.php";
			obj.ticket.value = idx;
			obj.submit();
		}
	}
BOF;

	$js = <<< BOF
	/**
	 * schAdd()
	 *     新增一所學校的校門
	 **/
	function schAdd(idx, sid, dm) {
		alert(idx);
	}

	/**
	 * schModify()
	 *     修改一所學校的校門
	 **/
	function schModify(idx, sid, dm) {
		var obj = document.getElementById("actForm");
		if (obj != null) {
			obj.action = "sch_priority.php";
			obj.ticket.value = idx;
			obj.sid.value = sid;
			obj.shost.value = dm;
			obj.submit();
		}
	}

	/**
	 * schDelete()
	 *     刪除一所學校的校門
	 **/
	function schDelete(idx, sid, dm, sname) {
		var obj = document.getElementById("actForm");
		if (window.confirm("{$MSG['del_confirm'][$sysSession->lang]}")) {
			if (obj != null) {
				obj.action = "sch_delete.php";
				obj.ticket.value = idx;
				obj.sid.value = sid;
				obj.shost.value = dm;
				obj.sname.value = sname;
				obj.submit();
			}
		}
	}

	/**
	 * schConfig()
	 * 常數定義
	 **/
	 function schConfig(school_id){
		var obj = document.getElementById("actForm");
		obj.action = '/academic/sys/sysop_config.php';
		obj.sid.value = school_id;
		obj.submit();
	 }
	 
	 function xchg(img)
	 {
		if (img.nextSibling.style.display == 'none')
		{
		    img.src = img.src.replace('-c.gif', '-cc.gif');
		    img.nextSibling.style.display = '';
		}
		else
		{
		    img.src = img.src.replace('-cc.gif', '-c.gif');
		    img.nextSibling.style.display = 'none';
		}
	 }
BOF;


	if (isset($isSingle) && ($isSingle == 'Single')) $mutilJS = '';
	// 設定車票
	setTicket();

	if ($isSingle == 'Single') {
		$RS = dbGetStMr('WM_school', '*', "school_id={$sysSession->school_id}", ADODB_FETCH_ASSOC);
	} else {
		$RS = dbGetStMr('WM_school', '*', 'school_host not like "[delete]%"', ADODB_FETCH_ASSOC);
	}
	if (!$RS) {
		echo $sysConn->ErrorMsg();
		die('');
	}

	showXHTML_head_B($MSG['html_title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('inline', $js . $mutilJS);
	showXHTML_head_E('');
	showXHTML_body_B('');

		$ary = array();
		if ($isSingle == 'Single') {
			$ary[] = array($MSG['school_setting'][$sysSession->lang],  'tabsTag');
		} else {
			$ary[] = array($MSG['multi_school_manage'][$sysSession->lang],  'tabsTag');
		}
		// $colspan = 'colspan="2"';
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1);
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('align="center"', $MSG['school_id'][$sysSession->lang]);
					showXHTML_td('align="center"', $MSG['school_name'][$sysSession->lang]);
					showXHTML_td('align="center"', 'Domain Name');
					showXHTML_td('align="center"', $MSG['school_academic'][$sysSession->lang]);
					$cols = ($isSingle == 'Single') ? '' : ' colspan="3"';
					showXHTML_td('align="center"' . $cols, $MSG['action'][$sysSession->lang]);
				showXHTML_tr_E('');

				if ($RS) {
					while (!$RS->EOF) {
						// 列出該校所有管理者
						$RSS = dbGetStMr('WM_manager AS A,WM_all_account AS B',
										 'B.username, B.first_name, B.last_name',
										 'A.school_id=' . $RS->fields['school_id'] . ' and A.username=B.username and A.username!="root" order by A.username, A.level',
										 ADODB_FETCH_ASSOC);
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('', $RS->fields['school_id']);
							showXHTML_td('', $RS->fields['school_name']);
							showXHTML_td('', $RS->fields['school_host']);
							showXHTML_td_B('');
							    echo '<img src="/theme/default/academic/icon-c.gif" onclick="xchg(this);" style="cursor: pointer"><ol style="display: none">';
								while (!$RSS->EOF) {
									$username = '&nbsp;';
                                    // Bug#1263 真實姓名的顯示不按照個人語系，而按照個人姓名的設定 by Small 2006/12/28
                                    $username = checkRealname($RSS->fields['first_name'],$RSS->fields['last_name']);
									echo '<li>', $username, ' (', $RSS->fields['username'], ')</li>';
									$RSS->MoveNext();
								}
								echo '</ol>';
							showXHTML_td_E('');
							showXHTML_td_B('align="center"');
								$ticket = md5($isSingle . $sysSession->ticket . 'Edit' . $sysSession->username . $RS->fields[school_id] . $RS->fields[school_host]);
								$extra = 'class="cssBtn" onClick="schModify(\'' . $ticket . '\', \'' . $RS->fields[school_id] . '\', \'' . $RS->fields[school_host] . '\')"';
								showXHTML_input('button', 'mdfBtn', $MSG['btn_edit'][$sysSession->lang], '', $extra);
							showXHTML_td_E('');
							// 檢查是不是單一學校維護，或是學校的筆數只有一筆
							if (($isSingle != 'Single') && ($RS->RecordCount() > 1)) {
								showXHTML_td_B('align="center"');
										$ticket = md5($isSingle . $sysSession->ticket . 'Delete' . $sysSession->username . $RS->fields[school_id] . $RS->fields[school_host] . $RS->fields[school_name]);
										$extra = 'class="cssBtn" onClick="schDelete(\'' . $ticket . '\', \'' . $RS->fields[school_id] . '\', \'' . $RS->fields[school_host] . '\', \'' . $RS->fields[school_name] . '\')"';
										showXHTML_input('button', 'delBtn', $MSG['btn_delete'][$sysSession->lang], '', $extra);
								showXHTML_td_E('');
							}
							if ($isSingle != 'Single'){
								// 常數定義
								showXHTML_td_B('');
									showXHTML_input('button', 'configBtn', $MSG['btn_sys_config'][$sysSession->lang], '', 'class="cssBtn" onClick="schConfig(\'' . $RS->fields[school_id] . '\')"');
								showXHTML_td_E('');
							}
						showXHTML_tr_E('');
						$RS->MoveNext();
					}  // End while(!$RS->EOF)
				}

				if ($isSingle != 'Single') {
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
					showXHTML_tr_B($col);
						showXHTML_td_B('colspan="7" align="center"');
							$ticket = md5($sysSession->ticket . 'Create' . $sysSession->username);
							$extra = 'class="cssBtn" onClick="schCreate(\'' . $ticket . '\')"';
							showXHTML_input('button', 'createBtn', $MSG['btn_create_school'][$sysSession->lang], '', $extra);
						showXHTML_td_E('');
					showXHTML_tr_E('');
				}

			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';

		showXHTML_form_B('method="post"', 'actForm');
			showXHTML_input('hidden', 'ticket', '', '', '');
			showXHTML_input('hidden', 'sid', '', '', '');
			showXHTML_input('hidden', 'shost', '', '', '');
			showXHTML_input('hidden', 'sname', '', '', '');
		showXHTML_form_E('');
	showXHTML_body_E('');
?>
