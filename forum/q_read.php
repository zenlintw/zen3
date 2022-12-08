<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');
	require_once(sysDocumentRoot . '/lib/lib_forum.php');
	require_once(sysDocumentRoot . '/lib/lib_newsfaq.php');
	require_once(sysDocumentRoot . '/lang/forum.php');
	require_once(sysDocumentRoot . '/forum/order.inc.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '900300900';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 各項排序依據
	$OB = $OrderBy['quint'];

	// 抓取每頁幾筆
	$rows_page = GetForumPostPerPage();

	// 如果用 alias link，抓取各項參數
	if (ereg('^(57[0-9]),([0-9]{10}),([0-9]+)\.php$', basename($_SERVER['PHP_SELF']), $reg)){
		if ($reg[2] != $sysSession->board_id) die('Error Board id: '.$reg[2]);
		$sysSession->q_post_no = intval($reg[3]);
		$sysSession->q_page_no = ceil($sysSession->q_post_no / $rows_page); //sysPostPerPage);
	}

	// 如果板號不對，則停止
	if (!ereg('^[0-9]{10}$', $sysSession->board_id)) {
	   wmSysLog($sysSession->cur_func, $sysSession->class_id , $sysSession->board_id , 1, 'auto', $_SERVER['PHP_SELF'], 'Error Board id');
	   die('Error Board id: '.$sysSession->board_id);
	}

	// 是否具刊登權限(含修改, 刪除)
	$post_right = $sysSession->q_right; //ChkRight($sysSession->board_id);

	// q_index.php 所存搜尋條件
	$where = "board_id=$sysSession->board_id and path='$sysSession->q_path'";
	$is_search = false;
	if(isset($_COOKIE['forum_qsearch'])) {

		// #47300 Chrome 精華區輸入關鍵字搜尋，檢視文章會發生致命錯誤，因為組成的搜尋條件有錯誤：反解網址編碼
        $_COOKIE['forum_qsearch'] = urldecode($_COOKIE['forum_qsearch']);
        $where = "board_id=$sysSession->board_id " . stripslashes($_COOKIE['forum_qsearch']);
		$is_search = true;
	}

	// 取得本板張貼數
	$RS = dbGetStMr('WM_bbs_collecting', 'count(*),type', "$where group by type", ADODB_FETCH_NUM);

	$total_dir = 0; $total_file = 0;
	if($RS)
	{
		while(!$RS->EOF){
			switch($RS->fields[1]){
				case 'D': $total_dir = $RS->fields[0]; break;
				case 'F': $total_file = $RS->fields[0]; break;
			}
			$RS->MoveNext();
		}
	}
	$total_post = $total_dir + $total_file;

	// 計算總共有幾頁
	$total_page = ceil($total_post / $rows_page);	//sysPostPerPage);

	// 如果 post_no 超出張貼數，則修正它
	if     ($sysSession->q_post_no > $total_post)    $sysSession->q_post_no = $total_post;
	elseif ($sysSession->q_post_no < ($total_dir+1)) $sysSession->q_post_no = ($total_dir+1);

	// 回存 SESSION
	$sysSession->restore();

	// 取得本 POST 內容
	$RS = dbGetStSr('WM_bbs_collecting', '*', "$where order by {$OB[$sysSession->q_sortby]} limit ".($sysSession->q_post_no-1).',1', ADODB_FETCH_ASSOC);
	if(!$RS || $RS['type']=='D') {	// 非檔案, 導至 q_index.php
		header("Location: q_index.php");
		exit();
	}

	// 增加點閱數
	dbSet('WM_bbs_collecting', 'hit=hit+1', "board_id=$sysSession->board_id and site={$RS['site']} and node='{$RS['node']}'");
	// 更新學習中心常見問題列表
	if(IsNewsBoard('faq'))
			createFAQXML($sysSession->school_id, 'faq');

	// 寫下閱讀紀錄
	dbNew('WM_bbs_readed','type,board_id,node,username,read_time',"'q',{$sysSession->board_id},'{$RS['node']}','{$sysSession->username}',Now()");
	if($sysConn->Affected_Rows() == 0)
		dbSet('WM_bbs_readed', 'read_time=Now()', "type='q',board_id={$sysSession->board_id} and node='{$RS['node']}' and username='{$sysSession->username}'");

	$ticket = md5(sysTicketSeed . 'Borad' . $_COOKIE['idx'] . $sysSession->board_id);
	$bTicket = md5($sysSession->username . 'quint' . $sysSession->ticket . $sysSession->school_id);
	$js = <<< EOB
	var ticket = '{$ticket}';
	var MSG_SYS_ERROR = 'System Error!';
	var MSG_DELETE    = '{$MSG['msg_delete'][$sysSession->lang]}';

	var cur_post      = $sysSession->q_post_no;
	var total_post    = $total_post;
	var total_dir     = $total_dir;
	var board_id      = '$sysSession->board_id';
	var node          = '{$RS['node']}';
	var site_id       = '{$RS['site']}';
	var path          = '{$RS['path']}';
	var email         = '$sysSession->email';

	function loadwb(urlval)
	{
		var paraObj = new Object();
		paraObj.WM_BoardID     = "{$sysSession->board_id}";
		paraObj.WM_CourseID    = "{$sysSession->course_id}";
		paraObj.openerdocument = document;
		paraObj.preloaddata    = "";
		paraObj.blocked        = 1;
		if ( urlval != "" )
		{
				paraObj.preloaddata = 'http://' + document.location.host +"/lib/anicamWB/readWBFile.php?filepath="+escape(urlval);
				// paraObj.preloaddata = 'http://wm3.learn.com.tw/user/j/e/jeff/96610217542ac141498e492ec65a1c0e.awp';
		}

		var WB_Window = window.showModalDialog("whiteboard.html", paraObj, "status:no; dialogWidth:635px; dialogHeight:545px; edge:raised; center:yes; unadorned:no; help:no; scroll:no; status:no; resizable:no;");
	}


EOB;

	// 開始呈現 HTML
	showXHTML_head_B($MSG['read'][$sysSession->lang]);
	showXHTML_script('include', '/lib/code.js');
	showXHTML_script('include', '/lib/dragLayer.js');
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('include', 'q_read.js');
	showXHTML_script('inline', $js);

	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E('');

	showXHTML_body_B('onload="document.getElementById(\'p2\').innerHTML = document.getElementById(\'p1\').innerHTML;"');
	showXHTML_form_B('action=""', 'toolbar1');

		showXHTML_table_B('width="740" border="0" cellspacing="0" cellpadding="0" align="center"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary = array();
					$ary[] = array($MSG['read'][$sysSession->lang], 'tabs1');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" class="cssTrEvn"');
					showXHTML_table_B('width="740" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td_B('nowrap="nowrap" colspan="2" width="740" id="p1"');
								showXHTML_input('button', '', $MSG['list'][$sysSession->lang], '', 'onclick="location.replace(\'q_index.php\');" class="cssBtn"'); echo '&nbsp;';
								showXHTML_input('button', '', $MSG['first_rec'][$sysSession->lang], '', 'onclick="go_post(-1);" class="cssBtn"'.($sysSession->q_post_no==($total_dir+1)?' disabled="disabled"':'')); echo '&nbsp;';
								showXHTML_input('button', '', $MSG['prev_rec'][$sysSession->lang] , '', 'onclick="go_post(-2);" class="cssBtn"'.($sysSession->q_post_no==($total_dir+1)?' disabled="disabled"':'')); echo '&nbsp;';
								showXHTML_input('button', '', $MSG['next_rec'][$sysSession->lang] , '', 'onclick="go_post(-3);" class="cssBtn"'.($sysSession->q_post_no==$total_post   ?' disabled="disabled"':'')); echo '&nbsp;';
								showXHTML_input('button', '', $MSG['last_rec'][$sysSession->lang] , '', 'onclick="go_post(-4);" class="cssBtn"'.($sysSession->q_post_no==$total_post   ?' disabled="disabled"':'')); echo '&nbsp;';
								showXHTML_input('button', '', $MSG['post'][$sysSession->lang],   '', $post_right?('onclick="location.replace(\'q_write.php?bTicket=' . $bTicket . '\');" class="cssBtn"'):(' disabled="disabled" class="cssBtn"'));
								showXHTML_input('button', '', $MSG['edit'][$sysSession->lang],   '', $post_right?('onclick="edit();" class="cssBtn"'):' disabled="disabled" class="cssBtn"'); echo '&nbsp;';
								showXHTML_input('button', '', $MSG['move'][$sysSession->lang],   '', $post_right?('onclick="move(); return false;" class="cssBtn"'):' disabled="disabled" class="cssBtn"');
								showXHTML_input('button', '', $MSG['del'][$sysSession->lang],    '', $post_right?('onclick="removeArticle(\''.($sysSession->board_id.','.$RS['node'].','.$RS['site']).'\');" class="cssBtn"'):' disabled="disabled" class="cssBtn"'); echo '&nbsp;';

								showXHTML_input('button', '', $MSG['mail'][$sysSession->lang],   '', 'onclick="mail(this);" class="cssBtn"'); echo '&nbsp;';
								showXHTML_input('button', '', $MSG['nbook'][$sysSession->lang],  '', ($sysSession->username == 'guest' ? 'disabled' : 'onclick="nbook();"') . ' class="cssBtn"'); echo '&nbsp;';
								showXHTML_input('button', '', $MSG['export'][$sysSession->lang],    '', 'onclick="displayExportOptions(true);" class="cssBtn" id="btnExport"');

							showXHTML_td_E('');
						showXHTML_tr_E('');
						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('align="right" nowrap="nowrap" width="100"', $MSG['bname'][$sysSession->lang]);
							showXHTML_td('width="640"', $sysSession->board_name . ' - ' . $MSG['quint'][$sysSession->lang]);
						showXHTML_tr_E('');
						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td('align="right" nowrap="nowrap" width="100"', $MSG['path'][$sysSession->lang]);
							showXHTML_td('width="640"', $RS['path']);
						showXHTML_tr_E('');

						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('align="right" nowrap="nowrap" width="100"', $MSG['which'][$sysSession->lang]);
							showXHTML_td('width="640"', $sysSession->q_post_no.' / '.$total_post);
						showXHTML_tr_E('');
						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td('align="right" nowrap="nowrap" width="100"', $MSG['posters'][$sysSession->lang]);
							showXHTML_td('id="poster" width="640"', "<a href=\"mailto:{$RS['email']}\" class=\"cssAnchor\">{$RS['poster']}</a> ".($RS['homepage']?("(<a href=\"{$RS['homepage']}\" target=\"_blank\" class=\"cssAnchor\">{$RS['realname']} </a> )"):"({$RS['realname']} )"));
						showXHTML_tr_E('');
						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('align="right" nowrap="nowrap" width="100"', $MSG['times'][$sysSession->lang]);
							showXHTML_td('id="pt" width="640"', $RS['pt']);
						showXHTML_tr_E('');
						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td('align="right" nowrap="nowrap" width="100"', $MSG['subjs'][$sysSession->lang]);
							showXHTML_td('id="o_subject" width="640"',$RS['subject']);
						showXHTML_tr_E('');
						showXHTML_tr_B('class="cssTrEvn"');
							// showXHTML_td('align="right" nowrap="nowrap" width="100"', $MSG['contents'][$sysSession->lang]);
							showXHTML_td('width="773" colspan="2"','<table><tr><td id="o_content"><br />'.$RS['content'].'<p /></td></tr></table>');
						showXHTML_tr_E('');
						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td('align="right" nowrap="nowrap" width="100"', $MSG['attach'][$sysSession->lang]);
							showXHTML_td('width="640" style="padding-top: 0; padding-bottom: 0"', generate_attach_link(get_attach_file_path('quint', $sysSession->board_ownerid).DIRECTORY_SEPARATOR.$RS['node'], $RS['attach'], 'quint'));
						showXHTML_tr_E('');
						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('nowrap="nowrap" colspan="2" width="740" id="p2"','');
						showXHTML_tr_E('');
					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
	showXHTML_form_E('');

	$ary = array();
	$ary[] = array($MSG['export'][$sysSession->lang], 'export_options');
	echo '<div align="center">';
	showXHTML_tabFrame_B($ary, 1, 'form_export_options', 'export_options', 'style="display: inline"', true);

		showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="tabs2" class="cssTable"');
			showXHTML_tr_B('class="cssTrOdd"');
			    showXHTML_td_B('nowrap="nowrap"');
				echo $MSG['export_note'][$sysSession->lang] . "<br />";
				showXHTML_input('button','btnExp',$MSG['export'][$sysSession->lang],'','onclick="OnExportOptions(true);" class="cssBtn"');
				showXHTML_input('button','btnExp',$MSG['cancel'][$sysSession->lang],'','onclick="OnExportOptions(false);" class="cssBtn"');
			    showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');

	showXHTML_tabFrame_E();
	echo '</div>';

	showXHTML_form_B('method="post" action="q_export.php"', 'form_export');
	  showXHTML_input('hidden', 'node'       , $RS['node'], '', '');
	  showXHTML_input('hidden', 'site'       , $RS['site'], '', '');
	  showXHTML_input('hidden', 'export_type', ''         , '', '');
	showXHTML_form_E();

	showXHTML_form_B('method="post" action="q_reply.php?bTicket=' . $ticket . '"', 'post3');
		showXHTML_input('hidden', 'subject', ''         , '', '');
		showXHTML_input('hidden', 'content', ''         , '', '');
		showXHTML_input('hidden', 'node'   , $RS['node'], '', '');
	showXHTML_form_E('');

	showXHTML_form_B('method="post" action="q_edit.php?bTicket=' . $ticket . '"', 'post4');
		showXHTML_input('hidden', 'subject', ''           , '', '');
		showXHTML_input('hidden', 'content', ''           , '', '');
		showXHTML_input('hidden', 'mnode'  , $RS['node']  , '', '');
		showXHTML_input('hidden', 'etime'  , $RS['pt']    , '', '');
		showXHTML_input('hidden', 'o_att'  , $RS['attach'], '', '');
	showXHTML_form_E('');
	showXHTML_body_E('');
?>
