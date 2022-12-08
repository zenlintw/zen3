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
	require_once('xml_recording.php');
	require_once('recording_status.php');

	function mcuRecordingDeleteFiles($userId, $meetingId) {
		$dir = utilGetMeetingRecordingDir($userId, $meetingId);
		$fileList = utilFindFileList($dir, PATTERN_RECORDING_JNR);

		$stat_ok = true;
		for ($idx = 0, $size = count($fileList); $idx < $size; $idx++) {
			$entry = $dir . '/' . $fileList[$idx];
			if (mcuConfigProductionMode()) {
				// suppress warning in production mode
				$stat_ok = @unlink($entry);
			} else {
				$stat_ok = unlink($entry);
			}
			if (! $stat_ok) {
				mcuLogError('MCU Recording - Cannot delete file ' . $entry);
				break;
			}
		}
		return($stat_ok);
	}

	function mcuRecordingDeleteDir($userId, $meetingId) {
		$stat_ok = true;
		// perform validation to avoid unintentional deletion
		if (strlen($userId) > 0 && strlen($meetingId) > 0) {
			$dir = utilGetMeetingRecordingDir($userId, $meetingId);
			$stat_ok = utilDeleteDir($dir);
		}
		return($stat_ok);
	}

	function mcuRecordingDeleteUserDir($userId) {
		$stat_ok = true;
		// perform validation to avoid unintentional deletion
		if (strlen($userId) > 0) {
			$dir = utilGetMeetingRecordingDir($userId);
			$stat_ok = utilDeleteDir($dir);
		}
		return($stat_ok);
	}

	function mcuRecordingCopy($fromUserId, $fromMeetingId, $recordingFile, $toUserId, $toMeetingId) {
		$stat_ok = true;
		// perform validation to avoid unintentional copy
		if (strlen($fromUserId) > 0 && strlen($fromMeetingId) > 0
			&& strlen($recordingFile) > 0
			&& strlen($toUserId) > 0 && strlen($toMeetingId) > 0)
		{
			$fromDir = utilGetMeetingRecordingDir($fromUserId, $fromMeetingId);
			$toDir = utilGetMeetingRecordingDir($toUserId, $toMeetingId);
			if (utilCreateDir($toDir)) {
				$fromFile = $fromDir . '/' . $recordingFile . FILE_EXT_XML;
				$toFile = $toDir . '/' . $recordingFile . FILE_EXT_XML;
				$stat_ok = copy($fromFile, $toFile);
				if ($stat_ok) {
					$fromFile = $fromDir . '/' . $recordingFile . FILE_EXT_JNR;
					$toFile = $toDir . '/' . $recordingFile . FILE_EXT_JNR;
					$stat_ok = copy($fromFile, $toFile);
				}
			}
		}
		return($stat_ok);
	}

	function mcuRecordingGetStatus($userId, $meetingId) {
		$dir = utilGetMeetingRecordingDir($userId, $meetingId);
		$fileList = utilFindFileList($dir, PATTERN_RECORDING_XML);

		$recordingStatus = null;
		$savedStartTime = 0;
		for ($idx = 0, $size = count($fileList); $idx < $size; $idx++) {
			$xmlFile = $dir . '/' . $fileList[$idx];
			$xmlRecording = new XmlRecording($xmlFile);
			if (! $xmlRecording->hasError()) {
				$recStatus = &$xmlRecording->recordingStatus;
				if ($recStatus->preparationMode != 1) {
					if ($recStatus->startTime > $savedStartTime) {
						$savedStartTime = $recStatus->startTime;
						$recordingStatus = &$recStatus;
						$recordingStatus->recordingFile = basename($xmlFile, FILE_EXT_XML);
						$recordingStatus->recordingPath = utilFileReplaceExt($xmlFile, FILE_EXT_XML, FILE_EXT_JNR);
					}
				}
			}
		}
		if (! is_null($recordingStatus)) {
			$recordingStatus->userId = $userId;
			$recordingStatus->meetingId = $meetingId;
			if (is_file($recordingStatus->recordingPath)) {
				$recordingStatus->recordingSize = filesize($recordingStatus->recordingPath);
			} else {
				$recordingStatus->recordingPath = null;
				$recordingStatus->recordingFile = null;
			}
		}
		return($recordingStatus);
	}

	function mcuRecordingGetStatusList($userId) {
		$userDir = utilGetMeetingRecordingDir($userId);
		if (! is_dir($userDir)) {
			return(null);
		}
		if (mcuConfigProductionMode()) {
			// suppress warning in production mode
			$dh = @opendir($userDir);
		} else {
			$dh = opendir($userDir);
		}
		if ($dh === false) {
			mcuLogError('MCU Recording - Cannot open user directory ' . $userDir);
			return(null);
		}
		$recordingStatusList = array();
		while (($entry = readdir($dh)) !== false) {
			if (($entry != '.') && ($entry != '..')) {
				$recordingStatus = mcuRecordingGetStatus($userId, $entry);
				if ((! is_null($recordingStatus)) && (! is_null($recordingStatus->recordingFile))) {
					$recordingStatusList[] = $recordingStatus;
				}
			}
		}
		usort($recordingStatusList, 'mcuRecordingStatusCompare');
		return($recordingStatusList);
	}
?>
