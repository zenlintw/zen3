<?php
	/**
	 * 討論版閱讀一般區文章
	 *
	 * @version $Id: read.php,v 1.1 2010/02/24 02:39:01 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');
	require_once(sysDocumentRoot . '/lib/lib_forum.php');
	require_once(sysDocumentRoot . '/lang/forum.php');
	require_once(sysDocumentRoot . '/lang/app_server_push.php');
	require_once(sysDocumentRoot . '/forum/order.inc.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '900200100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 各項排序依據
	$OB = $OrderBy['board'];

	$IsNews = $sysSession->news_board?1:0;

	$rows_page = GetForumPostPerPage();

	// 如果用 alias link，抓取各項參數
	if (ereg('^(51[0-9]),([0-9]{10}),([0-9]+)\.php$', basename($_SERVER['PHP_SELF']), $reg)){
		if ($reg[2] != $sysSession->board_id) {
		   wmSysLog($sysSession->cur_func, $sysSession->class_id , $sysSession->board_id , 1, 'auto', $_SERVER['PHP_SELF'], 'Error Board id: '.$reg[2]);
		   die('Error Board id: '.$reg[2]);
		}
		$sysSession->post_no = intval($reg[3]);
		//echo "<!-- get rows_page:$rows_page -->\r\n";
		$sysSession->page_no = ceil($sysSession->post_no / $rows_page); //sysPostPerPage);
	}

	// 如果板號不對，則停止
	if (!ereg('^[0-9]{10}$', $sysSession->board_id)) {
	   wmSysLog($sysSession->cur_func, $sysSession->class_id , $sysSession->board_id , 1, 'auto', $_SERVER['PHP_SELF'], 'Error Board id: '.$sysSession->board_id);
	   die('Error Board id: '.$sysSession->board_id);
	}


	// index.php 所存搜尋條件
	$where_c = isset($_COOKIE['forum_search'])?stripslashes($_COOKIE['forum_search']):'';
	if($sysSession->news_board && !$sysSession->b_right) {	// 是公共消息類型且無刊登權限
		// 找出公佈期間內之節點
		$NEWS_RS = dbGetCol('WM_bbs_posts as B left join WM_news_posts as N on B.board_id = N.board_id and B.node = N.node',
			                'B.node',
			                'B.board_id=' . $sysSession->board_id .
		                    ' and (N.open_time is NULL or N.open_time="0000-00-00" or N.open_time<=NOW())' .
		                    ' and (N.close_time is NULL or N.close_time="0000-00-00" or N.close_time>NOW())');

		$where   = $where_c . ' and node in("'. implode('","', $NEWS_RS) .'")';
	} else {
		$where = $where_c;
	}

    /* #56148 (B) [MOOCs]  判斷是否為公告版 By Spring */
    $isAnnouncement = dbGetOne('`WM_term_course`', 'count(*)', '`course_id` = ' . $sysSession->course_id . ' AND `bulletin` = ' . $sysSession->board_id);
    /* #56148 (E) */
	// 取得本板張貼數
	$total_post = getTotalPost($where, 'board');
	// 計算總共有幾頁
	//echo "<!-- before total_page rows_page:$rows_page -->\r\n";
	$total_page = ceil($total_post / $rows_page);	//sysPostPerPage);

	// 如果 post_no 超出張貼數，則修正它
	$sysSession->post_no = max(1, min($sysSession->post_no, $total_post));

	// 回存 SESSION
	$sysSession->restore();

	// 取得本 POST 內容
	$RS = dbGetStSr('WM_bbs_posts', '*', "board_id={$sysSession->board_id} {$where} order by {$OB[$sysSession->sortby]} limit ".($sysSession->post_no-1).',1', ADODB_FETCH_ASSOC);
	if(!$RS) {
		header("Location:index.php");
		exit();
	}

	// 是否具刊登權限(含張貼, 修改, 刪除)
	$updt_right = $sysSession->q_right;
	if($sysSession->board_readonly) {
		$post_right = $sysSession->q_right;
	} else {
		$post_right = true;
		$updt_right = $updt_right || ($RS['poster']==$sysSession->username);
	}

	// 增加點閱數
	dbSet('LOW_PRIORITY WM_bbs_posts', 'hit=hit+1', "board_id={$sysSession->board_id} and node='{$RS['node']}' and site={$RS['site']} limit 1");

	// 寫下閱讀紀錄
	dbNew('WM_bbs_readed','type,board_id,node,username,read_time',"'b',{$sysSession->board_id},'{$RS['node']}','{$sysSession->username}',Now()");
	if($sysConn->Affected_Rows() == 0)
		dbSet('LOW_PRIORITY WM_bbs_readed', 'read_time=Now()', "type='b' and board_id={$sysSession->board_id} and node='{$RS['node']}' and username='{$sysSession->username}'");
	$ticket = md5(sysTicketSeed . $sysSession->username . 'read' . $sysSession->ticket . $sysSession->board_id);
	$bTicket = md5(sysTicketSeed . 'Board' . $_COOKIE['idx'] . $sysSession->board_id);
	$sysMailsRule = sysMailsRule;

	// APP 推播訊息
    $appAlert = $sysSession->school_name . $MSG['school_post_news'][$sysSession->lang];
	$appContent = $MSG['app_news_date_time'][$sysSession->lang] . $RS['pt'];
	$appMessageID = $sysSession->board_id . '#' . $RS['node'];
	$js = <<< EOB
	var MSG_SYS_ERROR = 'System Error!';
	var MSG_DELETE    = '{$MSG['msg_delete'][$sysSession->lang]}';
	var cur_post      = $sysSession->post_no;
	var total_post    = $total_post;
	var board_id      = '$sysSession->board_id';
	var node          = '{$RS['node']}';
	var site_id       = '{$RS['site']}';
	var email         = '$sysSession->email';
	var ticket        = '{$ticket}';
	var IsNews        = {$IsNews};
	var MSG_EMAIL     = '{$MSG["write_to_msg"][$sysSession->lang]}';
	var sysMailsRule  = {$sysMailsRule};
	var appSender = '{$sysSession->username}';
	var appSubject    = '{$appAlert}';
	var appContent    = '{$appContent}';
	var appMessageID  = '{$appMessageID}';
	var MSG_APP_PUSH_COMPLETE = "{$MSG['app_push_message_complete'][$sysSession->lang]}";

	/*APP 選擇清單 */
	function appPushUserSelect() {
		var win = new WinAPPPushUserSelect('doAppPush');
		
		win.run();
	}
	function doAppPush(arr) {
		var pushObject = new Object();
        user_ids = arr[0];
                        
		pushObject = {
			data: {
				alert: appSubject,
	            content: appContent,
	            sender: appSender,
	            channel: user_ids.split(','),
				alertType: 'NEWS',
				messageID: appMessageID
			}
        };
        
		$.ajax({
            url: '../xmlapi/push-handler.php',
            type: 'POST',
            dataType: 'json',
            contentType: 'application/json',
            data: JSON.stringify(pushObject)
        });
        alert(MSG_APP_PUSH_COMPLETE);
	}
	
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
		}

		var WB_Window = window.showModalDialog("whiteboard.html", paraObj, "status:no; dialogWidth:635px; dialogHeight:545px; edge:raised; center:yes; unadorned:no; help:no; scroll:no; status:no; resizable:no;");
	}

	window.onload = function () {
		document.getElementById('p2').innerHTML = document.getElementById('p1').innerHTML;
		chkBrowser();
	};

EOB;
	// 開始呈現 HTML
	showXHTML_head_B($MSG['read'][$sysSession->lang]);
	showXHTML_script('include', '/lib/code.js');
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('include', '/lib/dragLayer.js');
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('include', '/lib/popup/popup.js');
	showXHTML_script('include', 'read.js');
	showXHTML_script('include', '/lib/jquery/jquery.min.js', true, null, 'UTF-8');
	showXHTML_script('inline', $js);

	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	//showXHTML_css('include', 'index.css');
	showXHTML_head_E();

	showXHTML_body_B('');
	showXHTML_form_B('action=""', 'toolbar1');
        showXHTML_input('hidden','token',$csrfToken,'','');
		showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" align="center"');
			showXHTML_tr_B();
				showXHTML_td_B();
					$ary = array();
					$ary[] = array($MSG['read'][$sysSession->lang], 'tabs1');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E();
			showXHTML_tr_E();
			showXHTML_tr_B();
				showXHTML_td_B('valign="top" class="cssTrOdd"');
					showXHTML_table_B('border="0" cellspacing="1" cellpadding="3" class="cssTable"');

						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td_B('nowrap="nowrap" colspan="2" width="740" id="p1"');
								showXHTML_input('button', '', $MSG['list'][$sysSession->lang],      '', 'onclick="location.replace(\'index.php\');" class="cssBtn"');
								showXHTML_input('button', '', $MSG['first_rec'][$sysSession->lang], '', 'onclick="go_post(-1);" class="cssBtn"'.($sysSession->post_no==1          ?' disabled="disabled"':''));
								showXHTML_input('button', '', $MSG['prev_rec'][$sysSession->lang] , '', 'onclick="go_post(-2);" class="cssBtn"'.($sysSession->post_no==1          ?' disabled="disabled"':''));
								showXHTML_input('button', '', $MSG['next_rec'][$sysSession->lang] , '', 'onclick="go_post(-3);" class="cssBtn"'.($sysSession->post_no==$total_post?' disabled="disabled"':''));
								showXHTML_input('button', '', $MSG['last_rec'][$sysSession->lang] , '', 'onclick="go_post(-4);" class="cssBtn"'.($sysSession->post_no==$total_post?' disabled="disabled"':'')); echo '&nbsp;&nbsp;';
                                if ($isAnnouncement  < 1) {
                                    showXHTML_input('button', '', $MSG['reply'][$sysSession->lang],     '', $post_right?'onclick="reply();" class="cssBtn"':' disabled="disabled" class="cssBtn"');
                                }
								showXHTML_input('button', '', $MSG['post'][$sysSession->lang],      '', $post_right?('onclick="location.replace(\'write.php?bTicket=' . $bTicket . '\');" class="cssBtn"'):' disabled="disabled" class="cssBtn"');
								showXHTML_input('button', '', $MSG['edit'][$sysSession->lang],      '', $updt_right?('onclick="edit();" class="cssBtn"'):' disabled="disabled" class="cssBtn"');
								//#47295 Chrome [Chorme][全體/討論板/文章內容/刪除] 按下「刪除」後，按鈕就不見了，文章沒有被刪除。
                                showXHTML_input('button', '', $MSG['del'][$sysSession->lang],       '', $updt_right?('onclick="removeArticle(\''.($sysSession->board_id.','.$RS['node'].','.$RS['site']).'\');" class="cssBtn"'):' disabled="disabled" class="cssBtn"');
								showXHTML_input('button', '', $MSG['mail'][$sysSession->lang],      '', 'onclick="mail(this);" class="cssBtn"');
								showXHTML_input('button', '', $MSG['repost'][$sysSession->lang],    '', $sysSession->b_right?'id="btnRepost" onClick="displayRepostDlg(true)" class="cssBtn"':'disabled="disabled" class="cssBtn"');
								//showXHTML_input('button', '', $MSG['nbook'][$sysSession->lang],   '', 'onclick="nbook();" class="cssBtn"');
								showXHTML_input('button', '', $MSG['copy_q'][$sysSession->lang],    '', $sysSession->q_right?'onclick="collect(0,this);" class="cssBtn"':' disabled="disabled" class="cssBtn"');
								showXHTML_input('button', '', $MSG['export'][$sysSession->lang],    '', 'onclick="displayExportOptions(true);" class="cssBtn" id="btnExport"');
								// APP 推播 - Begin
								if (isBoardManager($sysSession->username, $sysSession->board_id) && $sysSession->env === 'academic' && $sysSession->news_board) {
                                    $appPushBoardID = $sysSession->board_id;
                                    $appPushNode = $RS['node'];
                                    showXHTML_input('button','btnAppPush',$MSG['app_push_button'][$sysSession->lang],'','onclick="appPushUserSelect();"');
								}
								// APP 推播 - End
							showXHTML_td_E();
						showXHTML_tr_E();
						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['bname'][$sysSession->lang]);
							showXHTML_td('width="640"', $sysSession->board_name);
						showXHTML_tr_E();
						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['which'][$sysSession->lang]);
							showXHTML_td('width="640"', $sysSession->post_no.' / '.$total_post);
						showXHTML_tr_E();
						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['posters'][$sysSession->lang]);
							showXHTML_td('id="poster" width="640"', "<a href=\"mailto:{$RS['email']}\" class=\"cssAnchor\">{$RS['poster']}</a> ".($RS['homepage']?("(<a href=\"{$RS['homepage']}\" target=\"_blank\" class=\"cssAnchor\">{$RS['realname']} </a> )"):"({$RS['realname']} )"));
						showXHTML_tr_E();
						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['times'][$sysSession->lang]);
							showXHTML_td('id="pt" width="640"', $RS['pt']);
						showXHTML_tr_E();
						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['subjs'][$sysSession->lang]);
							showXHTML_td('id="o_subject" width="640"',$RS['subject']);
						showXHTML_tr_E();

						if($IsNews) {
							$NEWS = dbGetStSr('WM_news_posts','open_time,close_time',"board_id={$sysSession->board_id} and node='{$RS['node']}'", ADODB_FETCH_ASSOC);
// echo "<!-- board_id={$sysSession->board_id} and node='{$RS['node']}' ::: {$NEWS['open_time']}, {$NEWS['close_time']} -->\r\n";
							$ot = $sysConn->UnixTimeStamp($NEWS['open_time']);
							$ct = $sysConn->UnixTimeStamp($NEWS['close_time']);
							$openT = empty($ot)?$MSG['unlimit'][$sysSession->lang]:substr($NEWS['open_time'], 0, 16);
							$closeT= empty($ct)?$MSG['unlimit'][$sysSession->lang]:substr($NEWS['close_time'], 0, 16);

							showXHTML_tr_B('class="cssTrEvn"');
								showXHTML_td('align="right"', $MSG['start_time'][$sysSession->lang]);
								showXHTML_td('id="o_open_time" width="640"',$openT);
							showXHTML_tr_E();
							showXHTML_tr_B('class="cssTrOdd"');
								showXHTML_td('align="right" nowrap="nowrap"', $MSG['end_time'][$sysSession->lang]);
								showXHTML_td('id="o_close_time" width="640"',$closeT);
							showXHTML_tr_E();
						}

						showXHTML_tr_B('class="cssTrEvn"');
							// showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['contents'][$sysSession->lang]);
							showXHTML_td('width="773" colspan="2"','<table><tr><td id="o_content"><br />'.$RS['content'].'<p /></td></tr></table>');
						showXHTML_tr_E();
						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td('align="right"', $MSG['attach'][$sysSession->lang]);
							showXHTML_td('width="640" style="padding-top: 0; padding-bottom: 0"', generate_attach_link(get_attach_file_path('board', $sysSession->board_ownerid) .DIRECTORY_SEPARATOR. $RS['node'], $RS['attach']));
						showXHTML_tr_E();

						$css = 'class="cssTrEvn"';
						// echo "<!-- extras=".getExtras('rank')." -->\r\n";
						if(getExtras('rank')) {
						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['rank1'][$sysSession->lang]);
							showXHTML_td_B('width="640"');
								showXHTML_input('radio', 'rank', array('1'=>'1','2'=>'2','3'=>'3','4'=>'4','5'=>'5','6'=>'6','7'=>'7'), '', '');
								echo '&nbsp;&nbsp;',$MSG['rank2'][$sysSession->lang],'&nbsp;&nbsp;';
								showXHTML_input('button', '', $MSG['rank3'][$sysSession->lang],   '', 'onclick="ranking();" class="cssBtn"');
							showXHTML_td_E();
						showXHTML_tr_E();
						$css = 'class="cssTrOdd"';
						}

						showXHTML_tr_B($css);
							showXHTML_td('nowrap="nowrap" colspan="2" width="740" id="p2"','');
						showXHTML_tr_E();

					showXHTML_table_E();
				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E();

//	showXHTML_table_B('border="1" cellpadding="2" cellspacing="0" width="740" style="border-collapse: collapse"');
	showXHTML_form_E();

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

	$ary = array();
	$ary[] = array($MSG['repost_content'][$sysSession->lang], 'RepostDialog');
	echo '<div align="center">';
	showXHTML_tabFrame_B($ary, 1, 'form_repost', 'RepostDialog', 'style="display: inline"', true);
        showXHTML_input('hidden','token',$csrfToken,'','');
		showXHTML_table_B(' width="100%" border="0" cellspacing="1" cellpadding="3" id="tabs2" class="cssTable"');
			showXHTML_tr_B('class="cssTrHead"');
				showXHTML_td('colspan="2" nowrap="nowrap" id="helpMsg"',$MSG['repost_note'][$sysSession->lang]);
			showXHTML_tr_E('');
			showXHTML_tr_B('class="cssTrEvn"');
			    showXHTML_td('nowrap="nowrap"', $MSG['teach_course'][$sysSession->lang]);
			    showXHTML_td_B('nowrap="nowrap"');
                    //#47293 Chrome [全體/討論板/文章內容/轉貼] 選擇課程後，沒有把課程帶回轉貼視窗。：修正屬性id參數位置
			    	showXHTML_input('text','repost_course','', '', 'size=45 READONLY id="repost_course" '); echo '&nbsp;&nbsp;&nbsp;';
			    	showXHTML_input('button','',$MSG['group_course'][$sysSession->lang],'','name="btnGroup" onClick="chooseGroup()" class="cssBtn"');
			    showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('class="cssTrOdd"');
			    showXHTML_td('nowrap="nowrap"', $MSG['board'][$sysSession->lang]);
			    showXHTML_td_B('nowrap="nowrap"');
			    	$boards = Array();
			    	showXHTML_input('select','repost_board',$boards,'','id="repost_board" exclude="true" class="cssInput"');
			    showXHTML_td_E('');
			showXHTML_tr_E('');

			showXHTML_tr_B('class="cssTrEvn"');
			    showXHTML_td_B('nowrap="nowrap" colspan="2"');
				showXHTML_input('button','btnOK',$MSG['ok'][$sysSession->lang].$func_type,'','onclick="repost()" class="cssBtn"');
				showXHTML_input('button','btnCancel',$MSG['cancel'][$sysSession->lang],'','onclick="displayRepostDlg(false)" class="cssBtn"');
			    showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');

	showXHTML_tabFrame_E();
	echo '</div>';


	showXHTML_form_B('method="post" action="export.php"', 'form_export');
      showXHTML_input('hidden', 'token', $csrfToken, '', '');
	  showXHTML_input('hidden', 'node', $RS['node'], '', '');
	  showXHTML_input('hidden', 'site', $RS['site'], '', '');
	  showXHTML_input('hidden', 'export_type', '', '', '');
	showXHTML_form_E();

	showXHTML_form_B('method="post" action="reply.php?bTicket=' . $ticket . '"', 'post3');
      showXHTML_input('hidden', 'token', $csrfToken, '', '');
	  showXHTML_input('hidden', 'subject', '', '', '');
	  showXHTML_input('hidden', 'content', '', '', '');
	  showXHTML_input('hidden', 'awppathre', '', '', '');
	  if($IsNews) {
	  	showXHTML_input('hidden', 'open_time',  $NEWS['open_time'], '', '');
	  	showXHTML_input('hidden', 'close_time', $NEWS['close_time'], '', '');
	  }
	  showXHTML_input('hidden', 'node', $RS['node'], '', '');
	showXHTML_form_E();

	showXHTML_form_B('method="post" action="edit.php?bTicket=' . $ticket . '"', 'post4');
      showXHTML_input('hidden', 'token', $csrfToken, '', '');
	  showXHTML_input('hidden', 'subject', '', '', '');
	  showXHTML_input('hidden', 'content', '', '', '');
	  if($IsNews) {
	  	showXHTML_input('hidden', 'open_time',  $NEWS['open_time'], '', '');
	  	showXHTML_input('hidden', 'close_time', $NEWS['close_time'], '', '');
	  }
	  showXHTML_input('hidden', 'mnode', $RS['node'],   '', '');
	  showXHTML_input('hidden', 'etime', $RS['pt'],     '', '');
	  showXHTML_input('hidden', 'o_att', $RS['attach'], '', '');
	showXHTML_form_E();
	showXHTML_body_E();
?>
