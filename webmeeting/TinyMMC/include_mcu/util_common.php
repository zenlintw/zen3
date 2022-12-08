<?php
//
//    ------------------------------------------------------------------------------------
//    Copyright (c) 2004 HOMEMEETING INC. All rights reserved.
//    ------------------------------------------------------------------------------------
//    This source file is subject to HomeMeeting license,
//    that is bundled with this package in the file "license.txt".
//    ------------------------------------------------------------------------------------
//

	require_once('mcu_wrapper.php');

	function utilGetMeetingRecordingDir($userId = null, $meetingId = null, $subdir = null) {
		$dir = mcuConfigMcuRecordingDir();

		$last = substr($dir, strlen($dir) - 1);
		if ($last == '/' || $last == "\\") {
			$dir = substr($dir, 0, strlen($dir) - 1);
		}
		/*
 		 * expand the directory based on function parameters
		 */
		if (! is_null($userId)) {
			$dir .= '/_user/' . $userId;
		}
		if (! is_null($meetingId)) {
			$dir .= '/' . $meetingId;
		}
		if (! is_null($subdir)) {
			$dir .= '/' . $subdir;
		}
		return($dir);
	}

	define('FILE_EXT_JNR', '.jnr');
	define('FILE_EXT_XML', '.xml');

	define('PATTERN_RECORDING_JNR', '/^_recording_.*\.jnr$/i');
	define('PATTERN_RECORDING_XML', '/^_recording_.*\.xml$/i');
	define('PATTERN_PARTICIPANT'  , '/^_participant_.*\.xml$/i');

	function utilFileReplaceExt($fileName, $fromExt, $toExt) {
		return(preg_replace('/' . preg_quote($fromExt) . '$/', $toExt, $fileName));
	}

	function utilFindFile($dir, $pattern) {
		$entry = null;

		if (! is_dir($dir)) {
			return($entry);
		}
		if (mcuConfigProductionMode()) {
			// suppress warning in production mode
			$dh = @opendir($dir);
		} else {
			$dh = opendir($dir);
		}
		if ($dh === false) {
			mcuLogWarning('MCU Find - Cannot open directory ' . $dir);
			return($entry);
		}
		while (($entry = readdir($dh)) !== false) {
			if (is_file($dir . '/' . $entry)) {
				if (preg_match($pattern, $entry)) {
					closedir($dh);
					return($entry);
				}
			}
		}
		closedir($dh);
		return($entry);
	}

	function utilFindFileList($dir, $pattern) {
		$fileList = array();

		if (! is_dir($dir)) {
			return($fileList);
		}
		if (mcuConfigProductionMode()) {
			// suppress warning in production mode
			$dh = @opendir($dir);
		} else {
			$dh = opendir($dir);
		}
		if ($dh === false) {
			mcuLogWarning('MCU Find - Cannot open directory ' . $dir);
			return($fileList);
		}
		while (($entry = readdir($dh)) !== false) {
			if (is_file($dir . '/' . $entry)) {
				if (preg_match($pattern, $entry)) {
					$fileList[] = $entry;
				}
			}
		}
		closedir($dh);
		return($fileList);
	}

	function utilDeleteDir($dir) {
		if (! is_dir($dir)) {
			return(true);
		}

		$productionMode = mcuConfigProductionMode();
		if ($productionMode) {
			// suppress warning in production mode
			$dh = @opendir($dir);
		} else {
			$dh = opendir($dir);
		}
		if ($dh === false) {
			mcuLogWarning('MCU Delete - Cannot open directory ' . $dir);
			return(false);
		}
		$stat_ok = true;
		while (($entry = readdir($dh)) !== false) {
			if ($entry != '.' && $entry != '..') {
				$entry = $dir . '/' . $entry;
				if (is_file($entry)) {
					if ($productionMode) {
						// suppress warning in production mode
						$stat_ok = @unlink($entry);
					} else {
						$stat_ok = unlink($entry);
					}
					if (! $stat_ok) {
						mcuLogWarning('MCU Delete - Cannot delete file ' . $entry);
					}
				} else {
					$stat_ok = utilDeleteDir($entry);
				}
			}
			if (! $stat_ok) {
				break;
			}
		}
		closedir($dh);
		if ($stat_ok) {
			if ($productionMode) {
				// suppress warning in production mode
				$stat_ok = @rmdir($dir);
			} else {
				$stat_ok = rmdir($dir);
			}
			if (! $stat_ok) {
				mcuLogWarning('MCU Delete - Cannot delete directory ' . $dir);
			}
		}
		return($stat_ok);
	}

	function utilCreateDir($dir, $mode = 0777) {
		if (is_dir($dir)) {
			return(true);
		}

		$parent = dirname($dir);
		if (! utilCreateDir($parent, $mode)) return(false);

		if (mcuConfigProductionMode()) {
			// suppress warning in production mode
			$stat_ok = @mkdir($dir, $mode);
		} else {
			$stat_ok = mkdir($dir, $mode);
		}
		if (! $stat_ok) {
			mcuLogWarning('MCU Create - Cannot create directory ' . $dir);
		}
		return($stat_ok);
	}
?>
