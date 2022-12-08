<?php
    /**
     * 課程退選
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
        require_once(sysDocumentRoot . '/lib/acl_api.php');
        include_once(sysDocumentRoot . '/lib/lib_stud_rm.php');
        require_once(sysDocumentRoot . '/mooc/common/common.php');
        require_once(sysDocumentRoot . '/lang/mooc.php');
        
	// 退選課只允許有註冊的帳號
	if ($sysSession->username == 'guest') {
	    header("LOCATION: /mooc/login.php");
	    exit;
	}

	if (strlen($_POST['cancelCourseId']) != 8) {
	    die('illegeal Access');
	}

	$course_id = intval($_POST['cancelCourseId']);
    
    // 如果 mooc 未開啟(不包含mooc皮)，就只能旁聽生退選
    if (defined('sysEnableMooc') && (sysEnableMooc > 0)) {
        $delRole = $sysRoles['student'] | $sysRoles['auditor'];
    } else {
        $delRole = $sysRoles['auditor'];
    }
    

    // 有學校ID的話判斷有無該學校並跨校退選課程
    $sId = intval($_POST['sid']);
    if (strlen($_POST['sid']) == 5) {
        if ($sId != $sysSession->school_id) {
            $sHost = dbGetOne(sysDBname.'.`WM_school`', '`school_host`', sprintf('`school_id` = %d', $sId));
            if (!empty($sHost)) {
                $sysConn->Execute('use '.sysDBprefix.$sId);
                $sqls = sprintf('select count(*) from WM_term_major as M inner join WM_term_course as C on M.course_id=C.course_id and C.status != 9 where M.username="%s" and M.course_id=%d and M.role&%d' , $sysSession->username, $course_id, $delRole);
                if ($sysConn->GetOne($sqls) >= 1) {
                    $result = DelStudentAll($course_id,$sysSession->username, false, $sId);
                    $sysConn->Execute("DELETE FROM ".sysDBprefix.$sId.".`WM_term_major` WHERE role=0");
                }else{
                    die('illegeal Access');
                }
            } else {
                die('access denied.');
            }        
        }
    }

    // 刪除該學員在該課程的所有資料
    if ($sId == $sysSession->school_id || !isset($sId)) {
        if (aclCheckRole($sysSession->username, $delRole, $course_id)>=1)
        {
            if (aclCheckRole($sysSession->username, $sysRoles['teacher'], $course_id)>=1) {
                dbSet('WM_term_major', sprintf("role=%d", $sysRoles['teacher']), sprintf("username='%s' and course_id=%d and role&%d", $sysSession->username, $course_id, ($sysRoles['teacher']+$sysRoles['student'])|($sysRoles['teacher']+$sysRoles['auditor'])));
                $result2 = mysql_affected_rows();
            } else {
                $result = DelStudentAll($course_id,$sysSession->username);
            }
            dbDel('WM_term_major', 'role=0');
        } else {
                die('illegeal Access');
        }
    }
    
    if ($_POST['method'] == 'ajax') {
        // 如果是 ajax 過來
        if (isset($result) && $result !== '') {
            // 回傳: 0:刪除成功、1:刪除失敗、2:無此帳號、3.5:系統保留帳號、4:格式不正確
            echo $result;
        } else {
            // 回傳: 6:老師退選無異動(0)、7:老師退選異動筆數(1) ，註: $result 會回傳0-5，故加6與之區別
            echo $result2 + 6;
        }
    } else {
        echo '<script type="text/javascript">top.location.href="/mooc/message.php?type=16&cid='.$course_id.'&msg='. $result.'";</script>';
    }
