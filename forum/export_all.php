<?php
	set_time_limit(600);

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');
	require_once(sysDocumentRoot . '/lib/lib_forum.php');
	require_once(sysDocumentRoot . '/forum/lib_export_all.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/archive_api.php');

	$sysSession->cur_func = '900100700';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}
	
	if($_POST['boaid']!=''){
		$board_id=intval($_POST['boaid']);
		$co_own_id=dbGetOne('WM_bbs_boards','owner_id ','board_id="'.$board_id.'"');;
	}else{
		$board_id=$sysSession->board_id;
		$co_own_id=$sysSession->board_ownerid;;
	}

	$ticket = md5(sysTicketSeed . 'BoardExp' . $_COOKIE['idx'] . $board_id);

	
	if( ($ticket != $_POST['ticket'])  ) {//|| !$sysSession->b_right
		wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], '拒絕存取!');
		die('Access Deny');
	}

	$D_R = DIRECTORY_SEPARATOR;

	// 暫存路徑
	$tem_path        = sysTempPath . $D_R . uniqid('BBS');
	$temp_board_path = $tem_path   . $D_R . 'board';
	$temp_quint_path = $tem_path   . $D_R . 'quint';
	mkdirs($temp_board_path);
	mkdirs($temp_quint_path);

	// 1.建立Tar 物件( 直接寫入 Tar)
	$tgz_name = 'board'. $board_id . '.zip';
	$tgz_path = dirname($tem_path) . $D_R . $tgz_name;

	// 注意: 不要以 Archive_Tar 直接存 gz 格式 ===> 很很很很慢
	$export_tar = new ZipArchive_php4($tgz_name); // WM3_Tar($tar_path);

	// 2.產生清單
	$dom = gen_list_xml( $board_id );
	$forum_node =  $dom->document_element();

	add_post_list_xml($tem_path, $forum_node, $board_id, 'board');
	add_post_list_xml($tem_path, $forum_node, $board_id, 'quint');
	// 儲存總清單
	// $export_tar->addString('list.xml', @$dom->dump_mem(true));
	// $export_tar->add_string(@$dom->dump_mem(true), 'list.xml');
    saveFile($tem_path . $D_R . 'list.xml', @$dom->dump_mem(true));

	// 3.複製夾檔

	$base_dir = getOwnerDir($co_own_id);

	$src_b_dir = $base_dir . $D_R . 'board' . $D_R . $board_id;
	$src_q_dir = $base_dir . $D_R . 'quint' . $D_R . $board_id;
	
	$dst_b_dir = 'files' . $D_R . 'board';	// 一般區夾檔存放位置
	$dst_q_dir = 'files' . $D_R . 'quint';	// 精華區夾檔存放位置

	// CopyDirs( $src_b_dir, $dst_b_dir, TRUE );	// 複製一般區夾檔
	// CopyDirs( $src_q_dir, $dst_q_dir, TRUE );	// 複製精華區夾檔
	// $export_tar->addDir( $src_b_dir, $dst_b_dir );
	// $export_tar->addDir( $src_q_dir, $dst_q_dir );
	@mkdir("{$tem_path}{$D_R}files");
	@exec("cp -R $src_b_dir {$tem_path}{$D_R}{$dst_b_dir}");
	@exec("cp -R $src_q_dir {$tem_path}{$D_R}{$dst_q_dir}");

	// 4. 壓縮
	chdir($tem_path);

	$filelist = Array();
	if ($handle = opendir($tem_path)) {
	    while (false !== ($file = readdir($handle))) {
			if (strpos($file, '.') === 0) continue;
			$filelist[] = $file;
	    }
	    closedir($handle);
		if ($export_tar->add_files($filelist)) {
			// chdir( dirname($tem_path) );
			// if (gzip($tar_path)) {
				// $len = filesize($tgz_path);
				header('Cache-control: no-cache, no-store, private, must-revalidate, post-check=0, pre-check=0');
				header('pragma:no-cache');
				header('expires:0');
				header('Content-Type: application/zip');
				header('Content-Disposition: filename="' . $tgz_name . '"');
				header('Content-transfer-encoding: binary');
				// header('Accept-Ranges: bytes');
				// header("Content-Length: {$len}");
				$export_tar->readfile();
                $export_tar->delete();

// 				@unlink($tgz_path) AND
 				@exec("rm -Rf $tem_path");
// 			} else {
// 				wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], 'compression failed:'.$tem_path);
// 				echo "<br><font color=red>Compression FAILED!!</font><br>";
// 			}
		} else {
			wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], 'add to tar failed:'.$tem_path);
			echo "<br><font color=red>Add to tar FAILED!!</font><br>";
		}
	}
	else {
		wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], 'open directory failed:'.$tem_path);
		echo "<br><font color=red>Open directory FAILED!!</font><br>";
	}
?>
