<?php
//
//    ------------------------------------------------------------------------------------
//    Copyright (c) 2004 HOMEMEETING INC. All rights reserved.
//    ------------------------------------------------------------------------------------
//    This source file is subject to HomeMeeting license,
//    that is bundled with this package in the file "license.txt".
//    ------------------------------------------------------------------------------------
//

	class mcuRecordingStatus {
		var $userId;
		var $meetingId;
		var $recordingFile = null;
		var $recordingPath = null;
		var $recordingSize = 0;

		// XML Elements
		var $startTime;
		var $duration;		// in seconds
		var $recording;
		var $recordingReadTime;
		var $preparationMode;
		var $title;
		var $how;
		var $recordingParticipantList;

		function isFinsihed() {
			$finished = false;

			if ($this->how == 0 || $this->how == 1 || $this->how == 2) {
				$finished = true;
			}
			return($finished);
		}

		function isRecordingRead() {
			return(! empty($this->recordingReadTime));
		}

		function isCoordinatorAbsent() {
			$absent = true;

			if (count($this->recordingParticipantList) == 0) {
				return($absent);
			}
			$recordingParticipantList = &$this->recordingParticipantList;
			for ($idx = 0, $size = count($recordingParticipantList); $idx < $size; $idx++) {
				$recordingParticipant = &$recordingParticipantList[$idx];
				if ($recordingParticipant->userId == $this->userId) {
					$absent = ($recordingParticipant->absent ? true : false);
					break;
				}
			}
			return($absent);
		}

		function isParticipant($userId, $email) {
			$found = false;

			if (count($this->recordingParticipantList) == 0) {
				return($found);
			}
			$recordingParticipantList = &$this->recordingParticipantList;
			for ($idx = 0, $size = count($recordingParticipantList); $idx < $size; $idx++) {
				$recordingParticipant = &$recordingParticipantList[$idx];
				if ($recordingParticipant->absent != 1) {
					if (is_numeric($recordingParticipant->userId) && is_numeric($userId)) {
						if ($recordingParticipant->userId == $userId) {
							$found = true;
							break;
						}
					} else {
						if (strtolower($recordingParticipant->userId) == strtolower($email)) {
							$found = true;
							break;
						}
					}
				}
			}
			return($found);
		}

		function getParticipantNames($maxCount) {
			$participantNames = '';

			if (count($this->recordingParticipantList) == 0) {
				return($participantNames);
			}
			$recordingParticipantList = &$this->recordingParticipantList;
			$count = 0;
			for ($idx = 0, $size = count($recordingParticipantList); $idx < $size; $idx++) {
				$recordingParticipant = &$recordingParticipantList[$idx];
				if ($recordingParticipant->absent != 1) {
					if ($count > 0) {
						$participantNames .= ', ';
					}
					$participantNames .= $recordingParticipant->name;
					$count++;
					if (($maxCount > 0) && ($count >= $maxCount)) {
						break;
					}
				}
			}
			if (($idx < $size - 1) && ($maxCount > 0) && ($count >= $maxCount)) {
				$participantNames .= ', ...';
			}
			return($participantNames);
		}

		function getParticipantList() {
			$participantList = array();

			if (count($this->recordingParticipantList) == 0) {
				return($participantList);
			}
			$recordingParticipantList = &$this->recordingParticipantList;
			for ($idx = 0, $size = count($recordingParticipantList); $idx < $size; $idx++) {
				$recordingParticipant = &$recordingParticipantList[$idx];
				if ($recordingParticipant->absent != 1) {
					$participantList[] = $recordingParticipant;
				}
			}
			return($participantList);
		}
	}

	// callback function for usort()
	function mcuRecordingStatusCompare($c1, $c2) {
		return($c1->startTime > $c2->startTime);
	}
?>
