<?php
//
//    ------------------------------------------------------------------------------------
//    Copyright (c) 2004 HOMEMEETING INC. All rights reserved.
//    ------------------------------------------------------------------------------------
//    This source file is subject to HomeMeeting license,
//    that is bundled with this package in the file "license.txt".
//    ------------------------------------------------------------------------------------
//
	class mcuStatusOnline {
		var $ipAddr;

		// XML Elements
		var $maxMeeting;
		var $maxReservedMeeting;
		var $maxConnection;
		var $maxOutsideConnection;
		var $maxReservedConnection;
		var $maxReservedOutsideConnection;
		var $statusUserList;
		
		function setIpAddr($ipAddr) {
			$this->ipAddr = $ipAddr;
			$statusUserList = &$this->statusUserList;
			for ($idx = 0, $size = count($statusUserList); $idx < $size; $idx++) {
				$statusUserList[$idx]->ip = $ipAddr;
			}
		}
	}
?>
