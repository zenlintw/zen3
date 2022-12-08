<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lib_forum.php');
	require_once(sysDocumentRoot . '/lang/forum.php');
	require_once(sysDocumentRoot . '/forum/lib_rightcheck.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	$sysSession->cur_func = '900300700';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$move_err_msg = Array(-1=>$MSG['msg_move_1'][$sysSession->lang],
						  -2=>$MSG['copyfile_fail1'][$sysSession->lang].' '.
							  $MSG['db_busy'][$sysSession->lang].' '.
							  $MSG['try_later'][$sysSession->lang],
						  -3=>$MSG['msg_move_3'][$sysSession->lang],
						  -4=>$MSG['msg_move_4'][$sysSession->lang]
						);

	function js_exit() {
		global $failed_msgs, $MSG,$sysSession;
		$js_txt = '';
		if(count($failed_msgs) > 0) {	// 有錯誤發生
			foreach($failed_msgs as $k=>$v)
				$js_txt .= $v . "\\n";
		} else {
			$js_txt = $MSG['move'][$sysSession->lang].$MSG['success_to'][$sysSession->lang];
		}

		$js = "alert('" . $js_txt . "');\r\n";
		$js .= "location.replace('q_read.php');\r\n";

		showXHTML_script('inline',$js);
		exit();
	}

	/***
	 *	"搬移"處理程式
	 *	參數: $node : 搬移文章
	 *		  $folder_id : 搬移目標資料夾節點編號(即 node)
	 */
	function do_move($node, $folder_id, &$path) {
		global $sysSession, $sysConn, $sysSiteNo, $move_err_msg, $_SERVER;

		// 取出該資料夾
		if($folder_id!=='0') {
			$folder_rs = dbGetStSr('WM_bbs_collecting' , 'path, subject' ,"board_id={$sysSession->board_id} and node='{$folder_id}' and type='D'", ADODB_FETCH_ASSOC);
			if(!$folder_rs) return -3;	// 資料夾不存在

			$path = ($folder_rs['path']=='/' ? '' : $folder_rs['path']) . "/{$folder_rs['subject']}";
		} else {
			$path = '/';
		}

		$RS = dbGetStSr('WM_bbs_collecting', 'picker', "board_id='{$sysSession->board_id}' and node='{$node}'", ADODB_FETCH_ASSOC);
		if(!$RS) { // 資料庫查詢失敗
		   return -2;
		}

		$picker= $RS['picker'];

		if(($picker==$sysSession->username) || $sysSession->q_right) {	// 檢查權限
			dbSet('WM_bbs_collecting', "path='$path'",  "board_id='{$sysSession->board_id}' and node='{$node}' and site={$sysSiteNo}");
		} else {
			return -4;	// 權限不足
		}
		return 0;
	}

	/****************************************************
	 *	主要程式
	 *
	 ****************************************************/
	$ticket    = $_GET['ticket'];
	$node      = $_GET['node'];
	$site      = $_GET['site'];
	$folder_id = $_GET['folder_id'];

	$failed_msgs = Array();	// 失敗訊息

	// 驗證 ticket
	if(	$ticket != md5(sysTicketSeed . 'Borad' . $_COOKIE['idx'] . $sysSession->board_id))
	{
		$failed_msgs[] = 'Access denied';
		js_exit();
	}

	// 是否具刊登權限(含修改, 搬移, 刪除)
	$post_right = ChkRight($sysSession->board_id);
	if(!$post_right) {
		$failed_msgs[] = $move_err_msg[-4];	// "Permission denied";
		js_exit();
	}

   	if( !ereg('[0-9]{6,}',$node) || !ereg('[0-9]{10}',$site) || (!ereg('[0-9a-zA-Z]{32}',$folder_id) && $folder_id!=0) )
   	{
   		$failed_msgs[] = $MSG['move'][$sysSession->lang] . $MSG['failed'][$sysSession->lang];
		js_exit();
   	}

	// 搬移文章
	$path = '/';
	if(($ret = do_move($node, $folder_id, $path)) !== 0) {
		$failed_msgs[] = $move_err_msg[$ret];
		wmSysLog($sysSession->cur_func, $sysSession->board_id , $node , $ret, 'auto', $_SERVER['PHP_SELF'], 'Move essential post to ' . $folder_id . ', path=' . $path . $move_err_msg[$ret]);
		js_exit();
	}
	
	wmSysLog($sysSession->cur_func, $sysSession->board_id , $node , 0, 'auto', $_SERVER['PHP_SELF'], 'Move essential post to ' . $folder_id . ', path=' . $path);
	
	$sysSession->q_path = $path; // $folder;
	if($sysSession->q_sortby != 'pt') {	// 不是依張貼時間排序者
		$where      = getSQLwhere($is_search, 'quint');	// 取得 SQL 過濾條件
		$total_post = getTotalPost($where, 'quint');	// 取得本板張貼數
		$rows_page  = GetForumPostPerPage();			// 取得一頁幾筆
		$total_page = ceil($total_post / $rows_page); 	// 計算總共有幾頁

		$sysSession->q_page_no = $total_page;
	} else {
		$sysSession->q_page_no = 1;
	}

	$sysSession->restore();

	header("Location:q_index.php");
?>
