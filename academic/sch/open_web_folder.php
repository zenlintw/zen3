<?php
	/**
	 * 教材上傳
	 * $Id: open_web_folder.php,v 1.1 2010/02/24 02:38:41 saly Exp $
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func='700600100';
	$sysSession->restore();
	if (!aclVerifyPermission(700600100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}

	if ((defined('CONTENT') && CONTENT && ereg('^[0-9]{5}_[0-9]{6}$', $_SERVER['argv'][0])) ||
		(defined('COURSE')  && COURSE  && ereg('^[0-9]{5}_[0-9]{8}$', $_SERVER['argv'][0])) ||
		ereg('^[0-9]{5}_door$', $_SERVER['argv'][0]))
	{

		$headers = apache_request_headers();
                        
                                setcookie('forum_sortby', '', time()-3600, '/');
                                setcookie('idx', $_COOKIE['idx'], time()+21600, "/");
                                setcookie('idx', $_COOKIE['idx'], time()+21600, "/{$_SERVER['argv'][0]}");
		
		$urlx = $_SERVER['HTTP_HOST'] . ($_SERVER['SERVER_PORT']=='80' ? '':":{$_SERVER['SERVER_PORT']}") . '/' . $_SERVER['argv'][0];
		
		// 參考資料 http://support.microsoft.com/kb/938203/zh-tw
		 echo <<< EOB
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<style>
        .hFolder{ behavior:url(#default#httpFolder); }
</style>

<script>
        function fnNavigate(){
                oViewFolder.navigateFrame("http://$urlx","_self");
        }
        window.onload = function () {
                document.getElementById("oViewFolder").click();
                if (navigator.userAgent.search(/ MSIE (\d+)\./) > -1 && parseInt(RegExp.$1) > 6 && parseInt(RegExp.$1) < 8) self.close();
        }
</script>
</head>
<body>
        <a id="oViewFolder" class="hFolder" onclick="fnNavigate()">Display this page in folder view.</a>
</body>
</html>




EOB;
		
	}
	else if (!defined('CONTENT') && !defined('COURSE'))
	{
		echo <<< EOB
<HTML>
<HEAD>
<script>
window.open('open_web_folder.php?{$sysSession->school_id}_door', 'upload_win', 'width=500,height=375,status=0,toolbar=1,menubar=1,resizable=1');
</script>
</HEAD>
<BODY>
</BODY>
</HTML>

EOB;
	
	}
?>
