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

	class mcuDiskStatus {
		var $userId;
		var $diskQuota;
		var $diskUsage;
		var $diskQuotaRemaining;

		function mcuDiskStatus($userId, $quota) {
			$this->userId = $userId;
			$this->diskQuota = $quota * 1024 * 1024;
			$this->updateUsage();
		}

		function updateUsage() {
			$this->diskUsage = $this->usageSummary();
			$this->diskQuotaRemaining = $this->diskQuota - $this->diskUsage;
		}

		function usageSummary() {
			$diskUsage = 0;
			$userDir = utilGetMeetingRecordingDir($this->userId);
			if (! is_dir($userDir)) {
				// This is not an error since the user directory might have not been created yet
				return($diskUsage);
			}
			if (mcuConfigProductionMode()) {
				// suppress warning in production mode
				$dh = @opendir($userDir);
			} else {
				$dh = opendir($userDir);
			}
			if ($dh === false) {
				mcuLogError('MCU Disk Status - Cannot open user directory ' . $userDir);
				return($diskUsage);
			}
			while (($entry = readdir($dh)) !== false) {
				if (($entry != '.') && ($entry != '..')) {
					$path = $userDir . '/' . $entry;
					$fileList = utilFindFileList($path, PATTERN_RECORDING_JNR);
					for ($idx = 0, $size = count($fileList); $idx < $size; $idx++) {
						$diskUsage += filesize($path . '/' . $fileList[$idx]);
					}
				}
			}
			closedir($dh);
			return($diskUsage);
		}
	}
?>
