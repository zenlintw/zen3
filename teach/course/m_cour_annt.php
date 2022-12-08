<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');
	require_once(sysDocumentRoot . '/lib/lib_forum.php');
    require_once(sysDocumentRoot . '/forum/lib_rightcheck.php');
	require_once(sysDocumentRoot . '/lang/forum.php');
	require_once(sysDocumentRoot . '/forum/order.inc.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '900200100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}
	
	/**
	 * GetBoardSubject()
	 *    取得議題主旨( 複製自 /teach/course/cour_lib.php )
	 *    @pram $board_id : 討論版編號
	 *    @return $subject
	 **/
	function GetBoardSubject($board_id) {
		list($subject) = dbGetStSr('WM_bbs_boards','title',"board_id={$board_id}", ADODB_FETCH_NUM);
		return str_replace("\n","<br />", $subject);
	}
    
    /* 取得公告版編寫權限，移植gotoboard功能(B) */
    $bid = dbGetOne('WM_term_course', '`bulletin`', "`course_id`='{$sysSession->course_id}'", ADODB_FETCH_ASSOC);
    $bTicket = md5(sysTicketSeed . 'Board' . $_COOKIE['idx'] . $bid);
    $enbid = base64_encode($bid);
    $bname = '';
    $board_readonly = false;
    $sysSession->sortby = '';
    
    $lang = array();
    $RS = dbGetStSr('WM_bbs_boards', 'bname,default_order, open_time, close_time, share_time', "board_id={$bid}", ADODB_FETCH_ASSOC);
    // 這隻程式為老師使用不需判斷開放時間 
    /* 
    $nt = time();
    $ot = $sysConn->UnixTimeStamp($RS['open_time']);
    $ct = $sysConn->UnixTimeStamp($RS['close_time']);
    $st = $sysConn->UnixTimeStamp($RS['share_time']);
    $status = getBoardStatus($nt, $ot, $ct, $st);
    switch($status) {
        case 'close':
        case 'notopen':
            wmSysLog($sysSession->cur_func, $sysSession->course_id , $bid , 2, 'auto', $_SERVER['PHP_SELF'], 'board_'.$status);
            die('board_'.$status);					
        break;
        case 'share':
                $board_readonly = true;
        break;
    }
     * 
     */
    $lang = unserialize($RS['bname']);
    $sysSession->sortby = $RS['default_order'];
    $bname = addslashes($lang[$sysSession->lang]);
    
    $sysSession->board_id    = $bid;
    $sysSession->page_no     = '';
    $sysSession->page_no     = '';
    $sysSession->post_no     = '';
    $sysSession->q_sortby    = $sysSession->sortby;
    $sysSession->q_page_no   = '';
    $sysSession->q_post_no   = '';
    $sysSession->news_board  = 0;
    $sysSession->board_qonly = 0;
    if(getBoardOwner($bid)){
        $sysSession->board_ownerid  =$Board_OwnerID;
        $sysSession->board_ownername=$Board_Owner;
    }
    // 課程公告板僅限老師或助教
    if(!$board_readonly)	// 雖非 readonly , 仍需判斷是否為課程公告版
        $sysSession->board_readonly = IsCourseBBS($Board_OwnerID, $bid)?1:0;
    else
        $sysSession->board_readonly = 1;

    // 是否具刊登權限(含修改, 刪除)
    $sysSession->q_right = ChkRight($bid);
    if( !$sysSession->q_right) {	// 無權限
            $RS1 = dbGetStSr('WM_term_subject', 'state, visibility', "board_id={$bid}", ADODB_FETCH_ASSOC);
            if($RS1) {
                if ($RS1['visibility']=='hidden')	{ // 課程板為隱藏 
                    wmSysLog($sysSession->cur_func, $sysSession->course_id , $bid , 3, 'auto', $_SERVER['PHP_SELF'], '課程版為隱藏');
                    die('board_close');
                }
                else if ($RS1['state']=='disable')	{ // 課程版為停用
                    wmSysLog($sysSession->cur_func, $sysSession->course_id , $bid , 4, 'auto', $_SERVER['PHP_SELF'], '課程版為停用');
                    die('board_disable');
                }
                else if ($RS1['state']=='taonly')	{ // 課程版為教師專用
                    wmSysLog($sysSession->cur_func, $sysSession->course_id , $bid , 5, 'auto', $_SERVER['PHP_SELF'], '課程版為教師專用');
                    die('board_taonly');
                }
            }
    }

    $sysSession->b_right = $sysSession->q_right;	// 目前兩者一樣

    $sysSession->restore();
    dbSet('WM_session', "board_name='{$bname}', q_path=''", "idx='{$_COOKIE['idx']}'");
    $sysSession->board_name = $bname;
    // 清除 Cookie 所存搜尋條件
    ClearForumCookie();

    // 讀出 extras 值到 cookie 中
    loadExtras2Cookie($sysSession->board_id);
    /* 取得公告版編寫權限，移植gotoboard功能(E) */
    
    
	// 各項排序依據
	$OB = $OrderBy['board'];

	$sysSession->post_no = '';
	// 如果用 alias link，抓取各項參數
	if (ereg('^(50[7-9]),([0-9]{10}),([0-9]+),([a-z_]+)\.php$', basename($_SERVER['PHP_SELF']), $reg)){
		if ($reg[2] != $sysSession->board_id) die('Error Board id: '.$reg[2]);
		$sysSession->page_no = intval($reg[3]);
		$user_sort = $reg[4];
	}

    // 如果板號不對，則停止
	if (!ereg('^[0-9]{10}$', $sysSession->board_id)) {
		wmSysLog($sysSession->cur_func, $sysSession->class_id , $sysSession->board_id , 1, 'auto', $_SERVER['PHP_SELF'], 'Error Board id');
		die('Error Board id: '.$sysSession->board_id);
	}
    
    // 取得 nid 供設定使用
    $nid = dbGetOne('WM_term_subject', '`node_id`', "`board_id`='{$sysSession->board_id}'", ADODB_FETCH_ASSOC);

	// if (!getBoardOwner($sysSession->board_id))	die('Wrong board owner!');
	$Board_OwnerID = $sysSession->board_ownerid;
	$Board_Owner   = $sysSession->board_ownername;

	$where_c = getSQLwhere($is_search, 'board');	// 詳見 /lib/lib_forum.php

	// 刊登權限(含修改, 刪除) $q_right 跟 $b_right 已移至 $sysSession 中

	// 是否具刊登權限(含張貼, 修改, 刪除)
	$BoardNotExistedArray = array('1000000000','1000000001');
	$boardNotExisted = false;
	if(in_array($sysSession->board_id,$BoardNotExistedArray))
		$boardNotExisted = true;
	if ($sysSession->board_readonly || $boardNotExisted) {
		$post_right = $sysSession->q_right;
		$updt_right = $sysSession->q_right;
	} else {
		$post_right = true;
		$updt_right = $sysSession->q_right;
	}
	if($boardNotExisted)
	{
		$post_right = false;
		$updt_right = false;
	}
	if ($sysSession->news_board && !$post_right) {	// 是公共消息類型且無刊登權限
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
	// 目前每頁筆數
	$rows_page = GetForumPostPerPage();	// 見 /lib/lib_forum.php

	// 取得本板張貼數
	$total_post = getTotalPost($where, 'board');

	// 計算總共有幾頁
	$total_page = ceil($total_post / $rows_page); //sysPostPerPage);

	// 排序法
	if (isset($user_sort)) {
		$sysSession->sortby = $user_sort;	// user 自訂
	} elseif (empty($sysSession->sortby)) {
		$sysSession->sortby = sysSortBy;	// 系統內定
	}
	if ($sysSession->sortby != 'node') { // 記錄目前所用 sortby, 以便回覆循序式
		setcookie('forum_sortby',  $sysSession->sortby,  time() +86400	, '/');
	}

	// 若有設頁號則檢查區間，沒有則跳到最後一頁
	if ($sysSession->page_no)
	    $sysSession->page_no = max(1, min($sysSession->page_no, $total_page));
	else
	    $sysSession->page_no = $total_page;

	// 計算資料庫抓取 record 號
	$cur_page = ($sysSession->page_no - 1) * $rows_page; //sysPostPerPage;

	// 回存 SESSION
	$sysSession->restore();

	// 產生 SQL 指令(在 config/db_initialize.php)
	$get_post_list = 'select node,subject,pt,poster,realname,email,homepage,attach,rcount,rank,hit ' .
					 'from WM_bbs_posts where board_id=%d order by %s limit %d,' . sysPostPerPage;
	$sqls = sprintf($get_post_list, $sysSession->board_id, $OB[$sysSession->sortby], $cur_page);
	$sqls = ereg_replace('where .* order', "where board_id={$sysSession->board_id} $where order", $sqls);
	if ($is_search == 'true') {
		SetForumCookie($where_c, $_POST['search_type'], $_POST['keyword'], 86400);
	} else {
		ClearForumCookie();
	}

	// 如果是要列全部，把 limit 去掉
	if ($reg[1] == '508')
		$sqls = ereg_replace('limit .*$', '', $sqls);
	else
		$sqls = ereg_replace('limit .*$', "limit $cur_page,$rows_page", $sqls);

	// 取得本頁資料
	$keep             = $ADODB_FETCH_MODE;
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	if(!$boardNotExisted)
		$RS               = $sysConn->Execute($sqls);
	$ADODB_FETCH_MODE = $keep;

	// 頁數下拉框
	$all_page    = range(0, $total_page);
	$all_page[0] = $MSG['all_page'][$sysSession->lang];

	// 每頁筆數下拉框
	$rows_per_page = Array(-1=>$MSG['default_per_page'][$sysSession->lang],
				20=>20,50=>50,100=>100,200=>200,400=>400);

	// 批次作業下拉框
	$batch_do = array(
		0=>'...',
		2=>$MSG['nbook'][$sysSession->lang]
	);
	if ($updt_right) {
		$batch_do[1] = $MSG['del'][$sysSession->lang];
		$batch_do[3] = $MSG['copy_q'][$sysSession->lang];
		$batch_do[4] = $MSG['move_q'][$sysSession->lang];
		ksort($batch_do);
	}
	// 搜尋種類下拉框
	$search_type = array('subject'=>$MSG['subj'][$sysSession->lang],
						 'poster' =>$MSG['poster'][$sysSession->lang],
						 'content'=>$MSG['content'][$sysSession->lang]);
	list($myorder) = dbGetStSr('WM_bbs_order','count(*)',"board_id={$sysSession->board_id} and username='{$sysSession->username}'", ADODB_FETCH_NUM);
	$my_subscribe = ($myorder==0)?'false':'true';

	// 是否已到關閉時間
	// $board_timeout = false;
	// $board_time = getBoardTime($sysSession->board_id);
	// $board_timeout = ($board_time['close_time']>0 && $board_time['close_time']<=time());

	$ticket = md5(sysTicketSeed . 'Board' . $_COOKIE['idx'] . $sysSession->board_id);

	$js = <<< BOF
	var bTicket        = '{$ticket}';
	var rows_page      = $rows_page;
	var cur_page       = $sysSession->page_no;
	var total_page     = $total_page;
	var total_post     = $total_post;
	var board_id       = '$sysSession->board_id';
	var sortby         = '$sysSession->sortby';
	var ErrorMsg       = '{$MSG['error_postno'][$sysSession->lang]}';
	var ErrorPostRange = '{$MSG['error_postrange'][$sysSession->lang]}';
	var MsgKeyword     = '{$MSG['keyword'][$sysSession->lang]}';
	var MsgSubs        = '{$MSG['subscribe'][$sysSession->lang]}';
	var MsgUnsubs      = '{$MSG['unsubscribe'][$sysSession->lang]}';
	var MsgSureDelFrom = '{$MSG['sure_del_from'][$sysSession->lang]}';
	var MsgSureDelTo   = '{$MSG['sure_del_to'][$sysSession->lang]}';
	var my_subscribe   = {$my_subscribe};
	var MsgInputFile   = "{$MSG['input_file'][$sysSession->lang]}";
	var ErrorPostNo    = '{$MSG['error_postno'][$sysSession->lang]}';
	var DEL_ERROR      = '{$MSG['error_del'][$sysSession->lang]}';
	var MSG_nbook      = '{$MSG['nbook'][$sysSession->lang]}';
	var MSG_del        = '{$MSG['del'][$sysSession->lang]}';
	var MSG_copy_q     = '{$MSG['copy_q'][$sysSession->lang]}';
	var MSG_move_q     = '{$MSG['move_q'][$sysSession->lang]}';
	var MsgExt1        = '{$MSG['msg_ext'][$sysSession->lang]}';
	var MsgExt2        = '{$MSG['msg_ext2'][$sysSession->lang]}';

	function onFocusKeyword(e) {	e.select(); }
	function onBlurKeyword(e) {
		if(e.value=='') {	e.value = MsgKeyword; }
	}
	function displaySubscribeButton(subsc) {
		my_subscribe = subsc;
		var btn = document.getElementById('btnOrder');
		btn.value = my_subscribe?MsgUnsubs:MsgSubs;
	}

	function loadwb(urlval)
	{
		var paraObj = new Object();
		paraObj.WM_BoardID = "{$sysSession->board_id}";
		paraObj.WM_CourseID = "{$sysSession->course_id}";
		paraObj.openerdocument = document;
		paraObj.preloaddata = "";
		paraObj.blocked = 1;
		if ( urlval != "" )
		{
				paraObj.preloaddata = 'http://' + document.location.host +"/lib/anicamWB/readWBFile.php?filepath="+escape(urlval);
				// paraObj.preloaddata = 'http://wm3.learn.com.tw/user/j/e/jeff/96610217542ac141498e492ec65a1c0e.awp';
		}

		var WB_Window = window.showModalDialog("whiteboard.html", paraObj, "status:no; dialogWidth:635px; dialogHeight:545px; edge:raised; center:yes; unadorned:no; help:no; scroll:no; status:no; resizable:no;");
	}


	function OnLoad() {
		document.getElementById('tb2').innerHTML = document.getElementById('tb1').innerHTML;
		displaySubscribeButton(my_subscribe);

		var frm = document.getElementById('mainform');
		frm.btnUnSearch.disabled = !(frm.is_search.value == 'true');
	}
BOF;
	// 開始呈現 HTML
	showXHTML_head_B($sysSession->board_name);
	showXHTML_script('include', '/lib/dragLayer.js');
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('include', '/teach/course/m_cour_annt.js');
	showXHTML_script('inline', $js);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E('');

	showXHTML_body_B('onload=OnLoad()');
	showXHTML_script('inline', "
if (opener != null && typeof(opener) != 'undefined') document.write('<p align=center><input type=button value=\"{$MSG['close_window'][$sysSession->lang]}\" onclick=\"window.close();\" class=cssBtn></p>');
else if (parent == self) document.write('<p align=center><input type=button value=\"{$MSG['return_home'][$sysSession->lang]}\" onclick=\"top.location.replace(\'/logout.php\');\" class=cssBtn></p>');
");
	showXHTML_form_B('action="" method="post"', 'mainform');
		showXHTML_input('hidden','is_search',$is_search);
		showXHTML_input('hidden','rows_page',0);
		showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" align="center"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary = array();
					$ary[] = array($MSG['management'][$sysSession->lang],   'tabs1');
					$ary[] = array($MSG['setting'][$sysSession->lang],      'tabs2', 'go_setting()');
					// $ary[] = array($MSG['threading mode'][$sysSession->lang], 'tabs3', 'location.replace("t_index.php")');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top"');
					showXHTML_table_B('border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						showXHTML_tr_B('class="cssTrHead"');
							if($boardNotExisted)
								$baordname = $MSG['msg_board_id_fail'][$sysSession->lang];
							else
								$baordname = '&nbsp;' . $Board_Owner . '&nbsp;>&nbsp;' .$sysSession->board_name;
							showXHTML_td('colspan="3"',  $baordname);
							showXHTML_td_B('colspan="3" align="right"');
								showXHTML_input('button','',$MSG['export_all'][$sysSession->lang],'','class="cssBtn" id="btnExportAll"' .($sysSession->b_right?' onClick="showExportAllDlg()"':' disabled="disabled"'));
								showXHTML_input('button','btnImportAll',$MSG['import_all'][$sysSession->lang],'','class="cssBtn" id="btnImportAll"' .($sysSession->b_right?' onClick="displayImportAllUI(true)"':' disabled="disabled"'));
                                showXHTML_input('button','',$MSG['import'][$sysSession->lang],'', !$post_right?'class="cssBtn" disabled="disabled"':'id="btnImport" onClick="displayImportUI(true)"');
                                showXHTML_input('button','', $MSG['quint'][$sysSession->lang],'', 'id="btnEssential" onClick="go_quint()"');
							showXHTML_td_E('');
							showXHTML_td_B('colspan="1"');                                
                                $nEnv = $sysSession->env == 'teach' ? 2 : 1;
                                showXHTML_input('button','', $MSG['go_bulletin'][$sysSession->lang],'', 'onClick="parent.chgCourse('.$sysSession->course_id.', '.$nEnv.', 1, \'SYS_04_01_001\');"');
							showXHTML_td_E('');
						showXHTML_tr_E('');

						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('valign="top" align="right"', $MSG['title'][$sysSession->lang]);
							if($boardNotExisted)
								$subject = '';
							else
								$subject = GetBoardSubject($sysSession->board_id);
							showXHTML_td('colspan="6"', $subject);
						showXHTML_tr_E('');

						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td('valign="top" align="right"',  $MSG['search'][$sysSession->lang]);
							showXHTML_td_B('colspan="5"');
								showXHTML_input('select', 'search_type', $search_type, $_POST['search_type'], 'class="cssInput"');
								echo '&nbsp;&nbsp;'.$MSG['contain'][$sysSession->lang] . '&nbsp;&nbsp';
								showXHTML_input('text','keyword', $is_search=='true'?$_POST['keyword']:$MSG['keyword'][$sysSession->lang], '','size="20" class="cssInput" onFocus="onFocusKeyword(this)"'); // onBlur="onBlurKeyword(this)"
								echo '&nbsp;&nbsp;'.$MSG['in_content'][$sysSession->lang];
								showXHTML_input('button','',$MSG['ok'][$sysSession->lang],'','id="btnSearch" onClick=search()');
								showXHTML_input('button','',$MSG['cancel'][$sysSession->lang],'','id="btnUnSearch" onClick=unsearch()');
							showXHTML_td_E('');
							showXHTML_td_B('');
								showXHTML_input('button','',$MSG['subscribe'][$sysSession->lang],'','id="btnOrder" ' . ($sysSession->username == 'guest' ? 'disabled' : 'onClick="subscribe(event)"'));
							showXHTML_td_E('');
						showXHTML_tr_E('');

						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('valign="top" align="right"',  $MSG['page1'][$sysSession->lang]);
							showXHTML_td_B('nowrap="nowrap" colspan="6" id="tb1"');
								showXHTML_input('select', 'ap', $all_page, $reg[1]=='508'?0:$sysSession->page_no, 'class="cssInput" onchange="go_page(this.value);" style="width: 50px"');
								echo $MSG['per_page'][$sysSession->lang];
								showXHTML_input('select', 'rp', $rows_per_page, $rows_page==sysPostPerPage?'default':$rows_page, 'class="cssInput" onchange="go_rowspage(this.value);" style="width: 60px"');
								echo $MSG['posts_per_page'][$sysSession->lang];

								showXHTML_input('button', 'nd', $MSG[$sysSession->sortby=='node'?'by_order':'by_node'][$sysSession->lang], '', 'onclick="sortBy(' .($sysSession->sortby=='node'?$js_OrderBy[$_COOKIE['forum_sortby']]:'0'). ');" class="cssBtn"'); echo '&nbsp;';
								showXHTML_input('button', 'fp', $MSG['first'][$sysSession->lang], '', 'class="cssBtn" '.($sysSession->page_no<=1          ?'disabled="disabled"':'onclick="go_page(-1);"'));
								showXHTML_input('button', 'pp', $MSG['prev'][$sysSession->lang] , '', 'class="cssBtn" '.($sysSession->page_no<=1          ?'disabled="disabled"':'onclick="go_page(-2);"'));
								showXHTML_input('button', 'np', $MSG['next'][$sysSession->lang] , '', 'class="cssBtn" '.($sysSession->page_no>=$total_page?'disabled="disabled"':'onclick="go_page(-3);"'));
								showXHTML_input('button', 'lp', $MSG['last'][$sysSession->lang] , '', 'class="cssBtn" '.($sysSession->page_no>=$total_page?'disabled="disabled"':'onclick="go_page(-4);"')); echo '&nbsp;&nbsp;';
								showXHTML_input('button', 'po', $MSG['post'][$sysSession->lang] , '', !$post_right?'class="cssBtn" disabled="disabled"':'class="cssBtn" onclick="location.replace(\'/forum/write.php?bTicket=' . $bTicket . '&enbid='.$enbid.'\');"');
							  if ($sysSession->username != 'guest')
							  {
								echo '&nbsp;&nbsp;',$MSG['from'][$sysSession->lang];
								showXHTML_input('text', 'st', '', '', 'size="4" maxlength="4" class="cssInput"');
								echo $MSG['to'][$sysSession->lang];
								showXHTML_input('text', 'en', '', '', 'size="4" maxlength="4" class="cssInput"');
								echo $MSG['batch'][$sysSession->lang];
								showXHTML_input('select', 'bd', $batch_do, '', ' class="cssInput" onchange="batch(this);"');
							  }
							showXHTML_td_E('');
						showXHTML_tr_E('');

						// $icon_up = '<img src="/theme/default/learn/dude07232001up.gif" border="0" align="absmiddl">';
						// $icon_dn = '<img src="/theme/default/learn/dude07232001down.gif" border="0" align="absmiddl">';
						// echo "<!-- user_sort:{$user_sort} , sort: {$sysSession->sortby} -->\r\n";
						showXHTML_tr_B('class="cssTrHead"');
							showXHTML_td('width="30"  nowrap="nowrap" align="center"', $MSG['serial'][$sysSession->lang]);
							showXHTML_td('width="280" nowrap="nowrap" align="center"', generate_order_link('subject',	$MSG['subj'][$sysSession->lang] , $sysSession->sortby));
							showXHTML_td('width="140" nowrap="nowrap" align="center"', generate_order_link('poster',	$MSG['poster'][$sysSession->lang] , $sysSession->sortby)); //($sysSession->sortby == 'poster' )?('<b>'.$MSG['poster'][$sysSession->lang].$icon_up.'</b>'):('<a href="javascript:;" onclick="sortBy(3); return false;" class="cssAnchor" title="'.$MSG['order'][$sysSession->lang].'">'.$MSG['poster'][$sysSession->lang].'</a>'));
							showXHTML_td('width="140" nowrap="nowrap" align="center"', generate_order_link('pt', 		$MSG['time'][$sysSession->lang] , $sysSession->sortby)); //($sysSession->sortby == 'pt'     )?('<b>'.$MSG['time'][$sysSession->lang]  .$icon_dn.'</b>'):('<a href="javascript:;" onclick="sortBy(1); return false;" class="cssAnchor" title="'.$MSG['order'][$sysSession->lang].'">'.$MSG['time'][$sysSession->lang]  .'</a>'));
							showXHTML_td('width="50"  nowrap="nowrap" align="center"', generate_order_link('hit', 		$MSG['hit'][$sysSession->lang], $sysSession->sortby) ); //($sysSession->sortby == 'hit'    )?('<b>'.$MSG['hit'][$sysSession->lang]   .$icon_dn.'</b>'):('<a href="javascript:;" onclick="sortBy(4); return false;" class="cssAnchor" title="'.$MSG['order'][$sysSession->lang].'">'.$MSG['hit'][$sysSession->lang]   .'</a>'));
							showXHTML_td('width="80"  nowrap="nowrap" align="center"',
							(getExtras('rank')?generate_order_link('rank', 		$MSG['rank'][$sysSession->lang], $sysSession->sortby):$MSG['rank'][$sysSession->lang]) ); //($sysSession->sortby == 'rank'   )?('<b>'.$MSG['rank'][$sysSession->lang]  .$icon_dn.'</b>'):('<a href="javascript:;" onclick="sortBy(5); return false;" class="cssAnchor" title="'.$MSG['order'][$sysSession->lang].'">'.$MSG['rank'][$sysSession->lang]  .'</a>'));
							showXHTML_td('width="40"  nowrap="nowrap" align="center"', $MSG['attach'][$sysSession->lang]);
						showXHTML_tr_E('');

/* 列全部? */						$i = $reg[1]=='508'?1:($cur_page + 1);

						$tdArr = Array();
						$nodeFilter = Array();

						if (is_object($RS))
						while(!$RS->EOF){
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							$k = array_shift($RS->fields);
							$tdArr[$k] = $RS->fields + array('color' => $col);
							$nodeFilter[] = "'" . $k ."'";
							$RS->MoveNext();
						}
						if(count($nodeFilter)>0) {
							$nodeWhere = "type='b' and board_id={$sysSession->board_id} And username='{$sysSession->username}' AND node in (" . implode(",",$nodeFilter) . ")";
							$RS1 = dbGetStMr('WM_bbs_readed','node,read_time',$nodeWhere, ADODB_FETCH_ASSOC);
							if (is_object($RS1))
							while(!$RS1->EOF) {
								$tdArr[$RS1->fields['node']]['read_time'] = $RS1->fields['read_time'];
								$RS1->MoveNext();
							}
						}

						$i = $reg[1]=='508'?1:($cur_page + 1);

						foreach($tdArr as $k=>$v){
							showXHTML_tr_B($v['color'] . ' onclick="read('.($cur_post+$i).');"');
								$img_new = empty($v['read_time'])?"&nbsp;&nbsp;<img src=/theme/{$sysSession->theme}/learn/new.gif>":"";
								showXHTML_td('nowrap="nowrap" align="center"', $i++);
								showXHTML_td('width="280"', indent($k, $sysSession->sortby).'<a href="' . $sysSession->board_id . $k.'.htm" class="cssAnchor" target="empty" title="' . htmlspecialchars($v['subject']) . '  " onclick="return false;">' . $v['subject'] . '</a>'.$img_new);
								showXHTML_td('width="140"', "<a href=\"mailto:{$v['email']}\" class=\"cssAnchor\" onclick=\"event.cancelBubble=true;\">{$v['poster']}</a> ".($v['homepage']?("(<a href=\"{$v['homepage']}\" target=\"_blank\" class=\"cssAnchor\" onclick=\"event.cancelBubble=true;\">{$v['realname']}</a>)"):"({$v['realname']})"));
								showXHTML_td('nowrap="nowrap" align="center"', $v['pt']);
								showXHTML_td('nowrap="nowrap" align="right"', $v['hit']);
								showXHTML_td('nowrap="nowrap" align="center"', $v['rank'].' / '.$v['rcount']);
								showXHTML_td('', generate_attach_link(get_attach_file_path('board', $sysSession->board_ownerid).'/'.$k, $v['attach']));
							showXHTML_tr_E('');
						}

						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('valign="top" align="right"',  $MSG['page1'][$sysSession->lang]);
							showXHTML_td_B('nowrap="nowrap" colspan="7" id="tb2"');
							showXHTML_td_E('');
						showXHTML_tr_E('');

					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
	showXHTML_form_E('');

	$ary = array();
	$ary[] = array($MSG['import'][$sysSession->lang], 'import_ui');
	echo '<div align="center">';
	showXHTML_tabFrame_B($ary, 1, 'form_import', 'import_ui', 'action="/forum/import.php" style="display: inline" method="post" enctype="multipart/form-data"', true);
		showXHTML_table_B(' width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
			showXHTML_tr_B('class="cssTrEvn"');
				showXHTML_td('nowrap="nowrap"', $MSG['import_note'][$sysSession->lang]);
			showXHTML_tr_E('');
			showXHTML_tr_B('class="cssTrOdd"');
				showXHTML_td_B();
					showXHTML_input('file','file_import','','','class="cssInput"');

					showXHTML_input('button','btnImpOK',$MSG['import'][$sysSession->lang],'','onclick="OnImportButton(true);"');
					showXHTML_input('button','btnImpCancel',$MSG['cancel'][$sysSession->lang],'','onclick="OnImportButton(false);"');
				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E('');
	showXHTML_tabFrame_E();
	echo '</div>';

	$ary = array();
	$ary[] = array($MSG['import_all'][$sysSession->lang], 'importall_ui');
	echo '<div align="center">';
	showXHTML_tabFrame_B($ary, 1, 'form_importall', 'importall_ui', 'action="/forum/import_all.php" style="display: inline" method="post" enctype="multipart/form-data"', true);
		showXHTML_input('hidden','ticket',$ticket);
		showXHTML_table_B(' width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
			showXHTML_tr_B('class="cssTrEvn"');
				showXHTML_td('nowrap="nowrap"', $MSG['import_note1'][$sysSession->lang]);
			showXHTML_tr_E('');
			showXHTML_tr_B('class="cssTrOdd"');
				showXHTML_td_B();
					showXHTML_input('file','file_import','','','class="cssInput" size="50"');
				showXHTML_td_E();
			showXHTML_tr_E('');
			if(strlen($sysSession->board_ownerid)==8) {	// 只有課程討論版可以新建
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B();
					$rdo_items = Array(
								'new'=>$MSG['import_choice1'][$sysSession->lang],
								'old'=>$MSG['import_choice2'][$sysSession->lang]
								);
						showXHTML_input('radio','import_choice',$rdo_items,'new','',"<br>");
					showXHTML_td_E();
				showXHTML_tr_E('');
			} else {
					showXHTML_input('hidden','import_choice','old');
			}
			showXHTML_tr_B('class="cssTrOdd"');
				showXHTML_td_B();
					showXHTML_input('button','',$MSG['import_all'][$sysSession->lang],'','onclick="OnImportAllButton(true);"');
					showXHTML_input('button','',$MSG['cancel'][$sysSession->lang],'','onclick="OnImportAllButton(false);"');
				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E('');
	showXHTML_tabFrame_E();
	echo '</div>';

	showXHTML_form_B('action="" method="post" style="display:none"', 'export_all_form');
		showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . 'BoardExp' . $_COOKIE['idx'] . $sysSession->board_id));
	showXHTML_form_E();
    
	showXHTML_form_B('action="" method="post" style="display:none"', 'board_setting');
		showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . 'setAnnt' . $_COOKIE['idx']));
        showXHTML_input('hidden', 'nid', $nid);
	showXHTML_form_E();
    
    showXHTML_form_B('action="" method="post" style="display:none"', 'board_post');
        showXHTML_input('hidden', 'bid', $bid);
        showXHTML_input('hidden', 'env', $sysSession->env);
	showXHTML_form_E();
    
	showXHTML_script('inline', "
if (opener != null && typeof(opener) != 'undefined') document.write('<p align=center><input type=button value=\"{$MSG['close_window'][$sysSession->lang]}\" onclick=\"window.close();\" class=cssBtn></p>');
else if (parent == self) document.write('<p align=center><input type=button value=\"{$MSG['return_home'][$sysSession->lang]}\" onclick=\"top.location.replace(\'/logout.php\');\" class=cssBtn></p>');
");
	showXHTML_body_E('');
?>
