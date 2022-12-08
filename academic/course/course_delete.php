<?php
	/**
	 * 移除課程
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

	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			header("Content-type: text/xml");
			echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
			echo '<manifest></manifest>';
			exit;
		}

		// 刪除課程，並非真的刪除實體的資料，而是將課程的狀態設為 9 (刪除)
		$csids = trim(getNodeValue($dom, 'course_id'));
		$course_array = explode(',', $csids);
		foreach ($course_array as $key => $val) {
			$course_array[$key] = sysDecode($val);
		}
		$csids = implode(',', $course_array);
		define('COURSES_LIST', "course_id in ({$csids})");

		dbSet('WM_term_course', '`content_id`=0, `status`=9', COURSES_LIST);
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0, 0, 'manager', $_SERVER['PHP_SELF'], '移除課程 course_id = '.$csids);
		// *************************
		// 刪除 WM_term_teacher & WM_term_group & WM_term_major

		dbDel('WM_term_teacher', COURSES_LIST);
		dbDel('WM_term_group', "child in ({$csids})");

		// 抓取 測驗的編號
        $exam_ids = $sysConn->GetCol('select exam_id from WM_qti_exam_test where ' . COURSES_LIST);
		$str_exam_ids = implode(',', $exam_ids);
		// 抓取 作業的編號
        $homework_ids = $sysConn->GetCol('select exam_id from WM_qti_homework_test where ' . COURSES_LIST);
		$str_homework_ids = implode(',', $homework_ids);
		// 抓取 問卷的編號
        $qnaire_ids = $sysConn->GetCol('select exam_id from WM_qti_questionnaire_test where ' . COURSES_LIST);
		$str_qnaire_ids = implode(',', $qnaire_ids);
		// 抓取 討論版的編號
		$board_ids = $sysConn->GetCol('select board_id from WM_bbs_boards where owner_id in (' . $csids . ')');
		$str_board_ids = implode(',', $board_ids);
        // 抓取 成績的編號
        $grade_ids = $sysConn->GetCol('select grade_id from WM_grade_list where ' . COURSES_LIST);
        $str_grade_ids = implode(',', $grade_ids);

		// 修課
		$sqls = 'insert into WM_history_term_major select * from WM_term_major where ' . COURSES_LIST;
        $sysConn->Execute($sqls);

        dbDel('WM_term_major', COURSES_LIST);
        dbDel('WM_roll_call' , COURSES_LIST);

		// 作業
		if (count($homework_ids) > 0){
			$sqls = 'insert into WM_history_qti_homework_result select * from WM_qti_homework_result where exam_id in (' . $str_homework_ids . ')';
            $sysConn->Execute($sqls);

            dbDel('WM_qti_homework_result', ' exam_id in (' . $str_homework_ids . ')');
		}

		// 測驗
		if (count($exam_ids) > 0){
			$sqls = 'insert into WM_history_qti_exam_result select * from WM_qti_exam_result where exam_id in (' . $str_exam_ids . ')';
            $sysConn->Execute($sqls);

            dbDel('WM_qti_exam_result', ' exam_id in (' . $str_exam_ids . ')');
		}

		// 問卷
		if (count($qnaire_ids) > 0){
			$sqls = 'insert into WM_history_qti_questionnaire_result select * from WM_qti_questionnaire_result where exam_id in (' . $str_qnaire_ids . ')';
            $sysConn->Execute($sqls);

            dbDel('WM_qti_questionnaire_result', ' exam_id in (' . $str_qnaire_ids . ')');
		}

		// 修課成績
		$sqls = 'insert into WM_history_grade_stat select * from WM_grade_stat where ' . COURSES_LIST;
       	$sysConn->Execute($sqls);

        dbDel('WM_grade_stat', COURSES_LIST);

		// 閱讀記錄
		$sqls = 'insert into WM_history_record_reading select * from WM_record_reading where ' . COURSES_LIST;
        $sysConn->Execute($sqls);

        dbDel('WM_record_reading', COURSES_LIST);

		$sqls = 'insert into WM_history_record_daily_personal select * from WM_record_daily_personal where ' . COURSES_LIST;
        $sysConn->Execute($sqls);

        dbDel('WM_record_daily_personal', COURSES_LIST);

		$sqls = 'insert into WM_history_scorm_tracking select * from WM_scorm_tracking where ' . COURSES_LIST;
        $sysConn->Execute($sqls);

        dbDel('WM_scorm_tracking', COURSES_LIST);

		// 小組分組名單
        dbDel('WM_student_div', COURSES_LIST);

        // 小組組長名單
        dbDel('WM_student_group',COURSES_LIST);

		// 訂閱討論版文章
		if (count($board_ids) > 0){
			dbDel('WM_bbs_order', 'board_id in (' . $str_board_ids . ')');
			dbDel('WM_bbs_readed', 'board_id in (' . $str_board_ids . ')');
		}

		// 學員的每項成績
		if (count($grade_ids) > 0){
			dbDel('WM_grade_item', 'grade_id in (' . $str_grade_ids . ')');
		}

		// 小組名稱
       	dbDel('WM_student_separate', COURSES_LIST);

       	// 課程行事曆
        $calendar_begin_type='course_begin';
        $calendar_end_type='course_end';
        $calendar_ids = $sysConn->GetCol("select idx from WM_calendar where (relative_type='{$calendar_begin_type}' or relative_type='{$calendar_end_type}') and relative_id in ({$csids}) limit 2");
        if (is_array($calendar_ids) && count($calendar_ids)) dbDel('WM_calendar', 'idx in (' . implode(',', $calendar_ids) . ')');

		// 課程閱讀記錄
		$sqls = 'insert into WM_history_record_daily_course select * from WM_record_daily_course where ' . COURSES_LIST;
       	$sysConn->Execute($sqls);

       	dbDel('WM_record_daily_course', COURSES_LIST);

		// 審核資料
		dbSet('WM_review_flow', 'state="close", result=9', 'discren_id in ('. $csids .') and state="open"');

        // APP 課程圖片設定
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
