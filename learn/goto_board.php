<?php
	/**
	 * 切換討論版
	 * $Id: goto_board.php,v 1.1 2010/02/24 02:39:05 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
	require_once(sysDocumentRoot . '/lib/lib_forum.php');
	require_once(sysDocumentRoot . '/forum/lib_rightcheck.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$sysSession->cur_func='900200100';
	$sysSession->restore();
	if (!aclVerifyPermission(900200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}

	/**
	 * 安全性檢查
	 *     1. 身份的檢查
	 *     2. 權限的檢查
	 *     3. .....
	 **/

	/**
	 * 切換討論版
	 **/
	function chgBoard($bid) {
		global $sysSession, $sysConn, $Board_OwnerID, $Board_Owner, $_SERVER, $sysRoles;
		$bname = '';
		$board_readonly = false;

		// $role判斷使用者是否為 [root] [administrator] [manager], 是的話bool為true
		$role = aclCheckRole($sysSession->username, $sysRoles['root']|$sysRoles['administrator']|$sysRoles['manager'], $sysSession->school_id);

		// 取得課程預設的版號
		if ($bid < 1000000000) {
			$csid = empty($sysSession->course_id)?10000000:$sysSession->course_id;
			if ($csid <= 10000000) $csid = 10000000;
			$RS = dbGetStSr('WM_term_course', 'discuss, bulletin', "course_id={$csid}", ADODB_FETCH_ASSOC);
			if ($RS) {
				if ($bid == 1) $bid = $RS['bulletin']; // 課程公告
				if ($bid == 2) $bid = $RS['discuss'];  // 課程討論
			}
			if ($bid < 1000000000)	$bid = 1000000000;
		}
		
		if (!$role) {
		    if (!ChkBoardReadRight($bid)) {
		        wmSysLog($sysSession->cur_func, $sysSession->course_id , $bid , 2, 'auto', $_SERVER['PHP_SELF'], 'board_deny');
		        die('board_deny');
		    }
		}

		$sysSession->sortby = '';
		if (($bid > 1000000000) && ($bid < 10000000000) && !isset($_REQUEST['HTTP_RAW_POST_DATA'])) {
			$lang = array();
			$RS = dbGetStSr('WM_bbs_boards', 'bname,default_order, open_time, close_time, share_time', "board_id={$bid}", ADODB_FETCH_ASSOC);

				$nt = time();
				$ot = $sysConn->UnixTimeStamp($RS['open_time']);
				$ct = $sysConn->UnixTimeStamp($RS['close_time']);
				$st = $sysConn->UnixTimeStamp($RS['share_time']);
				$status = getBoardStatus($nt, $ot, $ct, $st);
				
				// 如果使用者為 [root] [administrator] [manager]時, 不判斷當討論版是關閉中無法進入設定的問題。
				if($role!=='1' && $sysSession->env == 'learn'){
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
				}

			$lang = getCaption($RS['bname']);
			$sysSession->sortby = $RS['default_order'];
			$bname = addslashes($lang[$sysSession->lang]);
			// $sysSession->board_name = stripslashes($bname);
		} else {
			// 我的課程
			$bid = 1000000000;
		}
		
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
				// 管理者不須判斷權限，先取消此判斷
				if($RS1 && $role!=='1' && $sysSession->env == 'learn') {
					if ($RS1['visibility']=='hidden')	{ // 課程板為隱藏
					    wmSysLog($sysSession->cur_func, $sysSession->course_id , $bid , 3, 'auto', $_SERVER['PHP_SELF'], '課程版為隱藏');
						die('board_close'); //管理者不須判斷全限，先取消此判斷
					}
					else if ($RS1['state']=='disable')	{ // 課程版為停用
						wmSysLog($sysSession->cur_func, $sysSession->course_id , $bid , 4, 'auto', $_SERVER['PHP_SELF'], '課程版為停用');
						die('board_disable'); //管理者不須判斷全限，先取消此判斷
					}
					else if ($RS1['state']=='taonly')	{ // 課程版為教師專用
						wmSysLog($sysSession->cur_func, $sysSession->course_id , $bid , 5, 'auto', $_SERVER['PHP_SELF'], '課程版為教師專用');
						die('board_taonly'); //管理者不須判斷全限，先取消此判斷
					}
				}
		}

		$sysSession->b_right = $sysSession->q_right;	// 目前兩者一樣

		$sysSession->restore();
		dbSet('WM_session', "board_name='{$bname}', q_path=''", "idx='{$_COOKIE['idx']}'");

		// 清除 Cookie 所存搜尋條件
		ClearForumCookie();

		// 讀出 extras 值到 cookie 中
		loadExtras2Cookie($sysSession->board_id);
	}

	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
		    wmSysLog($sysSession->cur_func, $sysSession->course_id , $sysSession->board_id , 6, 'auto', $_SERVER['PHP_SELF'], 'domxml open fail');
			showError();
			exit;
		}
		$bid = getNodeValue($dom, 'board_id');
		$ary = array('1', '2');
		if (!in_array($bid, $ary)) {
			$bid = sysDecode($bid);
			if (!is_numeric($bid)) {
			   wmSysLog($sysSession->cur_func, $sysSession->course_id , $bid , 7, 'auto', $_SERVER['PHP_SELF'], 'bad borad id');
			   die('Bad_ID');
			}
			if (($bid <= 1000000000) || ($bid >= 10000000000)) {
			   wmSysLog($sysSession->cur_func, $sysSession->course_id , $bid , 8, 'auto', $_SERVER['PHP_SELF'], 'bad borad id range');
			   die('Bad_Range');
			}
		}
		chgBoard($bid);
	}
?>
