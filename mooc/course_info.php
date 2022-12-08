<?php
    /**
     * 課程資訊頁
     *
     * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
     *
     * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
     * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
     * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
     *
     * @package     WM3
     * @author      Jeff Wang <jeff@sun.net.tw>
     * @copyright   2014- SunNet Tech. INC.
     * @since       2014-03-05
     *
     */

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    require_once(sysDocumentRoot . '/mooc/models/course.php');
    require_once(sysDocumentRoot . '/lib/lib_adjust_char.php');
    require_once(sysDocumentRoot . '/lib/course.php');
    require_once(sysDocumentRoot . '/lang/course_manage.php');
    require_once(sysDocumentRoot . '/lang/mycourse.php');
    require_once(sysDocumentRoot . '/lang/cour_introduce.php');
    require_once(sysDocumentRoot . '/lang/mooc_teach.php');
    require_once(sysDocumentRoot . '/lib/Hongu/Validate/Validator/XssAttack.php');

    /**
     *
     * 取得課程介紹與課程安排的html
     * @param string $content
     */
    function getIntroHtml($content, $cid)
    {
        global $MSG,$sysSession;
        $html = '';
        if ($xmldoc = @domxml_open_mem($content)) {
            $ctx = xpath_new_context($xmldoc);
            $nodes = $ctx->xpath_eval('/manifest/intro[@checked="true"]');
            if (count($nodes->nodeset)) {
                $node = $nodes->nodeset[0];
                $type = $node->get_attribute('type');
                $text = $node->get_content();
                if ($text && trim($text) != '') {
                    if ($type == 'template')
                        $html = stripslashes($text);
                    else if ($type == 'upload') {
                        $basePath = sprintf('%s/base/%05d/course/%08d/content',				// 課程教材路徑
                            sysDocumentRoot,
                            $sysSession->school_id,
                            $cid);
                        $text = un_adjust_char($text);
                        if (file_exists($basePath . $text)) {
                            $url = substr($basePath . $text, strlen(sysDocumentRoot));
                            $html = '<a href="'.$url.'" target="_blank">link</a>';
                        }
                    }
                }
            }
        }

        return $html;
    }

    // 全校課程 - 詳細資料會以GET的方式傳 cour_id參數
    if (strpos($_SERVER['REQUEST_URI'],'?') !== false) {
        $argv = explode('/', substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],'?')));
    } else {
        $argv = explode('/', $_SERVER['REQUEST_URI']);
    }

    // XSS防護
    $honguXss = new Hongu_Validate_Validator_XssAttack();
    for($i=0, $size=count($argv); $i<$size; $i++) {
        if (!$honguXss->validate($argv[$i])) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }
    }

    if (isset($argv[3])&&!empty($argv[3])&&!is_numeric($argv[3])){
        header('HTTP/1.1 403 Forbidden');
        exit;
    }

    if ($argv[2] == 'course_info.php'){
        $pageEmbed = true;
        if (isset($_GET['cour_id'])&&strlen(intval($_GET['cour_id']))==8) {
            $courseStatus = dbGetOne('WM_term_course','status',sprintf('course_id=%d',$_GET['cour_id']));
            if (in_array($courseStatus,array(1,2,3,4))) {
                $course_id = intval($_GET['cour_id']);
            }
        }else{
            $course_id = $sysSession->course_id;
        }
    }else{
        $pageEmbed = false;
        $course_id = intval($argv[2]);
    }

    // 入口網校可顯示其他指定學校的課程
    $assignSch = '';
    if (is_portal_school) {
        if ('' != $argv[3]) {
            $assignSch = intval($argv[3]);
        } else {
            $assignSch = $sysSession->school_id;
        }
        $useDb = sysDBprefix.$assignSch.'.';
    }

    // 如果 mooc 沒開的話
    if (defined('sysEnableMooc') && (sysEnableMooc <= 0)) {
        if ($sysSession->username=='guest') {
            echo '不允許觀看';
            die();
        }
        if ($rsSchool->getSchoolStudentMooc($sysSession->school_id) > 0) {
            $moocDisabled =  false;
            $pageEmbed = true;
        } else {
            echo '不允許觀看';
            die();
        }
    }

    $rsCourse = new course();

    // 課程編號不合法或課程編號不存在，導回首頁
    if (!$rsCourse->isCourseExists($course_id, $assignSch)) {
        header("LOCATION: /");
        exit;
    }

    //取得課程資料
    $course = $rsCourse->getCourseById($course_id, $assignSch);

    //設定報名起訖日期顯示字串
    $course['enployDateStr'] = sprintf('%s<span class="%s" style="font-weight:normal;">%s</span>&nbsp;%s<span class="%s" style="font-weight:normal;">%s</span>',
        $MSG['from2'][$sysSession->lang],
        (empty($course['en_begin']) ? 't18_w' : 't18_o'),
        (empty($course['en_begin']) ? $MSG['now'][$sysSession->lang] : $course['en_begin']),
        $MSG['to2'][$sysSession->lang],
        (empty($course['en_end']) ? 't18_w' : 't18_o'),
        (empty($course['en_end']) ? $MSG['forever'][$sysSession->lang] : $course['en_end'])
    );

    // 狀態：0: 不可報名, 1: 可報名
    $course['enDenyMsg'] = '';
    if (in_array($course['status'], array(1,2,3,4))) {
        if (isset($course['en_begin']) && (time() < strtotime($course['en_begin'].' 00:00:00'))) {
            $course['enStatus'] = 0;
            $course['enDenyMsg'] = $MSG['enployNotStart'][$sysSession->lang];
        }else if (isset($course['en_end']) && (time() > strtotime($course['en_end'].' 23:59:59'))) {
            $course['enStatus'] = 0;
            $course['enDenyMsg'] = $MSG['enployClosed'][$sysSession->lang];
        } else if (intval($course['n_limit']) > 0){ //正式生的人數限制
            // 取得目前此課程的正式生人數
            $studentNum = dbGetOne('WM_term_major','count(*)',sprintf("course_id=%d and role&%d", $course_id, $sysRoles['student']));
            if ($studentNum >= $course['n_limit']) {
                $course['enStatus'] = 0;
                $course['enDenyMsg'] = $MSG['enployStudentLimitFull'][$sysSession->lang];
            }else{
                $course['enStatus'] = 1;
            }
        } else {
            $course['enStatus'] = 1;
        }
        
	    if (in_array($course['status'], array(3,4))) {
	    	$course['enStatus'] = 0;
	    	$course['enDenyMsg'] = '';
	    }
        
    }else{
        $course['enStatus'] = 0;
        $course['enDenyMsg'] = $MSG['courseclosed'][$sysSession->lang];
    }

    // 不能報名，卻沒有預設訊息，則一律給訊息"不允許報名"
    if (($course['enStatus'] == 0) && empty($course['enDenyMsg'])) {
        $course['enDenyMsg'] = $MSG['notallowsign'][$sysSession->lang];
    }

    //設定上課起訖日期顯示字串
    $course['studyDateStr'] = sprintf('%s<span class="%s" style="font-weight:normal;">%s</span>&nbsp;%s<span class="%s" style="font-weight:normal;">%s</span>',
        $MSG['from2'][$sysSession->lang],
        (empty($course['st_begin']) ? 't18_w' : 't18_o'),
        (empty($course['st_begin']) ? $MSG['now'][$sysSession->lang] : $course['st_begin']),
        $MSG['to2'][$sysSession->lang],
        (empty($course['st_end']) ? 't18_w' : 't18_o'),
        (empty($course['st_end']) ? $MSG['forever'][$sysSession->lang] : $course['st_end'])
    );

    // 是否在上課期間
    if (strtotime(date('Y-m-d')) < strtotime($course['st_begin']) && $course['st_begin'] != null) {
        // 上課時間未開始
        $course['st_period'] = '2';
    } else if (strtotime(date('Y-m-d')) > strtotime($course['st_end']) && $course['st_end'] != null) {
        // 上課時間已過期
        $course['st_period'] = '1';
    } else {
        $course['st_period'] = '0';
    }

    //設定課程資訊的URL
    if ($_SERVER['HTTP_HOST'] == '192.168.10.155') {
        $_SERVER['SCRIPT_URI'] = str_replace($_SERVER['SERVER_NAME'], $_SERVER['HTTP_HOST'], $_SERVER['SCRIPT_URI']);
    }

    $course['uri'] = urlencode($_SERVER['SCRIPT_URI']);

    $parseurl = parse_url($_SERVER['SCRIPT_URI'] );
    $host = $parseurl['scheme'] . '://' . $parseurl['host'];
    $course['qrcode_url'] = getQrcodePath($host . '/info/'  . $course_id . '/' . $assignSch . '?lang=' . $sysSession->lang, '1', 'L', 5);


    //課程分類資訊
    $course['parentGroups'] = $rsCourse->getBelongCourseGroups($course_id);

    //取相關課程
    $parentGroupIds = array();
    $relativeCoures = array();
    for ($i = 0, $size=count($course['parentGroups']); $i < $size; $i++) {
        $parentGroupIds[] = $course['parentGroups'][$i]['parent'];
    }
    
    // 取子孫，但不取明細資料
    $tmpFamily = $rsCourse->getCourseTreeFamily(array($parentGroupIds), null, false);
    // 取課程編號
    $cids = array();
    // 組子孫陣列，以銜接原邏輯
    foreach ($tmpFamily[$sysSession->school_id] as $k => $v) {
        $cids[sprintf("'%d%d'", $sysSession->school_id, $v)] = $v;
    }
    $family = $cids;
    
    $familyCnt = count($family);
    $allRelativeCoures = @array_rand($family, (($familyCnt <= 4) ? $familyCnt : 5));

    $course['relativeCourses'] = array();
    $arcAryCnt = 0;
    for ($i = 0, $size=count($allRelativeCoures); $i < $size && $size >1; $i++) {
        $arcSId = substr($allRelativeCoures[$i], 1,5);
        $arcCId = substr($allRelativeCoures[$i], 6,8);
        if ($arcCId == $course_id) continue;
        $course['relativeCourses'][$arcSId][] = $arcCId;
        $arcAryCnt++;
        if ($arcAryCnt == 4) break;
    }

    if ($arcAryCnt>0){
        $course['relativeCourses'] = $rsCourse->getAllCourse('signing',$course['relativeCourses']);
    }

    //課程介紹影片
    $course['introVideo'] = '';
    $courseFolder = sprintf("/base/%d/course/%d/content/public", (($assignSch != '') ? $assignSch : $sysSession->school_id), $course_id);
	$videoFileAbsolutePath = realpath(sysDocumentRoot.$courseFolder)."/course_introduce.mp4";
	if (file_exists($videoFileAbsolutePath)) {
	    $course['introVideo'] = $courseFolder."/course_introduce.mp4";
            if (file_exists(realpath(sysDocumentRoot.$courseFolder)."/course_introduce.jpg")) {
                $course['introVideoPreview'] = $courseFolder."/course_introduce.jpg";
            }else{
                $course['introVideoPreview'] = "/lib/app_show_course_picture.php?courseId=" . base64_encode($course_id);
            }
	}

    //課程介紹的html
    $course['introHTML'] = '';
    list($content) = dbGetStSr('WM_term_introduce', 'content', 'course_id=' . $course_id . ' and intro_type="C"', ADODB_FETCH_NUM);
    if ($content) {
        $course['introHTML'] = getIntroHtml($content, $course_id);
    }
    //若沒有資料給預設的html
    if ($course['introHTML'] == ''){
        $course['introHTML'] = stripslashes($MSG['cour_intro_content'][$sysSession->lang]);
    }
    
    // 課程介紹
    $course['content'] = htmlspecialchars($course['content']);

    //課程安排的html
    $course['syllabusHTML'] = '';
    list($content) = dbGetStSr('WM_term_introduce', 'content', 'course_id=' . $course_id . ' and intro_type="R"', ADODB_FETCH_NUM);
    if ($content) {
        $course['syllabusHTML'] = getIntroHtml($content, $course_id);
    }
    //若沒有資料給預設的html
    if ($course['syllabusHTML'] == ''){
        $course['syllabusHTML'] = stripslashes($MSG['cour_arrange_content'][$sysSession->lang]);
    }

    //取出課程的授課老師 - 秀出大頭照
    $teachers = $rsCourse->getTeachersByCId($course_id, $assignSch);

    $course['teachers'] = array();
    for ($i = 0, $size=count($teachers); $i < $size; $i++) {
        $teachers[$i]['id'] = base64_encode(urlencode($teachers[$i]['username']));
        
        if (512&$teachers[$i]['ROLE']) {
             $teachers[$i]['title'] = $MSG['teacher'][$sysSession->lang];
        } else if (128&$teachers[$i]['ROLE']) {
             $teachers[$i]['title'] = $MSG['instructor'][$sysSession->lang];
        } else if (64&$teachers[$i]['ROLE']) {
             $teachers[$i]['title'] = $MSG['assistant'][$sysSession->lang];
        }
        $course['teachers'][] = $teachers[$i];
//        if (count($course['teachers']) == 2) break;
    }
    
    //取推薦課程
    $teachs = $rsCourse->getUserTeachCourses($course['teachers'][0]['username']);

    $teachsCnt = count($teachs);
    $allRecommendCoures = @array_rand($teachs, (($teachsCnt <= 5) ? $teachsCnt : 6));

    $course['recommendCourses'] = array();
    $rcAryCnt = 0;
    for ($i = 0, $size=count($allRecommendCoures); $i < $size && $size > 1; $i++) {
        $arcSId = substr($allRecommendCoures[$i], 0,5);
        $arcCId = substr($allRecommendCoures[$i], 5,8);
        if ($arcCId == $course_id) continue;
        $course['recommendCourses'][$arcSId][] = $arcCId;
        $rcAryCnt++;
        if ($rcAryCnt == 5) break;
    }

    if ($rcAryCnt > 0){
        $course['recommendCourses'] = $rsCourse->getAllCourse('signing',$course['recommendCourses']);
    }


    // 目前使用者是否有選修這門課,正式生為 1，旁聽生為 2，未修為 0 
    if (is_portal_school) {
        $course['hasMajored'] = 0;
        // 判斷是否為此課正式生
        $courseSt = dbGetOne(sysDBname.'.`CO_all_major` as M inner join '.sysDBname.'.`CO_all_course` as C on M.course_id=C.course_id AND M.school=C.school AND C.status != 9',
                'count(username)',
                'M.username="' . $sysSession->username . '" and M.course_id=' . $course_id . ' and M.school=' . $assignSch . ' and M.role&' . $sysRoles['student']);
        if ($courseSt >= 1) {
            $course['hasMajored'] = 1;
        } else {
            // 判斷是否為此課;旁聽生
            $courseAr = dbGetOne(sysDBname.'.`CO_all_major` as M inner join '.sysDBname.'.`CO_all_course` as C on M.course_id=C.course_id AND M.school=C.school AND C.status != 9',
                'count(username)',
                'M.username="' . $sysSession->username . '" and M.course_id=' . $course_id . ' and M.school=' . $assignSch . ' and M.role&' . $sysRoles['auditor']);
            if ($courseAr >= 1) {
                $course['hasMajored'] = 2;
            }
        }
    } else {
        $course['hasMajored'] = aclCheckRole($sysSession->username, $sysRoles['student'], $course_id) ? 1 : (aclCheckRole($sysSession->username, $sysRoles['auditor'], $course_id) ? 2 : 0);
    }
    
    // 查詢是否已有送審的選課單
    $course['courseReviewing'] = 0;
    if ($course['hasMajored'] == 0) {
        if (intval(dbGetOne('WM_review_flow','count(*)',sprintf("username='%s' and discren_id=%d and state='open'",$sysSession->username,$course_id)))){
            $course['courseReviewing'] = 1;
        }
    }
    
    
    $course['cpic'] = base64_encode($course_id);
    $course['spic'] = base64_encode($assignSch);
    
    //啟用否
    if ('' != $course['is_use']) {
	    $course['is_use'] = get_course_data($course['is_use']);
    } else {
        // 改回傳array('null')，以避免跟欄位數值為 a:0:{} 回傳空陣列混淆
        $course['is_use'] = array('null');
    }

    //學習目標
    if (''!=$course['goal']) {
        $course['goal'] = get_course_data($course['goal']);
    } else {
        unset($course['goal']);
    }

    
    //聽眾
    if (''!=$course['audience']) {
        $course['audience'] = get_course_data($course['audience']);
    } else {
        unset($course['audience']);
    }
    
    //參考資料
    if (''!=$course['ref_title']) {
        $course['ref_title'] = get_course_data($course['ref_title']);
        $course['ref_url'] = get_course_data($course['ref_url']);
    } else {
        unset($course['ref_title']);
        unset($course['ref_url']);
    }
    
    //一般生通過條件
    $course['formal'] = get_course_data($course['formal_pass']);

    //旁聽生通過條件
    $course['gallery'] = get_course_data($course['gallery_pass']);
    
    
    $pContent = dbGetOne($useDb.'`WM_term_path`', '`content`', '`course_id` ='.$course_id.' ORDER BY `update_time` desc ');
    if (false != strpos($pContent, 'item')) {
        $show_set = true;
    } else {
        $show_set = false;
    }
    $langSW = array(
        'Big5'          =>      0,
        'GB2312'        =>      1,
        'en'            =>      2,
        'EUC-JP'        =>      3,
        'user_define'   =>      4
    );
    // 取得課程節點
    if (!empty($pContent)) {
        $pContent = xpath_new_context(domxml_open_mem(preg_replace('/xmlns\s*=\s*"[^"]+"/', '', $pContent, 1)));
        // 解析 xml title
        $xrs = $pContent->xpath_eval('//manifest/organizations/organization/item');
        if (is_array($xrs->nodeset) && count($xrs->nodeset) >= 1) {
            foreach($xrs->nodeset as $content){
                // 隱藏節點不顯示
                if (strcmp($content->get_attribute('isvisible'),'false') == 0) continue;
                // 使用 firstChild xml 有換行或空白時會取到空白，改用 getElementByTagName
                $title = $content->myDOMNode->getElementsByTagName('title')->item(0);
                if ($title != null && $title->firstChild != null) {
                    $tmpCaption = co_lang_default(preg_split('/[\t]/', $title->nodeValue));
                    $pTitleAry[] = ($tmpCaption[$langSW[$sysSession->lang]])?$tmpCaption[$langSW[$sysSession->lang]]:$tmpCaption[0];
                } else {
                    // 如果沒有 title 顯示 identifier
                    $pTitleAry[] = $content->get_attribute('identifier');
                }
            }
        }
    }
    
    // 取得內容商學校名稱
    if ($assignSch != '' && $assignSch != $sysSession->school_id) {
        $assignSchName = dbGetOne('`CO_school`', '`banner_title1`', sprintf('`school_id`=%s',$assignSch));
    }
    
    $smarty->assign('show_set', $show_set);
    $smarty->assign('pageEmbed', $pageEmbed);
    $smarty->assign('courseData', $course);
    $smarty->assign('titleAry', $pTitleAry);

    // meta
    $tmpCaption = getCaption($course['caption']);
    $caption = $tmpCaption[$sysSession->lang];
    if ($caption === null) {
        $caption = '';
    }


    // 取得分享社群 html，$socialShare: common.php 已宣告
    $shareIcon = $rsSchool->getShareSocialHtml($socialShare, $baseUrl, $course['course_id'], $caption, $sysSession->lang, $assignSch, $profile['isPhoneDevice']);
    $smarty->assign('shareIcon', $shareIcon);
    
    // 學校ID，用於入口網校顯示內容商之課程
    $smarty->assign('assignSch', (($assignSch != '') ? $assignSch : $sysSession->school_id));
    $smarty->assign('assignSchName', (($assignSchName != '') ? $assignSchName : ''));
        
    // student_mooc的值1:false 0:true
    $smarty->assign('moocDisabled', isset($moocDisabled)?$moocDisabled:true);

    $smarty->assign('metaTitle', $caption);
    // $smarty->assign('metaDescription', strip_tags($course['content']));
    $smarty->assign('metaSitename', $sysSession->school_name);
    $smarty->assign('metaUrl', $_SERVER['SCRIPT_URI']);
    $smarty->assign('metaImage', $baseUrl. '/lib/app_show_course_picture.php?courseId=' . base64_encode($course_id).(($assignSch != '') ? '&sId='.base64_encode($assignSch) : ''));

    if ($pageEmbed) {
        $smarty->display('common/tiny_header.tpl');
        $smarty->display('learn/course_info.tpl');
        $smarty->display('common/tiny_footer.tpl');
    } else {
        $smarty->display('course_info.tpl');
    }
