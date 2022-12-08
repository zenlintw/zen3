<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/lib_forum.php');
	require_once(sysDocumentRoot . '/lang/forum_io.php');
	require_once(sysDocumentRoot . '/forum/lib_import.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');
	require_once(sysDocumentRoot . '/lib/archive_api.php');
	require_once(sysDocumentRoot . '/forum/lib_rightcheck.php');
	
	if (!defined('BOARD_TYPE')) define('BOARD_TYPE', 'board');

	$sysSession->cur_func = '900100800';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}
	
	$alert_str = mb_convert_encoding('不具刊登權限', 'UTF-8','BIG5' );
	
	// 是否具刊登權限(含張貼, 修改, 刪除)
	if ($sysSession->board_readonly) {
	    if(!ChkRight($sysSession->board_id)){
		$js = <<< BOF
	window.onload = function ()
	{
		alert("{$alert_str}");
		location.replace("/forum/index.php");
	};
BOF;
			showXHTML_script('inline', $js);
				wmSysLog($sysSession->cur_func, $sysSession->class_id , $sysSession->board_id , 1, 'auto', $_SERVER['PHP_SELF'], $alert_str);
				exit();
	    }
	}
	function clean_tmpdir() {
		global $tmp_dir;
        if (is_dir($tmp_dir)) {
            system("rm -rf {$tmp_dir}");
        }
	}

	function die_clean($s) {
		global $sysSession, $_SERVER;
		clean_tmpdir();
		wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 3, 'auto', $_SERVER['PHP_SELF'], $s);
		die($s);
	}

	/******************************************
	 * Step 1. Check If post.xml exists or not.
	 ******************************************/
	$up_file     = $_FILES['file_import']['tmp_name'];
	$up_filename = basename($up_file);
	$up_dir      = dirname($up_file);

	// 處理檔案的暫存路徑 ( 非系統暫存路徑 )
	$tmp_dir = $up_dir . DIRECTORY_SEPARATOR . uniqid('post');
	mkdir($tmp_dir);
	$import_zip = new Archive();
	$import_zip->extract_it($up_file, $tmp_dir, '.zip');
	
	/******************************************
	 * Step 3. Load XML data from post.xml
	 ******************************************/
	$xml_file = $tmp_dir . DIRECTORY_SEPARATOR . 'post.xml';
	if(!is_file($xml_file))	die_clean(sprintf($MSG['no_import_data'][$sysSession->lang], 'post.xml'));

	$post_obj = new bbsPost;
	$ret      = $post_obj->initial($xml_file);
	if( $ret != 0)	{
		// echo "<!-- $xml_file -->\r\n";
		die_clean($import_errmsg[$ret]);
	}

	/******************************************
	 * Step 4. save data
	 ******************************************/
	$ret = $post_obj->save(BOARD_TYPE);
	if($sysSession->news_board)
		$ret = $post_obj->saveNews();

	if( $ret != 0)	die_clean($import_errmsg[$ret]);

	/***************************************************
	 * Step 4. Finally Clear the temp dir and remove it.
	 ***************************************************/
	clean_tmpdir();

	dbSet('WM_term_major', 'post_times=post_times+1', "username='{$sysSession->username}' and course_id='{$sysSession->course_id}'");
	dbSet('WM_term_course', 'post_times=post_times+1', "course_id='{$sysSession->course_id}'");

	$where      = getSQLwhere($is_search, BOARD_TYPE);  // 取得 SQL 過濾條件
	$total_post = getTotalPost($where   , BOARD_TYPE);  // 取得本板張貼數
	$rows_page  = GetForumPostPerPage();                // 取得一頁幾筆
	$total_page = ceil($total_post / $rows_page);       // 計算總共有幾頁

	if (BOARD_TYPE == 'board')
		$sysSession->page_no = $total_page;
	else
		$sysSession->q_page_no = $total_page;

	$sysSession->restore();

	// 回到閱讀或列表
	header('Location:' . (BOARD_TYPE == 'board' ? 'index.php' : 'q_index.php'));
?>
