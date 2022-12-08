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
	require_once('status_online.php');
	require_once('status_user.php');
	
	class XmlStatus extends XmlParser {
		var $statusOnline;		
		
		function XmlStatus($xmlFile) {
			$this->parserName = 'XmlStatus';
			$this->parserFile = $xmlFile;
			$this->load();
		}

		function load() {
			parent::load();
		}

		function startElement($parser, $name, $attrs) {
			parent::startElement($parser, $name, $attrs);
			$statusOnline = &$this->statusOnline;
			switch ($this->xmlPath){
			case '/thirdparty/':
				$statusOnline = new mcuStatusOnline();
				$statusOnline->maxMeeting                   = '';
				$statusOnline->maxReservedMeeting           = '';
				$statusOnline->maxConnection                = '';
				$statusOnline->maxOutsideConnection         = '';
				$statusOnline->maxReservedConnection        = '';
				$statusOnline->maxReservedOutsideConnection = '';
				$statusOnline->statusUserList = array();
				break;

			case '/thirdparty/info/':
				$statusOnline->maxMeeting                   = utilGetHashValue($attrs, 'max_meeting'                    , 0);
				$statusOnline->maxReservedMeeting           = utilGetHashValue($attrs, 'max_reserved_meeting'           , 0);
				$statusOnline->maxConnection                = utilGetHashValue($attrs, 'max_connection'                 , 0);
				$statusOnline->maxOutsideConnection         = utilGetHashValue($attrs, 'max_outside_connection'         , 0);
				$statusOnline->maxReservedConnection        = utilGetHashValue($attrs, 'max_reserved_connection'        , 0);
				$statusOnline->maxReservedOutsideConnection = utilGetHashValue($attrs, 'max_reserved_outside_connection', 0);
				break;
				
			case '/thirdparty/directory/user/':
				$statusUser = new mcuStatusUser();
				$statusUser->name            = utilGetHashValue($attrs, 'name'           , '');
				$statusUser->userId          = utilGetHashValue($attrs, 'userid'         , '');
				$statusUser->meetingId       = utilGetHashValue($attrs, 'meetingid'      , '');
				$statusUser->preparationMode = utilGetHashValue($attrs, 'preparationmode',  0);
				$statusOnline->statusUserList[] = $statusUser;
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
