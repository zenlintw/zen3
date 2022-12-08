<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lib_forum.php');
	require_once(sysDocumentRoot . '/lib/lib_newsfaq.php');
	require_once(sysDocumentRoot . '/lang/forum.php');
	require_once(sysDocumentRoot . '/forum/file_api.php');
	require_once(sysDocumentRoot . '/forum/lib_notebook.php');
	require_once(sysDocumentRoot . '/forum/order.inc.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '900301000';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 各項排序依據
	$OB = $OrderBy['quint'];

	/****************************
		調整頁數並回到 index.php
	 ****************************/
	function GotoIndex() {
		global $sysSession;

		// 頁數調整
		$where      = getSQLwhere($is_search, 'quint'); // 取得 SQL 過濾條件
		$total_post = getTotalPost($where, 'quint');    // 取得本板張貼數
		$rows_page  = GetForumPostPerPage();            // 取得一頁幾筆
		$total_page = ceil($total_post / $rows_page);   // 計算總共有幾頁
		if($sysSession->q_page_no>$total_page)
			$sysSession->q_page_no = $total_page;

		$sysSession->restore();

		header('Location: q_index.php');
		exit();
	}

	/***
	 *	取得此次指定範圍(v1 ~ v2)的 SQL 語法
	 ***/
	function GetSQL($v1, $v2) {
		global $sysSession, $OB;

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

	/***
	 *	"刪除"處理程式
	 *	參數: $v1 $v2 : 刪除範圍
	 */
	function do_delete($v1, $v2) {
		global $sysSession, $sysConn;

	   	$sql              = GetSQL($v1, $v2);
		$sql              = ereg_replace('select .* from', "select node,site,path,subject,type,poster,picker,post_node from", $sql);
	   	$board_id         = $sysSession->board_id;
        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$RS               = $sysConn->Execute($sql);
		while(!$RS->EOF)
		{
			$node     = $RS->fields['node'];
			$site     = $RS->fields['site'];
			$path     = $RS->fields['path'];
			$subject  = $RS->fields['subject'];
			$type     = $RS->fields['type'];
			$poster   = $RS->fields['poster'];
			$picker   = $RS->fields['picker'];
			$post_node= $RS->fields['pose_node'];

			if(($picker==$sysSession->username) || $sysSession->q_right) {	// 檢查權限
				if($type=='D') {
					DelFolder($board_id, ($path=='/'?'':$path). "/{$subject}");
				}
				else if($type=='F')
					delete_qost($board_id, $node, $site, $post_node);
			}

			$RS->MoveNext();
		}

		wmSysLog($sysSession->cur_func, $board_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], "Essential batch delete: {$v1}->{$v2}");

		// 更新學習中心常見問題列表
		if(IsNewsBoard('faq'))
				createFAQXML($sysSession->school_id, 'faq');
		header('Location: q_index.php');
	}


	/****************************************************
	 *	"收入筆記本"處理程式
	 *	參數: $v1 $v2 : 範圍
	 *	      $folder : 筆記本資料夾編號(folder id)
	 ****************************************************/
	function do_notebook($v1, $v2, $folder) {
		global $sysSession, $sysConn, $MSG;
		global $nodeTARGET, $sysLang;

	   	$board_id         = $sysSession->board_id;
	   	$sql              = GetSQL($v1, $v2);
		$sql              = ereg_replace('select (.*) from', 'select node,site,path,poster,type,pt,realname,email,homepage,subject,attach,content from', $sql);
	   	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$RS               = $sysConn->Execute($sql);

		$base_path        = get_attach_file_path('quint', $sysSession->board_ownerid);
		$target_path      = MakeUserDir($sysSession->username);

		$index            = intval($v1);	// 第幾則 ( 收入失敗時告知用 )
		$failed_msgs      = array();	// 失敗訊息

		$res              = nb_makeFolders($RS, $folder);
		if ($res === false) exit();
		$RS->MoveFirst();

		$nb_folder_id = $folder;

		while(!$RS->EOF)
		{
			$node    = $RS->fields['node'];
			$site    = $RS->fields['site'];
			$poster  = $RS->fields['poster'];
			$type    = $RS->fields['type'];

			$subject = mysql_escape_string($RS->fields['subject']);
			if($type=='D')	{ // 資料夾
				$q_path = ($sysSession->q_path=='/'?'':$sysSession->q_path).'/'.$subject;
				$ctx    = xpath_new_context($nodeTARGET);

				$filter = "/folder/title/{$sysLang}[text()='" . $RS->fields['subject'] . "']/parent::*/parent::*";

				$foo = xpath_eval($ctx, $filter);
				if(count($foo->nodeset)>0) {
					if(!nb_copyFolderFiles($target_path, $base_path,$q_path, $foo->nodeset[0])) {
						$txt = $MSG['copyfile_th'][$sysSession->lang] .$index .$MSG['copyfile_fail'][$sysSession->lang]. '(2)';
						$failed_msgs[] = $txt;
					}
				} else {
					$txt = $MSG['copyfile_th'][$sysSession->lang] .$index .$MSG['copyfile_fail'][$sysSession->lang]. '(3)';
					$failed_msgs[] = $txt;
				}
				$RS->MoveNext();
				continue;
			}

			$content   = mysql_escape_string(nb_recompose($RS));
			$from_path = $base_path . '/' . $node;
			$attach    = '';

			// 複製檔案
			if(!b_copyfiles( $from_path , $target_path , trim($RS->fields['attach']), $attach))
			{
				$txt = $MSG['copyfile_th'][$sysSession->lang] .$index .$MSG['copyfile_fail'][$sysSession->lang].'(1)';
				$failed_msgs[] = $txt;
			} else {
				$fields = '`folder_id`, `sender`, `receiver`, `submit_time`, `receive_time`, `priority`, ' .
						  '`subject`, `content`, `attachment`, `note`, `content_type`';
				$values = "'{$folder}','{$sysSession->username}', '{$sysSession->username}', ".
						  "Now(), Now(), 0, '{$subject}', '{$content}', " .
						  "'{$attach}', '', 'html'";

				if(!dbNew('WM_msg_message', $fields, $values)) {

					$txt = $MSG['savedb_th'][$sysSession->lang] .$index .$MSG['savedb_fail'][$sysSession->lang];
					//$txt = "第{$index}則存入資料庫時失敗!";
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

			foreach($failed_msgs as $k=>$v) {
				$js_txt .= $v . "\\n";
			}
		} else {
			$js_txt = $MSG['notebook_success'][$sysSession->lang];
		}

		wmSysLog($sysSession->cur_func, $board_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], "Essential batch notebook: {$v1}->{$v2}=>{$folder}. {$js_txt}");

		$js = "alert('" . $js_txt . "');\r\n";
		$js .= "location.replace('q_index.php');\r\n";
		showXHTML_script('inline',$js);
		exit();
	}


	/****************************************************
	 *	"移入精華區"處理程式
	 *	參數: $v1 $v2 : 範圍
	 ****************************************************/

	$move_err_msg = Array(-1 => $MSG['msg_move_1'][$sysSession->lang],
						  -2 => $MSG['copyfile_fail1'][$sysSession->lang].' '.
								$MSG['db_busy'][$sysSession->lang].' '.
								$MSG['try_later'][$sysSession->lang],
						  -3 => $MSG['msg_move_2'][$sysSession->lang]
						);

	/***
	 *	"搬移"檢查程式 ( 1.避免交錯搬移, 2.資料夾重複 )
	 *	參數: $RS : 搬移範圍之資料
	 *		  $folder : 搬移目標資料夾
	 */
	function chk_move(&$RS, $folder) {
		global $sysSession, $sysConn;

		while(!$RS->EOF)
		{
			$type = $RS->fields['type'];
			if($type=='D') {
				$node    = $RS->fields['node'];
				$site    = $RS->fields['site'];
				$path    = $RS->fields['path'];
				$subject = $RS->fields['subject'];

				if(strpos( $folder, ($path=='/'?'':$path)."/$subject")===0)
					return -1;	// 交錯放置

				// 檢查目的資料夾是否已存在
				$RS1 = dbGetStSr('WM_bbs_collecting','count(*) as total',
								 "board_id='{$sysSession->board_id}' and path='$folder' and subject='{$subject}' and type='D'", ADODB_FETCH_ASSOC);
				if(!$RS1)
					return -2;	// 查詢失敗

				if($RS1['total'] != 0)
					return -3;	// 資料夾已存在
			}
			$RS->MoveNext();
		}
		$RS->MoveFirst();
		return 0;
	}


	/***
	 *	"搬移"處理程式
	 *	參數: $v1 $v2 : 搬移範圍
	 *		  $folder_id : 搬移目標資料夾節點編號(即 node)
	 */
	function do_move($v1, $v2, $folder_id) {
		global $sysSession, $sysConn, $move_err_msg;

	   	$sql              = GetSQL($v1, $v2);
		$sql              = ereg_replace('select .* from', 'select node,site,path,subject,type,picker from', $sql);
	   	$board_id         = $sysSession->board_id;
	   	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
		$RS               = $sysConn->Execute($sql);

		// 取出該資料夾
		if($folder_id!=='0') {
			$folder_rs = dbGetStSr('WM_bbs_collecting' , 'path, subject' ,"board_id={$sysSession->board_id} and node='{$folder_id}' and type='D'", ADODB_FETCH_ASSOC);
			if(!$folder_rs) {
					$js = <<<EOB
		alert('{$move_err_msg[-2]}');
		location.replace( 'q_index.php' );
EOB;
					showXHTML_script('inline', $js);
					exit();
			}

			$folder = ($folder_rs['path']=='/'?'':$folder_rs['path'])."/{$folder_rs['subject']}";
		} else {
			$folder = '/';
		}

		$chk_result = chk_move($RS, $folder);
		if($chk_result != 0) {

				$js = <<< EOB
	alert('{$move_err_msg[$chk_result]}');
	location.replace( 'q_index.php' );
EOB;
				showXHTML_script('inline', $js);
				exit();
		}

		while(!$RS->EOF)
		{
			$node    = $RS->fields['node'];
			$site    = $RS->fields['site'];
			$path    = $RS->fields['path'];
			$subject = $RS->fields['subject'];
			$type    = $RS->fields['type'];

			$picker  = $RS->fields['picker'];

			if(($picker==$sysSession->username) || $sysSession->q_right) {	// 檢查權限
				if($type=='D') {
					MoveFolder($board_id, $path, $subject, $folder);
				} else {
					dbSet('WM_bbs_collecting', "path='$folder'",  "board_id='{$board_id}' and node='{$node}' and site={$site} and path='{$path}'");
				}
			}

			$RS->MoveNext();
		}

		wmSysLog($sysSession->cur_func, $board_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], "Essential batch move: {$v1}->{$v2}=>{$folder}");

		$where      = getSQLwhere($is_search, 'quint'); // 取得 SQL 過濾條件
		$total_post = getTotalPost($where, 'quint');    // 取得本板張貼數
		$rows_page  = GetForumPostPerPage();            // 取得一頁幾筆
		$total_page = ceil($total_post / $rows_page);   // 計算總共有幾頁

		$sysSession->q_path    = $folder;
		$sysSession->q_page_no = $total_page;
		$sysSession->restore();

		header('Location: q_index.php');
		exit();
	}

	function PerDenyExit() {
		global $MSG, $sysSession, $_SERVER;
		$js = <<< EOB
	alert('{$MSG['msg_move_4'][$sysSession->lang]}');
	location.replace( 'q_index.php' );
EOB;
		showXHTML_script('inline', $js);
		wmSysLog($sysSession->cur_func, $sysSession->class_id , $sysSession->board_id , 1, 'auto', $_SERVER['PHP_SELF'], '權限不足!');
		exit();
	}


	/****************************************************
	 *	主要程式
	 *
	 ****************************************************/
	if (ereg('^59([0-3]),([0-9]{10}),([0-9]+),([0-9]+)\.php$', basename($_SERVER['PHP_SELF']), $reg) &&
	    $reg[2] == $sysSession->board_id
	   ){
		$v1 = IntVal($reg[3]);
		$v2 = IntVal($reg[4]);
	   switch($reg[1]) {
	   	case '0': // 590 整批刪除
	   		if($sysSession->q_right) {
		   		do_delete($v1,$v2);
		   	}
		   	else
		   		PerDenyExit();
		   	break;
	   	case '1': // 591 整批收入個人筆記本
	   		if(isset($_GET['folder'])) {
		   		do_notebook($v1,$v2, $_GET['folder']);
		   	}
		   	break;
	   	case '2': // 592 整批搬移( Move )
	   		if($sysSession->q_right) {
		   		do_move($v1,$v2, $_GET['folder_id']);
		   	}
		   	else
		   		PerDenyExit();
		   	break;
	   }
	}
?>
