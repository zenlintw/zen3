<?php
	/**
	 * �����ҵ{
	 *
	 * @since   2003/10/03
	 * @author  ShenTing Lin
	 * @version $Id: course_delete.php,v 1.1 2010/02/24 02:38:19 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	
	$sysSession->cur_func = '700400100';
	$sysSession->restore();

	if (!aclVerifyPermission(700400100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	// �o�䪺�P�_�i��|�]�� PHP ���������Ӧ����ܰ�
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			header("Content-type: text/xml");
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			echo '<manifest></manifest>';
			exit;
		}

		// �R���ҵ{�A�ëD�u���R�����骺��ơA�ӬO�N�ҵ{�����A�]�� 9 (�R��)
		$csids = trim(getNodeValue($dom, 'course_id'));
		$course_array = explode(',', $csids);
		foreach ($course_array as $key => $val) {
			$course_array[$key] = sysDecode($val);
		}
		$csids = implode(',', $course_array);
		define('COURSES_LIST', "course_id in ({$csids})");

		dbSet('WM_term_course', '`content_id`=0, `status`=9', COURSES_LIST);
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0, 0, 'manager', $_SERVER['PHP_SELF'], '�����ҵ{ course_id = '.$csids);
		// *************************
		// �R�� WM_term_teacher & WM_term_group & WM_term_major

		dbDel('WM_term_teacher', COURSES_LIST);
		dbDel('WM_term_group', "child in ({$csids})");

		// ��� ���窺�s��
        $exam_ids = $sysConn->GetCol('select exam_id from WM_qti_exam_test where ' . COURSES_LIST);
		$str_exam_ids = implode(',', $exam_ids);
		// ��� �@�~���s��
        $homework_ids = $sysConn->GetCol('select exam_id from WM_qti_homework_test where ' . COURSES_LIST);
		$str_homework_ids = implode(',', $homework_ids);
		// ��� �ݨ����s��
        $qnaire_ids = $sysConn->GetCol('select exam_id from WM_qti_questionnaire_test where ' . COURSES_LIST);
		$str_qnaire_ids = implode(',', $qnaire_ids);
		// ��� �Q�ת����s��
		$board_ids = $sysConn->GetCol('select board_id from WM_bbs_boards where owner_id in (' . $csids . ')');
		$str_board_ids = implode(',', $board_ids);
        // ��� ���Z���s��
        $grade_ids = $sysConn->GetCol('select grade_id from WM_grade_list where ' . COURSES_LIST);
        $str_grade_ids = implode(',', $grade_ids);

		// �׽�
		$sqls = 'insert into WM_history_term_major select * from WM_term_major where ' . COURSES_LIST;
        $sysConn->Execute($sqls);

        dbDel('WM_term_major', COURSES_LIST);
        dbDel('WM_roll_call' , COURSES_LIST);

		// �@�~
		if (count($homework_ids) > 0){
			$sqls = 'insert into WM_history_qti_homework_result select * from WM_qti_homework_result where exam_id in (' . $str_homework_ids . ')';
            $sysConn->Execute($sqls);

            dbDel('WM_qti_homework_result', ' exam_id in (' . $str_homework_ids . ')');
		}

		// ����
		if (count($exam_ids) > 0){
			$sqls = 'insert into WM_history_qti_exam_result select * from WM_qti_exam_result where exam_id in (' . $str_exam_ids . ')';
            $sysConn->Execute($sqls);

            dbDel('WM_qti_exam_result', ' exam_id in (' . $str_exam_ids . ')');
		}

		// �ݨ�
		if (count($qnaire_ids) > 0){
			$sqls = 'insert into WM_history_qti_questionnaire_result select * from WM_qti_questionnaire_result where exam_id in (' . $str_qnaire_ids . ')';
            $sysConn->Execute($sqls);

            dbDel('WM_qti_questionnaire_result', ' exam_id in (' . $str_qnaire_ids . ')');
		}

		// �׽Ҧ��Z
		$sqls = 'insert into WM_history_grade_stat select * from WM_grade_stat where ' . COURSES_LIST;
       	$sysConn->Execute($sqls);

        dbDel('WM_grade_stat', COURSES_LIST);

		// �\Ū�O��
		$sqls = 'insert into WM_history_record_reading select * from WM_record_reading where ' . COURSES_LIST;
        $sysConn->Execute($sqls);

        dbDel('WM_record_reading', COURSES_LIST);

		$sqls = 'insert into WM_history_record_daily_personal select * from WM_record_daily_personal where ' . COURSES_LIST;
        $sysConn->Execute($sqls);

        dbDel('WM_record_daily_personal', COURSES_LIST);

		$sqls = 'insert into WM_history_scorm_tracking select * from WM_scorm_tracking where ' . COURSES_LIST;
        $sysConn->Execute($sqls);

        dbDel('WM_scorm_tracking', COURSES_LIST);

		// �p�դ��զW��
        dbDel('WM_student_div', COURSES_LIST);

        // �p�ղժ��W��
        dbDel('WM_student_group',COURSES_LIST);

		// �q�\�Q�ת��峹
		if (count($board_ids) > 0){
			dbDel('WM_bbs_order', 'board_id in (' . $str_board_ids . ')');
			dbDel('WM_bbs_readed', 'board_id in (' . $str_board_ids . ')');
		}

		// �ǭ����C�����Z
		if (count($grade_ids) > 0){
			dbDel('WM_grade_item', 'grade_id in (' . $str_grade_ids . ')');
		}

		// �p�զW��
       	dbDel('WM_student_separate', COURSES_LIST);

       	// �ҵ{��ƾ�
        $calendar_begin_type='course_begin';
        $calendar_end_type='course_end';
        $calendar_ids = $sysConn->GetCol("select idx from WM_calendar where (relative_type='{$calendar_begin_type}' or relative_type='{$calendar_end_type}') and relative_id in ({$csids}) limit 2");
        if (is_array($calendar_ids) && count($calendar_ids)) dbDel('WM_calendar', 'idx in (' . implode(',', $calendar_ids) . ')');

		// �ҵ{�\Ū�O��
		$sqls = 'insert into WM_history_record_daily_course select * from WM_record_daily_course where ' . COURSES_LIST;
       	$sysConn->Execute($sqls);

       	dbDel('WM_record_daily_course', COURSES_LIST);

		// �f�ָ��
		dbSet('WM_review_flow', 'state="close", result=9', 'discren_id in ('. $csids .') and state="open"');

        // APP �ҵ{�Ϥ��]�w
        dbDel('CO_course_picture', COURSES_LIST);

		// *************************

		$result = '<' . '?xml version="1.0" encoding="UTF-8" ?' . ">\n" .
				  '<manifest>' . $result . '</manifest>';

        header("Content-type: text/xml");
		if (!empty($result)) {
			echo str_replace('<manifest>', "<manifest><ticket>{$ticket}</ticket>", $result);
		} else {
			echo '<', '?xml version="1.0" encoding="UTF-8" ?', ">\n", '<manifest></manifest>';
		}
	}

?>
