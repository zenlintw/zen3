<?php
		
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	
	function savefile($fpath)
	{
		global $_FILES;
		move_uploaded_file($_FILES['ff']['tmp_name'], mb_convert_encoding($fpath, 'utf-8', 'utf-8,cp950,gb2312,gbk,JIS,eucjp-win,sjis-win'));
		return $fpath;
	}

	$fp = fopen ('IPLOG.txt', 'a+');
	fwrite($fp, date('Ymd H:i:s') . 'Processed IP: ' . $HTTP_SERVER_VARS['REMOTE_ADDR'] . "\r\n");
	
	// uniqid() 為建立一唯一識別 ID
	ob_start();
	print_r($_POST);
	$str = ob_get_contents();	
	ob_end_clean();
	fwrite($fp, $str);
	$fname = md5(uniqid(rand(),1)) . '.mp3';
	$fpath = sysDocumentRoot . "/base/{$sysSession->school_id}/board/{$_POST['bid']}/";
	fwrite($fp, $fpath . '||||');
	if (!is_dir($fpath)) {
		if (strpos($HTTP_ENV_VARS['OS'], 'Windows') !== false) {
			exec("mkdir -p '$fpath'");
		}else{
			exec("mkdir -pm 755 '$fpath'");
		}
	}

	$fpath = $fpath.$fname;

	// $ff 為上傳檔名	
	if ( !is_uploaded_file($_FILES['ff']['tmp_name']) ) echo '<ERROR>File Not Uploaded</ERROR>';
	$upfile = savefile($fpath);
	
	
	if ($upfile == ''){
		echo '<ERROR>File Not Uploaded</ERROR>';
	}else{
		echo '<FILEPATH>' , $fname , '</FILEPATH>';
	}
	fclose($fp);
			

	
?>
