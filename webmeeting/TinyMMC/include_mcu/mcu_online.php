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

	/*
	 * The calling function to the following functions can decide whether to check MCU socket connection or not
	 * The optional function argument $checkSocketTimeout defines the timeout of socket connection
	 * If the value of $checkSocketTimeout is negative, checking of MCU socket connection will not be performed
	 * If $checkSocketTimeout is not provided, the configuration setting defines the default value of $checkSocketTimeout
	 */
	function mcuOnlineIsOnline($checkSocketTimeout = null) {
		if (is_null($checkSocketTimeout)) {
			$checkSocketTimeout = mcuConfigMcuCheckSocketTimeout();
		}
		$mcuIpList = mcuConfigMcuIpList();
		for ($idx = 0, $size = count($mcuIpList); $idx < $size; $idx++) {
			// return TRUE for online if any MCU is alive
			$ipAddr = $mcuIpList[$idx];
			if (mcuOnlineIsAlive($ipAddr, $checkSocketTimeout)) {
				return(true);
			}
		}
		return(false);
	}

	function mcuOnlineIsAlive($ipAddr, $checkSocketTimeout = null) {
		$isAlive = false;

		if (_mcuOnlineFileStatus($ipAddr)) {
			if (is_null($checkSocketTimeout)) {
				$checkSocketTimeout = mcuConfigMcuCheckSocketTimeout();
			}
			if ($checkSocketTimeout < 0) {
				$isAlive = true;
			} else {
				// check MCU socket connection only if $checkSocketTimeout is >= 0
				$mcuPort = mcuConfigMcuPort();
				$isAlive = _mcuOnlineSocketStatus($ipAddr, $mcuPort, $checkSocketTimeout);
			}
		}
		return($isAlive);
	}

	function _mcuOnlineFileStatus($ipAddr) {
		$stat_ok = false;

		$recordingDir = utilGetMeetingRecordingDir();

		$file = $recordingDir . '/_cluster_ip_' . $ipAddr . '.txt';
		if (! is_file($file)) {
			// If the file does not exist, MCU is not alive
			return($stat_ok);
		}
		if (mcuConfigProductionMode()) {
			// suppress warning in production mode
			$fh = @fopen($file, 'r');
		} else {
			$fh = fopen($file, 'r');
		}
		if ($fh === false) {
			mcuLogError('MCU Online - Cannot open file ' . $file);
			return($stat_ok);
		}
		$data = fgets($fh);
		if (trim($data) == '1') {
			$stat_ok = true;
		}
		@fclose($fh);

		return($stat_ok);
	}

	function _mcuOnlineSocketStatus($ipAddr, $port, $timeout) {
		$stat_ok = false;

		if (mcuConfigProductionMode()) {
			// suppress warning in production mode
			$fp = @fsockopen($ipAddr, $port, $errno, $errstr, $timeout);
		} else {
			$fp = fsockopen($ipAddr, $port, $errno, $errstr, $timeout);
		}
		if ($fp !== false) {
			$stat_ok = true;
			@fclose($fp);
		}
		return($stat_ok);
	}
?>
