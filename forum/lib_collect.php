<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/forum.php');
	require_once(sysDocumentRoot . '/lib/lib_forum.php');
	require_once(sysDocumentRoot . '/forum/file_api.php');

	/*************************

	新增精華區資料 db_new_collect($RS)

	@param array $RS ( dbGetStSr() 所取得的 WM_bbs_posts 陣列 )
		( 所需路徑 path 已放好在 $RS['path'] 中, 此新增不含 attach 欄位 )
	@param int $replyto_node : 若是收錄者為回覆文章, 將目前此點轉成 $replyto_node 下的一點
			則新 Node 規則 node = $replyto_node (9碼)+  $RS['node'] 後9碼 ( 以維持原次序)

	@return int : 失敗傳回 0
		成功傳回節點編號 (node)

	 *************************/
	function db_new_collect($RS, $replyto_node=0) {
		global $sysConn,$sysSiteNo,$sysSession;

		if (!is_array($RS)) return 0;
		if ($replyto_node) { // 有回覆主題節點
			$nnode = $replyto_node . substr($RS['node'], 9,9); // 新 Node 規則 node = $replyto_node (9碼)+  $RS['node'] 後9碼 ( 以維持原次序)
		} else {
			// 取得目前精華區中最大的 node
			list($mnode) = dbGetStSr('WM_bbs_collecting', 'MAX(node)', "board_id={$RS['board_id']} and length(node) = 9", ADODB_FETCH_NUM);
			// 產生本篇的 node
			$nnode = empty($mnode) ? '000000001' : sprintf("%09d", $mnode+1);
		}

		// 加入資料庫
		$fields = 'board_id,node,site,path,pt,poster,realname,email,homepage,subject,content,'.
				  'rcount,rank,hit,lang,ctime,picker,post_node';
		$RS['rcount'] = ($RS['rcount'] ? $RS['rcount'] : 'NULL');
		$RS['rank']   = ($RS['rank']   ? $RS['rank']   : 'NULL');

		foreach ($RS as $k => $v) {
			$RS[$k] = mysql_escape_string($v);
		}

		//MIS#23781 收入精華區需保留點閱次數 by Small 2012/01/30
		$values = "{$RS['board_id']}, '$nnode',$sysSiteNo,'{$RS['path']}',".
			      "'{$RS['pt']}', '{$RS['poster']}', '{$RS['realname']}', ".
			      "'{$RS['email']}', '{$RS['homepage']}', '{$RS['subject']}', '{$RS['content']}',".
			      "{$RS['rcount']},{$RS['rank']},{$RS['hit']},{$RS['lang']},".
			      "Now(),'{$sysSession->username}','{$RS['node']}'";

		dbNew('WM_bbs_collecting', $fields, $values);

		if ($sysConn->Affected_Rows() == 0 || $sysConn->ErrorNo() > 0)
			return 0;
		else
			return $nnode;
	}

	/*****
	 *	夾檔複製 ( 從一般區到精華區實體資料夾 )
	 *	參數: $node, $q_node, $attach, $new_attach
	 *	回傳值:
	 *		true 成功
	 *		false 失敗
	 *****/
	function process_files($node, $q_node, $attach, &$new_attach) {

		global $sysSession;

		$board_id = $sysSession->board_id;
		$b_attach = trim($attach);
		$q_attach = '';

		if (empty($b_attach)) {
			$new_attach = '';
			return true;
		}

		$b_path = get_attach_file_path('board', $sysSession->board_ownerid);	// . "/{$node}";
		$q_path = get_attach_file_path('quint', $sysSession->board_ownerid);	// . "/{$q_node}";

		$from_path = "{$b_path}/{$node}";
		$to_path   = "{$q_path}/{$q_node}";


		if (!is_dir($q_path))  @System::mkDir("-p $q_path");
		if (!is_dir($to_path)) @System::mkDir("-p $to_path");

		// 複製檔案
		return b_copyfiles( $from_path , $to_path , $b_attach, $new_attach);
	}

	// 錯誤代碼 (0 為成功)
	$err_id = Array(
		'success'     =>  0,
		'db_error'    => -1,
		'file_error'  => -2,
		'query_error' => -3
	);
	// 錯誤代碼所代表字串
	$err_msg = Array(
		 0 => $MSG['collect'][$sysSession->lang] . $MSG['success_to'][$sysSession->lang],
		-1 => $MSG['db_busy'][$sysSession->lang],
		-2 => $MSG['copyfile_fail'][$sysSession->lang],
		-3 => $MSG['query_post_fail'][$sysSession->lang]
	);


	/**************************************
		執行收入精華區動作
		@param int $board_id	: 討論板編號
		@param int $node		: 一般區文章編號
		@param int $new_node	: 新產生的精華區文章編號
		@param int $site		: 站號
		@param string $path		: 將收入之路徑	( 路徑預設為 "/" )
		@param bool $is_move	: 是否為搬移( true : 搬移 		false : 複製 )
		@param int $replyto_node: 欲回覆之主題節點

		@return int : error code
	 **************************************/
	function do_collect($board_id, $node, $site, &$new_node, $path=DIRECTORY_SEPARATOR, $is_move=false, $replyto_node=0) {
		global $sysSession, $sysConn, $MSG, $err_id;

		$board_id = intval($board_id);
		$site     = intval($site);
		// 取得一般區文章內容
		$RS = dbGetStSr('WM_bbs_posts','*', "board_id={$board_id} and node='{$node}' and site={$site}", ADODB_FETCH_ASSOC);
		if($RS) {

			$RS['path'] = $path;
			$attach     = $RS['attach'];

			// 新增入資料庫
			$new_node = db_new_collect($RS, $replyto_node);
			if ($new_node == 0) { // 失敗
				return $err_id['db_fail'];
			} else {
				$q_attach = '';
				if (process_files($node, $new_node, $attach,$q_attach)) {
					if ($q_attach != '')
						dbSet('WM_bbs_collecting', "attach='{$q_attach}'", "board_id={$board_id} and node='{$new_node}' and site={$site}");

					if ($is_move) { // 搬移(1) , 需刪除原一般區
						// 刪除一般區夾檔
						$attach_path = get_attach_file_path('board', $sysSession->board_ownerid). DIRECTORY_SEPARATOR . "{$node}";
						if (is_dir($attach_path)) @System::rm("-rf $attach_path");
						// 刪除一般區資料
						delete_post($board_id, $node, $site);	// 在 /lib/lib_forum.php 中
					}
					return $err_id['success'];
				} else { // 搬檔案失敗, 刪除新增精華之文章
					dbDel('WM_bbs_collecting', "board_id={$board_id} and node='{$new_node}' and site={$site}");
					return $err_id['file_error'];
				}
			}
		} else {
			return $err_id['query_error'];
		}
	}
?>
