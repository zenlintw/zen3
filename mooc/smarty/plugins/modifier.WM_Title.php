<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty WM_Title modifier plugin
 *
 * Type:     modifier<br>
 * Name:     WM_Title<br>
 * Purpose:  解析 WMPro 的多語系課程名稱，並輸出指定的語系標題
 * @param string
 * @return string
 */
function smarty_modifier_WM_Title($string, $lang = '')
{
    // 錯誤訊息樣板
    $error = '<span style="color:red;font-weight:bold;">#%s#</span>';

    $title = unserialize($string);
    // 無法 unserialize 直接輸出原字串
    if ($title === false) {
        return $string;
    }

    if ($lang === '') {
        // 沒有 sysSession
        if (!isset($GLOBALS['sysSession'])) {
            return sprintf($error, '-Can not detect Language-');
        }
        $sysSession = $GLOBALS['sysSession'];
        $lang = $sysSession->lang;
    }
    
    if (is_array($title)) 
	{
		foreach ($title as $key => $val) 
		{
			$title[$key] = htmlspecialchars($val);
		}
		foreach ($GLOBALS['sysAvailableChars'] as $key => $value) {
			if (!in_array($value,array_keys($title))) {
				$title[$value] = '';
			}
		}
	} else 
	{
	    foreach ($GLOBALS['sysAvailableChars'] as $key => $value) {
		    $title[$value] = htmlspecialchars($string);
		}
	}
    
    foreach ($title as $key => $val) 
	{
		if($val == "" || $val== "undefined" || $val== "--=[unnamed]=--"){
			$title[$key] = $title[sysDefaultLang];
		}
	}

    // 找不到對應的 keyword
    if (!isset($title[$lang])) {
        return sprintf($error, 'Special language of [' . $lang . '] not exists');
    }

    return $title[$lang];
}