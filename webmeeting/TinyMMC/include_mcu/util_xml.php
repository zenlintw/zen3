<?php
//
//    ------------------------------------------------------------------------------------
//    Copyright (c) 2004 HOMEMEETING INC. All rights reserved.
//    ------------------------------------------------------------------------------------
//    This source file is subject to HomeMeeting license,
//    that is bundled with this package in the file "license.txt".
//    ------------------------------------------------------------------------------------
//

	function utilGetHashValue($hashVar, $hashKey, $defaultValue) {
		if (isset($hashVar[$hashKey]))
			return($hashVar[$hashKey]);
		else
			return($defaultValue);
	}

	function utilXmlErrorString($parser, $data) {
		$str = sprintf('XML Parse Error: [%d] %s (%s)',
					xml_get_error_code($parser),
					xml_error_string(xml_get_error_code($parser)),
					$data);
		return($str);
	}

	class XmlParser {
		var $errorCode;
		var $errorMsg;

		var $parserFile;
		var $parserName;
		var $xmlPath;

		function hasError() {
			return($this->errorCode != 0 || strlen($this->errorMsg) != 0);
		}

		function getParserLogName() {
			if (strlen($this->parserName) > 0) {
				$name = $this->parserName;
			} else {
				$name = get_class($this);
			}
			return($name . ' - ');
		}

		function load() {
			$this->errorCode = 0;
			$this->errorMsg = '';
			$fh = @fopen($this->parserFile, 'r');
			if ($fh === false) {
				$this->errorCode = -1;
				$this->errorMsg = $this->getParserLogName() . 'Cannot open XML file ' . $this->parserFile;
				mcuLogWarning($this->errorMsg);
				return;
			}

			// Initialize XML parser
			$parser = xml_parser_create('UTF-8');
			xml_set_object($parser, $this);
			xml_set_element_handler($parser, 'startElement', 'endElement');
			xml_set_character_data_handler($parser, 'characterData');
			xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);

			// Skip Windows UTF-8 file header
			$data = fread($fh, 3);
			if ($data == "\xEF\xBB\xBF") {
				$data = '';
			}

			// Begin parsing
			$this->xmlPath = '/';
			while ($data .= fread($fh, 4096)) {
				$stat_ok = @xml_parse($parser, $data, feof($fh));
				if (! $stat_ok) {
					$this->errorCode = -2;
					$this->errorMsg = $this->getParserLogName() . utilXmlErrorString($parser, $data);
					//mcuLogError($this->errorMsg);
					break;
				}
				$data = '';
			}
			xml_parser_free($parser);
			@fclose($fh);
		}

		function startElement($parser, $name, $attrs) {
			// update xmlPath
			$this->xmlPath .= strtolower("$name/");
		}

		function characterData($parser, $data){
		}

		function endElement($parser, $name) {
			// remove last element in xmlPath
			$this->xmlPath = preg_replace("/[^\/]+\/$/", '', $this->xmlPath);
		}
	}
?>
