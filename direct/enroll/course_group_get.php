<?php
	/**
	 * ���o�ҵ{�s��
	 *
	 * @since   2004/07/21
	 * @author  ShenTing Lin
	 * @version $Id: course_group_get.php,v 1.1 2010/02/24 02:38:57 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/direct/enroll/course_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '700300400';
	$sysSession->restore();
	if (!aclVerifyPermission(700300400, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	header("Content-type: text/xml");
	// �o�䪺�P�_�i��|�]�� PHP ���������Ӧ����ܰ�
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			echo '<manifest></manifest>';
			wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 1, 'director', $_SERVER['PHP_SELF'], 'domxml open fail!');
			exit;
		}

		// �ˬd Ticket
		/*
		$ticket = md5(sysTicketSeed . $sysSession->username . 'MySchool' . $sysSession->ticket . $sysSession->school_id);
		if (getNodeValue($dom, 'ticket') != $ticket) {
			header("Content-type: text/xml");
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n";
			echo '<manifest>Access Fail.</manifest>';
			exit;
		}
		*/

		// ���s�إ� Ticket
		$ticket = md5(sysTicketSeed . $sysSession->username . 'MySchool' . $sysSession->school_id . $_COOKIE['idx']);
		// ���o�s�X�᪺ group_id
		$enc    = getNodeValue($dom, 'group_id');
		// �ѽX
		$csid   = intval(sysDecode($enc));
		$group  = getCoursesList($csid, 'group');
		foreach ($group as $key => $val) {
			// �N course_id �s�X
			$group[$key][0] = sysEncode($key);
		}

		echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		echo '<manifest>';
		echo '<ticket>' . $ticket . '</ticket>';
		echo Group2XML($group);
		echo '</manifest>';
	}
?>
