<?php
	/**
    * �����ҵ{�P����
    *
    * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
    *
    * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
    * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
    * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
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

		// �ˬd�ä����ҵ{��� (Begin)
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
		
		    // �]�w�i�J���ҵ{�s��
		    dbSet('WM_session', "`course_id`={$csid}, `course_name`='{$csname}'", "`idx`='{$_COOKIE['idx']}'");
		
		    if ($csid != $sysSession->course_id) {
		        // �W�[�n�J����
		        dbSet('WM_term_major', '`login_times`=`login_times`+1, `last_login`=NOW()', "`username`='{$sysSession->username}' and `course_id`={$csid}");
		        dbSet('WM_term_course', '`login_times`=`login_times`+1', "`course_id`={$csid}");
		
		        // �O���� log ��(�קK���Ƥ��@)
		        if ($env == 'teach') {
		            wmSysLog('2500200200', $csid, 0, '0', 'teacher', '', 'Goto office course_id=' . $csid);
		        } else {
		            wmSysLog('2500100200', $csid, 0, '0', 'classroom', '', 'Goto course course_id=' . $csid);
		        }
		    }
		    // �ק� Session
		    $sysSession->course_id = $csid;
		    $sysSession->course_name = $csname;
		    $sysSession->restore();
		    echo 'true';
		    exit;
		}
		// �ˬd�ä����ҵ{��� (End)
	}
	echo 'false';
?>