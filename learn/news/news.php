<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/acade_news.php');
	require_once(sysDocumentRoot . '/lib/lib_acade_news.php');
	require_once(sysDocumentRoot . '/lib/lib_forum.php');
	require_once(sysDocumentRoot . '/forum/lib_rightcheck.php');
	require_once(sysDocumentRoot . '/forum/order.inc.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	if (!aclVerifyPermission(2500200200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	// 各項排序依據
	$OB = $OrderBy['board'];


	/*
	 * GotoPagePost()
	 *    取得最新消息頁數及篇數 ( 補齊供 read.php 需要的 Session )
	 *    @pram string $node : 文章編號
	 *    @return bool : 成功 true, 失敗 false
	 */
	function GotoPagePost($node) {
		global $sysSession, $sysConn, $OB;
		if(empty($node)) {
			$sysSession->page_no = '';
			$sysSession->post_no = '';
			return false;
		}

		// 取得每頁筆數
		$rows_page = GetForumPostPerPage();
		$post_no = 0;
		$where = '';
		if($sysSession->news_board && !$sysSession->b_right) {	// 是公共消息類型且無刊登權限
			// 找出公佈期間內之節點
			$NEWS = dbGetCol('WM_news_posts','node',
							 "board_id={$sysSession->board_id} and (open_time='0000-00-00' or open_time<=NOW()) and (close_time='0000-00-00' or close_time>NOW())");
			$news_nodes = "'" . implode("','",$NEWS) ."'";
			$where = $where . " and node in (" . $news_nodes . ") ";
		}

		// 取得本 board 所有 POST
		$RS = dbGetStMr('WM_bbs_posts', 'node', "board_id={$sysSession->board_id} {$where} order by {$OB[$sysSession->sortby]}", ADODB_FETCH_ASSOC);
		while(!$RS->EOF) {
			$post_no++;
			if($RS->fields['node']==$node) {
				$sysSession->post_no = $post_no;
				$sysSession->page_no = ceil($post_no / $rows_page);
				return true;
			}
			$RS->MoveNext();
		}
		return false;
	}

	if(!dbGetNewsBoard($result, 'news')) {
		echo 'System Error!';
		wmSysLog($sysSession->cur_func, $sysSession->course_id , $sysSession->board_id , 1, 'auto', $_SERVER['PHP_SELF'], 'System Error!');
		exit();
	}

	if( empty($_GET['node']) ) {
		header('Location:index.php');
		exit();
	}
	elseif (!ereg('^[0-9]{9,}$', $_GET['node']))
	{
		wmSysLog($sysSession->cur_func, $sysSession->course_id , '' , 1, 'auto', $_SERVER['PHP_SELF'], 'Access Deny!');
		die('Access Deny!');
	}

	$sysSession->board_id        = $result['board_id'];
	$sysSession->news_board      = 1;	// 含時間(開啟及關閉)欄位類型之討論版
	$sysSession->board_readonly  = 1;
	$sysSession->board_qonly     = 0;
	// $sysSession->page_no      = '';
	// $sysSession->post_no      = '';
	$sysSession->q_sortby        = '';
	$sysSession->q_page_no       = '';
	$sysSession->q_post_no       = '';
	$sysSession->sortby          = 'pt';
	$sysSession->board_ownerid   = $sysSession->school_id;
	$sysSession->board_ownername = $sysSession->school_name;
	// 是否具刊登權限(含修改, 刪除)
	$sysSession->q_right         = ChkRight($result['board_id']);
	$sysSession->b_right         = $sysSession->q_right;	// 目前兩者一樣

	// 找出公佈期間內之節點
	$NEWS = dbGetCol('WM_news_posts','node',
					 "board_id={$sysSession->board_id} and (open_time='0000-00-00' or open_time<=NOW()) and (close_time='0000-00-00' or close_time>NOW())",
					 ADODB_FETCH_ASSOC);
	$sysSession->news_nodes = implode(',',$NEWS);

	// 將篇數計算出並存於 sysSession 中( 注意: 以下 function 需放在 news_nodes 及 q_right (b_right)取得之後 )
	GotoPagePost($_GET['node']);

	// 回存 SESSION
	$sysSession->restore();

	dbSet('WM_session', "board_name='{$MSG['news'][$sysSession->lang]}',q_path=''", "idx='{$_COOKIE['idx']}'");

	// 清除 Cookie 所存搜尋條件
	ClearForumCookie();

	header('Location:/forum/read.php');
?>
