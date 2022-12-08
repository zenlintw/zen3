<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/webmeeting/global.php');
#========= function =====================
	function delAnicamMeeting()
	{
		global $MMS_Server_addr, $MMS_Server_API_port, $WM3_Meeting_ID, $sysSession;
		$fsck = @fsockopen($MMS_Server_addr, $MMS_Server_API_port);
		if ($fsck)
		{
			$out = sprintf("DLPT %s \r\n",$WM3_Meeting_ID);
			$rtns = '';
			if (fwrite($fsck, $out) != FALSE)
			{
	   			while (!feof($fsck))
	   			{
	    			$rtns .= fgets($fsck, 4096);
	    		}
	    		if (strpos($rtns, '200') !== false)
	    		{
	    			return true;
	    		}
	    	}
    		fclose($fsck);
		}
		return false;
	}

	$Remote_IP = wmGetUserIp(); // 取IP函式，宣告在 "config/db_initialize.php"
	$Remote_Port = 8080;

	if (isChatroomMMCExists($WM3_Meeting_ID, 'anicam', $rtnObj))
	{
		DeleteExpireMeetingRid('',$sysSession->course_id, 'anicam');
	}

	if ($_POST['task'] == 'cancel')
	{

   		echo '<html>';
   		echo '<head><script language="javascript">';
   		if (delAnicamMeeting())
   		{
   			echo 'alert("'.iconv("Big5","UTF-8",'Anicam-Live會議已完成取消！').'");';
   		}else{
   			echo 'alert("'.iconv("Big5","UTF-8",'Anicam-Live會議取消不成功，請聯絡系統管理員！').'");';
   		}
   		echo 'document.location.href="oh_set.php";';
   		echo '</script></head>';
   		echo '<body></body>';
   		echo '</html>';
   		exit;
	}


	$fsck = @fsockopen($MMS_Server_addr, $MMS_Server_API_port);
	if ($fsck)
	{
		$out = sprintf("CTPT %s %s %s\r\n",$sysSession->ip, $Remote_Port, $WM3_Meeting_ID);
		$rtns = '';
		if (fwrite($fsck, $out) != FALSE)
		{
	   		while (!feof($fsck))
	   		{
	    		$rtns .= fgets($fsck, 4096);
	    	}
	    	if (strpos($rtns, '200') !== false)
	    	{
	    		$extra = sprintf('%s_%s',$_POST['SET_AV'],$_POST['Frame_size']);
	    		buildMeetingChatroom($sysSession->course_id, 'Anicam Live -'.$sysSession->course_name, $WM3_Meeting_ID, $sysSession->username, 'anicam', $extra);
	    		echo '<html>';
	    		echo '<head><script language="javascript">';
	    		echo 'alert("'.iconv("Big5","UTF-8",'Anicam-Live設定完成！').'");';
	    		echo 'document.location.href="oh_set.php";';
	    		echo '</script></head>';
	    		echo '<body></body>';
	    		echo '</html>';
	    		exit;
	    	}
	    }else{
	    	echo 'send error';
	    }
    	fclose($fsck);
	}else{
		echo 'can not connect';
	}

?>
