<?php
	/**
	 * 讀取夾檔
	 *
	 * 建立日期：2003/05/14
	 * @author  ShenTing Lin
	 * @version $Id: attach.php,v 1.1 2010/02/24 02:40:17 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/lang/msg_center.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/lib_adjust_char.php');

	// $sysSession->cur_func = '2200200500';
	// $sysSession->restore();
	if (!aclVerifyPermission(2200200500, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$ticket = md5($sysSiteNo . $sysSession->msg_serial . $sysSession->username . 'Attachment' . $sysSession->ticket . $sysSession->school_id . $_GET['f']);
	if ($ticket != $_GET['t'])
	{
		die($MSG['no_attachment'][$sysSession->lang]);
	}
	else
	{
		$filepath = MakeUserDir($sysSession->username);
		// $filepath = substr($filepath, strlen(sysDocumentRoot));
		list($attachment) = dbGetStSr('WM_msg_message', 'attachment', "`msg_serial`={$sysSession->msg_serial} AND `receiver`='{$sysSession->username}'", ADODB_FETCH_NUM);
		$f  = explode(chr(9), trim($attachment));

		// 回傳原本的檔名
		$name = $_GET['f'];
		$key  = array_search($_GET['f'], $f);
		//if (!empty($key)) $name = un_adjust_char($f[$key - 1]);
		if (!empty($key)) $name = $f[$key - 1];
		$filename = $filepath . DIRECTORY_SEPARATOR . $_GET['f'];
		if (!file_exists($filename) || !is_file($filename))
		{
		   die('<h2>no attachment.</h2>');
		}

		$len = filesize($filename);
        
        $ua = $_SERVER["HTTP_USER_AGENT"];
        //IE6~9 MSIE x.x
        if( preg_match("/MSIE [6-9]{1}/i", $ua) ){
            $name = un_adjust_char($name);
        //IE10 - Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)
        }else if (preg_match("/MSIE 10/i", $ua)) {
            $name = rawurlencode($name);
        //IE11 - gecko
        }else if (preg_match("/gecko/i", $ua)) { 
            $name = rawurlencode($name);
        }
        
        
		header('Cache-control: no-cache, no-store, private, must-revalidate, post-check=0, pre-check=0');
		header('pragma:no-cache');
		header('expires:0');
		header('Content-transfer-encoding: binary');
		header("Content-Disposition: attachment; filename={$name}\n");
		header("Content-Type: application/octet-stream; name={$name}\n");
		header('Accept-Ranges: bytes');
		// header("Content-Length: {$len}\n");

		readfile($filename);
	}
?>
