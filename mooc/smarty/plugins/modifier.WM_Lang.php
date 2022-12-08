<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty WM_Lang modifier plugin
 *
 * Type:     modifier<br>
 * Name:     WM_Lang<br>
 * Purpose:  輸出 WMPro 的 Language
 * @param string
 * @return string
 */
function smarty_modifier_WM_Lang($string, $lang = '')
{
    // 錯誤訊息樣板
    $error = '<span style="color:red;font-weight:bold;">#%s#</span>';

    // 沒有載入語言檔
    if (!isset($GLOBALS['MSG'])) {
        return sprintf($error, '-Can not found Message-');
    }

    $MSG = $GLOBALS['MSG'];
    if ($lang === '') {
        // 沒有 sysSession
        if (!isset($GLOBALS['sysSession'])) {
            return sprintf($error, '-Can not detect Language-');
        }
        $sysSession = $GLOBALS['sysSession'];
        $lang = $sysSession->lang;
    }

    // 找不到對應的 keyword
    if (!isset($MSG[$string][$lang])) {
        return sprintf($error, '[' . $lang . ']' . $string);
    }

    $txt = $MSG[$string][$lang];

    $argv = func_get_args();
    if (count($argv) > 2) {
        // 字串取代
        if (is_array($argv[2])) {
            // 使用 key => val 來取代字串
            $txt = str_replace(array_keys($argv[2]), $argv[2], $txt);
        } else if (is_string($argv[2])) {
            // 使用 sprintf 來輸出
            $argv = array_slice($argv, 2);
            $txt = vsprintf($txt, $argv);
        }
    }

    return $txt;
}