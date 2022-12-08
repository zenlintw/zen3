<?php
    /**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2005/02/21                                                                       *
	*		work for  : �ӤH�ϡ��ڪ��ҵ{�����սҵ{���ҵ{�G�i��ť                                                                      *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       $Id: add_audit.php,v 1.2 2010/02/25 06:45:02 small Exp $                                                                                          *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lang/audit_course.php');

/**
 * ========================================================================================
 *                                     �D�{���}�l
 * ========================================================================================

�d�ߪ� XML

< ?xml version="1.0" encoding="UTF-8" ? >
<manifest>
	<ticket></ticket>
	<classes_id></classes_id>     <- �d�ߪ� ��b���Ӹ`�I
</manifest>

**/

	// �o�䪺�P�_�i��|�]�� PHP ���������Ӧ����ܰ�
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		header('Content-type: text/xml');
		echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";

		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			die('<manifest></manifest>');
		}

		// ticket
		$ticket = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Add_Audit' . $sysSession->username);

		/* �ץ� (begin) MIS#015279 by chiahua
		*  �쥻�{���g�k��get_ticket����intval()
		*  ���O�o�˷|�����D�A�]��md5()�X�Ӫ�ticket�|���^�Ʀr
		*  ��intval()�X�ӥu���Ʀr
		*  �ҥH�|����藍�Ū����εo��
		*  �]��get_ticket�ץ����������ǹL�Ӫ��ȡA���A��intval()�ഫ�F
		*/
		//$get_ticket = intval(getNodeValue($dom, 'ticket'));
        $get_ticket = getNodeValue($dom, 'ticket');
		/* �ץ� (end) */

        if ($ticket != $get_ticket){
        	echo '<manifest><state_msg>' . $MSG['illege_access'][$sysSession->lang] . '.</state_msg></manifest>';
		}
		// course_id
        $course_id = intval(getNodeValue($dom, 'course_id'));

		if (strlen($course_id) != 8){
        	echo '<manifest><state_msg>' . $MSG['illege_access'][$sysSession->lang] . '..</state_msg></manifest>';
		}

		// get course name
		list($cour_name, $a_limit) = dbGetStSr('WM_term_course','caption, a_limit','course_id=' . $course_id, ADODB_FETCH_NUM);
		$cour_lang = unserialize($cour_name);

		// �P�_�O�_�W�L��ť�ͤH�ƭ���
		list($a_cnt) = dbGetStSr('WM_term_major', 'count(*)', 'course_id='. $course_id . ' and role & ' . $sysRoles['auditor'], ADODB_FETCH_NUM);
		if ($a_limit && $a_cnt >= $a_limit) {
			$state_msg = $MSG['add_fail4'][$sysSession->lang];
			$state_msg = str_replace('%COURSE_NAME%',$cour_lang[$sysSession->lang],$state_msg);
			$state_code = 1;
		}
		else {
			// �P�_�O�_�������Ҫ��ǥ�
			list($role) = dbGetStSr('WM_term_major','role','course_id=' . $course_id . ' and username="' . $sysSession->username . '"', ADODB_FETCH_NUM);
			if (empty($role)){
				$RS = dbNew('WM_term_major', 'username,course_id,role,add_time', "'" . $sysSession->username . "'," . $course_id . ',' . $sysRoles['auditor'] . ',NOW()');

				if ($sysConn->ErrorNo() == 0){
					$state_msg = $MSG['add_success'][$sysSession->lang];
					$state_msg = str_replace('%COURSE_NAME%',$cour_lang[$sysSession->lang],$state_msg);
					$state_code = 0; // success
				}else{
					$state_msg = $MSG['add_fail2'][$sysSession->lang];
					$state_code = 1;  // fail
				}
			}else{
				if ($role & $sysRoles['student']) {	// �w�g�O������
					$state_msg = $MSG['add_fail'][$sysSession->lang];
					$state_msg = str_replace('%COURSE_NAME%',$cour_lang[$sysSession->lang],$state_msg);
					$state_code = 1;  // fail
				}
				else if ($role & $sysRoles['auditor']) {	// �w�g�O��ť��
					$state_msg = $MSG['add_fail3'][$sysSession->lang];
					$state_msg = str_replace('%COURSE_NAME%',$cour_lang[$sysSession->lang],$state_msg);
					$state_code = 1;
				}
				else if ($role & $sysRoles['teacher']) {	// �Юv�h�i�H�[�J��ť��
					$mask = $sysRoles['all'] ^ $sysRoles['student'];
					dbSet('WM_term_major', "role=role & $mask | {$sysRoles['auditor']},add_time=NOW()", "username='{$sysSession->username}' and course_id={$course_id}");
					if ($sysConn->ErrorNo() == 0) {
						$state_msg = $MSG['add_success'][$sysSession->lang];
						$state_msg = str_replace('%COURSE_NAME%',$cour_lang[$sysSession->lang],$state_msg);
						$state_code = 0; // success
					}
					else {
						$state_msg = $MSG['add_fail2'][$sysSession->lang];
						$state_code = 1;  // fail
					}
				}
			}
		}
		$result = '<manifest><state_code>' . $state_code . '</state_code><state_msg>' .
				  htmlspecialchars($state_msg) . '</state_msg></manifest>';

		if (!empty($result)) {
			echo str_replace('<manifest>', "<manifest><ticket>{$ticket}</ticket>", $result);
		} else {
			echo "<manifest><ticket>{$ticket}</ticket></manifest>";
		}
		
        // if ($group_id > 1000000) end
	}
?>
