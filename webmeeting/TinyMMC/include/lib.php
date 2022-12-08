<?php
	function _mcuStatusGetOnlineList($refresh = false) {
		// see notes on $refresh in calling function _mcuStatusGetUserList()
		static $mcuStatusOnlineList = null;
		if (is_null($mcuStatusOnlineList) || $refresh) {
			/*
			 * go through each MCU IP to find online status
			 * make sure the MCU is alive
			 */
			$recordingDir = DEFAULT_RECORDING_DIR;
			$mcuIpList    = DEFAULT_MCU_IP;
			$mcuStatusOnlineList = array();
			$ipAddr       = DEFAULT_MCU_IP;
			if (mcuOnlineIsAlive($ipAddr)) {
				$xmlFile = $recordingDir . '/_status_ip_' . $ipAddr . '.xml';
				$xmlStatus = new XmlStatus($xmlFile);
				if (! $xmlStatus->hasError()) {
					$statusOnline = &$xmlStatus->statusOnline;
					$statusOnline->setIpAddr($ipAddr);
				}
				return $statusOnline;
			}
		}
	}
?>