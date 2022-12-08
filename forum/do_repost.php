<?php
	/**
	 * 轉貼文章至其他討論版
	 *
	 * @since   2004/09/12
	 * @author  KuoYang Tsao
	 * @version $Id: do_repost.php,v 1.1 2010/02/24 02:38:59 saly Exp $
	 * @copyright 2004 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/forum.php');
	require_once(sysDocumentRoot . '/lib/lib_forum.php');
	require_once(sysDocumentRoot . '/forum/file_api.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lib/username.php');

	$sysSession->cur_func = '900200400';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

//---------------------------------------------
// 函式開始
//---------------------------------------------

	/*************************

	新增討論區文章資料 db_new_post($RS)

	參數 : $RS ( dbGetStSr() 所取得的 WM_bbs_posts 陣列 )
		( 所需路徑 path 已放好在 $RS['path'] 中, 此新增不含 attach 欄位 )

	傳回值: 失敗傳回 0
		成功傳回節點編號 (node)

	 *************************/
	function db_new_post($RS) {
		global $sysConn,$sysSiteNo,$sysSession, $_SERVER;

		// 取得目前精華區中最大的 node
		list($mnode) = dbGetStSr('WM_bbs_posts', 'MAX(node)',"board_id={$RS['board_id']} and length(node) = 9", ADODB_FETCH_NUM);
		// 產生本篇的 node
		$nnode = empty($mnode)?'000000001':sprintf('%09d', $mnode+1);

		// 加入資料庫
		// 換掉可能之引號
		$username = mysql_escape_string($sysSession->username);
		$realname = mysql_escape_string($sysSession->realname);
		foreach($RS as $k=>$v) {
			$RS[$k] = mysql_escape_string($v);
		}
		$fields = 'board_id,node,site,pt,poster,realname,email,homepage,subject,content,lang';
		$values = "{$RS['board_id']}, '$nnode',$sysSiteNo".
			      ", NOW(), '{$username}', '{$realname}', ".
			      "'{$sysSession->email}', '{$sysSession->homepage}', '{$RS['subject']}', '{$RS['content']}',".
			      $RS['lang'];

		dbNew('WM_bbs_posts', $fields, $values);
		if ($sysConn->Affected_Rows() == 0 || $sysConn->ErrorNo() > 0) {
		    wmSysLog($sysSession->cur_func, $sysSession->class_id , $RS['board_id'] , 3, 'auto', $_SERVER['PHP_SELF'], '新增討論區文章資料fail!');
			return 0;
		}
		else {
			wmSysLog($sysSession->cur_func, $sysSession->class_id , $RS['board_id'] , 0, 'auto', $_SERVER['PHP_SELF'], '新增討論區文章資料success!');
			return $nnode;
		}
	}

	/* 產生討論板夾檔存放目錄 (不含 node)
	 * input : $board_id : 討論版編號
	 * return: 傳回路徑
	 */
	function get_board_attach_path($board_id, $owner_id=null){
		global $sysSession;

		$ret = '/base/' . $sysSession->school_id;

		switch(strlen($owner_id)) {
			case 5: //學校
				break;
			case 7:// 班級
			case 15:// 班級群組
				$ret .= '/class/'.$owner_id;
				break;

			case 8:// 課程
			case 16:// 課程群組
				$ret .= '/course/'.substr($owner_id, 0, 8);
				break;
			default:
			{
				if ($sysSession->course_id){
					$ret .= '/course/' . $sysSession->course_id;
				} else if($sysSession->class_id) {
					$ret .= '/class/' . $sysSession->class_id;
				}
			}
		}
		$ret .= '/board/' . $board_id ;
		return sysDocumentRoot . $ret;
	}

	/*****
	 *	夾檔複製 ( 實體資料夾 )
	 *	參數: $node, $q_node, $attach, $new_attach
	 *	回傳值:
 *		true 成功
	 *		false 失敗
	 *****/
	function process_files($src_board, $src_node, $to_board, $to_node, $src_attach, &$new_attach) {

		global $sysSession, $Board_OwnerID, $Board_Owner;
		$board_id = $sysSession->board_id;

		// $b_attach  = trim($attach);
		// $q_attach  = '';
		$src_attach = trim($src_attach);
		$to_attach  = '';

		if($src_attach=='') {
			$new_attach = $to_attach;
			return true;
		}

		getBoardOwner($to_board);
		$to_ownerid = $Board_OwnerID;
		$src_path = get_board_attach_path($src_board, $sysSession->board_ownerid). DIRECTORY_SEPARATOR . $src_node;
		$to_path  = get_board_attach_path($to_board, $to_ownerid). DIRECTORY_SEPARATOR . $to_node;

		if (!is_dir($src_path)) @System::mkDir("-p $src_path");
		if (!is_dir($to_path)) @System::mkDir("-p $to_path");

		// 複製檔案
		return b_copyfiles( $src_path , $to_path , $src_attach, $new_attach);
	}

	// 錯誤代碼 (0 為成功)
	$err_id = Array(
				'success'		=> 0,
				'db_error'		=>-1,
				'file_error'	=>-2,
				'query_error'	=>-3
			  );
	// 錯誤代碼所代表字串
	$err_msg = Array(
				0 => $MSG['repost'][$sysSession->lang] . $MSG['success_to'][$sysSession->lang],
				-1=> $MSG['db_busy'][$sysSession->lang],
				-2=> $MSG['copyfile_fail'][$sysSession->lang],
				-3=> $MSG['query_post_fail'][$sysSession->lang]
			  );


	/**************************************
		執行收入精華區動作
		參數:
			$src_board	: 來源討論板編號
			$src_node	: 來源文章編號
			$to_board	: 目標討論板
			$is_move	: 是否為搬移( true : 搬移 		false : 複製 )(保留功能)
	 **************************************/
	function do_repost($src_board, $src_node, $to_board, $is_move=false) {
		global $sysSession, $sysConn, $sysSiteNo, $MSG, $err_id;

		// 取得來源文章內容
		$RS = dbGetStSr('WM_bbs_posts','*', "board_id={$src_board} and node='{$src_node}' and site={$sysSiteNo}", ADODB_FETCH_ASSOC);
		if($RS) {
			// Bug 1051 轉貼時,加上原張貼課程/板名/張貼者/張貼時間 Begin
			list($bname, $owner) = dbGetStSr('WM_bbs_boards', 'bname, owner_id', 'board_id=' . $src_board, ADODB_FETCH_NUM);
			switch(strlen($owner)) {
				case 5: //學校
					$owner = $sysSession->school_name;
					break;
				case 7:// 班級
				case 15:// 班級群組
					list($owner) = dbGetStSr('WM_class_main', 'caption', 'class_id='.substr($owner, 0, 7), ADODB_FETCH_NUM);
					$owner = $owner ? unserialize($owner) : array();
					$owner = $owner[$sysSession->lang];
					break;
				case 8:// 課程
				case 16:// 課程群組
					list($owner) = dbGetStSr('WM_term_course', 'caption', 'course_id='.substr($owner, 0, 8), ADODB_FETCH_NUM);
					$owner = $owner ? unserialize($owner) : array();
					$owner = $owner[$sysSession->lang];
					break;
			}
			$bname = $bname ? unserialize($bname) : array();	// 取得板名
			list($fname, $lname) = dbGetStSr('WM_user_account', 'first_name, last_name', 'username="' . $RS['poster']. '"', ADODB_FETCH_NUM); // 取得原張貼者
			$RS['poster'] = $RS['poster'] . '(' . checkRealname($fname, $lname) . ')';
			$RS['subject'] = '['. $MSG['repost'][$sysSession->lang] . ']' . $RS['subject'];	// 原張貼標題加上[轉貼]
			$RS['content'] = $MSG['repost_from_board'][$sysSession->lang] . $owner . ' - ' .$bname[$sysSession->lang] . '<br />' .
							 $MSG['repost_from_user'][$sysSession->lang]  . $RS['poster']  . '<br />' .
							 $MSG['repost_from_time'][$sysSession->lang]  . $RS['pt']	   . '<br /><br />' .
							 $RS['content'];
			// Bug 1051 轉貼時,加上原張貼課程/板名/張貼者/張貼時間 End

			$attach = $RS['attach'];
			$RS['board_id'] = $to_board;	// 變更 board_id , 供 db_new_post() 使用

			// 新增入資料庫
			$to_node = db_new_post($RS);
			if($to_node==0) { // 失敗
				return $err_id['db_fail'];
			} else {
				$to_attach = '';
			if(process_files($src_board,$src_node, $to_board, $to_node, $attach,$to_attach)) {
					if($to_attach != '')
					dbSet('WM_bbs_posts',"attach='{$to_attach}'","board_id={$to_board} and node='{$to_node}' and site={$sysSiteNo}");

					if($is_move) {	// 搬移(1) , 需刪除原一般區

						// 刪除一般區夾檔
						$attach_path = get_board_attach_path($src_board). DIRECTORY_SEPARATOR ."{$src_node}";
						if (is_dir($attach_path)) @System::rm("-rf $attach_path");
						// 刪除一般區資料
						delete_post($src_board, $src_node, $sysSiteNo);	// 在 /lib/lib_forum.php 中

					}
					return $err_id['success'];

				} else	{ // 搬檔案失敗, 刪除新增精華之文章
					dbDel('WM_bbs_posts',"board_id={$to_board} and node='{$to_node}' and site={$sysSiteNo}");

					return $err_id['file_error'];
				}
			}
		} else {
			return $err_id['query_error'];
		}
	}
//---------------------------------------------
// 函式結束
//---------------------------------------------


//---------------------------------------------
// 主程式開始
//---------------------------------------------

	header('Content-type: text/xml');
	echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			echo '<manifest></manifest>';
			wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'domxml open fail!');
			exit;
		}

		// 檢查 Ticket
		$ticket = md5(sysTicketSeed . $sysSession->username . 'read' . $sysSession->ticket . $sysSession->board_id);
		if (getNodeValue($dom, 'ticket') != $ticket) {
			echo '<manifest>Access Fail.</manifest>';
			wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], '拒絕存取!');
			exit;
		}

		$action = getNodeValue($dom, 'action');
		$result = '';
		switch ($action) {
			case 'repost' :   // 轉貼
				$src_board = $sysSession->board_id;
				$src_node  = getNodeValue($dom, 'src_node');
				$to_board  = getNodeValue($dom, 'to_board');
				$ret       = do_repost($src_board, $src_node, $to_board);
				$result    = '<manifest>' .
				             "<code>{$ret}</code>" .
				             "<message>{$err_msg[$ret]}</message>" .
				             '</manifest>';
				break;
		}

		if (!empty($result)) {
			echo str_replace('<manifest>', "<manifest><ticket>{$ticket}</ticket>", $result);
		} else {
			echo '<manifest></manifest>';
		}
	}
?>
