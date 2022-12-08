<?php
//
//    ------------------------------------------------------------------------------------
//    Copyright (c) 2004 HOMEMEETING INC. All rights reserved.
//    ------------------------------------------------------------------------------------
//    This source file is subject to HomeMeeting license,
//    that is bundled with this package in the file "license.txt".
//    ------------------------------------------------------------------------------------
//
	require_once('xml_license.php');
	require_once('mcu_wrapper.php');
	
	function mcuLicenseIsExpired() {
		$expired = true;
		
		$mcuLicense = mcuLicenseGet();
		if (is_null($mcuLicense)) {
			return($expired);
		}
		if (strtolower($mcuLicense->expireDate) == "never") {
			$expired = false;
		} else {
			// set expiration time to the end of the expiration date
			$expireTime = strtotime($mcuLicense->expireDate . " 23:59:59");
			// grace period is specified in hours
			$checkTime = time() - (mcuConfigMcuLicenseGracePeriod() * 3600);
			if ($expireTime >= $checkTime) {
				$expired = false;
			}
		}
		return($expired);
	}
	
	function mcuLicenseGet() {
		static $mcuLicense = null;

		if (is_null($mcuLicense)) {
			$xmlFile = mcuConfigMcuLicenseFile();
			$xmlLicense = new XmlLicense($xmlFile);
			if (! $xmlLicense->hasError()) {
				$mcuLicense = &$xmlLicense->license;
				$mcuLicense->licensePath = $xmlFile;
			}
		}
		return($mcuLicense);
	}
?>
