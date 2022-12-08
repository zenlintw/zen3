<?php
/**
 * 首頁
 *
 * @since   2004/10/27
 * @author  ShenTing Lin
 * @version $Id: index.php,v 1.1 2010/02/24 02:38:55 saly Exp $
 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

// mooc 模組開啟的話將網頁導向index.php
if (defined('sysEnableMooc') && (sysEnableMooc > 0)) {
    header('Location: /mooc/index.php');
    exit;
}

if ($_GET['lang'] != '') {
    $sysSession->lang = trim($_GET['lang']);
    $sysSession->restore();
}
$lang       = $sysSession->lang;
$theme_lang = strtolower($lang);

// 如果是因為分享筆記時尚未登入，則必須把筆記資訊寫在cookie，以便稍後登入時使用
if (isset($_GET['action']) && $_GET['action'] === 'receive-share-note') {
    setcookie('noteAction', 'receive-share-note', time() + 60);
    setcookie('shareNoteKey', $_GET['share-key'], time() + 60);
}

require_once(sysDocumentRoot . '/lib/wise_template.php');
require_once(sysDocumentRoot . '/lang/door.php');
require_once(sysDocumentRoot . '/lib/common.php');

// 共用函數 -------------------------------------------------------------------

/**
 * 取得樣板的路徑
 * @param string $filename : 檔案名稱，不含路徑
 * @return string $file : 包含路徑與檔名的字串
 **/
function getTemplate($filename)
{
    global $sysSession, $tplSysPath, $tplSchPath, $lang;
    
    $tpl_lang = strtolower($lang);
    $file     = $tplSchPath . $tpl_lang . '/' . $filename;
    if (file_exists($file))
        return $file;
    $file = $tplSchPath . $filename;
    if (file_exists($file))
        return $file;
    $file = $tplSysPath . $filename;
    if (file_exists($file))
        return $file;
}

/**
 * 取得一定長度內的字串，不包含 HTML 的 TAG
 * @param string  $html  : 原始字串
 * @param integer $limit : 想要取得的長度，預設是 400 個字，
 * @return string $text : 傳回小於或等於 $total 長度的字串
 **/
function getLimitStr($html, $limit = 400)
{
    $stack   = array();
    $singles = array(
        'img',
        'br',
        'hr',
        'input'
    );
    
    $all     = preg_split('!(</?\w+[^>]*>\s*)!', $html, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
    $l       = count($all);
    $str_len = 0;
    $noCut   = true;
    
    for ($i = 0; $i < $l; $i++) {
        if ($noCut) {
            $strip = trim(strip_tags($all[$i]));
            
            if (($c = strlen($strip)) + $str_len >= $limit) {
                $all[$i] = @preg_replace('/(' . preg_quote(mb_strcut($strip, 0, $limit - $str_len, 'UTF-8')) . ').*$/s', '\1', $all[$i]);
                for ($j = $i + 1; $j < $l; $j++)
                // 					    if ($all[$j][0] != '<')
                    $all[$j] = '';
                // 						else
                // 						    $all[$j] =  preg_replace('/>.*$/s', '>', $all[$j]);
                
                $noCut = false;
            } else
                $str_len += $c;
        }
        
        if ($all[$i][0] != '<' || $all[$i] == '')
            continue;
        elseif ($all[$i][1] == '/') {
            preg_match('!</(\w+)!', $all[$i], $regs);
            $tag      = strtolower($regs[1]);
            $prev_tag = array_pop($stack);
            if ($tag != $prev_tag) {
                $all[$i] = '';
                array_push($stack, $prev_tag);
            }
        } else {
            preg_match('!<(\w+)!', $all[$i], $regs);
            $tag = strtolower($regs[1]);
            if (!in_array($tag, $singles))
                array_push($stack, strtolower($regs[1]));
        }
    }
    
    $l = count($stack);
    if ($l > 0) {
        for ($i = $l - 1; $i >= 0; $i--)
            array_push($all, '</' . $stack[$i] . '>');
    }
    
    return implode('', $all);
}

/**
 * 所有樣板預設會轉換的文字
 * @param object $obj : 樣板物件
 * @return void
 **/
function genDefaultTrans(&$obj)
{
    global $sysSession, $path_door, $path_theme, $lang, $theme_lang;
    $obj->add_replacement('<%DOOR_PATH%>', $path_door);
    $obj->add_replacement('<%THEME_PATH%>', $path_theme);
    $obj->add_replacement('<%LANGUAGE%>', $theme_lang);
    $obj->add_replacement('<%USERNAME%>', $sysSession->username);
    $obj->add_replacement('<%SCHOOL_NAME%>', $sysSession->school_name);
    $obj->add_replacement('<!---->', '');
}

$tplSysPath = sysDocumentRoot . '/sys/tpl/';
$tplSchPath = sysDocumentRoot . "/base/{$sysSession->school_id}/door/tpl/";

$path_theme = "/theme/{$sysSession->theme}/sys/";
$path_door  = "/base/{$sysSession->school_id}/door/";

// 頁首 -----------------------------------------------------------------------
require_once(sysDocumentRoot . '/sys/door/mod_head.php');
$cont_head = mod_head();

// 頁尾 -----------------------------------------------------------------------
require_once(sysDocumentRoot . '/sys/door/mod_foot.php');
$cont_foot = mod_foot();

// 工具列 ---------------------------------------------------------------------
require_once(sysDocumentRoot . '/sys/door/mod_tools.php');
$cont_tools = mod_tools();

// 登入模組 -------------------------------------------------------------------
require_once(sysDocumentRoot . '/sys/door/mod_login.php');
$cont_login = mod_login();

// 最新消息 -------------------------------------------------------------------
$sysNewsContLeng = 200;
require_once(sysDocumentRoot . '/sys/door/mod_news.php');
$cont_news = mod_news();

// 新開課程 -------------------------------------------------------------------
$sysCourseList = 5;
require_once(sysDocumentRoot . '/sys/door/mod_new_course.php');
$cont_new_course = mod_new_course();

// 校務行事曆 -----------------------------------------------------------------
require_once(sysDocumentRoot . '/sys/door/mod_cale_school.php');
$cont_cale_school = mod_cale_school();

// 計數器 ---------------------------------------------------------------------
require_once(sysDocumentRoot . '/lib/lib_counter.php');
$counter = '&nbsp;&nbsp;' . $MSG['visiter'][$sysSession->lang] . getCountHtml() . $MSG['people'][$sysSession->lang];

// 版面的總 Layout ------------------------------------------------------------
$tpl = getTemplate('index.htm');

$myTemplate = new Wise_Template($tpl);
genDefaultTrans($myTemplate);
$css = getTemplate('door.css');
if (file_exists($css)) {
    $css = sprintf('<link rel="stylesheet" type="text/css" href="%s">', $path_door . 'tpl/door.css');
} else {
    $css = '';
}

$myTemplate->add_replacement('<%USER_THEME%>', $css);
$myTemplate->add_replacement('<%NEED_MSXML%>', $MSG['msg_need_msxml'][$sysSession->lang]);

$myTemplate->add_replacement('<%MOD_HEAD%>', $cont_head);
$myTemplate->add_replacement('<%MOD_FOOT%>', $cont_foot);
$myTemplate->add_replacement('<%MOD_TOOLS%>', $cont_tools);

$myTemplate->add_replacement('<%MOD_LOGIN%>', $cont_login);
$myTemplate->add_replacement('<%MOD_NEWS%>', $cont_news);
$myTemplate->add_replacement('<%MOD_NEW_COURSE%>', $cont_new_course);

$myTemplate->add_replacement('<%MOD_CALE_SCHOOL%>', $cont_cale_school);

$myTemplate->add_replacement('<%BTN_CLOSE%>', $MSG['btn_close'][$sysSession->lang]);
$myTemplate->add_replacement('<%MSG_NEWS_DETAIL%>', $MSG['msg_news_detail'][$sysSession->lang]);

$myTemplate->add_replacement('<%MSG_NEW_COURSE1%>', $MSG['msg_new_course1'][$sysSession->lang]);
$myTemplate->add_replacement('<%MSG_NEW_COURSE2%>', $MSG['msg_new_course2'][$sysSession->lang]);
$myTemplate->add_replacement('<%TITLE_NEW_COURSE%>', $MSG['title_new_course'][$sysSession->lang]);
$myTemplate->add_replacement('<%MSG_NO_LIMIT%>', $MSG['msg_no_limit'][$sysSession->lang]);

$myTemplate->add_replacement('<%COURSE_ID%>', $MSG['th_course_id'][$sysSession->lang]);
$myTemplate->add_replacement('<%COURSE_NAME%>', $MSG['th_course_name'][$sysSession->lang]);
$myTemplate->add_replacement('<%COURSE_TEACHER%>', $MSG['th_course_teacher'][$sysSession->lang]);
$myTemplate->add_replacement('<%COURSE_ENROLL_DATE%>', $MSG['th_enroll_date'][$sysSession->lang]);
$myTemplate->add_replacement('<%COURSE_STUDY_DATE%>', $MSG['th_study_date'][$sysSession->lang]);
$myTemplate->add_replacement('<%COURSE_INTRODUCTION%>', $MSG['th_introduction'][$sysSession->lang]);
$myTemplate->add_replacement('<%COURSE_LIMIT%>', $MSG['th_limit'][$sysSession->lang]);
$myTemplate->add_replacement('<%BTN_CLOSE%>', $MSG['btn_close'][$sysSession->lang]);
$myTemplate->add_replacement('<%STUDENT%>', $MSG['rule_student'][$sysSession->lang]);
$myTemplate->add_replacement('<%AUDITOR%>', $MSG['rule_auditor'][$sysSession->lang]);
$myTemplate->add_replacement('<%PEOPLE%>', $MSG['people'][$sysSession->lang]);
$myTemplate->add_replacement('<%COUNTER%>', $counter);
$myTemplate->add_replacement('<%LANG%>', $sysSession->lang);
echo preg_replace('/<%\w+%>/', '', $myTemplate->get_result(false));