<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lib_forum.php');
	require_once(sysDocumentRoot . '/lib/lib_newsfaq.php');
	require_once(sysDocumentRoot . '/lang/forum.php');
	require_once(sysDocumentRoot . '/forum/file_api.php');
	require_once(sysDocumentRoot . '/forum/lib_collect.php');
	require_once(sysDocumentRoot . '/forum/order.inc.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');

	$sysSession->cur_func = '900201100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 各項排序依據
	$OB = $OrderBy['board'];

	/****************************
		調整頁數並回到 index.php
	 ****************************/
	function GotoIndex() {
		global $sysSession;

		// 頁數調整
		$where      = getSQLwhere($is_search);			// 取得 SQL 過濾條件
		$total_post = getTotalPost($where);				// 取得本板張貼數
		$rows_page  = GetForumPostPerPage();			// 取得一頁幾筆
		$total_page = ceil($total_post / $rows_page); 	// 計算總共有幾頁
		if($sysSession->page_no>$total_page)
			$sysSession->page_no = $total_page;

		$sysSession->restore();

		header('Location: index.php');
		exit();
	}

	/***
	 *	取得此次指定範圍(v1 ~ v2)的 SQL 語法
	 ***/
	function GetSQL($v1, $v2) {
		global $sysSession, $OB;

		$get_post_list = 'select node,subject,pt,poster,realname,email,homepage,attach,rcount,rank,hit ' .
						 'from WM_bbs_posts where board_id=%d order by %s limit %d,' . sysPostPerPage;
		$sqls = sprintf($get_post_list, $sysSession->board_id, $OB[$sysSession->sortby], $cur_page);

		if(isset($_COOKIE['forum_search'])) {	// 先前曾紀錄搜尋條件
			$where = isset($_COOKIE['forum_search'])?stripslashes($_COOKIE['forum_search']):'';
			$sqls = ereg_replace('where .* order', "where board_id={$sysSession->board_id} $where order", $sqls);
		}

		// 範圍為$v1 ~ $v2，把 limit 換掉
		$count = $v2 - $v1 + 1;
		$v1--;	// mySQL 以 0 為第一篇
		$sqls = ereg_replace('limit .*$', "limit $v1,$count", $sqls);
		return $sqls;
	}

	/***
	 *	"刪除"處理程式
	 *	參數: $v1 $v2 : 刪除範圍
	 */
	function do_delete($v1, $v2) {
		global $sysSession, $sysConn, $ADODB_FETCH_MODE;

	   	$sql = GetSQL($v1, $v2);
		$sql = ereg_replace('select .* from', 'select node,site,poster from', $sql);
	   	//echo $sql."<br>\r\n";
	   	$board_id = $sysSession->board_id;
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$RS = $sysConn->Execute($sql);
		while(!$RS->EOF)
		{
			$node   = $RS->fields['node'];
			$site   = $RS->fields['site'];
			$poster = $RS->fields['poster'];

			if(($poster==$sysSession->username) || $sysSession->b_right) {	// 檢查權限
				delete_post($board_id, $node, $site);	// 在 /lib/lib_forum.php 中
			}

			$RS->MoveNext();
		}

		wmSysLog($sysSession->cur_func, $board_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], "Bulletin batch delete:{$v1}->{$v2}");

		GotoIndex();
	}

	/****************************************************
	 *	"收入筆記本"處理程式
	 *	參數: $v1 $v2 : 範圍
	 *	      $folder : 筆記本資料夾
	 ****************************************************/
	function do_notebook($v1, $v2, $folder) {
		global $sysSession, $sysConn, $MSG, $ADODB_FETCH_MODE;

	   	$board_id = $sysSession->board_id;
	   	$sql = GetSQL($v1, $v2);
		$sql = ereg_replace('select (.*) from', 'select node,site,poster,pt,realname,email,homepage,subject,attach,content from', $sql);
	   	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$RS = $sysConn->Execute($sql);

		$base_path   = get_attach_file_path('board', $sysSession->board_ownerid);
		$target_path = MakeUserDir($sysSession->username);

		$index = intval($v1);	// 第幾則 ( 收入失敗時告知用 )
		$failed_msgs = array();	// 失敗訊息

		while(!$RS->EOF)
		{
			$node   = $RS->fields['node'];
			$site   = $RS->fields['site'];
			$poster = $RS->fields['poster'];

			$subject = mysql_escape_string($RS->fields['subject']);
			$content = mysql_escape_string(nb_recompose($RS));

			$from_path = $base_path . DIRECTORY_SEPARATOR . $node;

			$attach = '';

			// 複製檔案
			if(!b_copyfiles( $from_path , $target_path , trim($RS->fields['attach']), $attach))
			{
				$txt = $MSG['copyfile_th'][$sysSession->lang] .$index .$MSG['copyfile_fail'][$sysSession->lang];
				$failed_msgs[] = $txt;
			} else {
				$fields = '`folder_id`, `sender`, `receiver`, `submit_time`, `receive_time`, `priority`, ' .
					'`subject`, `content`, `attachment`, `note`, `content_type`';
				$values = "'{$folder}','{$sysSession->username}', '{$sysSession->username}', ".
					"Now(), Now(), 0, '{$subject}', '{$content}', " .
					"'{$attach}', '', 'html'";

				if(!dbNew('WM_msg_message', $fields, $values)) {
					$txt = $MSG['savedb_th'][$sysSession->lang] .$index .$MSG['savedb_fail'][$sysSession->lang];
					$failed_msgs[] = $txt;
					nb_rollback_files( $target_path, $attach );
					wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], '新增到訊息中心失敗(do_notebook)：' . $sysSession->username . '|' . $sysSession->username . '|' . $subject);
				}
			}

			$RS->MoveNext();
			$index++;
		}

		$js_txt = '';
		if(count($failed_msgs) > 0) {	// 有錯誤發生
			echo "<!-- failed ";
			print_r($failed_msgs);
			echo " -->\r\n";

			foreach($failed_msgs as $k=>$v) {
				$js_txt .= $v . "\\n";
			}
		} else {
			$js_txt = $MSG['notebook_success'][$sysSession->lang];
		}

		wmSysLog($sysSession->cur_func, $board_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], "Bulletin batch notebook: {$v1}->{$v2}=>{$folder}. {$js_txt}");

		$js = "alert('" . $js_txt . "');\r\n";
		$js .= "location.replace('index.php');\r\n";
		showXHTML_script('inline',$js);
		exit();

	}

	// 重組文章內容
	function nb_recompose(&$RS)
	{
		global $sysSession, $MSG;
		ob_start();

		showXHTML_table_B('border="0" cellspacing="1" cellpadding="3" class="cssTable"');
		showXHTML_tr_B('class="cssTrOdd"');
			showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['bname'][$sysSession->lang]);
			showXHTML_td('width="640"', $sysSession->board_name);
		showXHTML_tr_E();

		showXHTML_tr_B('class="cssTrOdd"');
			showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['posters'][$sysSession->lang]);
			showXHTML_td('width="640"', "<a href=\"mailto:{$RS->fields['email']}\" class=\"cssAnchor\">{$RS->fields['poster']}</a> ".($RS->fields['homepage']?("(<a href=\"{$RS->fields['homepage']}\" target=\"_blank\" class=\"cssAnchor\">{$RS->fields['realname']} </a> )"):"({$RS->fields['realname']} )"));
		showXHTML_tr_E();
		showXHTML_tr_B('class="cssTrEvn"');
			showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['times'][$sysSession->lang]);
			showXHTML_td('width="640"', $RS->fields['pt']);
		showXHTML_tr_E();
		showXHTML_tr_B('class="cssTrOdd"');
			showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['subjs'][$sysSession->lang]);
			showXHTML_td('width="640"',$RS->fields['subject']);
		showXHTML_tr_E();
		showXHTML_tr_B('class="cssTrEvn"');
			showXHTML_td('align="right" nowrap="nowrap" width="120"', $MSG['contents'][$sysSession->lang]);
			showXHTML_td('width="640"','<table><tr><td><br />'.$RS->fields['content'].'<p /></td></tr></table>');
		showXHTML_tr_E();
		showXHTML_table_E();

		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	// 自討論版複製實體檔案到筆記本資料夾中
	// 參數: $to_path   : 目的路徑   ( 尾端不含 '/' )
	//	 $attach    : 新夾檔字串 ( '名稱'[TAB]'實體檔名'[TAB]'名稱'[TAB]'實體檔名'[TAB]... )
	// 傳回值:
	//	   無
	function nb_rollback_files( $to_path, $attach ) {
		$files  = explode(Chr(9), $attach);	// 原夾檔字串
		if(count($files)==0) return;		// 無夾檔

		for($i=0;$i<count($files);$i+=2) {
			$path = $to_path . "/" . $files[$i+1];
			unlink($path);
		}
	}

	/****************************************************
	 *	"收(移)入精華區"處理程式
	 *	參數: $v1 $v2 : 範圍
	 *		  $folder_id : 精華區目錄 node 編號
	 *		  $is_move: 複製(false) 或 搬移(true)
	 ****************************************************/
	function do_copy($v1, $v2, $folder_id,$is_move=false) {
		global $sysSession, $sysConn, $MSG, $err_msg, $err_id, $ADODB_FETCH_MODE;

	   	$sql = GetSQL($v1, $v2);
		$sql = ereg_replace('select .* from', "select node,site from", $sql);
	   	$board_id = $sysSession->board_id;

		$index = IntVal($v1);	// 第幾則 ( 收入失敗時告知用 )
		$failed_msgs = Array();	// 失敗訊息

		// 取出該資料夾
		if($folder_id) {
			$folder_rs = dbGetStSr('WM_bbs_collecting' , 'path, subject' ,"board_id={$sysSession->board_id} and node='{$folder_id}' and type='D'", ADODB_FETCH_ASSOC);
			if(!$folder_rs) {
					$js = <<<EOB
	alert('query folder failed!\nboard_id={$sysSession->board_id} and node={$folder_id}');
	location.replace( 'index.php' );
EOB;
					showXHTML_script('inline', $js);
					exit();
			}
			$folder = ($folder_rs['path']=='/'?'':$folder_rs['path'])."/{$folder_rs['subject']}";
		} else {
			$folder = '/';
		}

		if($sysSession->q_right) {	// 檢查權限
		    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
			$RS = $sysConn->Execute($sql);

			// 先跑主節點
			while(!$RS->EOF)
			{
				$node = $RS->fields['node'];
				$site = $RS->fields['site'];
				$new_node = 0;

					// 收入精華區
					$ret = do_collect($board_id, $node, $site, $new_node, $folder, $is_move);
					if($ret!=0) {	// 0 為成功 ( 見 lib_collect.php )
						$txt = $MSG['savedb_th'][$sysSession->lang] .$index .$MSG['th'][$sysSession->lang].$err_msg[$ret];
						//$txt = "第{$index}則存入資料庫時失敗!";
						$failed_msgs[] = $txt;
					}



				$RS->MoveNext();
				$index++;
			}

			// 處理最新消息
			if($is_move && $sysSession->news_board) {
				// 更新最新消息列表
				if(IsNewsBoard('news'))
					createNewsXML($sysSession->school_id, 'news');
			}
		} else {
			$failed_msgs[] = $MSG['msg_move_4'];	// 權限不足
		}

		$js_txt = ($is_move?$MSG['move_q'][$sysSession->lang]:$MSG['copy_q'][$sysSession->lang]);
		if(count($failed_msgs) > 0) {	// 有錯誤發生
			$js_txt .= "\\n";
			foreach($failed_msgs as $k=>$v) {
				$js_txt .= $v . "\\n";
			}
		} else {
			$js_txt .= $MSG['success_to'][$sysSession->lang] . "!" ;
		}

		wmSysLog($sysSession->cur_func, $board_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], 'Bulletin batch ' . ($is_move ? 'move' : 'copy') . ": {$v1}->{$v2}=>{$folder_id}");

		$js = "alert('" . $js_txt . "');\r\n";
		$js .= "location.replace('index.php');\r\n";
		showXHTML_script('inline',$js);
		exit();
	}


	/****************************************************
	 *	主要程式
	 *
	 ****************************************************/
	if (ereg('^53([0-9]),([0-9]{10}),([0-9]+),([0-9]+)\.php$', basename($_SERVER['PHP_SELF']), $reg) &&
	    $reg[2] == $sysSession->board_id
	   ){
	   $v1 = IntVal($reg[3]);
	   $v2 = IntVal($reg[4]);
	   /* #56417 (B) 因 v1 為 0 時，會造成 sql 指令(limit -1)出錯  2015/02/04 BY Spring */
	   if ($v1 < 1) {
			$js = "alert('Error Post No !');\r\n";
			$js .= "location.replace('index.php');\r\n";
			showXHTML_script('inline',$js);
			exit();
	   }
	   /* #56417 (E) */
	   switch($reg[1]) {
	   	case '0': // 530 整批刪除
		   	do_delete($v1,$v2);
		   	break;
	   	case '1': // 531 整批收入個人筆記本
	   		if(isset($_GET['folder']))
		   		do_notebook($v1,$v2, $_GET['folder']);
		   	break;
	   	case '2': // 532 整批收入精華區( Copy )
		   	do_copy($v1,$v2, $_GET['folder_id']);
		   	break;
	   	case '3': // 533 整批移入精華區( Move )
		   	do_copy($v1,$v2, $_GET['folder_id'],true);
		   	break;
	   }
	}
?>
