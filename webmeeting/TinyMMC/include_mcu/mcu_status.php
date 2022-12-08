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
	require_once('xml_status.php');
	require_once('status_online.php');
	require_once('mcu_online.php');

	class mcuStatusInfo {
		var $statusOnlineList = null;

		var $maxReservedMeeting = 0;
		var $maxReservedConnection = 0;
		var $maxReservedOutsideConnection = 0;
		var $realMaxReservedConnection = 0;
	}

	function mcuStatusGetInfo($refresh = false) {
		$statusOnlineList = _mcuStatusGetOnlineList($refresh);
		$statusInfo = new mcuStatusInfo();
		$statusInfo->statusOnlineList = &$statusOnlineList;

		for ($idx = 0, $size = count($statusOnlineList); $idx < $size; $idx++) {
			$statusOnline = &$statusOnlineList[$idx];

			if ($statusOnline->maxReservedMeeting == 0) {
				$statusInfo->maxReservedMeeting += $statusOnline->maxMeeting;
			} else {
				$statusInfo->maxReservedMeeting += $statusOnline->maxReservedMeeting;
			}
			$statusInfo->maxReservedConnection += $statusOnline->maxReservedConnection;
			$statusInfo->maxReservedOutsideConnection += $statusOnline->maxReservedOutsideConnection;
		}
		if (mcuConfigMcuMaxReservedOutsideConnection()) {
			$statusInfo->realMaxReservedConnection = $statusInfo->maxReservedOutsideConnection;
		} else {
			$statusInfo->realMaxReservedConnection = $statusInfo->maxReservedConnection;
		}
		return($statusInfo);
	}

	function mcuStatusIsMeetingOnline($meetingId) {
		$isOnline = false;

		$statusUserList = _mcuStatusGetUserList();
		for ($idx = 0, $size = count($statusUserList); $idx < $size; $idx++) {
			$statusUser = &$statusUserList[$idx];
			if ($statusUser->preparationMode != 1) {
				if ($statusUser->meetingId == $meetingId) {
					$isOnline = true;
					break;
				}
			}
		}
		return($isOnline);
	}

	function mcuStatusIsUserOnline($userId) {
		$isOnline = false;

		$statusUserList = _mcuStatusGetUserList();
		for ($idx = 0, $size = count($statusUserList); $idx < $size; $idx++) {
			$statusUser = &$statusUserList[$idx];
			if ($statusUser->preparationMode != 1) {
				if ($statusUser->userId == $userId) {
					$isOnline = true;
					break;
				}
			}
		}
		return($isOnline);
	}

	function mcuStatusGetOnlineMeetingsByUser($userId) {
		$meetingIds = array();

		$statusUserList = _mcuStatusGetUserList();
		for ($idx = 0, $size = count($statusUserList); $idx < $size; $idx++) {
			$statusUser = &$statusUserList[$idx];
			if ($statusUser->preparationMode != 1) {
				if ($statusUser->userId == $userId) {
					$meetingIds[] = $statusUser->meetingId;
				}
			}
		}
		return($meetingIds);
	}

	function _mcuStatusGetUserList($refresh = false) {
		/*
		 * The calling function, such as mcuStatusIsMeetingOnline(), might be called inside a loop
		 * Declare $mcuStatusUserList as static variable to prevent the list from being retrieved multiple times
		 * However, the optional argument $refresh will force the list to be retrieved
		 */
		static $mcuStatusUserList = null;

		if (is_null($mcuStatusUserList) || $refresh) {
			/*
			 * combine users from all MCU IPs
			 */
			$statusOnlineList = _mcuStatusGetOnlineList($refresh);

			$mcuStatusUserList = array();
			for ($idx = 0, $size = count($statusOnlineList); $idx < $size; $idx++) {
				$statusOnline = &$statusOnlineList[$idx];
				$statusUserList = &$statusOnline->statusUserList;
				if (count($statusUserList) > 0) {
					$mcuStatusUserList = array_merge($mcuStatusUserList, $statusUserList);
				}
			}
		}
		return($mcuStatusUserList);
	}

	function _mcuStatusGetOnlineList($refresh = false) {
		// see notes on $refresh in calling function _mcuStatusGetUserList()
		static $mcuStatusOnlineList = null;

		if (is_null($mcuStatusOnlineList) || $refresh) {
			/*
			 * go through each MCU IP to find online status
			 * make sure the MCU is alive
			 */
			$recordingDir = utilGetMeetingRecordingDir();

			$mcuIpList = mcuConfigMcuIpList();
			$mcuStatusOnlineList = array();
			for ($idx = 0, $size = count($mcuIpList); $idx < $size; $idx++) {
				$ipAddr = $mcuIpList[$idx];

				if (mcuOnlineIsAlive($ipAddr)) {
					$xmlFile = $recordingDir . '/_status_ip_' . $ipAddr . '.xml';
					$xmlStatus = new XmlStatus($xmlFile);
					if (! $xmlStatus->hasError()) {
						$statusOnline = &$xmlStatus->statusOnline;
						$statusOnline->setIpAddr($ipAddr);
						$mcuStatusOnlineList[] = $statusOnline;
					}
				}
			}
		}
		return($mcuStatusOnlineList);
	}
?>
