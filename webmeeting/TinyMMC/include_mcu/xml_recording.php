<?php
//
//    ------------------------------------------------------------------------------------
//    Copyright (c) 2004 HOMEMEETING INC. All rights reserved.
//    ------------------------------------------------------------------------------------
//    This source file is subject to HomeMeeting license,
//    that is bundled with this package in the file "license.txt".
//    ------------------------------------------------------------------------------------
//
	require_once('util_xml.php');
	require_once('recording_status.php');
	require_once('recording_participant.php');
	
	class XmlRecording extends XmlParser {
		var $recordingStatus;

		function XmlRecording($xmlFile) {
			$this->parserName = 'XmlRecording';
			$this->parserFile = $xmlFile;
			$this->load();
		}

		function load() {
			parent::load();
		}

		function startElement($parser, $name, $attrs) {
			parent::startElement($parser, $name, $attrs);

			$recordingStatus = &$this->recordingStatus;
			
			switch ($this->xmlPath){
			case '/status/':
				$recordingStatus = new mcuRecordingStatus();
				$recordingStatus->startTime = '';
				$recordingStatus->duration  = '';
				$recordingStatus->recording = '';
				$recordingStatus->title     = '';
				$recordingStatus->how       = '';
				$recordingStatus->preparationMode = '';
				$recordingStatus->recordingParticipantList = array();
				break;

			case '/status/starttime/':
				$recordingStatus->startTime = utilGetHashValue($attrs, 'utc', null);
				break;

			case '/status/recording/':
				$recordingStatus->preparationMode   = utilGetHashValue($attrs, 'preparationmode', 0);
				$recordingStatus->recordingReadTime = utilGetHashValue($attrs, 'read'           , null);
				break;

			case '/status/participant/':
				$recordingParticipant = new mcuRecordingParticipant();
				$recordingParticipant->name   = utilGetHashValue($attrs, 'name'  , '');
				$recordingParticipant->userId = utilGetHashValue($attrs, 'userid', '');
				$recordingParticipant->absent = utilGetHashValue($attrs, 'absent',  0);

				$recordingStatus->recordingParticipantList[] = $recordingParticipant;
				break;
			}
		}
		
		function characterData($parser, $data) {
			parent::characterData($parser, $data);

			$recordingStatus = &$this->recordingStatus;

			switch ($this->xmlPath){
			case '/status/duration/':
				$recordingStatus->duration .= $data;
				break;

			case '/status/recording/':
				$recordingStatus->recording .= $data;
				break;

			case '/status/title/':
				$recordingStatus->title .= $data;
				break;

			case '/status/how/':
				$recordingStatus->how .= $data;
				break;
			}
		}
		
		function endElement($parser, $name) {
			parent::endElement($parser, $name);
		}
	}
?>
