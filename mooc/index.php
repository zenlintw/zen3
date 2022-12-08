<?php
// error_reporting(E_ALL^E_NOTICE);
// ini_set('display_errors', 1);
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/lib_counter.php');// 線上
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/course.php');
require_once(sysDocumentRoot . '/mooc/models/school.php');
require_once(sysDocumentRoot . '/mooc/models/forum.php');
require_once(sysDocumentRoot . '/lib/lib_layout.php');// 首頁輸出
require_once(sysDocumentRoot . '/lib/lib_acade_news.php'); //取得最新消息
require_once(sysDocumentRoot . '/lib/lib_calendar.php'); //取得行事曆
require_once(sysDocumentRoot . '/lang/mooc_forum.php');
require_once(sysDocumentRoot . '/lib/common.php');
require_once(sysDocumentRoot . '/lib/lib_cal_alert.php');
require_once(sysDocumentRoot . '/lib/Mobile_Detect.php');

// mooc 模組未開啟的話將網頁導向index.php
if (!defined('sysEnableMooc') || !(sysEnableMooc > 0)) {
	header('Location: /index.php');
    exit;
}

//// 取報名中的課程：課程編號、課程名稱、課程狀態、老師群姓名、老師大頭照
//$rsCourse = new course();
//$course = $rsCourse->getAllCourse('signing');
//
//if (!isset($_POST['groupId'])) {
//    $course = $rsCourse->getAllCourse('signing');
//} else {
//    $smarty->assign('group_id', $_POST['groupId']);
//}


// 取瀏覽人次
$counter = number_format(intval(getCountHtml()));
// 目前可報名的課程數
$allCourseCount = intval(
    dbGetOne('WM_term_course','count(*)',
        'kind="course" and ('.
        '((status = 1 or status = 3) and ( Isnull(en_begin) or en_begin <= curdate()) and (Isnull(en_end) or en_end >= curdate())) '.
        'or '.
        '((status = 2 or status = 4) and ( Isnull(en_begin) or en_begin <= curdate()) and (Isnull(en_end) or en_end >= curdate()) and (Isnull(st_end) or st_end >= curdate())) '.
        ') '
    )
);

$onlineCount = intval(dbGetOne('WM_session','count(*)','chance<3'));
$allMemberCount = intval(dbGetOne('WM_user_account','count(*)','1'));

$statTimes = array(
    'members' => number_format($allMemberCount),
    'online' => number_format($onlineCount),
    'course' => number_format($allCourseCount)
);
$smarty->assign('statTimes', $statTimes);

// 取得分享社群，$socialShare: common.php 已宣告
$schooShareIcon = '';
foreach ($socialShare as $val) {
    switch ($val) {
        case 'FB':
            $schooShareIcon .= '<div class="pic col-md-3"><a href="javascript: void(window.open(\'http://www.facebook.com/share.php?u=\'.concat(encodeURIComponent(location.href))));"><i class="fa fa-facebook" title="Facebook"></i></a></div>';
            break;
        case 'PLURK':
        	if ($profile['isPhoneDevice']) {
        		$schooShareIcon .= '<div class="pic col-md-3"><a href="javascript: void(window.open(\'http://www.plurk.com/m?qualifier=shares&content=\'.concat(encodeURIComponent(\''.$sysSession->school_name.'\')).concat(\' \').concat(encodeURIComponent(location.href))));"><div class="plk" title="Plurk"></div></a></div>';
        	}  else {
                $schooShareIcon .= '<div class="pic col-md-3"><a href="javascript: void(window.open(\'http://www.plurk.com/?qualifier=shares&status=\'.concat(encodeURIComponent(\''.$sysSession->school_name.'\')).concat(\' \').concat(encodeURIComponent(location.href))));"><div class="plk" title="Plurk"></div></a></div>';
        	}
            break;
        case 'TWITTER':
        	if ($profile['isPhoneDevice']) {
        		$schooShareIcon .= '<div class="pic col-md-3"><a href="javascript: void(window.open(\'https://mobile.twitter.com/compose/tweet?text=\'.concat(encodeURIComponent(\''.$sysSession->school_name.'\')).concat(\' \').concat(encodeURIComponent(location.href))));"><div class="tw" title="Twitter"></div></a></div>';
        	} else {
                $schooShareIcon .= '<div class="pic col-md-3"><a href="javascript: void(window.open(\'http://twitter.com/home/?status=\'.concat(encodeURIComponent(\''.$sysSession->school_name.'\')) .concat(\' \').concat(encodeURIComponent(location.href))));"><div class="tw" title="Twitter"></div></a></div>';
        	}
            break;
        case 'LINE':
            $schooShareIcon .= '<div class="pic col-md-3"><a id="share-ln" href="#inline-ln" title="Line"><div class="ln"></div></a></div>';
            break;
        case 'WECHAT':
        	if ($profile['isPhoneDevice']) {
                $schooShareIcon .= "<div class='pic col-md-2'><a id='share-wct' data-fancybox-type='iframe' href='" . getQrcodePath($_SERVER['SCRIPT_URI'], '1', 'L', 9) . "' title='Wechat'><div class='wct'></div></a></div>";
        	} else {
        		$schooShareIcon .= "<div class='pic col-md-2'><a id='share-wct' data-fancybox-type='iframe' href='" . getQrcodePath($_SERVER['SCRIPT_URI']) . "' title='Wechat'><div class='wct'></div></a></div>";
        	}
            break;
    }
}
$smarty->assign('schooShareIcon', $schooShareIcon);

//SWITCH
$switch = getSwitch();

// 行事曆通知
$alert = 'false';
if($sysSession->username != 'guest') {
	if (!isset($_COOKIE['cal_alert']) || $_COOKIE['cal_alert']==0) {
		$cal_type = array('personal','course','class','school');
		$cal_num  = count($cal_type);
		$cal_msg  = GetCalendarAlert();
		$doc 	  = domxml_open_mem(preg_replace('/\sxmlns\s*=\s*"[^"]*"\s/U', ' ', $cal_msg));
		$xpath 	  = @xpath_new_context($doc);
		
		for ($i = 0;$i < $cal_num;$i++){
			$obj = xpath_eval($xpath, '/manifest/' . $cal_type[$i] . '[@num > 0]/memo');
			$p_nodeset = $obj->nodeset;
			$t_p_count = count($p_nodeset);
			
			if ($t_p_count > 0) {
				$alert = 'true';
				break;
			}
		}
		$now = strtotime(date("Y-m-d H:i:s"));
		$end = strtotime(date('Y-m-d',strtotime('+1 day')).' 00:00:00');
		$diff = $end-$now;
		setcookie('cal_alert', 1, time()+$diff, '/', '', $http_secure);
	}
}

$smarty->assign('cal_alert', $alert);

// 有設定顯示的版面才取值與設定值給TPL使用
if (is_array($switch['content_sw']) === TRUE) {
    foreach ($switch['content_sw'] as $k => $v) {
        if ($v === 'true') {
            switch ($k) {
                case 'courselist':
                    //課程群組
                    $group = getGroup();
                    $smarty->assign('group', $group);
                    break;

                case 'forum':
                    // 首頁-取建立最新且公開的討論版名稱3個
                    $forumList = getSchoolForumList();
                    $smarty->assign('forumList', $forumList);

                    // 取第一個討論版文章
                    $firstForum = array_shift($forumList);
                    //$forumData = getSchoolForumData($firstForum['board_id']);
                    $forumData = getDiscussNewsForumData($firstForum['board_id']);
                    $smarty->assign('forumData', $forumData['data']);
                    $smarty->assign('hasForum', 'true');
                    break;
                case 'news':
                    // 取得最新消息討論版名稱
                    dbGetNewsBoard($newsresult); //取得新聞板號
                    $rsForum = new forum();
                    $news = $rsForum->getForumNameByBid($newsresult['board_id']);
                    $smarty->assign('news',$news);

                    // 取得第一個最新消息討論版文章
                    $news_forumData = getDiscussNewsForumData($newsresult['board_id']);
                    $smarty->assign('news_forumData',$news_forumData);
                    $smarty->assign('hasNews', 'true');
                    break;
                case 'calendar':
                    //取行事曆
                    if ($switch['content_sw']['calendar'])
                    $smarty->assign('newCalendarTicket',md5($sysSession->username . 'newCalendar' . $sysSession->ticket . $sysSession->school_id));
                    $smarty->assign('isGuest',$sysSession->username=='guest'); // 沒有登入guest不能切換顯示行事曆 永遠顯示學校行事曆
                    $smarty->assign('MyCalendarSettings',$MyCalendarSettings);
                    $todayDate=date("Y-m-d");
                    $oneweekDate=date("Y-m-d",strtotime("+1 week"));
                    $person=getPersonNewCalendar($todayDate,$oneweekDate);
                    $course=getMyCourseNewCalendar($todayDate, $oneweekDate);
                    $school=getSchoolNewCalendar($todayDate, $oneweekDate);
                    $event=array_merge($person,$course,$school);
                    usort($event, function($a, $b){
                        //var_dump($a['time_begin'],$a['time_begin']==null,$b['time_begin'],$b['time_begin']==null);
                        $aTimeStamp=strtotime($a['memo_date']);
                        $bTimeStamp=strtotime($b['memo_date']);
                        if ($aTimeStamp == $bTimeStamp){
                            if($a['time_begin']==null) return 1;
                            if($b['time_begin']==null) return -1;
                            $aTimeBegin=strtotime($a['time_begin']);
                            $bTimeBegin=strtotime($b['time_begin']);
                            if ($aTimeBegin == $bTimeBegin){
                                return 0;
                            }else if ($aTimeBegin < $bTimeBegin){
                                return -1;
                            }else {
                                return 1;
                            }
                        }else if ($aTimeStamp < $bTimeStamp){
                            return -1;
                        }else {
                            return 1;
                        }
                    });
                    $smarty->assign('event',$event);
                    $smarty->assign('todayDate',$todayDate);
                    $alertBeforeAry=array(
                        "{$MSG['zero_day'][$sysSession->lang]}",
                        "1{$MSG['day_before'][$sysSession->lang]}",
                        "2{$MSG['day_before'][$sysSession->lang]}",
                        "3{$MSG['day_before'][$sysSession->lang]}",
                        "4{$MSG['day_before'][$sysSession->lang]}",
                        "5{$MSG['day_before'][$sysSession->lang]}",
                        "6{$MSG['day_before'][$sysSession->lang]}",
                        "7{$MSG['day_before'][$sysSession->lang]}");

                    $smarty->assign('hasCalendar', 'true');
                    break;

                case 'searchbar':
                    //底圖
                    $advimg = getSearchImg();
                    $smarty->assign('keyword', $_POST['keyword']);
                    $smarty->assign('advimg', $advimg);
                    break;
            }
        // 沒有搜尋功能時，改顯示廣告輪播
    //    } else if ($v === 'false' && $k === 'searchbar') {
    //        //輪播
    //        $ads = ShowAds();
    //        $smarty->assign('ads', $ads);
          }
    }
}

//輪播
$ads = dbGetAll(
    'CO_adv', '*',
    "(open_date='0000-00-00' OR CURDATE()>=open_date) AND (close_date='0000-00-00' OR CURDATE()<=close_date) ORDER BY permute", 
    ADODB_FETCH_ASSOC
);
$siteBannerHTML = '';
for($i=0, $size=count($ads); $i<$size; $i++) {
    $siteBannerHTML .= sprintf('<div data-src="/base/%05d/door/%s"%s></div>', 
        $sysSession->school_id, $ads[$i]['img_path'],
        (!empty($ads[$i]['url'])?' data-link="'.$ads[$i]['url'].'" data-target="_blank"':'')
    );
}
$smarty->assign('siteBannerHTML', $siteBannerHTML);
$smarty->assign('ads_num', count($ads));

//$smarty->assign('courseIng', $course);
$smarty->assign('counter', $counter);
$smarty->assign('contact', $contact);
// 判斷行事曆及常見問題有無開放，有則開啟forum.tpl
if ($switch['content_sw']['forum'] === 'true' || $switch['content_sw']['news'] === 'true' || $switch['content_sw']['calendar'] === 'true') {
    $switch['content_sw']['forum'] = 'true';
    $smarty->assign('msg', $MSG);
    $smarty->assign('cid', $sysSession->school_id);
}

// 網站連結
$links = dbGetAll("CO_links","*","(open_date='0000-00-00' OR CURDATE()>=open_date) AND (close_date='0000-00-00' OR CURDATE()<=close_date) order by permute");

// for desktop
$links_desktop = '';
for ($i = 0, $size=count($links); $i < $size; $i+=4) {
    $links_desktop .= '<div class="item'.(($i==0)?' active':'').'">';
    $links_desktop .= '<div class="row-fluid">';
    $size1 = ($i+4 >= $size)?$size:$i+4;
    for ($j = $i; $j < $size1; $j++) {
        $links_desktop .= sprintf('<div class="span3"><a href="%s" class="thumbnail" target="_blank"><img src="/base/10001/door/%s" alt="Image" style="width:240px;height:100px;max-width:240px;max-height:100px;" /></a></div>',$links[$j]['url'],$links[$j]['img_path']);
    }
    $links_desktop .= '</div>';
    $links_desktop .= '</div>';
}

$links_pad = '';
for ($i = 0, $size=count($links); $i < $size; $i+=3) {
    $links_pad .= '<div class="item'.(($i==0)?' active':'').'">';
    $links_pad .= '<div class="row-fluid">';
    $size1 = ($i+3 >= $size)?$size:$i+3;
    for ($j = $i; $j < $size1; $j++) {
        $links_pad .= sprintf('<div class="span3"><a href="%s" class="thumbnail" target="_blank"><img src="/base/10001/door/%s" alt="Image" style="width:240px;height:100px;max-width:240px;max-height:100px;" /></a></div>',$links[$j]['url'],$links[$j]['img_path']);
    }
    $links_pad .= '</div>';
    $links_pad .= '</div>';
}

$links_phone = '';
for ($i = 0, $size=count($links); $i < $size; $i+=2) {
    $links_phone .= '<div class="item'.(($i==0)?' active':'').'">';
    $links_phone .= '<div class="row-fluid">';
    $size1 = ($i+2 >= $size)?$size:$i+2;
    for ($j = $i; $j < $size1; $j++) {
        $links_phone .= sprintf('<div class="span3"><a href="%s" class="thumbnail" target="_blank"><img src="/base/10001/door/%s" alt="Image" style="max-width:105px;max-height:41px;" /></a></div>',$links[$j]['url'],$links[$j]['img_path']);
    }
    $links_phone .= '</div>';
    $links_phone .= '</div>';
}
$smarty->assign('links_desktop', $links_desktop);
$smarty->assign('links_desktop_count', ceil($size/4));
$smarty->assign('links_pad', $links_pad);
$smarty->assign('links_pad_count', ceil($size/3));
$smarty->assign('links_phone', $links_phone);
$smarty->assign('links_phone_count', ceil($size/2));
$smarty->assign('switch', $switch);
//是否為獨立校
$smarty->assign('is_independent', is_independent_school);
//是否為入口網校
$smarty->assign('is_portal', is_portal_school);

$smarty->display('index.tpl');

// 清除 寄放在 lcms 的 wm_learning_hash cookie
if ($_COOKIE['wm_learning_hash_clean'] === 'N' && $sysSession->username === 'guest') {
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    
    $protocol = isset($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' ? 'https' : 'http';
    $wm_learning_hash = _3desEncode(json_encode(array('clear' => 'Y', 'time' => time(), 'school_domain' => $protocol . '://' . $_SERVER['SERVER_NAME']))); 
    
    $pathWmCookieHash2Lcms = sysLcmsHost . '/lms/wmcookie/clear/' . rawurlencode(base64_url_encode($wmCookieHash));
    echo '<img src="' . $pathWmCookieHash2Lcms . '" style="display: none;">';
    setcookie('wm_learning_hash_clean', 'Y', time() - 3600, '/');
    setcookie('wm_learning_hash_start', '', time() - 3600, '/');
}