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
	require_once('license.php');
	
	class XmlLicense extends XmlParser {
		var $license;

		var $ipRangeBase;
		var $ipRangeMask;
		var $relayIp;

		function XmlLicense($xmlFile) {
			$this->parserName = 'XmlLicense';
			$this->parserFile = $xmlFile;
			$this->load();
		}

		function load() {
			parent::load();
		}

		function startElement($parser, $name, $attrs) {
			parent::startElement($parser, $name, $attrs);

			$license = &$this->license;
			
			switch ($this->xmlPath){
			case '/license/':
				$license = new mcuLicense();
				$license->ipRangeBaseList = array();
				$license->ipRangeMaskList = array();
				$license->relayIpList = array();

				$this->ipRangeBase = '';
				$this->ipRangeMask = '';
				$this->relayIp = '';
				break;
			}
		}
		
		function characterData($parser, $data) {
			parent::characterData($parser, $data);

			$license = &$this->license;

			switch ($this->xmlPath){
			case '/license/source/':
				$license->source .= $data;
				break;

			case '/license/serial/':
				$license->serial .= $data;
				break;

			case '/license/agent/':
				$license->agent .= $data;
				break;

			case '/license/customer/':
				$license->customer .= $data;
				break;

			case '/license/service_type/model/':
				$license->model .= $data;
				break;

			case '/license/service_type/server/':
				$license->server .= $data;
				break;

			case '/license/expire/create_date/':
				$license->createDate .= $data;
				break;

			case '/license/expire/expire_date/':
				$license->expireDate .= $data;
				break;

			case '/license/max_meeting/':
				$license->maxMeeting .= $data;
				break;

			case '/license/max_connection/':
				$license->maxConnection .= $data;
				break;

			case '/license/max_outconnection/':
				$license->maxOutConnection .= $data;
				break;

			case '/license/max_office/':
				$license->maxOffice .= $data;
				break;

			case '/license/box_sn/':
				$license->boxSerial .= $data;
				break;

			case '/license/fixed_mac/':
				$license->fixedMac .= $data;
				break;

			case '/license/fixed_ip/':
				$license->fixedIp .= $data;
				break;

			case '/license/enable_application_data_channel/':
				$license->appDataChannel .= $data;
				break;

			case '/license/multiple_video_audio/':
				$license->multiVideoAudio .= $data;
				break;

			case '/license/enable_sip_client/':
				$license->enableSipClient .= $data;
				break;

			case '/license/enable_sip_proxy/':
				$license->enableSipProxy .= $data;
				break;

			case '/license/accept_mcu_relay2/':
				$license->acceptRelay .= $data;
				break;

			case '/license/enforce_auth/':
				$license->enforceAuth .= $data;
				break;

			case '/license/authserver/https/':
				$license->authServerHttps .= $data;
				break;

			case '/license/authserver/name/':
				$license->authServerName .= $data;
				break;

			case '/license/authserver/port/':
				$license->authServerPort .= $data;
				break;

			case '/license/authserver/path/':
				$license->authServerPath .= $data;
				break;

			case '/license/id0/':
				$license->id0 .= $data;
				break;

			case '/license/id1/':
				$license->id1 .= $data;
				break;

			case '/license/ip_range/base/':
				$this->ipRangeBase .= $data;
				break;
			case '/license/ip_range/mask/':
				$this->ipRangeMask .= $data;
				break;
			case '/license/allowed_mcu_relay_ip/':
				$this->relayIp .= $data;
				break;
			}
		}
		
		function endElement($parser, $name) {
			$license = &$this->license;
			
			switch ($this->xmlPath){
			case '/license/ip_range/':
				$license->ipRangeBaseList[] = $this->ipRangeBase;
				$license->ipRangeMaskList[] = $this->ipRangeMask;
				$this->ipRangeBase = '';
				$this->ipRangeMask = '';
				break;

			case '/license/allowed_mcu_relay_ip/':
				$license->relayIpList[] = $this->relayIp;
				$this->relayIp = '';
				break;
			}

			parent::endElement($parser, $name);
		}
	}
?>
