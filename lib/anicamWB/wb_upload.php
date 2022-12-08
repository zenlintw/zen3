<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');	


	// uniqid() 為建立一唯一識別 ID
	function getBoardOwner($bid)
	{
		list($owner_id) = dbGetStSr('WM_bbs_boards','owner_id',"board_id='{$bid}'", ADODB_FETCH_NUM);
		return intval($owner_id);
	}
	
	function savefile($fpath)
	{
		global $_FILES;
		move_uploaded_file($_FILES['ff']['tmp_name'], mb_convert_encoding($fpath, 'utf-8', 'utf-8,cp950,gb2312,gbk,JIS,eucjp-win,sjis-win'));
		return $fpath;
	}	


	if (!isset($_GET['EditFile']))
	{
		$fname = md5(uniqid(rand(),1)) . '.awp';
		$owner_id = getBoardOwner(intval($_POST['bid']));
		$fpath = sysDocumentRoot . "/base/{$sysSession->school_id}/board/wb_temp/";
		if (!is_dir($fpath)) {
			if (strpos($HTTP_ENV_VARS['OS'], 'Windows') !== false) {
				exec("mkdir -p '$fpath'");
			}else{
				exec("mkdir -pm 755 '$fpath'");
			}
		}
		$fpath = $fpath.$fname;
	}else{
		if (!file_exists($_GET['EditFile']))
		{
			echo '<ERROR>File Not Exist.</ERROR>';
			exit;
		}
		$fpath = $_GET['EditFile'];
		$fname = basename($_GET['EditFile']);
	}
	// $ff 為上傳檔名	
	if (!is_uploaded_file($_FILES['ff']['tmp_name'])) echo '<ERROR>File Not Uploaded</ERROR>';
	$upfile = savefile($fpath);
	
	if ($upfile == ''){
		echo '<ERROR>File Not Uploaded</ERROR>';
	}else{
		echo '<FILEPATH>', $fname, '</FILEPATH>';
	}
	
?>
