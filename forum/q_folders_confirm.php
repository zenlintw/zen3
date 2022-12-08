<?php
	/**
	 * 檢查
	 *
	 * 建立日期：2004/07/29
	 * @author  KuoYang Tsao
	 * @version $Id: q_folders_confirm.php,v 1.1 2010/02/24 02:39:00 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lib_forum.php');
	require_once(sysDocumentRoot . '/lang/forum.php');
	require_once(sysDocumentRoot . '/forum/order.inc.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '900300100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	// 各項排序依據
	$OB = $OrderBy['quint'];

	$v1 = intval($_GET['v1']);
	$v2 = intval($_GET['v2']);
	$function = $_GET['function'];

	$func_type = '';
	if($function=='do_delete') {
		$func_type = $MSG['del'][$sysSession->lang];
	} else {
		$func_type = $MSG['move'][$sysSession->lang];
	}
	$title = $func_type . $MSG['confirm'][$sysSession->lang];


	/***
	 *	取得此次指定範圍(v1 ~ v2)的 SQL 語法
	 ***/
	Function GetSQL($v1, $v2) {
		global $sysSession, $OB;

		// 產生 SQL 指令(在 config/db_initialize.php)
		$get_qost_list = 'select node,subject,type,pt,poster,realname,email,homepage,attach,rcount,rank,hit ' .
						 'from WM_bbs_collecting where board_id=%d and path=\'%s\' order by %s limit %d,' .
				 		 sysPostPerPage ;
		$sqls = sprintf($get_qost_list, $sysSession->board_id, $sysSession->q_path, $OB[$sysSession->q_sortby], $cur_page);
		if(isset($_COOKIE['forum_qsearch'])) {	// 先前曾紀錄搜尋條件
			$where = isset($_COOKIE['forum_qsearch'])?stripslashes($_COOKIE['forum_qsearch']):'';
			$sqls = ereg_replace('where .* order', "where board_id={$sysSession->board_id} $where order", $sqls);
		}

		// 範圍為$v1 ~ $v2，把 limit 換掉
		$count = $v2 - $v1 + 1;
		$v1--;	// mySQL 以 0 為第一篇
		$sqls = ereg_replace('limit .*$', "limit $v1,$count", $sqls);
		return $sqls;
	}

	$OK_to_delete   = true;	// 是否可以刪除( 下層子資料夾無內含 )
	$trouble_folder = '';

   	$sql = GetSQL($v1, $v2);
	$sql = ereg_replace('select .* from', "select node,site,subject,path,type from", $sql);
   	$board_id = $sysSession->board_id;

	$RS = $sysConn->Execute($sql);
	$trouble_folders = Array();
	while(!$RS->EOF)
	{
		// $node = $RS->fields['node'];
		// $site = $RS->fields['site'];
		$subject = $RS->fields['subject'];
		$path    = $RS->fields['path'];
		$type    = $RS->fields['type'];

		if($type == 'D') // 先不處理資料夾
		{
			$the_path = ($path == '/' ? '' : $path) . '/' . $subject;
			if(!IsFolderEmpty($board_id, $the_path))
			{
				$trouble_folders[] = $the_path;
			}
		}

		$RS->MoveNext();
	}

	$confirm_str = '';
	if (count($trouble_folders) > 0) {
		$confirm_str .= $func_type . $MSG['msg_folder_empty1'][$sysSession->lang];
		$confirm_str .= '<br><br>';
		$confirm_str .= implode('<br>', $trouble_folders);
		$confirm_str .= '<br><br>';
	}
	$confirm_str .= "{$MSG['sure_del_from'][$sysSession->lang]} {$v1} {$MSG['sure_del_to'][$sysSession->lang]} {$v2} ?<br><br>";


	$js = <<< BOF
	function on_ok() {
		var win =  dialogArguments;
		win.{$function}();
		window.close();
	}
	function on_cancel() {	window.close();	}
BOF;

	showXHTML_head_B($title);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B('');

	$ary = array();
	$ary[] = array($title, '');
	// $colspan = 'colspan="2"';
	showXHTML_tabFrame_B($ary, 1); //, '', table_id, form_extra, isDragable);
		showXHTML_table_B(' width="100%" border="0" cellspacing="1" cellpadding="3" id="tabs2" class="cssTable"');
			showXHTML_tr_B('class="cssTrHead"');
				showXHTML_td('colspan="2" nowrap="nowrap" id="helpMsg"', $MSG['csv_note'][$sysSession->lang]);
			showXHTML_tr_E('');
			showXHTML_tr_B('class="cssTrOdd"');
			    showXHTML_td_B('nowrap="nowrap"');
				echo $confirm_str. '<br><br>';
				showXHTML_input('button','btnOK',$MSG['ok'][$sysSession->lang],'','onclick="on_ok()" class="cssBtn"');
				showXHTML_input('button','btnCancel',$MSG['cancel'][$sysSession->lang],'','onclick="on_cancel()" class="cssBtn"');
			    showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
	showXHTML_tabFrame_E();
	showXHTML_body_E('');
?>
