<?php
	/**
	 * 取得瀏覽器資訊
	 *     http://us2.php.net/manual/en/function.get-browser.php#86976
	 *
	 * @since   2009-07-10
	 * @author  不是我
	 * @version $Id: lib_browser.php,v 1.1 2010/02/24 02:39:33 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/

	function getBrowserInfo() {
		$SUPERCLASS_NAMES  = "gecko,mozilla,mosaic,webkit";
		$SUPERCLASSES_REGX = "(?:".str_replace(",", ")|(?:", $SUPERCLASS_NAMES).")";

		$SUBCLASS_NAMES    = "opera,msie,firefox,chrome,safari,trident";
		$SUBCLASSES_REGX   = "(?:".str_replace(",", ")|(?:", $SUBCLASS_NAMES).")";

		$browser      = "unsupported";
		$majorVersion = "0";
		$minorVersion = "0";
		$fullVersion  = "0.0";
		$os           = 'unsupported';

		$userAgent    = strtolower($_SERVER['HTTP_USER_AGENT']);

		$found = preg_match("/(?P<browser>" . $SUBCLASSES_REGX . ")(?:\D*)(?P<majorVersion>\d*)(?P<minorVersion>(?:\.\d*)*)/i", $userAgent, $matches);
		if (!$found) {
			$found = preg_match("/(?P<browser>" . $SUPERCLASSES_REGX . ")(?:\D*)(?P<majorVersion>\d*)(?P<minorVersion>(?:\.\d*)*)/i", $userAgent, $matches);
		}

		if ($found) {
			$browser      = $matches["browser"];
			$majorVersion = $matches["majorVersion"];
			$minorVersion = $matches["minorVersion"];
			$fullVersion  = $matches["majorVersion"].$matches["minorVersion"];
			if ($browser != "safari") {
				if (preg_match("/version\/(?P<majorVersion>\d*)(?P<minorVersion>(?:\.\d*)*)/i", $userAgent, $matches)) {
					$majorVersion = $matches["majorVersion"];
					$minorVersion = $matches["minorVersion"];
					$fullVersion  = $majorVersion.".".$minorVersion;
				}
			}
		}

		if (strpos($userAgent, 'linux')) {
			$os = 'linux';
		}
		else if (strpos($userAgent, 'macintosh') || strpos($userAgent, 'mac os x')) {
			$os = 'mac';
		}
		else if (strpos($userAgent, 'windows') || strpos($userAgent, 'win32')) {
			$os = 'windows';
		}

		$SUPERCLASS_NAMES  = "linux,macintosh,mac os x,windows,win32";
		$SUPERCLASSES_REGX = "(?:".str_replace(",", ")|(?:", $SUPERCLASS_NAMES).")";
		preg_match("/(?P<os>" . $SUPERCLASSES_REGX . ")(?:\D*)(?P<majorVersion>\d*)(?P<minorVersion>(?:\.\d*)*)/i", $userAgent, $matches);
		$osVersion = $matches["majorVersion"].$matches["minorVersion"];

		return array(
			"browser"      => $browser,
			"majorVersion" => $majorVersion,
			"minorVersion" => $minorVersion,
			"fullVersion"  => $fullVersion,
			"os"           => $os,
			"osVersion"    => $osVersion
		);
	}
?>
