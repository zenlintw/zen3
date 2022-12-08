<?php
	/**
	 * 讀取訊息內容
	 *
	 * 建立日期：2003/05/14
	 * @author  ShenTing Lin
	 * @version $Id: read.php,v 1.1 2010/02/24 02:40:18 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lang/msg_center.php');
	require_once(sysDocumentRoot . '/message/lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
	require_once(sysDocumentRoot . '/mooc/common/common.php');

	// $sysSession->cur_func = '2200100200';
	// $sysSession->restore();
	if (!aclVerifyPermission(2200200200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	/*
	 * 將內文中的 %IMGS[]% 轉成對應的夾檔中的圖片
	 * @param string $content : 內文
	 * @param array  $imgs : 圖檔陣列
	 * @return string : 轉換後的內文
	 */
	function parseContentImgs($content, $imgs)
	{
		$cnt = preg_match_all('/%IMGS\[(\d+)\]%/', $content, $reg);
		if ($cnt > 0)
		{
			for ($i = 0; $i < $cnt; $i++)
			{
				$content = str_replace($reg[0][$i], $imgs[$reg[1][$i]], $content);
			}
		}
		return $content;
	}

	$lang = strtolower($sysSession->lang);

	// 取得目前所在的資料夾 ID
	$folder_id = getFolderId();
	if ($sysSession->cur_func == $msgFuncID['notebook']) {
		$title     = $MSG['tabs_notebook_title'][$sysSession->lang];
		$target    = 'notebook.php';
		$tabs      = $MSG['tabs_notebook_title2'][$sysSession->lang];
		$TrashName = getNameFromID('sys_notebook_trash');
		$isNB      = true;
	} else {
		$title     = $MSG['title'][$sysSession->lang];
		$target    = 'index.php';
		$tabs      = $MSG['tabs2_title'][$sysSession->lang];
		$TrashName = getNameFromID('sys_trash');
		$isNB      = false;
	}
	$msgTrash  = sprintf($MSG['msg_del_alert'][$sysSession->lang], $TrashName);

	// 各項排序依據
	$OB = array(
		'sender'    => '`sender`',        // 寄件者
		'subject'   => '`subject`',     // 主旨
		'send_time' => '`submit_time`', // 傳送日期
		'priority'  => '`priority`',    // 優先順序
	);

	$priority = array(
		'-2' => $MSG['priority_lowest'][$sysSession->lang],
		'-1' => $MSG['priority_low'][$sysSession->lang],
		'0'  => $MSG['priority_normal'][$sysSession->lang],
		'1'  => $MSG['priority_high'][$sysSession->lang],
		'2'  => $MSG['priority_highest'][$sysSession->lang]
	);

	$msg_serial = trim($_POST['serial']);

	// 取得排序的欄位
	$sb     = '';
	$sortby = trim(getSetting('sort_by', ''));
	$sb     = $OB[$sortby];
	if (empty($sb)) $sb = '`submit_time`';

	// 取得排序的順序是遞增或遞減
	$order = trim(getSetting('order', ''));
	$od    = ($order == 'asc') ? 'ASC' : 'DESC';

	// 產生執行的 SQL 指令
	$sqls = " order by {$sb} {$od} ";

	$serial     = dbGetCol('WM_msg_message', 'msg_serial', "`folder_id`='{$folder_id}' AND `receiver`='{$sysSession->username}' {$sqls}");

	// 計算總共分幾頁
	$cnt        = count($serial) - 1;
	$total_page = ceil($cnt / $lines);

	// 翻頁的動作 (Begin)
	$index      = $cnt;
	for ($i = 0; $i <= $index; $i++) {
		if ($serial[$i] == $msg_serial) {
			$index = $i;
			break;
		}
	}

	if (isset($_POST['act'])) {
		$action = strtolower(trim($_POST['act']));
		switch ($action) {
			case 'fp' : $index = 0; break; // 首篇
			case 'lp' : $index = $cnt; break; // 末篇
			case 'pp' : // 上一篇
			case 'np' : // 下一篇
				$index = ($action == 'pp') ? ($index - 1) : ($index + 1);
				if ($index < 0) $index = 0;
				if ($index >= count($serial)) $index = $cnt;
				break;
			default :
				$index = $cnt;
		}
		$msg_serial = $serial[$index];
	}
	// 翻頁的動作 (End)

	$sysSession->msg_serial = $msg_serial;
	$sysSession->restore();

	// 計算目前所在頁面
	$page_no = ceil(($index + 1) / $lines);
	if (($page_no <= 0) || ($page_no > $total_page))
		$page_no = $total_page;
	saveSetting('page_no', $page_no, '');  // 回存設定

	//$sysConn->debug = true;
	// 多檢查是不是本人的訊息，避免不必要的問題
	$RS = dbGetStSr('WM_msg_message', '*', "`msg_serial`={$msg_serial} AND `receiver`='{$sysSession->username}'", ADODB_FETCH_ASSOC);

	if (empty($RS['receive_time']) || empty($RS['status'])) {
		$status  = trim($RS['status']);
		$status .= (empty($status)) ? 'read' : ',read';
		dbSet('WM_msg_message', "`status`='{$status}', `receive_time`=NOW()", "`msg_serial`={$msg_serial} AND `receiver`='{$sysSession->username}'");
	}
	// 寄件者
	if ($RS['sender'] == $sysSession->school_name) {
		list($email) = dbGetStSr('WM_school', 'school_mail', 'school_id="'.$sysSession->school_id.'" and school_host="'.$_SERVER['HTTP_HOST'].'"', ADODB_FETCH_NUM);
		$username    = $sysSession->school_name;
		$homepage    = 'http://' . $_SERVER['HTTP_HOST'];
	}
	else {
		$RSS      = dbGetStSr('WM_user_account', '`first_name`, `last_name`, `email`, `homepage`', "`username`='{$RS['sender']}'", ADODB_FETCH_ASSOC);
		$username = checkRealname($RSS['first_name'], $RSS['last_name']);
		$email    = $RSS['email'];
		$homepage = $RSS['homepage'];
	}

	$from  = (!empty($email)) ? ("<a href=\"mailto:{$email}\" class=\"cssAnchor\" onclick=\"event.cancelBubble=true;\">{$RS['sender']}</a>") : $RS['sender'];
	$from .= '&nbsp;';
	$from .= (!empty($homepage)) ? ("(<a href=\"{$homepage}\" class=\"cssAnchor\" target=\"_blank\" onclick=\"event.cancelBubble=true;\">{$username}</a>)") : "({$username})";

	// 收件者
	$to = "{$sysSession->username} ({$sysSession->realname})";
	if ($folder_id == 'sys_sent_backup') {
		if ($xmlvars = @domxml_open_mem($RS['note'])) {
			$ctx   = xpath_new_context($xmlvars);
			$xpath = xpath_eval($ctx, '//to');
			if (count($xpath->nodeset) > 0) {
				$child = $xpath->nodeset[0]->first_child();
                                if (isset($child) === true) {
                                    $to = $child->node_value();
                                } else {
                                    $to = '';
                                }
			}
		}
	}

	// 內容
	$content = $RS['content'];
	if ($RS['content_type'] == 'text') {
		$patterns = array("/(http:\/\/[^\s]+)/","/([\w\d]+(\.[_\w\d]+)*@[\w\d]+(\.[\w\d]+){1,4})/");
		$replace  = array("<a href=\"\\1\" target=\"_blank\" class=\"cssAnchor\">\\1</a>","<a href=\"mailto:\\1\">\\1</a>");
		$content  = nl2br(preg_replace($patterns, $replace, htmlspecialchars($content, ENT_QUOTES)));
	} else {
		$a = explode(chr(9), trim($RS['attachment']));
		for($i = 0; $i < count($a); $i += 2) {
			$ticket = md5($sysSiteNo . $sysSession->msg_serial . $sysSession->username . 'Attachment' . $sysSession->ticket . $sysSession->school_id . $a[$i+1]);
			$IMGS[] = 'attach.php?f=' . $a[$i+1] . '&t=' . $ticket;
		}
		$content = parseContentImgs($content, $IMGS);
	}
	$content = stripcslashes($content);
	
	$folder = nowPos($folder_id, true);

	$js = <<< EOF
	var folder_id = "{$folder_id}";

	var MSG_SEL_DEL     = "{$MSG['msg_del_sel'][$sysSession->lang]}";
	var MSG_CONFIRM_DEL = "{$MSG['msg_del_confirm'][$sysSession->lang]}";
	var MSG_SEL_MOVE    = "{$MSG['msg_move'][$sysSession->lang]}";
	var MSG_SEL_TARGET  = "{$MSG['msg_target'][$sysSession->lang]}";
	var MSG_SAME_FOLDER = "{$MSG['msg_same_folder'][$sysSession->lang]}";
	var MSG_DEL_ALERT   = "{$msgTrash}";

	/**
	 * return message list
	 * @param
	 * @return
	 **/
	function goList() {
		remove_unload();
		window.location.replace("{$target}");
	}
EOF;

	// 開始呈現 HTML
	ob_start();
	$xajax_save_temp->printJavascript('/lib/xajax/');
	$tmpHtml = ob_get_contents();
	ob_end_clean();
	$smarty->assign('inlineJS', $js);
	$smarty->assign('folder', $folder);
	$smarty->assign('folderPathNow',$folder[0][1]);
	$smarty->assign('msg_serial',   $msg_serial);
	$smarty->assign('first_serial', $serial[0]);
	$smarty->assign('last_serial',  $serial[$cnt]);
	$smarty->assign('MsgData',  $RS);
	$smarty->assign('MsgPriority',  $priority[$RS['priority']]);
	$smarty->assign('MsgFrom',  $from);
	$smarty->assign('MsgTo',  $to);
	$smarty->assign('MsgContent',  $content);
	$smarty->assign('MsgAttachment',  gen_msg_attach_link($RS['attachment']));
	
	// output
	$smarty->display('common/tiny_header.tpl');
	$smarty->display('message/read.tpl');
	$smarty->display('common/tiny_footer.tpl');
