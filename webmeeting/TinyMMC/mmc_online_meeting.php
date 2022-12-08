<?php
	require_once('include/config.php');
	require_once('include/online.php');
	require_once('include/lib.php');
	require_once('include_mcu/xml_status.php');
#======= Function =====================
	function isUserOnline($onlineUserList, $userId)
	{
		if (count($onlineUserList) == 0) return 0;
		for($i = 0, $size = count($onlineUserList); $i < $size; $i++)
		{
			$o_statususer = $onlineUserList[$i];
			if ($o_statususer->userId == $userId)
			{
				//$name = iconv('UTF-8', 'big5', $o_statususer->name);
				$name = $o_statususer->name;
				return $o_statususer->meetingId . ':' . $name;
			}
		}
		return 0;
	}
#======= Main ========================
	$statusOnline = _mcuStatusGetOnlineList();
	echo isUserOnline($statusOnline->statusUserList, $_GET['rid']);

?>
