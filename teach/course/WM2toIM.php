<?php
	/*
	 *	將 WM2 之教材節點，轉換為 SCORM (WM3) 之教材節點
	 *
	 *  since 2004/11/24 by Wiseguy
	 *  $Id: WM2toIM.php,v 1.1 2010/02/24 02:40:22 saly Exp $
	 */
$content_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/content', $sysSession->school_id, $sysSession->course_id);
chdir($content_path);
$content = (defined('CONTENT_FILE') && preg_match('/^[0-9]{5}.php$/', CONTENT_FILE) && file_exists($content_path . DIRECTORY_SEPARATOR . CONTENT_FILE)) ? 
				exec('grep "^\$path = \'" ' . CONTENT_FILE . ' | sed -e \'s/^.........//\' -e \'s/..$//\'') :
				'' ;

function escape_title($word)
{
	$ret = htmlspecialchars(trim(preg_replace('/[\x00-\x07\x0E-\x1F]+/', ' ', $word)), ENT_NOQUOTES);
	return empty($ret) ? '--= unnamed =--' : $ret;
}

$instances = array(2 => array(),
				   3 => array(),
				   4 => array(),
				   5 => array(),
				   6 => array()
				  );

$nodes = explode('@@', $content);		// 所有節點

$serial = 1;
$level = array();
$item_context = '';
$resource_context = '';

foreach($nodes as $node)
{
	$elements = explode('**', $node);
	$elements[1] = intval($elements[1]);

	if (!isset($prevLevel))
	{
		$level[] = $elements[1];
	}
	else
	{
		if ($prevLevel < $elements[1])					// 縮排
		{
			$level[] = $elements[1];
		}
		elseif ($prevLevel > $elements[1])				// 還原縮排
		{
			while(count($level))
			{
				if ($elements[1] >= array_pop($level))
				{
					$level[] = $elements[1];
					$item_context .= "</item>\n";
					break;
				}
				$item_context .= "</item>\n";
			}
		}
		else											// 同階
			$item_context .= "</item>\n";
	}
	$prevLevel = $elements[1];


	switch($elements[0])
	{
		case '1':
			$words = explode('~~', $elements[2]);
			if (empty($words[1]) || $words[1] == 'about:blank' || $words[1] == 'URL')
			{
				$item_context .= sprintf('<item identifier="I_WM2_%05d"%s><title>%s</title>', $serial++, ($elements[3]==='0'?' isvisible="false"':''), escape_title($words[0]));
			}
			else
			{
				$item_context .= sprintf('<item identifier="I_WM2_%05d" identifierref="WM2_%05d"%s%s><title>%s</title>', $serial, $serial, ($elements[3]==='0'?' isvisible="false"':''), ($words[2]==='1'?' parameters="target=&quot;_blank&quot;"':''), escape_title($words[0]));
				$resource_context .= sprintf('<resource identifier="WM2_%05d" adlcp:scormtype="asset" type="webcontent" href="%s" />', $serial++, htmlspecialchars(eregi('^([a-z]+://|/)', $words[1]) ? $words[1] : ('content/' . $words[1])));
			}
			break;
		case '2':
			$item_context .= sprintf('<item identifier="I_WM2_%05d" identifierref="WM2_%05d"%s><title>%s</title>', $serial, $serial, ($elements[4]==='0'?' isvisible="false"':''), escape_title($instances[3][$elements[2]]));
			$resource_context .= sprintf('<resource identifier="WM2_%05d" adlcp:scormtype="asset" type="webcontent" href="javascript:if(typeof(\'fetchWMinstance\')==\'function\')fetchWMinstance(3,%d);else alert(\'This is a WM instance.\')" />', $serial++, $elements[2]);
			break;
		case '3':
			$type = 2;
		case '4':
			if (!isset($type)) $type = 5;
		case '5':
			if (!isset($type)) $type = 4;
		case '6':
			if (!isset($type)) $type = 6;
			list($element_id) = explode('~~', $elements[2], 2);
			$item_context .= sprintf('<item identifier="I_WM2_%05d" identifierref="WM2_%05d"%s><title>%s</title>', $serial, $serial, ($elements[4]==='0'?' isvisible="false"':''),  escape_title($instances[$type][$element_id]));
			$resource_context .= sprintf('<resource identifier="WM2_%05d" adlcp:scormtype="asset" type="webcontent" href="javascript:if(typeof(\'fetchWMinstance\')==\'function\')fetchWMinstance(%d,%d);else alert(\'This is a WM instance.\')" />', $serial++, ($type), $element_id);
			break;
	}
	unset($type);

}
$item_context .= str_repeat("</item>\n", count($level));

$now = time();
$MSG['content_outline'][$sysSession->lang] = iconv('UTF-8', 'CP950', $MSG['content_outline'][$sysSession->lang]);
$x = <<< EOB
<?xml version="1.0"?>
<manifest xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_v1p3">
	<organizations default = "Course{$sysSession->course_id}">
		<organization identifier="Course{$sysSession->course_id}">
			<title>WM2 course</title>
			<item identifier="WM2_{$sysSession->course_id}_{$now}">
				<title>{$MSG['content_outline'][$sysSession->lang]}</title>
				{$item_context}
			</item>
		</organization>
	</organizations>
	<resources>
		{$resource_context}
	</resources>
</manifest>
EOB;

$filename = './imsmanifest.xml';
$fp = fopen($filename, 'w');
if ($fp){
	fwrite($fp, str_replace('><', ">\n<", iconv('CP950', 'UTF-8', $x)));
	fclose($fp);
}

?>
