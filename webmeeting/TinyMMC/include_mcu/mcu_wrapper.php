<?php
//
//    ------------------------------------------------------------------------------------
//    Copyright (c) 2004 HOMEMEETING INC. All rights reserved.
//    ------------------------------------------------------------------------------------
//    This source file is subject to HomeMeeting license,
//    that is bundled with this package in the file "license.txt".
//    ------------------------------------------------------------------------------------
//

	/*
	 * wrapper functions that are called by other PHP files in this directory
	 * these wrapper functions allow all the PHP files in this directory to be integrated independently
	 */
	function mcuConfigProductionMode() {
		global $_appConfig;
		// productionMode -- production mode (TRUE or FALSE)
		return($_appConfig->productionMode);
	}
	function mcuConfigMcuRecordingDir() {
		global $_appConfig;
		// mcuRecordingDir -- MCU recording root directory
		return($_appConfig->mcuRecordingDir);
	}
	function mcuConfigMcuLicenseFile() {
		global $_appConfig;
		// mcuLicenseFile -- path of MCU license file
		return($_appConfig->mcuLicenseFile);
	}
	function mcuConfigMcuLicenseGracePeriod() {
		global $_appConfig;
		// mcuLicenseGracePeriod -- grace period (in hours) after MCU license expires
		return($_appConfig->mcuLicenseGracePeriod);
	}
	function mcuConfigMcuIpList() {
		// mcuIpList -- array of MCU IP addresses
		return(configGetMcuIpList());
	}
	function mcuConfigMcuPort() {
		global $_appConfig;
		// mcuPort -- MCU port number
		return($_appConfig->mcuPort);
	}
	function mcuConfigMcuCheckSocketTimeout() {
		global $_appConfig;
		// mcuCheckSocketTimeout -- timeout (in seconds) of checking socket connection
		return($_appConfig->mcuCheckSocketTimeout);
	}
	function mcuConfigMcuMaxReservedOutsideConnection() {
		global $_appConfig;
		// mcuMaxReservedOutsideConnection -- Maximum Reserved Outside Connection should be used to enforce meeting reservation (TRUE or FALSE)
		return($_appConfig->mcuMaxReservedOutsideConnection);
	}

	function mcuLogError($msg) {
		logError($msg, 1);
	}
	function mcuLogWarning($msg) {
		logWarning($msg, 1);
	}
?>
