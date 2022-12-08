<?php
	/**
	 * 訊息中心
	 *
	 * 建立日期：2003/04/21
	 * @author  ShenTing Lin
	 * @version $Id: index.php,v 1.1 2010/02/24 02:40:17 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lstable.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/message/lib.php');	
	require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
	require_once(sysDocumentRoot . '/mooc/common/common.php');
	require_once(sysDocumentRoot . '/lang/msg_center.php');

	if (!aclVerifyPermission(2200100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}
	// 取得目前所在的資料夾 ID
	$reload = "\tvar reload = false;\n";
	if (!in_array($sysSession->cur_func, $msgFuncID)) {
		setMessageID('sys_inbox');
		$folder_id = 'sys_inbox';
		$reload    = "\tvar reload = true;\n";
	} else if ($sysSession->cur_func != $msgFuncID['message']){
		setMessageID('sys_inbox');
		$folder_id = 'sys_inbox';
		$reload    = "\tvar reload = true;\n";
	} else {
		$folder_id = getFolderId();
		if (ckNBFolder($folder_id)) {
			setMessageID('sys_inbox');
			$folder_id = 'sys_inbox';
			$reload    = "\tvar reload = true;\n";
		}
	}

	$TrashName = getNameFromID('sys_trash');

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

	$ticket = md5($sysSession->username . 'Message' . $sysSession->ticket . $sysSession->school_id);
	$lang   = strtolower($sysSession->lang);
	if (isset($_POST['search_text']))
		$_POST['search_text'] = trim(escape_LIKE_query_str(addslashes(strip_tags($_POST['search_text']))));

	$msgTrash  = sprintf($MSG['msg_del_alert'][$sysSession->lang], $TrashName);
	$is_search = (isset($_POST['is_search']))? $_POST['is_search']: 0;
	list($school_mail) = dbGetStSr('WM_school', 'school_mail', 'school_id="'.$sysSession->school_id.'" and school_host="'.$_SERVER['HTTP_HOST'].'"', ADODB_FETCH_NUM);

	$js = <<< EOF
	var theme      = "{$sysSession->theme}";
	var lang       = "{$lang}";
	var total_page = "{$total_page}";
	var folder_id  = "{$folder_id}";
	{$reload}

	var MSG_SELECT_ALL    = "{$MSG['msg_select_all'][$sysSession->lang]}";
	var MSG_SELECT_CANCEL = "{$MSG['msg_select_cancel'][$sysSession->lang]}";

	

	/**
	 * 同步全選或全消的按鈕與 checkbox
	 * @version 1.0
	 **/
	var nowSel = false;
	function selfunc() {
		var obj  = document.getElementById("ck");
		if (obj == null) return false;
		nowSel = !nowSel;
		obj.checked = nowSel;
		select_func('', obj.checked);
	}
	
	/**
	 * 切換全選或全消的 checkbox
	 * @version 1.0
	 **/
	function chgCheckbox() {
		var bol = true;
		var nodes = document.getElementsByTagName("input");
		var btn1 = document.getElementById("ck");
		if ((nodes == null) || (nodes.length <= 0)) return false;
		for (var i = 0, j = 0; i < nodes.length; i++) {
			if (nodes[i].type != "checkbox" || (nodes[i].id == "ck")) continue;
			if (nodes[i].checked == false) bol = false;
			else j++;
		}

		nowSel = bol && (j > 0);
		btn1.checked = nowSel;
		
	}

	function doSearch()
	{
		if (document.mainFm.search_text.value.length > 0)
		{
			document.mainFm.is_search.value = 1;
		}else{
			document.mainFm.is_search.value = 0;
		}
		document.mainFm.action = "{$_SERVER['PHP_SELF']}";
		remove_unload();
		document.mainFm.submit();
	}

	var MSG_SEL_DEL     = "{$MSG['msg_del_sel'][$sysSession->lang]}";
	var MSG_CONFIRM_DEL = "{$MSG['msg_del_confirm'][$sysSession->lang]}";
	var MSG_SEL_MOVE    = "{$MSG['msg_move'][$sysSession->lang]}";
	var MSG_SEL_TARGET  = "{$MSG['msg_target'][$sysSession->lang]}";
	var MSG_SAME_FOLDER = "{$MSG['msg_same_folder'][$sysSession->lang]}";
	var MSG_DEL_ALERT   = "{$msgTrash}";
EOF;
	function divMsg($width=100, $caption='&nbsp;', $title='') {
		if (empty($title)) $title = $caption;
		return $caption;
	}

	function showNum() {
		global $myTable;
		return $myTable->get_index();
	}

	function showAttach($att) {
		global $sysSession;
		return (!empty($att)) ?'Y':'N';
		// echo (!empty($att)) ? '<img src="/theme/' . $sysSession->theme . '/learn/file.gif" width="9" height="14" border="0" align="absmiddle">' : '&nbsp;';
	}

	function showSubject($status, $subject, $val) {
		global $sysSession, $MSG;
		if (empty($subject)) $subject = $MSG['no_subject'][$sysSession->lang];
		$str .= $val.',';
		$str .= (eregi('read', $status)) ? 'read02.gif' : 'read01.gif';
		$str .= ','.$subject;
		return $str;
	}

	function showPriority($val) {
		global $priority;
		return $priority[$val];
	}

	function showDatetime($val) {
		global $sysSession, $sysConn, $MSG;
		$time = $sysConn->UnixTimeStamp($val);
		return date('Y-m-d H:i:s', $time);
	}

	$user_ary = array();
	function showSender($sender) {
		global $user_ary, $lang, $sysSession, $school_mail, $_SERVER;
		$isSysAcnt = $sender == $sysSession->school_name ? true : false;
		if (!array_key_exists($sender, $user_ary)) {
			if (!$isSysAcnt) {
				$RSS = dbGetStSr('WM_user_account', '`first_name`, `last_name`, `email`, `homepage`', "`username`='{$sender}'", ADODB_FETCH_ASSOC);
				$username = checkRealname($RSS['first_name'], $RSS['last_name']);
				$user_ary[$sender] = array($username, $RSS['email'], $RSS['homepage']);
			}
			else {
				$username = $sender;
				$user_ary[$sender] = array($sender, $school_mail, 'http://' . $_SERVER['HTTP_HOST']);
			}
		} else {
			$username = $user_ary[$sender][0];
		}
		$username = htmlspecialchars($username, ENT_QUOTES);
		$email = $user_ary[$sender][1];
		$homepage = $user_ary[$sender][2];
		$from  = (!empty($email)) ? ("<a href=\"mailto:{$email}\" class=\"cssAnchor\" onclick=\"event.cancelBubble=true;\">{$username}</a>"): $sender;
		$from .= '&nbsp;';
		return divMsg(120, $from, "");
	}

	function showReciver($sender, $to, $note) {
		global $sysSession, $forder_id;
		$to = "{$sysSession->username} ({$sysSession->realname})";
		if ( (($folder_id == 'sys_sent_backup') && ($sender == $sysSession->username)) || (($to == $sysSession->username) && ($sender == $sysSession->username)) ) {
		//if (($folder_id == 'sys_sent_backup') && (trim($to) == trim($sysSession->username))) {
			if ($xmlvars = @domxml_open_mem($note)) {
				$ctx = xpath_new_context($xmlvars);
				$xpath = xpath_eval($ctx, '//to');
				if (count($xpath->nodeset) > 0) {
					if ($xpath->nodeset[0]->has_child_nodes()) {
						$child = $xpath->nodeset[0]->first_child();
						$to = $child->node_value();
					}
				}
			}
		}
		return divMsg(120, $to);
		// return $to;
	}
	

			$myTable = new table();
			$folder = nowPos($folder_id, true);
			$myTable->add_help($MSG['position'][$sysSession->lang] . implode('&nbsp;>&nbsp;', $folder));
			if (is_array($folder) && (count($folder)==1)) {
			    $smarty->assign('folderPathNow',$folder[0][1]);
			    $smarty->assign('folderPath', '');
			}else{
			    $nowFolder = array_pop($folder);
			    $smarty->assign('folderPathNow', $nowFolder[1]);
			    $smarty->assign('folderPath', $folder);
			}
			
                        // 如果是從其他功能過來，則從第一頁開始顯示，如果是本頁面再次點選，則從記錄檔取出剛剛在第幾頁，方便USER
                        if (trim($reload) === 'var reload = true;') {
                            $page_no = 1;
                        } else {
                            $pgno = isset($_POST['page'])?$_POST['page']:getSetting('page_no', '');
                            $page_no = (!empty($pgno)) ? intval($pgno) : 1;
                        }
			
                        // 每頁幾筆
			$perpg = isset($_POST['per_page'])?$_POST['per_page']:getSetting('per_page', '');
			$per_page = (!empty($perpg)) ? intval($perpg) : 10;
                        
			$myTable->set_page(true, $page_no, $per_page, 'remove_unload()');

			// 資料
			$myTable->add_sort('send_time', '`submit_time` DESC', '`submit_time` ASC');
			$myTable->add_sort('subject'  , '`subject` ASC'    , '`subject` DESC');
			$myTable->add_sort('priority' , '`priority` ASC'   , '`priority` DESC');
			$sortby = trim(getSetting('sort_by', ''));
			$order  = trim(getSetting('order', ''));
			$myTable->set_sort(true, $sortby, $order, 'remove_unload()');
			$myTable->add_field($MSG['number'][$sysSession->lang]    , ''                                       , ''         , ''        , 'showNum'      , 'align="center" nowrap="noWrap"');
			$myTable->add_field($MSG['subject'][$sysSession->lang]   , $MSG['subject_msg'][$sysSession->lang]   , 'subject'  , '%status %subject %msg_serial', 'showSubject'  , 'nowrap="noWrap" onclick="read(\'%msg_serial\');"');
			$myTable->add_field($MSG['priority'][$sysSession->lang]  , $MSG['priority_msg'][$sysSession->lang]  , 'priority' , '%priority'    , 'showPriority' , 'align="center" nowrap="noWrap"');
			$myTable->add_field($MSG['sender'][$sysSession->lang], $MSG['sender_msg'][$sysSession->lang]    , 'sender'   , '%sender'      , 'showSender'   , 'nowrap="noWrap"');
			$myTable->add_field($MSG['send_time'][$sysSession->lang] , $MSG['send_time_msg'][$sysSession->lang] , 'send_time', '%submit_time' , 'showDatetime' , 'nowrap="noWrap"');
			$myTable->add_field($MSG['attachment'][$sysSession->lang], '', '', '%attachment      '      , 'showAttach'   , 'align="center" nowrap="noWrap"');
			$tab    = 'WM_msg_message';
			$fields = '`msg_serial`, `sender`, `receiver`, `submit_time`, `status`, `priority`, `subject`, `attachment`, `note`';
			$where  = "`folder_id`='{$folder_id}' AND `receiver`='{$sysSession->username}'";
			$myTable->set_sqls($tab, $fields, $where);
			$datalist = $myTable->getDatalistView();
			$page_no = $myTable->get_page();
			$sortby  = $myTable->get_sort();
			saveSetting('page_no', $page_no, '');  // 回存設定
			saveSetting('sort_by', $sortby[0], '');  // 回存設定
			saveSetting('order', $sortby[1], '');  // 回存設定
			
// 		showXHTML_tabFrame_E();
// 		echo '</div>';

	for ($i = 0, $size=count($datalist); $i < $size; $i++) {
	    $datalist[$i][1] = explode(',',$datalist[$i][1],3);
	    $datalist[$i][4] = substr($datalist[$i][4],0,16);
	}
	
	// output
	$smarty->assign('total_count', $myTable->display['total_count']);
	$smarty->assign('inlineJS', $js);
	$smarty->assign('datalist', $datalist);
	$smarty->assign('page_no', $page_no);
	$smarty->assign('per_page', $per_page);
	$smarty->display('common/tiny_header.tpl');
	$smarty->display('message/index.tpl');
	$smarty->display('common/tiny_footer.tpl');
	exit;
	
?>