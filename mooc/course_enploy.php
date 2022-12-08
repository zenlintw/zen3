<?php
/**
 * 課程報名
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
 * @since       2014-03-06
 * 
 */

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lang/mycourse.php');
require_once(sysDocumentRoot . '/lang/cour_introduce.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/course.php');

$argv = explode('/', $_SERVER['REQUEST_URI']);
if ($argv[2] == 'course_info.php')
    die('access denied.');
$course_id = intval($argv[2]);

// 選課只允許有註冊的帳號
if ($sysSession->username == 'guest') {
    header("LOCATION: /mooc/login.php");
    exit;
}

// 有學校ID的話判斷有無該學校並轉址到該校
if (null != $argv[3]) {
    $sId   = intval($argv[3]);
    $sHost = dbGetOne(sysDBname . '.`WM_school`', '`school_host`', sprintf('`school_id` = %d', $sId));
    if (!empty($sHost)) {
        header('Location: http://' . $sHost . '/enploy/' . $course_id);
        die();
    } else {
        die('access denied.');
    }
}

// 從未選修過此門課
if (!aclCheckRole($sysSession->username, $sysRoles['student'] | $sysRoles['auditor'], $course_id)) {
    if ($sysConn->GetOne('select count(*) from WM_term_course where course_id=' . $course_id . ' and (status=1 or status=3 or ((status=2 or status=4) and (isnull(en_begin) or en_begin<=CURDATE()) and (isnull(en_end) or en_end>=CURDATE())))')) {
        if (aclCheckRole($sysSession->username, $sysRoles['teacher'], $course_id) >= 1) {
            dbSet('WM_term_major', sprintf("role=role+%d", $sysRoles['student']), sprintf("username='%s' and course_id=%d and role=%d", $sysSession->username, $course_id, $sysRoles['teacher']));
        } else {
            
            //是否需要審核
            $rid = intval(dbGetOne('WM_review_sysidx','flow_serial',sprintf('discren_id=%d',$course_id)));
            if ($rid <= 1) {    //不需審核
                $fields = array(
                    'username' => $sysSession->username,
                    'course_id' => $course_id,
                    'role' => $sysRoles['student'],
                    'add_time' => date('Y-m-d H:i:s')
                );
                $sysConn->AutoExecute('WM_term_major', $fields, 'INSERT');
            }else{  //寫入送審資料表
                $reviewContent = dbGetOne('WM_review_syscont','content',sprintf('flow_serial=%d',$rid));
                $xmlContent = '<?xml version="1.0" encoding="UTF-8" ?><wm_flow>';
                $xmlContent .= sprintf('<starter account="%s" email="%s" /><content><kind>course</kind><account user="%s" email="%s" /><description></description><discren_id>%d</discren_id></content>',$sysSession->usrname,$sysSession->email,$sysSession->usrname,$sysSession->email,$course_id);
                $xmlContent .= $reviewContent;
                $xmlContent .= '</wm_flow>';
                dbNew('WM_review_flow','flow_serial,username,create_time,kind,discren_id,state,content',sprintf("%d,'%s',NOW(),'course',%d,'open','%s'",$rid,$sysSession->username,$course_id,mysql_escape_string($xmlContent)));

            }
        }
    }
} elseif (aclCheckRole($sysSession->username, $sysRoles['auditor'], $course_id)) {
    //reuse 旁聽生的身份做為曾選修過的學生的身份
    dbSet('WM_term_major', sprintf("role=%d", $sysRoles['student']), sprintf("username='%s' and course_id=%d and role=%d", $sysSession->username, $course_id, $sysRoles['auditor']));
}

header("LOCATION: /info/".$course_id);