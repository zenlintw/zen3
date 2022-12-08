<?php
//
//    ------------------------------------------------------------------------------------
//    Copyright (c) 2004 HOMEMEETING INC. All rights reserved.
//    ------------------------------------------------------------------------------------
//    This source file is subject to HomeMeeting license,
//    that is bundled with this package in the file "license.txt".
//    ------------------------------------------------------------------------------------
//

	require_once('util_common.php');
	require_once('xml_participant.php');

	function mcuParticipantGetOnlineList($userId, $meetingId) {
		$dir = utilGetMeetingRecordingDir($userId, $meetingId, 'participant');
		$fileList = utilFindFileList($dir, PATTERN_PARTICIPANT);

		$savedTimeModified = 0;
		$xmlFile = null;
		for ($idx = 0, $size = count($fileList); $idx < $size; $idx++) {
			$file = $dir . '/' . $fileList[$idx];
			$timeModified = filemtime($file);
			if ($timeModified > $savedTimeModified) {
				$savedTimeModified = $timeModified;
				$xmlFile = $file;
			}
		}
		$participantOnlineList = null;
		if (! is_null($xmlFile)) {
			$xmlParticipant = new XmlParticipant($xmlFile);
			if (! $xmlParticipant->hasError()) {
				$participantOnlineList = &$xmlParticipant->participantOnlineList;
			}
		}
		return($participantOnlineList);
	}

	function mcuParticipantIsUserOnline($participantOnlineList, $userId, $email) {
		$found = false;

		for ($idx = 0, $size = count($participantOnlineList); $idx < $size; $idx++) {
			$participantOnline = &$participantOnlineList[$idx];
			if (is_numeric($participantOnline->userId) && is_numeric($userId)) {
				if ($participantOnline->userId == $userId) {
					$found = true;
					break;
				}
			} else {
				if (strtolower($participantOnline->userId) == strtolower($email)) {
					$found = true;
					break;
				}
			}
		}
		return($found);
	}
?>
