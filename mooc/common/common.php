<?php
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');// mod_short_link_lib.php 需用到 aclpermission2bitmap()
require_once(sysDocumentRoot . '/message/lib.php');// mod_short_link_lib.php 需用到 getNameFromID()
require_once(sysDocumentRoot . '/learn/mycourse/modules/mod_short_link_lib.php');// 作業、考試、訊息、文章未讀筆數
require_once(sysDocumentRoot . '/lang/mooc.php');
require_once(sysDocumentRoot . '/mooc/models/school.php');
require_once(sysDocumentRoot . '/mooc/models/course.php');
require_once(sysDocumentRoot . '/lib/lib_layout.php');
require_once(sysDocumentRoot . '/lib/Mobile_Detect.php');

// file address
$rev_file_path = sysDocumentRoot.'/public/meta/rev.txt';


// 取得學校資料
$rsSchool = new school();
$canRegister = $rsSchool->getRegisterSocial($sysSession->school_id);
$schoolInfo = $rsSchool->getSchoolIndexInfo($sysSession->school_id);
$socialShare = $rsSchool->getShareSocial($sysSession->school_id);
$myCourseView = $rsSchool->getMyCourseView($sysSession->school_id);

// 取得使用者課程資訊
if ($sysSession->username != 'guest') {
    $rsCourse = new course();
    $courseCnt = $rsCourse->getUserCourses($sysSession->username, $sysRoles['student']|$sysRoles['auditor'], true);
}else{
    $courseCnt = 0;
}

if (!is_portal_school && !is_independent_school) {
    $url = $sysConn->GetOne("SELECT school_host FROM ".sysDBprefix."MASTER.WM_school where school_id = 10001 ");
    $logo_target = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
    $logo_target .= '://'.$url;

} else {
    $logo_target .= $appRoot.'/mooc/index.php';
}

if (!is_portal_school && is_independent_school) {
    $url = $sysConn->GetOne("SELECT school_host FROM ".sysDBprefix."MASTER.WM_school where school_id = 10001 ");
    $powerby_target = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
    $powerby_target .= '://'.$url;
}

$smarty->assign('logo_target', $logo_target);
$smarty->assign('powerby_target', $powerby_target);
$smarty->assign('from_url', $_SERVER['PHP_SELF']);


//$baseUrl = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
//$baseUrl .= '://'. $_SERVER['HTTP_HOST'];
$baseUrl = '';

// 語系
$lang = array('Big5' => '正體中文', 'GB2312' => '简体中文', 'en' => 'English');
removeUnAvailableChars($lang);
$show_lang = true;
$lang_dropdown = '';
if (count($lang)==1) {
    $show_lang = false;
}else{
    $lang_dropdown .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false" style="width:100px;">'.$lang[$sysSession->lang].'<i class="fa fa-caret-down" aria-hidden="true" style="color:#FFFFFF;margin-left:0.3em;"></i></a>';
    $lang_dropdown .= '<ul class="dropdown-menu">';
    foreach ($lang as $langKey => $langVal) {
        if ($langKey == $sysSession->lang) continue;
        $lang_dropdown .= '<li style="height: 36px;"><a href="?lang='.$langKey.'">'.$langVal.'</a></li>';
    }
    $lang_dropdown .= '</ul>';

    $p_lang_dropdown = '<div style="float:left" onclick="showLang();">'.$lang[$sysSession->lang].'<i class="fa fa fa-caret-right" aria-hidden="true" style="margin-left:0.3em;"></i></div>';
    $p_lang_dropdown .= '<div id="menu_lang" style="float:left;display:none">';
    foreach ($lang as $langKey => $langVal) {
        if ($langKey == $sysSession->lang) continue;
        $p_lang_dropdown .= '<div class="lang"><a href="?lang='.$langKey.'">'.$langVal.'</a></div>';
    }
    $p_lang_dropdown .= '</div>';
 
    $smarty->assign('p_lang_dropdown', $p_lang_dropdown);
}

// $sysConn->debug=true;
// smarty參數
$smarty->compile_check = true;
$smarty->debugging = false;

/**
 * 組按鈕
 *
 * @param array $buttons array(按鈕ID => array(按鈕顯示名稱、按鈕超連結))
 * @return string $html 按鈕HTML
 *
 **/
function exportButton($buttons) {
    $i = 0;
    $j = count($buttons) - 1;
    foreach ($buttons as $k => $v) {
        // 只有一個按鈕
        if ($i === $j && $j === 0) {
            $css = 'btn-blue';
        // 多個按鈕，第一個
        } else if ($i === 0 && $j >= 1) {
            $css = 'margin-right-10 btn-blue';
        // 多個按鈕，最後一個
        } else if ($i === $j && $j >= 1) {
            $css = 'btn-gray';
        // 多個按鈕，其他
        } else {
            $css = 'margin-right-10 btn-gray';
        }
        $html .= '<a href="' . $v[1] . '" class="btn btn-primary aNormal ' . $css . '" id="' . $k . '">' . $v[0] . '</a>';
        $i++;
    }
    return $html;
}

/**
 * 組語系: 因應WMPRO會用到MSG
 *
 * @param array $arrMsg
 * @return string $html 語系JS
 *
 **/
function exportLang($msg) {
    $js = "var MSG = new Array();\n";
    foreach ($msg as $k => $v) {
        $js .= "MSG[" . ($k+1) . "]='" . $v . "';\n";
    }

    return $js;
}

/**
 * 禁止回上一頁
 *
 *
 **/
function noCacheRedirect($url) {
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', false);
    header('Location:' . $url);
}


//底色
function getThemeCarlo(){
    if (!is_portal_school && !is_independent_school) {
        $themedata = dbGetOne(sysDBprefix.portal_school_id.'.`WM_portal`', '`value`', '`portal_id` = "theme" AND `key` = "sub_style"');
    } else {
        $themedata = dbGetOne('`WM_portal`', '`value`', '`portal_id` = "theme" AND `key` = "style"');
    }

    return $themedata;
}

// 轉換檔案大小格式
function FileSizeConvert($bytes)
{
    $bytes   = floatval($bytes);
    $arBytes = array(
        0 => array(
            "UNIT" => "TB",
            "VALUE" => pow(1024, 4)
        ),
        1 => array(
            "UNIT" => "GB",
            "VALUE" => pow(1024, 3)
        ),
        2 => array(
            "UNIT" => "MB",
            "VALUE" => pow(1024, 2)
        ),
        3 => array(
            "UNIT" => "KB",
            "VALUE" => 1024
        ),
        4 => array(
            "UNIT" => "Bytes",
            "VALUE" => 1
        )
    );
    
    foreach ($arBytes as $arItem) {
        if ($bytes >= $arItem["VALUE"]) {
            $result = $bytes / $arItem["VALUE"];
            $result = str_replace(".", ".", strval(round($result, 2))) . " " . $arItem["UNIT"];
            break;
        } else {
            $result = '0 Byte';
        }
        
    }
    return $result;
}

// 設定 mooc 關閉時，各網頁進入條件
if (!defined('sysEnableMooc') || !(sysEnableMooc > 0)) {
    $pageurl = explode('.php', $_SERVER['REQUEST_URI']);
    $pageurl = $pageurl[0] . '.php';
    switch ($pageurl) {
        case '/mooc/register.php':
        case '/mooc/login.php':
        case '/mooc/forget.php':
        case '/mooc/resend.php':
        case '/mooc/resetpwd.php':
        case '/mooc/explorer.php':
            // 完全不允許進入
            echo 'Access denied.';
            die();
            break;
        case '/mooc/course_info.php':
        case '/mooc/mycourse.php':
        case '/mooc/course_cancel.php':
        case '/mooc/course_enploy.php':
            // 不允許參觀者
            if ($sysSession->username=='guest') {
                echo 'Access denied.';
                die();
            }
            break;
    }
    // 是否啟用MOOC流程
    $smarty->assign('moocFlow', 'N');
} else {
    $smarty->assign('moocFlow', 'Y');
}

// 判斷使用者是否使用行動裝置
$detect = new Mobile_Detect;

// 設定登入者基本資料
$profile = array(
    'realname' => $sysSession->realname,
    'username' => $sysSession->username,
    'email' => $sysSession->email,
    'userPicId' => base64_encode(@mcrypt_encrypt(MCRYPT_DES, sysTicketSeed . $_COOKIE['idx'], $username, 'ecb')). '&' . uniqid(''),
    'isTeacher' => aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']),
    'isCsOpener' => aclCheckRole($sysSession->username, $sysRoles['course_opener'], $sysSession->school_id),
    'isDirector' => aclCheckRole($sysSession->username, $sysRoles['director']| $sysRoles['class_instructor']),
    'isAdvManager' => aclCheckRole($sysSession->username, $sysRoles['root'] | $sysRoles['administrator'], $sysSession->school_id),
    'isManager' => aclCheckRole($sysSession->username, $sysRoles['root'] | $sysRoles['administrator'] | $sysRoles['manager'], $sysSession->school_id),
    'isMobileDevice' => $detect->isMobile(),
    'isPhoneDevice' => ($detect->isMobile() && !$detect->isTablet()),
//    'isPhoneDevice' => 1
);

// 取未讀訊息加總
if ($sysSession->username != 'guest') {
    $a = getQTIUndoCount($sysSession->username, 'homework');
    $b = getQTIUndoCount($sysSession->username, 'exam');
    $CMAry = checkMessage($sysSession->username);
    $c = intval($CMAry[1]);
    $d = checkPost($sysSession->username);
    $e = getQTIUndoCount($sysSession->username, 'peer');
}else{
    $a = 0;
    $b = 0;
    $CMAry = 0;
    $c = 0;
    $d = 0;
    $e = 0;
}
// $messageCnt = $a + $b + $c + $d + $e;
// 改用 課程數量
$messageCnt = $courseCnt;

$smarty->assign('appRoot', $baseUrl);
$smarty->assign('appTitle', $sysSession->school_name);
$smarty->assign('lang', $lang);
$smarty->assign('show_lang', $show_lang);
$smarty->assign('nowlang', $sysSession->lang);
$smarty->assign('lang_dropdown', $lang_dropdown);
$smarty->assign('profile', $profile);
$smarty->assign('message_cnt', $messageCnt);
$smarty->assign('hw_message_cnt', $a + $e);
$smarty->assign('hw_exam_cnt', $b);
$smarty->assign('IM_cnt', $c);
$smarty->assign('Post_cnt', $d);
$smarty->assign('Peer_cnt', $e);

// meta
$smarty->assign('metaTitle', $schoolInfo['banner_title1']);
$smarty->assign('metaDescription', strip_tags($schoolInfo['banner_title3']));
$smarty->assign('metaSitename', $schoolInfo['banner_title1']);
$smarty->assign('metaUrl', $baseUrl);
if (file_exists(sysDocumentRoot. '/base/' . $sysSession->school_id . '/door/tpl/brand_logo.png') === true) {
    $smarty->assign('metaImage', $baseUrl. '/base/' . $sysSession->school_id . '/door/tpl/brand_logo.png');
} else if (file_exists(sysDocumentRoot. '/base/' . $sysSession->school_id . '/door/tpl/logo.png') === true) {
    $smarty->assign('metaImage', $baseUrl. '/base/' . $sysSession->school_id . '/door/tpl/logo.png');
} else {
    $smarty->assign('metaImage', $baseUrl. '/theme/default/learn/co_fblogo.png');
}

// 首頁圖片
$smarty->assign('schoolId', $sysSession->school_id);
$smarty->assign('theme', getThemeCarlo());
$target = sysDocumentRoot . "/base/{$sysSession->school_id}/door/tpl/";
// 要確認的圖片檔案
$files = array(
        'logo',
        'banner_bg',
        'banner_logo'
);
foreach ($files as $v) {
    if(file_exists($target . $v . '.png')) {
        $smarty->assign($v.'Css', 'Y');
     }
}

// 是否允許註冊(一般註冊、社群註冊)
$smarty->assign('canReg', $canRegister);

// 取公告版編號
if (!empty($sysSession->course_id)) {
    $bltBid = dbGetOne('`WM_term_course`', 'bulletin', '`course_id` = ' . $sysSession->course_id);
    $smarty->assign('bltBid', $bltBid);
}else{
    $smarty->assign('bltBid', 0);
}

// 首頁資訊
if (isset($schoolInfo) && $schoolInfo !== 'X') {

    if('/learn/news/index_faq.php'==$schoolInfo['footer_faq']){
        $schoolInfo['footer_faq'] = $schoolInfo['footer_faq'].'?'.md5('faq');
    }

    $smarty->assign('bannerT1', strip_tags($schoolInfo['banner_title1']));
    $smarty->assign('bannerT2', strip_tags($schoolInfo['banner_title2']));
    $smarty->assign('bannerT3', $schoolInfo['banner_title3']);
    $smarty->assign('footAboutUrl', $schoolInfo['footer_about']);
    $smarty->assign('footContactUrl', $schoolInfo['footer_contact']);
    $smarty->assign('footFaqUrl', $schoolInfo['footer_faq']);
    $smarty->assign('footInfo', strip_tags($schoolInfo['footer_info']));
} else {

    $footer_faq = $MSG['faq_default_url'][$sysSession->lang].'?'.md5('faq');

    $smarty->assign('bannerT1', $MSG['adv_title'][$sysSession->lang]);
    $smarty->assign('bannerT2', $MSG['adv_subtitle'][$sysSession->lang]);
    $smarty->assign('bannerT3', $MSG['adv_description_1'][$sysSession->lang]);
    $smarty->assign('footAboutUrl', $MSG['about_default_url'][$sysSession->lang]);
    $smarty->assign('footContactUrl', $MSG['contact_default_url'][$sysSession->lang]);
    $smarty->assign('footFaqUrl', $baseUrl.$footer_faq);
    $smarty->assign('footInfo', $MSG['footer_info'][$sysSession->lang]);
}

// 如果課程列表不開啟，探索課程連結也不顯示
$cSwitch = getSwitch();
$smarty->assign('exploreEnable', $cSwitch['content_sw']['courselist']);
unset($cSwitch);

// 學校種類 入口網校
$smarty->assign('is_portal_school', is_portal_school);
$smarty->assign('is_independent_school', is_independent_school);
// 啟用付費
$smarty->assign('enablePaid', enablePaid);

// Share 的社群平台
$smarty->assign('socialShare', $socialShare);

// 時間格式(tpl裡代入 : {$time|date_format:$timeConfig})
$timeConfig = '%Y-%m-%d %H:%M';
$smarty->assign('timeConfig', $timeConfig);

// 解決上線 cache 問題，在 css, js 檔案後方加入版本參數
if (file_exists($rev_file_path)) {
    $rev_num = file_get_contents($rev_file_path);
    $smarty->assign('file_rev_num', substr(md5($rev_num), 0, 8));
}
// 是否啟用行動版，目前只單純判斷 $_COOKIE['mobileweb']
$smarty->assign('sysEnableApp', sysEnableApp);
$smarty->assign('sysAppIosUrl', sysAppIosUrl);
$smarty->assign('sysAppAndroidUrl', sysAppAndroidUrl);
// 取得網站 domain 供 cookie 使用
$generalDomain = $_SERVER['HTTP_HOST'];
$smarty->assign('generalDomain', $generalDomain);
unset($tmpDomainArray, $generalDomain);

// 暫時判斷是否為ipad
if(strstr($_SERVER['HTTP_USER_AGENT'],'iPad')) {
    $smarty->assign('is_ipad', true);    
} else {
    $smarty->assign('is_ipad', false);
}

// 手機版 - 課程header使用 begin
if (($profile['isPhoneDevice'])&&($sysSession->course_id > 10000000)) {
    // 設定課程名稱
    $smarty->assign('course_name', $sysSession->course_name);
    // 取得此課程的選單
    $getSysbar = true;
    $envWork = $sysSession->env;
    $envRead = 'learn';
    $GLOBALS['HTTP_RAW_POST_DATA'] = '<manifest><ticket></ticket></manifest>';
    ob_start();
    require_once(sysDocumentRoot . '/academic/goto.php');
    $menuXML = ob_get_contents();
    ob_end_clean();

    // goto.php 會送出content-type: text/xml 會造成錯誤
    header("Content-type: text/html");

    function xml2array($xmlObject, $out=array())
    {
        foreach ( (array) $xmlObject as $index => $node )
            $out[$index] = ( is_object ( $node ) ) ? xml2array ( $node ) : $node;
        return $out;
    }

    function menuItemObject2Array($menuItemObject, $level) {
        global $sysSession;
        $menuItemTitles = xml2array($menuItemObject->title);
        // menu所設定的語系是小寫，因此不能直接用$sysSession->lang
        $userSessionLang = strtolower($sysSession->lang);

        $menuItemHref = xml2array($menuItemObject->href);

        // 開始上課的href要更改
        if (strcmp($menuItemHref[0], '/learn/path/launch.htm') == 0) {
            $menuItemHref[0] = '/learn/path/m_pathtree.php';
        }

        return array(
            'title'=> $menuItemTitles[$userSessionLang], 
            'href' => $menuItemHref[0],
            'target' => $menuItemHref['@attributes']['target'],
            'level' => $level
        );
    }

    $menu = array();
    $menuArray = xml2array(simplexml_load_string($menuXML)); 
    if (is_array($menuArray['items']['item'])) {
        foreach($menuArray['items']['item'] as $menuGroup){
            $mGroupAttributes = array();
            foreach($menuGroup->attributes() as $a => $b) {
                $mGroupAttributes[$a] = $b;
            }
            if (strcmp($mGroupAttributes['hidden'],'true') == 0) continue;
            if (in_array($mGroupAttributes['id'], array('SYS_06_01_000','SYS_07_01_000'))) continue;

            $menu[] = menuItemObject2Array($menuGroup,1);

            if (count($menuGroup->item)) {
                foreach($menuGroup->item as $menuItem) {
                    $mItemAttributes = array();
                    foreach($menuItem->attributes() as $a => $b) {
                        $mItemAttributes[$a] = $b;
                    }

                    if (strcmp($mItemAttributes['hidden'],'true') == 0) {
                        continue;
                    }
                    $menu[] = menuItemObject2Array($menuItem,2);
                }
            }
        }
    }

    // 若是教師身份，則附加課程管理的選單
    if (aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'], $sysSession->course_id))
    {
        $menu[] = array('title' => '課程管理', 'href' => 'about:blank', 'target' => '_default', 'level' => 1);
        $menu[] = array('title' => '審核學員', 'href' => '/mooc/teach/review/review_review.php', 'target' => 'default', 'level' => 2);
        $menu[] = array('title' => '到課統計', 'href' => '/mooc/teach/student/stud_info.php', 'target' => 'default', 'level' => 2);
        $menu[] = array('title' => '設定助教', 'href' => '/mooc/teach/student/teacher_list.php', 'target' => 'default', 'level' => 2);
        $menu[] = array('title' => '討論板管理', 'href' => '/mooc/teach/course/cour_subject.php', 'target' => 'default', 'level' => 2);
        $menu[] = array('title' => '討論室管理', 'href' => '/mooc/teach/chat/chat_manage.php', 'target' => 'default', 'level' => 2);
        $menu[] = array('title' => '課程行事曆', 'href' => '/mooc/teach/calendar/calendar.php', 'target' => 'default', 'level' => 2);
    }
    $smarty->assign('course_menus', $menu);
}

// 手機版 - 課程header使用 end

// 單次上傳檔案容量
$smarty->assign('upload_max_filesize', ini_get('upload_max_filesize'));

//// 我的課程呈現方式
$smarty->assign('myCourseView', $myCourseView);

// 資安：CSRF token
$smarty->assign('csrfToken', md5($sysSession->idx));

$schoolcanReg = $sysConn->GetOne("SELECT canReg FROM ".sysDBprefix."MASTER.WM_school where school_id=$sysSession->school_id and school_host='$_SERVER[HTTP_HOST]' ");
$smarty->assign('schoolcanReg', $schoolcanReg);
