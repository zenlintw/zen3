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
	require_once('participant_online.php');
	
	class XmlParticipant extends XmlParser {
		var $participantOnlineList;
		
		function XmlParticipant($xmlFile) {
			$this->parserName = 'XmlParticipant';
			$this->parserFile = $xmlFile;
			$this->load();
		}

		function load() {
			parent::load();
		}

		function startElement($parser, $name, $attrs) {
			parent::startElement($parser, $name, $attrs);
			
			switch ($this->xmlPath){
			case '/participant/':
				$this->participantOnlineList = array();
				break;

			case '/participant/user/':
				$participantOnline = new mcuParticipantOnline();
				$participantOnline->name    = utilGetHashValue($attrs, 'name'   , '');
				$participantOnline->userId  = utilGetHashValue($attrs, 'userid' , '');
				$participantOnline->invited = utilGetHashValue($attrs, 'invited', 0);
				$participantOnline->ticket  = utilGetHashValue($attrs, 'ticket' , 0);

				$this->participantOnlineList[] = $participantOnline;
				break;
			}
		}
		
		function characterData($parser, $data) {
			parent::characterData($parser, $data);
		}
		
		function endElement($parser, $name) {
			parent::endElement($parser, $name);
		}
	}
?>
