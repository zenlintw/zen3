<?php
	/**
	 * 【程式功能】
	 * 建立日期：2004/08/16
	 * @author  Wiseguy Liang
	 * @version $Id: mime_detection.php,v 1.1 2010/02/24 02:39:34 saly Exp $
	 * @copyright 2003 SUNNET
	 **/

	function print_script()
	{
		echo <<< EOB

<script>
function generate_audio(obj, type)
{
	switch(type)
	{
		case 1:
			obj.parentNode.innHTML = '<a href="javascript:;" onclick="return false;">Stop</a>';
			break;
		case 3:
			obj.parentNode.innHTML = '<a href="javascript:;" onclick="return false;">Stop</a>';
			break;
	}
}

function generate_video(obj, type)
{
	switch(type)
	{
		case 1:
			obj.parentNode.innHTML = '<a href="javascript:;" onclick="return false;">Stop</a>';
			break;
		case 2:
			obj.parentNode.innHTML = '<a href="javascript:;" onclick="return false;">Stop</a>';
			break;
		case 3:
			obj.parentNode.innHTML = '<a href="javascript:;" onclick="return false;">Stop</a>';
			break;
	}
}

</script>

EOB;
	}


	function detect_mime($file)
	{
		switch(strtolower(strrchr($file, '.')))
		{
			case '.jpg' :
			case '.jpeg' :
			case '.jpe' :
				return 'image/jpeg';
			case '.gif' :
				return 'image/gif';
			case '.png' :
				return 'image/png';
			case '.bmp' :
				return 'image/bmp';
			case '.mpeg' :
			case '.mpg' :
			case '.mpe' :
				return 'video/mpeg';
			case '.qt' :
			case '.mov' :
				return 'video/quicktime';
			case '.avi' :
				return 'video/x-msvideo';
			case '.swf' :
				return 'application/x-shockwave-flash';
			case '.rm' :
			case '.ram' :
				return 'application/vnd.rn-realmedia';
			case '.asf' :
				return 'video/x-ms-asf';
			case '.asx' :
				return 'video/x-ms-asf';
			case '.wma' :
				return 'audio/x-ms-wma';
			case '.wax' :
				return 'audio/x-ms-wax';
			case '.wmv' :
				return 'video/x-ms-wmv';
			case '.wvx' :
				return 'video/x-ms-wvx';
			case '.wm' :
				return 'video/x-ms-wm';
			case '.wmx' :
				return 'video/x-ms-wmx';
			case '.wmz' :
				return 'application/x-ms-wmz';
			case '.wmd' :
				return 'application/x-ms-wmd';
			case '.mid' :
			case '.midi' :
				return 'audio/midi';
			case '.mp3' :
				return 'audio/mpeg';
			case '.ra' :
				return 'audio/x-pn-realaudio';
			case '.ogg' :
				return 'application/ogg';
			default :
				return false;
		}
	}

	function generate_html($file)
	{
		$ret = detect_mime($file);
		if ($ret)
		{
			if (strpos($ret, 'image/') === 0)
			{
				return sprintf('<img src="%s" border="0" valign="absmiddle" />', htmlspecialchars($file));
			}
			elseif ($ret == 'application/x-shockwave-flash')
			{
				return '<OBJECT classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"
codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,40,0"
WIDTH="230" HEIGHT="245">
<PARAM NAME=movie VALUE="' . $file . '"> <PARAM NAME=quality VALUE=high> <PARAM NAME=bgcolor VALUE=#FFFFFF>
<EMBED src="' . $file . '" quality=high bgcolor=#FFFFFF  WIDTH="230" HEIGHT="245" TYPE="application/x-shockwave-flash"
PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer"></EMBED>
</OBJECT>';
			}
			elseif (strpos($ret, 'audio/') === 0 ||
					strpos($ret, '/ogg') !== false
			       )
			{
				if (strpos($ret, '/x-pn-realaudio') !== false) // real audio
					return '<span><a href="javascript:;" onclick="generate_audio(this, 1); return false;">Play</a></span>';
				else
					return '<span><a href="javascript:;" onclick="generate_audio(this, 3); return false;">Play</a></span>';
			}
			elseif (strpos($ret, 'video/') === 0 ||
					strpos($ret, '/x-ms-wmz') !== false ||
					strpos($ret, '/x-ms-wmd') !== false
				   )
			{
				if (strpos($ret, '/x-pn-realaudio') !== false) // real video
					return '<span><a href="javascript:;" onclick="generate_video(this, 1); return false;">Play</a></span>';
				if (strpos($ret, '/x-pn-realaudio') !== false) // quicktime video
					return '<span><a href="javascript:;" onclick="generate_video(this, 2); return false;">Play</a></span>';
				else
					return '<span><a href="javascript:;" onclick="generate_video(this, 3); return false;">Play</a></span>';
			}
		}
		else
			return '<span>no-supported</span>';
	}

?>