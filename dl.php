<?php
/**
 * 將下載檔案的檔名轉換為本地檔名
 *
 * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
 *
 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
 *
 * @package     WM3
 * @author      Wiseguy Liang <wiseguy@mail.wiseguy.idv.tw>
 * @copyright   2000-2006 SunNet Tech. INC.
 * @version     CVS: $Id: dl.php,v 1.2 2010/04/28 06:52:01 small Exp $
 * @link        http://demo.learn.com.tw/1000110138/index.html
 * @since       2006-06-13
 */

ob_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/lib_adjust_char.php');

/**
 *	從 hash 表中以代名取得原名
 *
 * @param   string  $hash       原名TAB代名TAB原名TAB代名...所成字串
 * @param   string  $deputy     代名
 */
function fetchFilename($hash, $deputy)
{
    $h = explode(chr(9), $hash);
    return (($i = array_search($deputy, $h)) === FALSE) ? $deputy : $h[$i - 1];
}

/**
 * 遞迴式陣列搜尋
 *
 * @param   mix     $needle     欲尋找的值
 * @param   array   $haystack   欲尋找的陣列
 * @param   bool    $strict     是否要做型別比對
 * @return  mix                 傳回找到的 key 或 false
 */
function array_search_recursive($needle, $haystack, $strict = false)
{
    if (!is_array($haystack))
        return false;
    foreach ($haystack as $key => $value) {
        if (!is_array($needle) && is_array($value))
            return array_search_recursive($needle, $value, $strict);
        elseif (($strict && $value === $needle) || (!$strict && $value == $needle))
            return $key;
    }
    return false;
}

/**
 *	從 serialize 表中以代名取得原名
 *
 * @param   string  $serialize  原名TAB代名TAB原名TAB代名...所成 serialize 字串
 * @param   string  $deputy     代名
 */
function fetchSerialFilename($serialize, $deputy)
{
    $h = unserialize($serialize);
    return (($i = array_search_recursive($deputy, $h)) === FALSE) ? $deputy : $i;
}

/**
 * 增加額外的 mime type 判斷
 *
 * @param string $filePath 檔案路徑
 * @return string mime type
 */
function getFileMimeType($filePath) {
    $extension = end(explode('.', $filePath));
    $correspondence = array(
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
        'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template'
    );
    if (array_key_exists($extension, $correspondence)) {
        return $correspondence[$extension];
    } else {
        return mime_content_type($filePath);
    }
}

/* ====================================== 主程式開始 ============================================*/
$at   = '';
$type = 'application/octet-stream';

if (strpos($_SERVER['REQUEST_URI'], '..') !== FALSE) {
    header('HTTP/1.1 403 Forbidden');
    exit;
}

if (preg_match('!^/base/[0-9]{5}/(course/[0-9]{8}/)?(board|quint)/([0-9]+)/([0-9]+)/(.+)$!', $_SERVER['REQUEST_URI'], $regs)) {
    if (preg_match('/^[0-9A-Z]{32}\.mp3$/i', $regs[5])) {
        $f = $regs[5];
    } else {
        $a = $sysConn->GetOne('select attach from WM_bbs_' . ($regs[2] == 'board' ? 'posts' : 'collecting') . " where board_id={$regs[3]} and node='{$regs[4]}'");
        $f = fetchFilename($a, $regs[5]);
    }
    $at   = ' attachment;';
    $ext = strtolower(substr(strrchr($f, '.'), 1));
		if ($ext=='htm' || $ext=='html') {
		    $at = '';
		     $type = 'text/html';
		}
} elseif (preg_match('!^/base/[0-9]{5}/course/' . sprintf('%08u', $sysSession->course_id) . '/(homework|exam)/A/[0-9]{9}/[^/]+/ref/[0-9]{9}/([^/]+)$!', $_SERVER['REQUEST_URI'], $regs)) {
    $f                      = rawurldecode($regs[2]);
    $_SERVER['REQUEST_URI'] = str_replace($regs[2], $f, $_SERVER['REQUEST_URI']);

    if ($regs[1] == 'homework')
        $at = ' attachment;';
} elseif (preg_match('!^/base/[0-9]{5}/course/' . sprintf('%08u', $sysSession->course_id) . '/homework/A/[0-9]{9}/[^/]+/([^/]+)$!', $_SERVER['REQUEST_URI'], $regs)) {
    $us                     = parse_url($_SERVER['HTTP_REFERER']);
    $f                      = strpos($us['path'], '/base/') === 0 ? rawurldecode($regs[1]) : rawurldecode($regs[1]);
    $f = stripcslashes($f);
    $_SERVER['REQUEST_URI'] = str_replace($regs[1], $f, $_SERVER['REQUEST_URI']);
    $at                     = ' attachment;';

} elseif (preg_match('!^/base/[0-9]{5}/course/[0-9]{8}/(homework|questionnaire|exam)/Q/([^/\x22\x27]+)/([^/]+)$!', $_SERVER['REQUEST_URI'], $regs)) {
    $a = $sysConn->GetOne('select attach from WM_qti_' . $regs[1] . "_item where ident='{$regs[2]}'");
    if ($regs[1] == 'exam') {
        $data = unserialize($a);
        if (!empty($data)) {
            foreach ($data as $v1) {
                if (!empty($v1)) {
                    foreach ($v1 as $displayName => $realName) {
                        if ($realName == $regs[3]) {
                            $f = $displayName;
                        }
                    }
                }
            }
        }
    } else {
        $f = fetchSerialFilename($a, $regs[3]);
    }
    $at = ' attachment;';

    // 資安議題：當有設定 X-Content-Type-Options "nosniff" 時，IE對於不明確的 mime-type 會檔下來，造成圖片無法正確顯示
    // 測驗題庫附檔如果是圖片，無法直接呈現，會出現X，故需要給予明確的 mime-type
    if (empty($_COOKIE['show_me_info']) === FALSE) {
        echo '<pre>';
        var_dump($_SERVER['REQUEST_URI']);
        var_dump(preg_match('/\/base\/\d{5}\/course\/\d{8}\/[exam|homework|questionnaire]*\/[A|Q]\/[0-9_A-Z\/]*\/[\w]*.[0-9a-zA-z]*/', $_SERVER['REQUEST_URI']));
        echo '</pre>';
    }
    $type = mime_content_type(sysDocumentRoot . $_SERVER['REQUEST_URI']);
} elseif (preg_match('!^/base/[0-9]{5}/course/[0-9]{8}/(exam|homework|questionnaire)/A/\d{9}(/[^/\x22\x27]+){3}/([^/]+)$!', $_SERVER['REQUEST_URI'], $regs)) {
    $f = basename($_SERVER['REQUEST_URI']);
    if (preg_match('/%[0-9a-f]{2}/i', $f)) {
        $f                      = basename(urldecode($f));
        $_SERVER['REQUEST_URI'] = dirname($_SERVER['REQUEST_URI'] . '/' . $f);
    }

    if ($regs[1] == 'homework')
        $at = ' attachment;';
} elseif (preg_match('!^/base/[0-9]{5}/course/' . sprintf('%08u', $sysSession->course_id) . '/questionnaire/A/[0-9]{9}/[^/]+/([^/]+)$!', $_SERVER['REQUEST_URI'], $regs)) {
    $us                     = parse_url($_SERVER['HTTP_REFERER']);
    $f                      = strpos($us['path'], '/base/') === 0 ? rawurldecode($regs[1]) : rawurldecode($regs[1]);
    $_SERVER['REQUEST_URI'] = str_replace($regs[1], $f, $_SERVER['REQUEST_URI']);
    $at                     = ' attachment;';
} else {
    die('Bad Requirement.');
}
/**
WinXP繁中 + MOOC簡中 + IE8
1.輸出瀏覽器時要轉為Big5/GBK
2.不使用 rawurlencode 轉碼

Win7繁中 + MOOC簡中 + IE10
1.輸出瀏覽器轉為UTF-8
2.要使用 rawurlencode二次轉碼

Win7繁中 + MOOC簡中 + IE11
1.輸出瀏覽器轉為UTF-8
2.要使用 rawurlencode二次轉碼

Chrome
1.輸出瀏覽器轉為UTF-8
2.不使用 rawurlencode 轉碼
**/
$ua = $_SERVER["HTTP_USER_AGENT"];
//IE6~9 MSIE x.x
if (preg_match("/MSIE [6-9]{1}/i", $ua)) {
    $f = un_adjust_char($f);
    //IE10 - Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)
} else if (preg_match("/MSIE 10/i", $ua)) {
    $f = rawurlencode($f);
    //IE11 - gecko
} else if (preg_match("/gecko/i", $ua)) {
    $f = rawurlencode($f);
}

// APP 檔案不須下載，直接使用 webview 觀看
$fromAPP = isset($_COOKIE['connect_from']) && $_COOKIE['connect_from'] === 'app';
if ($fromAPP) {
    $at = '';
    // 取得檔案副檔名
    $type = getFileMimeType(sysDocumentRoot . $_SERVER['REQUEST_URI']);
}

// 因應開放檔名可以單引號
// 修正下載單引號，會變成%27
if (!preg_match("/gecko/i", $ua) || preg_match("/Firefox/i", $ua)) {
    $f = str_replace("'", "\'", rawurldecode($f));
}

while (@ob_end_clean());
header('Expires: ');
header('Pragma: ');
header('Cache-Control: ');
header('Content-Disposition:' . $at . ' filename="' . $f . '"');
header('Content-Transfer-Encoding: binary');
header('Content-Type: ' . $type . '; name="' . $f . '"');

@readfile(sysDocumentRoot . $_SERVER['REQUEST_URI']);