<?php
	/**
    * 切換課程與環境
    *
    * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
    *
    * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
    * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
    * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
    *
    * @package     WM3
    * @author      Edi Chen <edi@sun.net.tw>
    * @copyright   2000-2005 SunNet Tech. INC.
    * @version     CVS: $Id: goto_env.php,v 1.1 2010/02/24 02:40:26 saly Exp $
    * @link        http://demo.learn.com.tw/1000110138/index.html
    * @since       2005-12-30
    */
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,2, '', $_SERVER['PHP_SELF'], 'Data Error!');
			die('DataError');
		}

		$csid = intval(getNodeValue($dom, 'course_id'));
		$env  = preg_replace('/\W/', '', getNodeValue($dom, 'env'));
		$func = preg_replace('/\W/', '', getNodeValue($dom, 'func'));

		if ($csid < 10000000 || $csid > 99999999) die('false');
		if (!in_array($env, array('academic', 'direct', 'teach', 'learn'))) $env = 'learn';
		if ($func && $func != '')
		{
			$sysSession->goto_label = $func;
			$sysSession->restore();
		}

		// 檢查並切換課程資料 (Begin)
		$error_msg = '';
		if (in_array($env, array('learn', 'teach')) && ($csid > 10000000)) {
		    $RS = dbGetStSr('WM_term_course', '`caption`,`st_begin`,`st_end`, `status`', "`course_id`={$csid} AND `kind`='course'",ADODB_FETCH_ASSOC);
		    if (!$RS) die('CourseDelete1');
		    if (intval($RS['status']) >= 9) die('CourseDelete');
		    $isTeacher = aclCheckRole($sysSession->username, ($sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher']), $csid);
		
		    if (!$isTeacher)
		    {
		        $today = date('Y-m-d');
		        if (aclCheckRole($sysSession->username, $sysRoles['student'], $csid))
		        {
		            if ((( $RS['status']   == 1  || $RS['status'] == 3) ||
		            (($RS['status']   == 2  || $RS['status'] == 4) &&
		            ($RS['st_begin'] == '' || $RS['st_begin'] <= $today) &&
		            ($RS['st_end']   == '' || $RS['st_end']   >= $today)
		            )
		            )
		            )
		                $csid = $csid;
		            else
		            {
		                $csid = 10000000;
		                $error_msg = $MSG['tabs_deny_title'][$sysSession->lang];
		            }
		        }
		        elseif (aclCheckRole($sysSession->username, $sysRoles['auditor'], $csid))
		        {
		            if ((( $RS['status']   == 1) ||
		            (($RS['status']   == 2) &&
		            ($RS['st_begin'] == '' || $RS['st_begin'] <= $today) &&
		            ($RS['st_end']   == '' || $RS['st_end']   >= $today)
		            )
		            )
		            )
		                $csid = $csid;
		            else
		            {
		                $csid = 10000000;
		                $error_msg = $MSG['tabs_deny_title'][$sysSession->lang];
		            }
		        }
		        else
		        {
		            $csid = 10000000;
		            $error_msg = $MSG['msg_student_role'][$sysSession->lang];
		        }
		    }
		
		    if (!empty($error_msg)) die($error_msg);
		    
		    $lang   = getCaption($RS['caption']);
		    $csname = addslashes($lang[$sysSession->lang]);
		
		    // 設定進入的課程編號
		    dbSet('WM_session', "`course_id`={$csid}, `course_name`='{$csname}'", "`idx`='{$_COOKIE['idx']}'");
		
		    if ($csid != $sysSession->course_id) {
		        // 增加登入次數
		        dbSet('WM_term_major', '`login_times`=`login_times`+1, `last_login`=NOW()', "`username`='{$sysSession->username}' and `course_id`={$csid}");
		        dbSet('WM_term_course', '`login_times`=`login_times`+1', "`course_id`={$csid}");
		
		        // 記錄到 log 中(避免次數不一)
		        if ($env == 'teach') {
		            wmSysLog('2500200200', $csid, 0, '0', 'teacher', '', 'Goto office course_id=' . $csid);
		        } else {
		            wmSysLog('2500100200', $csid, 0, '0', 'classroom', '', 'Goto course course_id=' . $csid);
		        }
		    }
		    // 修改 Session
		    $sysSession->course_id = $csid;
		    $sysSession->course_name = $csname;
		    $sysSession->restore();
		    echo 'true';
		    exit;
		}
		// 檢查並切換課程資料 (End)
	}
	echo 'false';
?>