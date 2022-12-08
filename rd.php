<?php
    /**
     * �ҵ{���s��V�{��
     *
     * PHP 4.3.9+, MySQL 4.0.10+, Apache 1.3.33+
     *
     * LICENSE: ���{����l�X�ɮסA�����p��ުѥ��������q�Ҧ��C�D�g���p�ѭ����v
     * �h�Y�T��ŧ�B�y��B�����B���}�����Υ������e�C�Y���o���p�ѭ����v�ѡA��o��
     * �ӮѤ��ҭ���ϥνd��ϥΤ��A�_�h���H�I�v�רs�C
     *
     * @package     WM3
     * @author      Wiseguy Liang <wiseguy@mail.wiseguy.idv.tw>
     * @copyright   2000-2006 SunNet Tech. INC.
     * @version     CVS: $Id: rd.php,v 1.1 2010/02/24 02:38:55 saly Exp $
     * @link        http://demo.learn.com.tw/1000110138/index.html
     * @since       2006-07-20
     */

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/login/login.inc');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$argv = explode('/', $_SERVER['REQUEST_URI']);
	
	if ($argv[1] == 'rd.php') die('access denied.');
	$course_id = intval($argv[1]);
    if ((null != $argv[2])&&(intval($argv[2]) != $sysSession->school_id)) {
        $sId = intval($argv[2]);
        $sHost = dbGetOne(sysDBname.'.`WM_school`', '`school_host`', sprintf('`school_id` = %d', $sId));
        if (!empty($sHost)) {
            header('Location: http://'.$sHost.'/'.$course_id);
            die();
        } else {
            die('access denied.');
        }        
    }

	if ($sysSession->username == 'guest')
	{
	//3.1 �إ߭ӤHini
		setUserIni('guest');
	//3.2 ������cookie��idx�ª�session���
		removeExpiredSessionIdx($_COOKIE['idx']);
	//3.3 �������e�P�@��ϥΪ̪�ftp�{�ҳ]�w���
		removeExpiredFtpAuth();
	//3.4 ���]�s��idx���
	    $userinfo = array('username'		=> 'guest',
						  'password'		=> '',
						  'enable' =>		'Y',
						  'first_name'		=> 'Guest',
						  'last_name'		=> '',
						  'gender'			=> 'F',
						  'birthday'		=> '',
						  'personal_id'		=> '',
						  'email'			=> 'guest@somewhere.com',
						  'homepage'		=> '',
						  'home_tel'		=> '',
						  'home_fax'		=> '',
						  'home_address'	=> '',
						  'office_tel'		=> '',
						  'office_fax'		=> '',
						  'office_address'	=> '',
						  'cell_phone'		=> '',
						  'company'			=> '',
						  'department'		=> '',
						  'title'			=> '',
						  'language'		=> $sysConn->GetOne('select language from ' . sysDBname . '.WM_school where school_host="' . $_SERVER['HTTP_HOST'] . '"'),
						  'theme'			=> 'default',
						  'msg_reserved'	=> 0,
						  'hid'				=> 262075
						 );
        
        chkSchoolId('WM_term_course');
		$idx = $sysSession->init($userinfo);
		$_COOKIE['idx'] = $idx;
		$sysSession->restore();
		
		// �����k�A�p�G�o�����٤��\��ť�A�h�� guest �]����ť��
		if ($sysConn->GetOne('select count(*) from WM_term_course where course_id=' . $course_id . ' and (status=1 or (status=2 and (isnull(st_begin) or st_begin<=CURDATE()) and (isnull(st_end) or st_end>=CURDATE())))'))
		{
		    $fields = array('username'	=> 'guest',
		                    'course_id'	=> $course_id,
		                    'role'		=> $sysRoles['auditor'],
		                    'add_time'	=> date('Y-m-d H:i:s'),
						   );
			$sysConn->AutoExecute('WM_term_major', $fields, 'INSERT');
		}
	}

	//if (aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'] | $sysRoles['student'], $course_id)){
	    $GLOBALS['HTTP_RAW_POST_DATA'] = '<manifest><course_id>' . $course_id . '</course_id></manifest>';
    	ob_start();
    	require_once(sysDocumentRoot . '/learn/goto_course.php');
    	$output = ob_get_contents();
    	ob_end_clean();
	//}
	
	if (isset($argv[3])&&($argv[3]=='teach')) {
	    if (aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'], $course_id)) {
	        header('Location: /teach/');
	        exit;
	    }
	}
	
	header('Location: /learn/');
?>
