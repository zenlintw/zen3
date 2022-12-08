<?php
	/**
	 * 【程式功能】
	 * 建立日期：2004/10/27
	 * @author  Wiseguy Liang
	 * @version $Id: grade_team.php,v 1.1 2010/02/24 02:39:07 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	ob_start();
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/grade.php');

	$sysSession->cur_func = '1400200100';
	$sysSession->restore();
	if (!aclVerifyPermission(1400200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// #47346 Chrome [教室/資訊區/成績資訊] 組距圖表要是沒有資料時，出現的訊息是亂碼。->增加編碼片段
    showXHTML_head_B($MSG['empty_data'][$sysSession->lang]);
	showXHTML_head_E();

	$ticket = substr($_SERVER['argv'][0], 0, 32);
	$gid    = intval(substr($_SERVER['argv'][0], -8));
	if ($ticket != md5(sysTicketSeed . $_COOKIE['idx'] . $gid)) {
	   wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'Fake Ticket');
	   die('fake ticket.');
	   ob_end_flush();
	}

	if ($gid)
	{
		$ary = dbGetCol('WM_grade_item', 'score', "grade_id={$gid}");
		if (is_array($ary) && count($ary))
		{
			$ary[] = 0; $ary[] = 0;
			$aries = implode(',', $ary);

			echo <<< EOB
<body onload="document.getElementById('autos').submit();">
<form action="grade_graph1.php" method="POST" id="autos">
<input type="hidden" name="scores" value="$aries">
</form>
</body>
EOB;
		}
		else
			echo '<h2 align="center">'.$MSG['empty_data'][$sysSession->lang].'</h2>';
	}
	else
		echo '<h2 align="center">'.$MSG['format_error'][$sysSession->lang].'</h2>';
	ob_end_flush();
?>
