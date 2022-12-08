<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/lib_acade_news.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');
	require_once(sysDocumentRoot . '/lib/lib_forum.php');
	require_once(sysDocumentRoot . '/lang/forum.php');
	require_once(sysDocumentRoot . '/forum/order.inc.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '900300900';
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

	// 各項排序依據
	$OB = $OrderBy['quint'];

	$sysSession->q_post_no = '';
	$where = getSQLwhere($is_search, 'quint');	// 詳見 /lib/lib_forum.php

	// 如果用 alias link，抓取各項參數
	if (ereg('^(56[0-9]),([0-9]{10}),([0-9]+),([a-z_]+)(,([0-9a-zA-Z]{32}|root))?\.php$', basename($_SERVER['PHP_SELF']), $reg)){
		// 驗證板號
		if ($reg[2] != $sysSession->board_id) die('Error Board id: '.$reg[2]);

		if (ereg('^[0-9a-zA-Z]{32}$', $reg[6])){
			//$path = $sysSession->q_path;
			list($path_name, $dir_name) = dbGetStSr('WM_bbs_collecting', 'path, subject', "board_id='{$reg[2]}' and node='$reg[6]' and type='D'", ADODB_FETCH_NUM);
			$sysSession->q_path = ($path_name=='/'?'':$path_name) . "/{$dir_name}";
		}
		elseif($reg[6] == 'root'){	// 根目錄
			$sysSession->q_path = '/';
		}
		$sysSession->q_page_no = $reg[3];
		//$sysSession->q_sortby = $reg[4];
		$user_sort = $reg[4];
	}
	else {
		if (strlen($sysSession->q_path) < 2) $sysSession->q_path = '/';
	}

	// 如果板號不對，則停止
	if (!ereg('^[0-9]{10}$', $sysSession->board_id)) {
	   wmSysLog($sysSession->cur_func, $sysSession->class_id , $sysSession->board_id , 1, 'auto', $_SERVER['PHP_SELF'], 'Error Board id');
	   die('Error Board id: '.$sysSession->board_id);
	}

	// if (!getBoardOwner($sysSession->board_id)) die('Wrong board owner!');
	$Board_OwnerID = $sysSession->board_ownerid;
	$Board_Owner   = $sysSession->board_ownername;
    
    // 取得 nid 供設定使用
    $nid = dbGetOne('WM_term_subject', '`node_id`', "`board_id`='{$sysSession->board_id}'", ADODB_FETCH_ASSOC);

    // 判斷是否為公告或分組討論
    $bulletin = dbGetOne('WM_term_course', '`bulletin`', "`course_id`='{$sysSession->course_id}'", ADODB_FETCH_ASSOC);
    $Group = dbGetOne('WM_student_group', '`board_id`', "`course_id`='{$sysSession->course_id}' and `board_id`='{$sysSession->board_id}'", ADODB_FETCH_ASSOC);
    $settingUrl = ($bulletin == $sysSession->board_id) ? '/teach/course/m_cour_annt_property.php'
                                                       : (($Group == $sysSession->board_id) ? '/teach/course/cour_group_subject_property.php'
                                                                                            : '/teach/course/cour_subject_property.php');
    
	// 是否具刊登權限(含修改, 刪除) : 權限在 $sysSession 中

	// 目前每頁筆數
	$rows_page     = GetForumPostPerPage();	// 見 /lib/lib_forum.php
    
	if($is_search == 'true') {
        # 47300 Chrome 精華區輸入關鍵字搜尋，檢視文章後再返回列表會沒有資料，因為組成的搜尋條件有錯誤：反解網址編碼
        $where = urldecode($where);
	}
	
	// 取得本板張貼數
	$total_post    = getTotalPost($where, 'quint');

	// 計算總共有幾頁
	$total_page    = ceil($total_post / $rows_page); //sysPostPerPage);


	// 排序法
	if (isset($user_sort)) {
		$sysSession->q_sortby = $user_sort;	// user 自訂
	} elseif (empty($sysSession->q_sortby)) {
		$sysSession->q_sortby = sysQSortBy;	// 系統內定
	}
	if ($sysSession->q_sortby != 'node') { // 記錄目前所用 sortby, 以便回覆循序式
		setcookie('forum_qsortby', $sysSession->sortby, time() + 86400, '/');
	}

	// 若有設頁號則檢查區間，沒有則跳到最後一頁
	if ($sysSession->q_page_no)
	    $sysSession->q_page_no = max(1, min($sysSession->q_page_no, $total_page));
	else
	    $sysSession->q_page_no = $total_page;

	// 計算資料庫抓取 record 號
	$cur_page = $sysSession->q_page_no ? ( ($sysSession->q_page_no - 1) * $rows_page ):0;

	// 回存 SESSION
	if($is_search != 'true')
		dbSet('WM_session', "q_path='{$sysSession->q_path}'", "idx='{$_COOKIE['idx']}'");
	$sysSession->restore();

	$get_qost_list = 'select node,subject,type,pt,poster,realname,email,homepage,attach,rcount,rank,hit ' .
					 'from WM_bbs_collecting where board_id=%d and path=\'%s\' order by %s limit %d,' .
			 		 sysPostPerPage ;
	$sqls = sprintf($get_qost_list, $sysSession->board_id, $sysSession->q_path, $OB[$sysSession->q_sortby], $cur_page);
    if ("" != $where) {
        $sqls = ereg_replace('where .* order', "where board_id={$sysSession->board_id} $where order", $sqls);
    }
	if($is_search == 'true') {
		SetForumCookie($where,$_POST['search_type'],$_POST['keyword'],86400,'quint');
	} else {
		ClearForumCookie();
	}

	// 如果是要列全部，把 limit 去掉
	if ($reg[1] == '561')
		$sqls = ereg_replace('limit .*$', '', $sqls);
	else
		$sqls = ereg_replace('limit .*$', "limit $cur_page,$rows_page", $sqls);

	// 路徑顯示
	$cur_path   = $sysSession->q_path ? $sysSession->q_path : '/';
	$path_title = $MSG['folder'][$sysSession->lang] . ':' .($is_search == 'true' ? $MSG['search_no_folder'][$sysSession->lang] : $cur_path);

	// 取得本頁資料
	$keep             = $ADODB_FETCH_MODE;
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	$RS               = $sysConn->Execute($sqls);
	$ADODB_FETCH_MODE = $keep;

	// 頁數下拉框
	$all_page    = range(0, $total_page);
	$all_page[0] = $MSG['all_page'][$sysSession->lang];

	// 每頁筆數下拉框
	$rows_per_page = Array(-1=>$MSG['default_per_page'][$sysSession->lang],
				20=>20,50=>50,100=>100,200=>200,400=>400);

	// 批次作業下拉框
	if($sysSession->q_right) {
		$batch_do = array(0=>'...',
			  1=>$MSG['del'][$sysSession->lang],
			  2=>$MSG['nbook'][$sysSession->lang],
			  3=>$MSG['move'][$sysSession->lang]
			 );
	} else {
		$batch_do = array(0=>'...',
			  2=>$MSG['nbook'][$sysSession->lang],
			 );
	}

	// 搜尋種類下拉框
	$search_type = array('subject'=>$MSG['subj'][$sysSession->lang],
			'poster'=>$MSG['poster'][$sysSession->lang],
			'picker'=>$MSG['picker'][$sysSession->lang],
			'content'=>$MSG['content'][$sysSession->lang]);

	setTicket();
	$ticket = md5($sysSession->username . 'quint' . $sysSession->ticket . $sysSession->school_id);
	$import_ticket = md5(sysTicketSeed . 'Board' . $_COOKIE['idx'] . $sysSession->board_id);

    // 是否為常見問題版
    if (dbGetNewsBoard($result, 'faq')) {
        $isFaq = ($result['board_id'] == $sysSession->board_id) ? true : false;
    }
    
	$js = <<< BOF
	var bTicket           = '{$ticket}';
	var rows_page         = $rows_page;
	var cur_page          = $sysSession->q_page_no;
	var total_page        = $total_page;
	var total_post        = $total_post;
	var board_id          = '$sysSession->board_id';
	var sortby            = '$sysSession->q_sortby';
	var path              = '$sysSession->q_path';
	var ErrorPostNo       = '{$MSG['error_postno'][$sysSession->lang]}';
	var ErrorPostRange    = '{$MSG['error_postrange'][$sysSession->lang]}';
	var MsgKeyword        = '{$MSG['keyword'][$sysSession->lang]}';
	var MsgRenameAs       = '{$MSG['folder_rename_as'][$sysSession->lang]}';
	var MsgSuccess        = '{$MSG['successful'][$sysSession->lang]}';
	var MsgFailed         = '{$MSG['failed'][$sysSession->lang]}';
	var MsgIllegalChar    = '{$MSG['illegal_char'][$sysSession->lang]}';
	var MsgFolder         = '{$MSG['folder'][$sysSession->lang]}';
	var MsgNameCantBlank  = '{$MSG['name_cantblank'][$sysSession->lang]}';
	var MsgInputFile      = "{$MSG['input_file'][$sysSession->lang]}";
	var MsgNewFolder      = "{$MSG['new'][$sysSession->lang]}{$MSG['folder'][$sysSession->lang]}:";
	var MsgIllegalChars   = "{$MSG['msg_illegalchars'][$sysSession->lang]}";
	var DEL_ERROR         = '{$MSG['error_del'][$sysSession->lang]}';
	var MSG_nbook         = '{$MSG['nbook'][$sysSession->lang]}';
	var MSG_del           = '{$MSG['del'][$sysSession->lang]}';
	var MSG_move          = '{$MSG['move'][$sysSession->lang]}';
	var MSG_empty_dir     = '{$MSG['empty_dir'][$sysSession->lang]}';
	var MsgExt1        = '{$MSG['msg_ext'][$sysSession->lang]}';
	var MsgExt2        = '{$MSG['msg_ext2'][$sysSession->lang]}';

	var innerTemp         = '<span style="width: 280px; overflow: hidden"><a href="%1.htm" onclick="chdir(\'%1\');" class="cssAnchor" target="empty" title="%2">%2/</a></span>';

	var ticket            = "{$ticket}";

	function onFocusKeyword(e) {	e.select(); }
	function onBlurKeyword(e) {
		if(e.value=='') {	e.value = MsgKeyword; }
	}

	var isMZ = (navigator.userAgent.indexOf(' MSIE ') == -1);       // 瀏覽器是否為 Mozilla
	var xx = 0, yy = 0;                                             // 試算表游標位置
	var tblOfficeX = 0, tblOfficeY = 0;                             // 試算表游標座標

	/**
	 * 取得試算表游標應該出現的絕對座標( 取自成績管理 )
	 */
	function getParentOffset(obj, which){
		switch(obj.tagName){
		case 'HTML':
				return 0;
				break;
			case 'TABLE':
			case 'TD':
				return (which ? obj.offsetLeft : obj.offsetTop) + getParentOffset(obj.parentNode, which);
				break;
			default:
				return getParentOffset(obj.parentNode, which);
				break;
		}
	}

	function loadwb(urlval)
	{
		var paraObj            = new Object();
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

	function OnLoad() {
		xmlHttp                                  = XmlHttp.create();
		xmlVars                                  = XmlDocument.create();
		xmlDoc                                   = XmlDocument.create();

		document.getElementById('tb2').innerHTML = document.getElementById('tb1').innerHTML;

		var frm                                  = document.getElementById('mainform');
		frm.btnUnSearch.disabled                 = !(frm.is_search.value == 'true');

		ListPanel                                = document.getElementById('tblList');
		tblOfficeX                               = getParentOffset(ListPanel, true);
		tblOfficeY                               = getParentOffset(ListPanel, false);
	}

    function go_setting() {
        var frm = document.getElementById('board_setting');
        frm.action = '{$settingUrl}';
        frm.submit();
    }
BOF;

	// 開始呈現 HTML
	showXHTML_head_B($sysSession->board_name . ' ' . $MSG['quint'][$sysSession->lang] . ' [ ' . ($sysSession->q_path?$sysSession->q_path:'/') .' ]');
	showXHTML_script('include', '/lib/code.js');
	showXHTML_script('include', '/lib/xmlextras.js');
	showXHTML_script('include', '/lib/dragLayer.js');
	showXHTML_script('include', 'q_index.js');
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('inline', $js);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E('');

	showXHTML_body_B('onload=OnLoad()');
	showXHTML_script('inline', "
	if (opener != null && typeof(opener) != 'undefined') {
		document.write('<p align=center><input type=button value=\"{$MSG['close_window'][$sysSession->lang]}\" onclick=\"window.close();\" class=cssBtn></p>');
	} else if (parent == self) {
		if ('".(defined('sysEnableMooc') && sysEnableMooc > 0)."' === '1') {
			document.write('<p align=center><input type=button value=\"{$MSG['return_home'][$sysSession->lang]}\" onclick=\"top.location.replace(\'/mooc/index.php\');\" class=cssBtn></p>');
		} else {
			document.write('<p align=center><input type=button value=\"{$MSG['return_home'][$sysSession->lang]}\" onclick=\"top.location.replace(\'/logout.php\');\" class=cssBtn></p>');
		}
	}
");
	showXHTML_form_B('action="" method="post"', 'mainform');
		showXHTML_input('hidden','is_search',$is_search);
		showXHTML_input('hidden','rows_page',0);
		showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" align="center"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary = array();
					if($sysSession->board_qonly) {
						$ary[] = array($sysSession->board_name, 'tabs2');
						showXHTML_tabs($ary, 1);
					} else {
                        $ary[] = array($MSG['management'][$sysSession->lang]                  , 'tabs1', '');
                        if ($sysSession->board_readonly != 1 || $sysSession->env != 'learn') {
                            $ary[] = array($MSG['setting'][$sysSession->lang]                  , 'tabs2', 'go_setting()');
                        }
						// $ary[] = array($sysSession->board_name                  , 'tabs1', 'go_normal()');
						// $ary[] = array($MSG['quint'][$sysSession->lang]         , 'tabs2');
						// $ary[] = array($MSG['threading mode'][$sysSession->lang], 'tabs3', 'location.replace("t_index.php")');
						showXHTML_tabs($ary, 1);
					}
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top"');
					showXHTML_table_B('id="tblList" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						showXHTML_tr_B('class="cssTrHead"');

						if ($sysSession->board_qonly) {	// 只有精華區的討論版
							showXHTML_td('colspan="3"', '&nbsp;' . $Board_Owner . '&nbsp;>&nbsp;' . $sysSession->board_name .
									'&nbsp;:&nbsp;' . $path_title);
							showXHTML_td_B('colspan="2" align="right"');
                                if ($isFaq !== true) {
                                    showXHTML_input('button','',$MSG['export_all'][$sysSession->lang],'','class="cssBtn" id="btnExportAll"' .($sysSession->b_right?' onClick="showExportAllDlg()"':' disabled="disabled"'));
                                    showXHTML_input('button','btnImportAll',$MSG['import_all'][$sysSession->lang],'','class="cssBtn" id="btnImportAll"' .($sysSession->b_right?' onClick="displayImportAllUI(true)"':' disabled="disabled"'));
                                }
							showXHTML_td_E('');
						} else {
							showXHTML_td('colspan="5"', '&nbsp;' . $Board_Owner . '&nbsp;>&nbsp;' . $sysSession->board_name .
									'&nbsp;:&nbsp;' . $path_title);
						}
							showXHTML_td_B('colspan="2"');
                                if ($isFaq !== true) {
                                    showXHTML_input('button','',$MSG['import'][$sysSession->lang],'',
                                        $sysSession->q_right?'id="btnImport" onClick="displayImportUI(true)" class="cssBtn"':'id="btnImport" disabled="disabled" class="cssBtn"');
                                }
                                if (!$sysSession->board_qonly) {	// 只有精華區的討論版不顯示
                                    showXHTML_input('button','', $MSG['general'][$sysSession->lang],'', 'id="btnNormal" onClick="go_normal()"');
                                }
							showXHTML_td_E('');
						showXHTML_tr_E('');
						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('valign="top" align="right" nowrap="nowrap"', $MSG['title'][$sysSession->lang]);
							showXHTML_td('colspan="6"', GetBoardSubject($sysSession->board_id));
						showXHTML_tr_E('');

						showXHTML_tr_B('class="cssTrOdd"');
							showXHTML_td('align="right"',$MSG['search'][$sysSession->lang]);
							showXHTML_td_B('colspan="6"');
								showXHTML_input('select', 'search_type', $search_type, $_POST['search_type'], 'class="cssInput"');
								echo '&nbsp;&nbsp;'.$MSG['contain'][$sysSession->lang] . '&nbsp;&nbsp';
								showXHTML_input('text','keyword', $is_search=='true'?$_POST['keyword']:$MSG['keyword'][$sysSession->lang], '','size="20" class="cssInput" onFocus="onFocusKeyword(this)"');
								echo '&nbsp;&nbsp;'.$MSG['in_content'][$sysSession->lang];
								showXHTML_input('button','',$MSG['ok'][$sysSession->lang],'','id="btnSearch" onClick=search()');
								showXHTML_input('button','',$MSG['cancel'][$sysSession->lang],'','id="btnUnSearch" onClick=unsearch()');
							showXHTML_td_E('');
						showXHTML_tr_E('');

						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('align="right"',$MSG['page1'][$sysSession->lang]);
							showXHTML_td_B('nowrap="nowrap" colspan="6" id="tb1"');
								showXHTML_input('select', 'ap', $all_page, $reg[1]=='561'?0:$sysSession->q_page_no, 'class="cssInput" onchange="go_page(this.value);" style="width: 50px"');
								echo $MSG['per_page'][$sysSession->lang];
								showXHTML_input('select', 'rp', $rows_per_page, $rows_page==sysPostPerPage?'default':$rows_page, 'class="cssInput" onchange="go_rowspage(this.value);" style="width: 60px"');
								echo $MSG['posts_per_page'][$sysSession->lang];

								showXHTML_input('button', 'nd', $MSG[$sysSession->q_sortby=='node'?'by_order':'by_node'][$sysSession->lang], '', 'style="display:none" onclick="sortBy(' .($sysSession->q_sortby=='node'?$js_OrderBy[$_COOKIE['forum_qsortby']]:'0'). ');" class="cssBtn"'); echo '&nbsp;';
								showXHTML_input('button', 'fp', $MSG['first'][$sysSession->lang], '', 'class="cssBtn" '.($sysSession->q_page_no<=1          ?'disabled="disabled"':'onclick="go_page(-1);"'));
								showXHTML_input('button', 'pp', $MSG['prev'][$sysSession->lang] , '', 'class="cssBtn" '.($sysSession->q_page_no<=1          ?'disabled="disabled"':'onclick="go_page(-2);"'));

								/*Chrome*/
								showXHTML_input('button', 'np', $MSG['next'][$sysSession->lang] , '', 'class="cssBtn" '.($sysSession->q_page_no>=$total_page?'disabled="disabled"':'onclick="go_page(-3);"'));
								showXHTML_input('button', 'lp', $MSG['last'][$sysSession->lang] , '', 'class="cssBtn" '.($sysSession->q_page_no>=$total_page?'disabled="disabled"':'onclick="go_page(-4);"'));echo '&nbsp;&nbsp;';

								showXHTML_input('button', 'po', $MSG['post'][$sysSession->lang] , '', $sysSession->q_right?'class="cssBtn" onclick="post();"':'class="cssBtn"  disabled="disabled"');
								showXHTML_input('button', 'md', $MSG['mkdir'][$sysSession->lang], '', $sysSession->q_right?'class="cssBtn" onclick="mkdir();"':'class="cssBtn" disabled="disabled"');
							  if ($sysSession->username != 'guest')
							  {
								echo '&nbsp;&nbsp;',$MSG['from'][$sysSession->lang];
								showXHTML_input('text', 'st', '', '', 'class="cssInput" size="4" maxlength="4"');
								echo $MSG['to'][$sysSession->lang];
								showXHTML_input('text', 'en', '', '', 'class="cssInput" size="4" maxlength="4"');
								echo $MSG['batch'][$sysSession->lang];
								showXHTML_input('select', 'bd', $batch_do, '', 'class="cssInput" onchange="batch(this);"');
							  }
							showXHTML_td_E('');
						showXHTML_tr_E('');

						showXHTML_tr_B('class="cssTrHead"');
							showXHTML_td('width="30"  nowrap="nowrap" align="center"', $MSG['serial'][$sysSession->lang]);
							showXHTML_td('width="280" nowrap="nowrap" align="center"', generate_order_link('subject',	$MSG['subj'][$sysSession->lang] , $sysSession->q_sortby));
							showXHTML_td('width="140" nowrap="nowrap" align="center"', generate_order_link('poster',	$MSG['poster'][$sysSession->lang] , $sysSession->q_sortby)); //($sysSession->sortby == 'poster' )?('<b>'.$MSG['poster'][$sysSession->lang].$icon_up.'</b>'):('<a href="javascript:;" onclick="sortBy(3); return false;" class="cssAnchor" title="'.$MSG['order'][$sysSession->lang].'">'.$MSG['poster'][$sysSession->lang].'</a>'));
							showXHTML_td('width="140" nowrap="nowrap" align="center"', generate_order_link('pt', 		$MSG['time'][$sysSession->lang] , $sysSession->q_sortby)); //($sysSession->sortby == 'pt'     )?('<b>'.$MSG['time'][$sysSession->lang]  .$icon_dn.'</b>'):('<a href="javascript:;" onclick="sortBy(1); return false;" class="cssAnchor" title="'.$MSG['order'][$sysSession->lang].'">'.$MSG['time'][$sysSession->lang]  .'</a>'));
							showXHTML_td('width="50"  nowrap="nowrap" align="center"', generate_order_link('hit', 		$MSG['hit'][$sysSession->lang], $sysSession->q_sortby) ); //($sysSession->sortby == 'hit'    )?('<b>'.$MSG['hit'][$sysSession->lang]   .$icon_dn.'</b>'):('<a href="javascript:;" onclick="sortBy(4); return false;" class="cssAnchor" title="'.$MSG['order'][$sysSession->lang].'">'.$MSG['hit'][$sysSession->lang]   .'</a>'));
							showXHTML_td('width="90"  nowrap="nowrap" align="center"', generate_order_link('rank', 		$MSG['rank'][$sysSession->lang], $sysSession->q_sortby) ); //($sysSession->sortby == 'rank'   )?('<b>'.$MSG['rank'][$sysSession->lang]  .$icon_dn.'</b>'):('<a href="javascript:;" onclick="sortBy(5); return false;" class="cssAnchor" title="'.$MSG['order'][$sysSession->lang].'">'.$MSG['rank'][$sysSession->lang]  .'</a>'));
							showXHTML_td('width="40"  nowrap="nowrap" align="center"', $MSG['attach'][$sysSession->lang]);
						showXHTML_tr_E('');

						if (strlen($sysSession->q_path) > 1){
							$parent_path = dirname($sysSession->q_path);

							if($parent_path === '/')
								$up_dir = 'root';
							else {
								$parent_dir  = dirname($parent_path);
								$parent_name = basename($parent_path);
								list($up_dir) = dbGetStSr('WM_bbs_collecting','node',"board_id={$sysSession->board_id} and path='{$parent_dir}' and subject='{$parent_name}' and type='D'", ADODB_FETCH_NUM);
							}

							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col . " onclick=\"chdir('{$up_dir}');\"");
								showXHTML_td('align="center"', '&nbsp;');
								showXHTML_td('', '<a href="javascript:;" class="cssAnchor" onclick="chdir(\''.$up_dir . '\'); return false;"><b>..</b></a>');
								showXHTML_td('', '&nbsp;');
								showXHTML_td('', '&nbsp;');
								showXHTML_td('', '&nbsp;');
								showXHTML_td('', '&nbsp;');
								showXHTML_td('', '&nbsp;');
							showXHTML_tr_E('');
						}

						$i = $reg[1]=='561'?1:($cur_page + 1);

						$tdArr = Array();
						$nodeFilter = array();

						if (is_object($RS)) {
                            while(!$RS->EOF){
                                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';

                                $k = array_shift($RS->fields);
                                $tdArr[$k] = (($RS->fields['type'] == 'F') ? $RS->fields : array_slice($RS->fields, 0, 2)) +
                                             array('color' => $col);

                                if ($RS->fields['type'] == 'F') $nodeFilter[] = "'" . $k ."'";
                                $RS->MoveNext();
                            }
                        }
						if(count($nodeFilter)>0) {
							$nodeWhere = "type='q' and board_id={$sysSession->board_id} And node in (" . implode(",",$nodeFilter) . ")";
							$RS1 = dbGetStMr('WM_bbs_readed','node,read_time',$nodeWhere, ADODB_FETCH_ASSOC);
							if (is_object($RS1)) {
                                while(!$RS1->EOF) {
                                    $tdArr[$RS1->fields['node']]['read_time'] = $RS1->fields['read_time'];
                                    $RS1->MoveNext();
                                }
                            }
						}
                        $i = $reg[1]=='501'?1:($cur_page + 1);
                        if ($isFaq === true) {
                            // 取得內容網址
                            $nodeWhere2 = "type='F' and board_id={$sysSession->board_id} And node in (" . implode(",",$nodeFilter) . ")";
                            $rsUrl = dbGetStMr('`WM_bbs_collecting`', '`node`, `content`', '`board_id`='.$sysSession->board_id .' and ' . $nodeWhere2, ADODB_FETCH_ASSOC);
                            if ($rsUrl) {
                                while(!$rsUrl->EOF) {
                                    $tdArr[$rsUrl->fields['node']]['url_link'] = $rsUrl->fields['content'];
                                    $rsUrl->MoveNext();
                                }
                            }
                        }
						if(count($tdArr)>0)
						{
						   foreach($tdArr as $k=>$v){
							if ($v['type'] == 'D'){
								showXHTML_tr_B($v['color'] . ' onclick="chdir(\''.$k.'\');"');
									showXHTML_td('align="center"', $i++);
									if($sysSession->q_right)
										showXHTML_td('oncontextmenu="editCell(this, \''. $k .'\', \''. htmlspecialchars($v['subject']) .'\'); return false;"', '<span style="width: 280px; overflow: hidden"><a href="' . ($k).'.htm" onclick="chdir(\''.$k.'\');" class="cssAnchor" target="empty" title="'. htmlspecialchars($v['subject']) . '  ">' . $v['subject'] . '/</a></span>');
									else
										showXHTML_td('', '<span style="width: 280px; overflow: hidden"><a href="' . ($k).'.htm" onclick="return false;" class="cssAnchor" target="empty" title="'. htmlspecialchars($v['subject']) . '  ">' . $v['subject'] . '/</a></span>');

									showXHTML_td('', '&nbsp;');
									showXHTML_td('', '&nbsp;');
									showXHTML_td('', '&nbsp;');
									showXHTML_td('', '&nbsp;');
									// showXHTML_td('align="center"', '<input type="button" value="'.$MSG['btn_del'][$sysSession->lang].'" class="cssBtn" ' . ($sysSession->q_right?'onclick="rmdir(\''.$v['subject'].'\');':'disabled="disabled"'). ' event.cancelBubble=true;"');
									// Bug#1395 增加「更名」的button by Small 2006/10/17
									showXHTML_td_B('align="center"');
										// 無權限的user會將button disabled 掉
										$sysSession->q_right? $disabled='' : $disabled='disabled';
										showXHTML_input('button', $k,$MSG['btn_rename'][$sysSession->lang] , '', ' id="'.$k.'" name="'.$v['subject'].'" class="cssBtn" '.$disabled.' onclick="renameDir(this.id,this.name); event.cancelBubble=true;"');
										showXHTML_input('button', 'btn_del',$MSG['btn_del'][$sysSession->lang] , '', ' id="btn_del" name="" class="cssBtn"'.($sysSession->q_right?'onclick="rmdir(\''.$v['subject'].'\');':'disabled="disabled"'). ' event.cancelBubble=true;"');
									showXHTML_td_E('');
							}
							else{
                                
                                // 常見問題的連結改為外連
                                if ($isFaq === true && $_SERVER['argv'] !== null && $_SERVER['argv'][0] == md5('faq')) {
                                    $urlLink = ' onclick="location.replace(\''.trim(strip_tags($v['url_link'])).'\');"';
                                } else {
                                    $urlLink = ' onclick="read('.($cur_post+$i).');"';
                                }
								$img_new = empty($v['read_time'])?"&nbsp;&nbsp;<img src=/theme/{$sysSession->theme}/learn/new.gif>":"";

								showXHTML_tr_B($v['color'] . $urlLink);
									showXHTML_td('nowrap="nowrap" align="center"', $i++);
									showXHTML_td('width="280" style="word-break: break-all;"', indent($k, $sysSession->q_sortby).'<a href="q' . $sysSession->board_id . ($k).'.htm" onclick="return false;" class="cssAnchor" target="empty" title="'. htmlspecialchars($v['subject']) . '  ">' . $v['subject'] . '</a>'.$img_new);
									showXHTML_td('width="140"', "<a href=\"mailto:{$v['email']}\" class=\"cssAnchor\" onclick=\"event.cancelBubble=true;\">{$v['poster']}</a> ".($v['homepage']?("(<a href=\"{$v['homepage']}\" target=\"_blank\" class=\"cssAnchor\" onclick=\"event.cancelBubble=true;\">{$v['realname']}</a>)"):"({$v['realname']})"));
									showXHTML_td('nowrap="nowrap" align="center"', $v['pt']);
									showXHTML_td('nowrap="nowrap" align="right"', $v['hit']);
									showXHTML_td('nowrap="nowrap" align="center"', $v['rank'].' / '.$v['rcount']);
									showXHTML_td('', generate_attach_link(get_attach_file_path('quint', $sysSession->board_ownerid).DIRECTORY_SEPARATOR.$k, $v['attach'],'quint'));
							}
							showXHTML_tr_E('');

						   }
						}

						showXHTML_tr_B('class="cssTrEvn"');
							showXHTML_td('align="right"',$MSG['page1'][$sysSession->lang]);
							showXHTML_td_B('nowrap="nowrap" colspan="7" id="tb2"');
							showXHTML_td_E('');
						showXHTML_tr_E('');

					showXHTML_table_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
	showXHTML_form_E('');

	// 匯入文章
	$ary = array();
	$ary[] = array($MSG['import'][$sysSession->lang], 'import_ui');
	// $colspan = 'colspan="2"';
	showXHTML_tabFrame_B($ary, 1, 'form_import', 'import_ui', 'action="q_import.php" style="display: inline" method="post" enctype="multipart/form-data"', true);
        showXHTML_input('hidden','token',$csrfToken,'','');
		showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
			showXHTML_tr_B('class="cssTrEvn"');
				showXHTML_td('nowrap="nowrap"', $MSG['import_note'][$sysSession->lang]);
			showXHTML_tr_E('');
			showXHTML_tr_B('class="cssTrOdd"');
				showXHTML_td_B();
				showXHTML_input('file','file_import','','','class="cssInput"');
				showXHTML_input('button','btnExp',$MSG['import'][$sysSession->lang],'','onclick="OnImportButton(true);"');
				showXHTML_input('button','btnExp',$MSG['cancel'][$sysSession->lang],'','onclick="OnImportButton(false);"');
				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E();
	showXHTML_tabFrame_E();

	// 整版匯入
	$ary = array();
	$ary[] = array($MSG['import_all'][$sysSession->lang], 'importall_ui');
	echo '<div align="center">';
	showXHTML_tabFrame_B($ary, 1, 'form_importall', 'importall_ui', 'action="import_all.php" style="display: inline" method="post" enctype="multipart/form-data"', true);
		showXHTML_input('hidden','ticket',$import_ticket);
		showXHTML_table_B(' width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
			showXHTML_tr_B('class="cssTrEvn"');
				showXHTML_td('nowrap="nowrap"', $MSG['import_note1'][$sysSession->lang]);
			showXHTML_tr_E('');
			showXHTML_tr_B('class="cssTrOdd"');
				showXHTML_td_B();
					showXHTML_input('file','file_import','','','class="cssInput" size="50"');
				showXHTML_td_E();
			showXHTML_tr_E('');
			if (strlen($sysSession->board_ownerid)==8) {	// 只有課程討論版可以新建
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
				showXHTML_td('', nl2br($MSG['qonly_note'][$sysSession->lang]));
			showXHTML_tr_E('');
			showXHTML_tr_B('class="cssTrEvn"');
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
    
    // 討論版設定
    showXHTML_form_B('action="" method="post" style="display:none"', 'board_setting');
        showXHTML_input('hidden', 'bid', $sysSession->board_id);
        showXHTML_input('hidden', 'ticket', ($bulletin == $sysSession->board_id) ? md5(sysTicketSeed . 'setAnnt' . $_COOKIE['idx']) : md5(sysTicketSeed . 'setBoard' . $_COOKIE['idx']));
        showXHTML_input('hidden', 'nid', $nid);
	showXHTML_form_E();

	$ary = array();
	$ary[] = array($MSG['del'][$sysSession->lang] . $MSG['confirm'][$sysSession->lang], 'folderConfirm');
	// $colspan = 'colspan="2"';
	showXHTML_tabFrame_B($ary, 1, '', 'folderConfirm', 'style="display: inline;"', true);
		showXHTML_table_B(' width="100%" border="0" cellspacing="1" cellpadding="3" id="tabs2" class="cssTable"');
			showXHTML_tr_B('class="cssTrHead"');
				showXHTML_td('colspan="2" nowrap="nowrap" id="helpMsg"', $MSG['csv_note'][$sysSession->lang]);
			showXHTML_tr_E('');
			showXHTML_tr_B('class="cssTrEvn"');
				showXHTML_td('nowrap="nowrap" id="confirm_str"', '&nbsp;');
			showXHTML_tr_E('');
			showXHTML_tr_B('class="cssTrOdd"');
				showXHTML_td_B('align="center"');
					showXHTML_input('button','btnOK',$MSG['ok'][$sysSession->lang],'','onclick="do_rmdir()"');
					showXHTML_input('button','btnCancel',$MSG['cancel'][$sysSession->lang],'','onclick="displayFolderConfirm(false)"');
				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E('');
	showXHTML_tabFrame_E();
	  echo <<< EOB
<div id="inputBox" style="position: absolute; display: none">
  <form style="display: inline" onsubmit="return false;">
	<input type="hidden" name="token" value="{$csrfToken}">
    <input type="hidden" name="folder_id" value="">
	<input type="text" maxlength="255" style="border:2px orange solid; font-size: 16px">
  </form>
</div>

EOB;

	  $scr = <<< EOB
folder_cell = null;
/**
 * 編輯資料夾名稱
 */
function editCell(cell , folder_id, folder_name){

	folder_cell = cell;
	var lst     = document.getElementById('tblList');
	var obj     = document.getElementById('inputBox');
	var frm     = obj.getElementsByTagName('form')[0];
	var ipbox   = obj.getElementsByTagName('input')[1];

	frm.folder_id.value = folder_id;

	xx                 = cell.cellIndex;
	yy                 = cell.parentNode.rowIndex;
	ipbox.style.width  = cell.offsetWidth  - (isMZ ? 1 : 0);
	ipbox.style.height = cell.offsetHeight - (isMZ ? 1 : 0);
	obj.style.left     = cell.offsetLeft + tblOfficeX;
	obj.style.top      = cell.offsetTop  + tblOfficeY;
	obj.style.display  = '';
	ipbox.value        = folder_name;
	ipbox.focus();
	ipbox.select();
}

// 過濾User輸入的目錄名稱
function checkname(name){
	if (name == '') return false;

	if (name.search(/[\\\/:\*\?"<>\|%#+]/g) > -1)
	{
		alert('{$MSG['include_illegal_char'][$sysSession->lang]}');
		return false;
	}

	return true;
}

/**
 * 使用『prompt提示視窗』
 * 過濾User輸入的目錄名稱
 * 呼叫q_index.js中rename_dir()做更名的動作
 **/
function renameDir(folder_id,folder_name){
	// alert (folder_name);
	// 新目錄名稱
	var ret = prompt('{$MSG['new_folder_name'][$sysSession->lang]}', folder_name);
	if (ret != null && checkname(ret)){
		rename_dir(folder_id,ret);
	}
}

function remove_blank(s) {
	var re = /[ ]+/g;
	return s.replace(re, '');
}

var key_ENTER = 13;                                             // ENTER 按鈕值
var key_ESC   = 27;                                             // ESC 按鈕值
/**
 * 取得 USER 在編修資料夾名稱時，按鍵事件
 */
document.getElementById('inputBox').getElementsByTagName('input')[1].onkeydown=function (e){
	var key_code = isMZ ? e.keyCode : event.keyCode;
	var obj = document.getElementById('tblList');
	var frm = this.form;

	switch(key_code){
		case key_ENTER: // 按了 Enter
			v = remove_blank(this.value);
			if(v=='') {
				window.status = MsgFolder + MsgNameCantBlank + '!';
				this.select(); return false;
			}
			if (isIncludePunct(v)){
				alert("'" + this.value + "'" + MsgIllegalChar + "!");
				return false;
			}
			document.getElementById('inputBox').style.display = 'none';
			folder_id = frm.folder_id.value;
			rename_dir(folder_id, this.value);
			break;
		case key_ESC: // 按了 Esc
			document.getElementById('inputBox').style.display = 'none';
			break;
	}
	if(isMZ)
		e.cancelBubble=true;
	else
		event.cancelBubble=true;
	window.status = '';
}
EOB;
	  showXHTML_script('inline', $scr);
	  /*Chrome*/
	showXHTML_script('inline', "
	if (typeof(opener) != 'undefined' && typeof(opener) != 'object') {
		document.write('<p align=center><input type=button value=\"{$MSG['close_window'][$sysSession->lang]}\" onclick=\"window.close();\" class=cssBtn></p>');
	} else if (parent == self) {
		if ('".(defined('sysEnableMooc') && sysEnableMooc > 0)."' === '1') {
			document.write('<p align=center><input type=button value=\"{$MSG['return_home'][$sysSession->lang]}\" onclick=\"top.location.replace(\'/mooc/index.php\');\" class=cssBtn></p>');
		} else {
			document.write('<p align=center><input type=button value=\"{$MSG['return_home'][$sysSession->lang]}\" onclick=\"top.location.replace(\'/logout.php\');\" class=cssBtn></p>');
		}
	}
");
	showXHTML_body_E('');
?>
