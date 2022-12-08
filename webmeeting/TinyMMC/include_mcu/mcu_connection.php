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
	require_once('connection_status.php');
	require_once('mcu_online.php');

	class mcuConnectionInfo {
		var $connectionStatusList = null;

		var $nextMcuIpAddr = null;
		var $maxInstantConnection = 0;
		var $availableInstantConnection = 0;
	}

	function mcuConnectionGetInfo() {
		$connectionStatusList = _mcuConnectionGetStatusList();
		$connectionInfo = new mcuConnectionInfo();
		$connectionInfo->connectionStatusList = &$connectionStatusList;

		$savedDiff = 0;
		for ($idx = 0, $size = count($connectionStatusList); $idx < $size; $idx++) {
			$connectionStatus = &$connectionStatusList[$idx];

			$connectionInfo->maxInstantConnection +=
						$connectionStatus->maxConnectionSupported -
						$connectionStatus->reservedMaxConnectionSupported;
			$connectionInfo->availableInstantConnection +=
						$connectionStatus->maxConnectionSupported -
						$connectionStatus->reservedMaxConnectionSupported +
						$connectionStatus->reservedConnectionCount -
						$connectionStatus->connectionCount;

			$diff = $connectionStatus->maxConnectionSupported - $connectionStatus->connectionCount;
			if ($diff > $savedDiff) {
				$savedDiff = $diff;
				$connectionInfo->nextMcuIpAddr = $connectionStatus->ipAddr;
			}
		}
		return($connectionInfo);
	}

	function _mcuConnectionGetStatusList() {
		$connectionStatusList = null;

		/*
		 * go through each MCU IP to find connection status
		 * make sure the MCU is alive
		 */
		$recordingDir = utilGetMeetingRecordingDir();

		$mcuIpList = mcuConfigMcuIpList();
		$connectionStatusList = array();
		for ($idx = 0, $size = count($mcuIpList); $idx < $size; $idx++) {
			$ipAddr = $mcuIpList[$idx];

			if (mcuOnlineIsAlive($ipAddr)) {
				$connectionStatus = _mcuConnectionGetStatus($ipAddr);
				if (! is_null($connectionStatus)) {
					$connectionStatusList[] = $connectionStatus;
				}
			}
		}
		return($connectionStatusList);
	}

	function _mcuConnectionGetStatus($ipAddr) {
		$connectionStatus = null;

		$recordingDir = utilGetMeetingRecordingDir();

		$file = $recordingDir . '/_cluster_connection_' . $ipAddr . '_.txt';
		if (mcuConfigProductionMode()) {
			// suppress warning in production mode
			$fh = @fopen($file, 'r');
		} else {
			$fh = fopen($file, 'r');
		}
		if ($fh === false) {
			mcuLogError('MCU Connection - Cannot open file ' . $file);
			return($connectionStatus);
		}
		$data = fgets($fh);
		@fclose($fh);

		$valueArray = explode(' ', $data);
		if (count($valueArray) != 10) {
			return($connectionStatus);
		}
		$connectionStatus = new mcuConnectionStatus();
		$connectionStatus->ipAddr = $ipAddr;
		$i = 0;
		$connectionStatus->meetingCount = $valueArray[$i++];
		$connectionStatus->maxMeetingSupported = $valueArray[$i++];
		$connectionStatus->connectionCount = $valueArray[$i++];
		$connectionStatus->maxConnectionSupported = $valueArray[$i++];
		$connectionStatus->outsideConnectionCount = $valueArray[$i++];
		$connectionStatus->maxOutsideConnectionSupported = $valueArray[$i++];
		$connectionStatus->reservedConnectionCount = $valueArray[$i++];
		$connectionStatus->reservedMaxConnectionSupported = $valueArray[$i++];
		$connectionStatus->reservedOutsideConnectionCount = $valueArray[$i++];
		$connectionStatus->reservedMaxOutsideConnectionSupported = $valueArray[$i++];

		return($connectionStatus);
	}
?>
