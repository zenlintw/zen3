<?php
	/**
	 * 儲存教材設定
	 *
	 * 建立日期：2002/08/26
	 * @author  ShenTing Lin
	 * @version $Id: content_save.php,v 1.1 2010/02/24 02:38:19 saly Exp $
	 **/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/content_manage.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');

	$actType = '';
	$title = '';
	
	if (!preg_match('/^[A-Z0-9-_]+$/i', $_POST['content_sn']))
	{
		showXHTML_script('inline', 'alert("'.$MSG['msg_content_sn_error'][$sysSession->lang].'");location.replace("content_package_manager.php");');
		die();
	}

	// 新增教材
	$ticket = md5($sysSession->ticket . 'Create' . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
	if (trim($_POST['ticket']) == $ticket) {
		$actType = 'Create';
		$title = $MSG['title_add'][$sysSession->lang];
		$gid = intval($_POST['gid']) ? intval($_POST['gid']) : 100000;
	}

	// 修改教材
	$ticket = md5($sysSession->ticket . 'Edit' . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
	if (trim($_POST['ticket']) == $ticket) {
		$actType = 'Edit';
		$title = $MSG['title_edit'][$sysSession->lang];
	}
	
	if (empty($actType)) {
		wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,1, 'manager', $_SERVER['PHP_SELF'], '拒絕存取!');
		die($MSG['access_deny'][$sysSession->lang]);
	}
	
	$sysSession->cur_func = $actType == 'Create' ? '0700500100' : '0700500200';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}
	
	// 設定車票
	//setTicket();

	$lang['Big5']   = Filter_Spec_char(stripslashes(trim($_POST['Big5'])));
	$lang['GB2312'] = Filter_Spec_char(stripslashes(trim($_POST['GB2312'])));
	$lang['en']     = Filter_Spec_char(stripslashes(trim($_POST['en'])));
	$lang['EUC-JP'] = Filter_Spec_char(stripslashes(trim($_POST['EUC-JP'])));
	$lang['user_define'] = Filter_Spec_char(stripslashes(trim($_POST['user_define'])));

	$caption = addslashes(serialize($lang));

	if($_POST['content_type'] == 'digitization') {	// 電子類
		$formstr = '';
		$quota_limit = intval($_POST['quota_limit']);
		$status = preg_replace('/\W+/', '', $_POST['status']); // 去掉非英文數字字元
	}else{	// 非電子類
		//教材形式
		$idx = 'Option_Content_Type'.$_POST['content_form'];
		$formstr = $MSG[$idx][$sysSession->lang];
		// 空間
		$quota_limit = '';
		// 狀態
		$status = '';
	}
	// 建立教材
	if ($actType == 'Create') {
		// 先將資料儲存到資料庫中

		$RS = dbNew('WM_content', 'content_sn, caption, quota_limit, status, kind, content_type,
					content_form, content_note',
			        "'{$_POST['content_sn']}','{$caption}', '{$quota_limit}', '{$status}', 'content', '{$_POST['content_type']}',
			        '{$formstr}', '{$_POST['content_note']}'");
		if ($RS) {
			// 建立教材的目錄
			$InsertID = $sysConn->Insert_ID();
			
			/* 因 mysql5.7 重啟後會將auto_increment 變成1 加入防呆*/
			if($InsertID < 100001){
				$InsertID_auto = $InsertID + 100000;
				dbSet('WM_content',"content_id = '{$InsertID_auto}'","content_id	 = {$InsertID}");		
				$sysConn->Execute('ALTER TABLE WM_content AUTO_INCREMENT ='.($InsertID_auto+1));
				$InsertID = $InsertID_auto;
			}
			/* 因 mysql5.7 重啟後會將auto_increment 變成1 加入防呆*/
			
			$ContentPath ="/base/{$sysSession->school_id}";
			@mkdir (sysDocumentRoot . $ContentPath, 0755);
			$ContentPath ="/base/{$sysSession->school_id}/content";
			@mkdir (sysDocumentRoot . $ContentPath, 0755);
			$ContentPath ="/base/{$sysSession->school_id}/content/{$InsertID}";
			@mkdir (sysDocumentRoot . $ContentPath, 0755);
			// @mkdir (sysDocumentRoot . $ContentPath . '/content', 0755);

			$msg = $MSG['msg_add_success'][$sysSession->lang];
			// 將教材的目錄儲存到資料庫
			$RS = dbSet('WM_content', "path='{$ContentPath}'", "content_id={$InsertID}");
			
			// 教材類別設定
			if ($gid != 100000)
				dbNew('WM_content_group', 'parent,child,permute', "{$gid}, {$InsertID}, 0");
			
			wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,0, 'manager', $_SERVER['PHP_SELF'], $msg . $InsertID);
		} else {
			$msg = $MSG['msg_add_fail'][$sysSession->lang];
			wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,2, 'manager', $_SERVER['PHP_SELF'], $msg);
		}

	}

	// 修改教材
	if ($actType == 'Edit') {
		$InsertID = intval($_POST['content_id']);
		$RS = dbSet('WM_content', "content_sn='{$_POST['content_sn']}', content_type='{$_POST['content_type']}', content_form='{$formstr}', content_note='{$_POST['content_note']}', caption='{$caption}', quota_limit='{$quota_limit}', status='{$status}'", "content_id={$InsertID}");
		if ($RS) {
			$msg = $MSG['msg_edit_success'][$sysSession->lang];
			wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 , 0, 'auto',$_SERVER['PHP_SELF'], $msg . $InsertID);
		} else {
			$msg = $MSG['msg_edit_fail'][$sysSession->lang];
			wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 , 3, 'auto',$_SERVER['PHP_SELF'], $msg . $InsertID);
		}
	}

	// 開始呈現 HTML
	showXHTML_head_B($title);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_head_E('');
	showXHTML_body_B('');
		$ary[] = array($title, 'tabs');
		showXHTML_tabFrame_B($ary, 1, 'fmact', 'ListTable');
			showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');

				showXHTML_tr_B('class="font01 cssTrHead"');
					showXHTML_td('colspan="3"', $msg);
				showXHTML_tr_E('');

				showXHTML_tr_B('class="font01 ' . $col . '"');
					showXHTML_td_B('colspan="3" align="center"');
						if (strpos($_SERVER['PHP_SELF'], '/course/') === false)
							showXHTML_input('button', '', $MSG['btn_reutrn'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'content_package_manager.php\')"');
						else
							showXHTML_input('button', '', $MSG['btn_reutrn'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'content_list.php\')"');
						showXHTML_input('button', '', $MSG['btn_add'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'content_property.php'.($actType == 'Create' ? ('?gid='.$gid) : '').'\')"');
					showXHTML_td_E('');
				showXHTML_tr_E('');

			showXHTML_table_E('');
		showXHTML_tabFrame_E();
	showXHTML_body_E('');
?>
