<?php
//
//    ------------------------------------------------------------------------------------
//    Copyright (c) 2004 HOMEMEETING INC. All rights reserved.
//    ------------------------------------------------------------------------------------
//    This source file is subject to HomeMeeting license,
//    that is bundled with this package in the file "license.txt".
//    ------------------------------------------------------------------------------------
//
	require_once('include/recording.php');
	
	$users = array();
	GetUserList($users);
	$rec_info = array();
	for($i=0; $i<count($users); $i++)
	{
		//if (strpos($users[$i],$_GET['ownerId'])===FALSE) continue;	//取得此課程所有的錄影響(含群組討論)
		if (strcmp($users[$i],$_GET['ownerId']) != 0) continue;			//不含群組討論
		$meetings = array();
		$res = array();
		$meetings = GetMeetingList($users[$i]);
		for($j=0; $j<count($meetings); $j++)
		{
			$info = '';
			$o_holder = new RecordingHelper();
			$o_holder->GetInfo($users[$i], $meetings[$j]['meeting-id'], $meetings[$j]['recording-id'], &$info);
			$info["meeting-id"] = $meetings[$j]['meeting-id'];
			$info["recording-id"] = $meetings[$j]['recording-id'];
			$path = DEFAULT_RECORDING_DIR . "/_user/{$_GET['ownerId']}/{$info['meeting-id']}/{$info['recording-id']}.jnr";
			if (file_exists($path))
				$rec_info[$users[$i]][] = $info;
		}
	}
	echo base64_encode(serialize($rec_info));
?>