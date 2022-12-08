<?php
	/**
	 * 匯出
	 *
	 * 建立日期：2004/05/04
	 * @author  KuoYang Tsao
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/forum/lib_export.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');
	require_once(sysDocumentRoot . '/lib/archive_api.php');
	
	define('BOARD_TYPE', 'board');
	
	$sysSession->cur_func = '900100700';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}
	
	$post_node = $_POST['node'];
	$post_site = $_POST['site'];
	
	if(empty($post_node) || empty($post_site) ) {
		wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'Wrong parameters!');
		die('Wrong parameters!');
	}

	if (BOARD_TYPE == 'board') 
	{
		$post_obj = new bbsPost($sysSession->board_id, $post_node, $post_site, $sysSession->board_name);
		if($sysSession->news_board)	// 若是最新消息型式的文章, 需再取得日期欄位
			$post_obj->getNewsFields();
	}
	else
	{
		$post_obj  = new bbsPost($sysSession->board_id, $post_node, $post_site, $sysSession->board_name, $sysSession->q_path, 'quint');
	}

	$str        ='';
	$str_header = '';
	$getXML     = false;
	$getHTML    = false;

	$base_path = get_attach_file_path(BOARD_TYPE, $sysSession->board_ownerid). DIRECTORY_SEPARATOR . $post_node;
	mkdirs($base_path);

	$getXML = $post_obj->exportXML($str_header,$str);
	if($getXML) {
		$xml_data = $str;
	}


	$getHTML = $post_obj->exportHTML($str);
	if($getHTML) {
		$html_data = $str;
	}


	if($getXML && $getHTML) {
		$linkname = 'post.zip';
		$fname    = sysTempPath . DIRECTORY_SEPARATOR . $linkname;
		$export_obj = new ZipArchive_php4($linkname);
		$export_obj->add_string($xml_data, 'post.xml');
		$export_obj->add_string($html_data, 'post.html');
		if($post_obj->m_post['attach'] != '') {
			$a = explode(chr(9), trim($post_obj->m_post['attach']));
			for($i=0; $i<count($a); $i+=2){
				$filename = $base_path . DIRECTORY_SEPARATOR . $a[$i+1];
				$handle = fopen($filename, "rb");
				if($handle) {
					$contents = fread($handle, filesize($filename));
					$export_obj->add_string($contents, $a[$i+1]);
					fclose($handle);
				}
			}
		}

		header('Content-Disposition: attachment; filename="' . $linkname . '"');
		header('Content-Transfer-Encoding: binary');
		header('Content-Type: application/zip; name="' . $linkname . '"');
		$export_obj->readfile();
		$export_obj->delete();
	}
	else {
		wmSysLog($sysSession->cur_func, $sysSession->class_id , $sysSession->board_id , 2, 'auto', $_SERVER['PHP_SELF'], 'Data retrieve failed!');
		echo "<font>Data retrieve failed</font>";
	}
?>
