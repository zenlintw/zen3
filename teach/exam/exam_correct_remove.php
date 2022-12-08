<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/04/10                                                            *
	 *		work for  : 列出某考生對某次測驗的所有答案卷                                      *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/teach/grade/grade_recal.php');

	//ACL begin
	if (QTI_which == 'exam') {
		$sysSession->cur_func = '1600200300';
	}
	else if (QTI_which == 'homework') {
		include_once(sysDocumentRoot . '/lib/group_assignment_lib.php');

		$sysSession->cur_func = '1700200300';
	}
	else if (QTI_which == 'questionnaire') {
		$sysSession->cur_func = '1800200300';
	}
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}
	//ACL end

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	if (!isset($_SERVER['argv'][0])) {	// 檢查 ticket 是否存在
	   wmSysLog($sysSession->cur_func, $course_id , $_SERVER['argv'][1] , 1, 'auto', $_SERVER['PHP_SELF'], 'Access Denied!');
	   die('Access denied.');
	}
	$ticket_head = sysTicketSeed . $course_id . $_SERVER['argv'][1] . $_SERVER['argv'][2] . $_SERVER['argv'][3];
	if (md5($ticket_head) != $_SERVER['argv'][0]) {	// 檢查 ticket
	   wmSysLog($sysSession->cur_func, $course_id , $_SERVER['argv'][1] , 2, 'auto', $_SERVER['PHP_SELF'], 'Fake Ticket!');
	   die('Fake ticket.');
	}
	$ticket = md5(sysTicketSeed . $_COOKIE['idx'] . $_SERVER['argv'][1] . $_SERVER['argv'][2] . $_SERVER['argv'][3]);
	
	chkSchoolId('WM_student_div');
	if (QTI_which == 'homework' && ereg('^[0-9]+$', $_SERVER['argv'][2]) && isAssignmentForGroup($_SERVER['argv'][1]))
	{
		$keep = $ADODB_FETCH_MODE;
		$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        $sqls = 'SELECT D.team_id, D.group_id, R.* ' .
				'FROM WM_student_div AS D ' .
				'INNER JOIN WM_qti_homework_result AS R ON D.username = R.examinee ' .
				'WHERE D.course_id =' . $sysSession->course_id .
				' AND D.group_id =' . $_SERVER['argv'][2] .
				' AND R.exam_id =' . $_SERVER['argv'][1];
	    $fields = $sysConn->GetRow($sqls);
        $ADODB_FETCH_MODE = $keep;
	    $isGroupingAssignment = $_SERVER['argv'][2];
	    $_SERVER['argv'][2] = $fields['examinee'];
	}
	// 抓grade_id by Small 2006/10/26
	$exam_grade_id = $sysConn->GetOne('select grade_id from WM_grade_list where property=' . $_SERVER['argv'][1]);
	dbDel('WM_qti_' . QTI_which . '_result', sprintf('exam_id=%d and examinee="%s" and time_id=%d',
													 $_SERVER['argv'][1],
													 $_SERVER['argv'][2],
													 $_SERVER['argv'][3])
		 );

    $xml_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/C/%09u/%s/',
		  					 $sysSession->school_id,
		  					 $sysSession->course_id,
		  					 QTI_which,
		  					 $_SERVER['argv'][1],
		  					 $_SERVER['argv'][2]);
    $file = $_SERVER['argv'][3].'.xml';	  	

    $full_path = $xml_path.$file;
    if (file_exists($full_path)) unlink($full_path);	 
		 
	if (QTI_which == 'exam') {
        dbDel('WM_qti_' . QTI_which . '_result_extra', sprintf('exam_id=%d and examinee="%s" and time_id=%d',$_SERVER['argv'][1],$_SERVER['argv'][2],$_SERVER['argv'][3]));
	}

	// 同步儲存到成績系統
	if (reCalculateQTIGrade($_SERVER['argv'][2], $_SERVER['argv'][1], QTI_which))
			reCalculateGrades($sysSession->course_id);
	
	// 抓這份測驗在刪除後，還剩下幾次紀錄 by Small 2006/10/26
	$exam_results = $sysConn->GetOne('select count(*) from WM_qti_' . QTI_which . '_result where exam_id=' . $_SERVER['argv'][1] . ' and examinee="' . $_SERVER['argv'][2] . '"');
	if ($exam_results == 0)	// 如果教師已將該名學生的該份測驗所有紀錄刪除，則刪除WM_grade_item中該學生該份測驗的資料
		dbDel('WM_grade_item','grade_id='.$exam_grade_id.' and username="'.$_SERVER['argv'][2].'"');
			
	//刪除夾檔
	$attach_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09u/%s/',
			  					 $sysSession->school_id,
			  					 $sysSession->course_id,
			  					 QTI_which,
			  					 $_SERVER['argv'][1],
			  					 $_SERVER['argv'][2]);

	if (is_dir($attach_path))
	{
    	if ($dh = opendir($attach_path))
    	{
        	while (($file = readdir($dh)) !== false)
        	{
            	if (substr($file, 0, 1) == '.') continue;
            	if (file_exists($attach_path . $file))	unlink($attach_path . $file);
        	}
    		closedir($dh);
    	}
	}

	rmdir($attach_path);

	wmSysLog($sysSession->cur_func, $course_id , $_SERVER['argv'][1] , 0, 'auto', $_SERVER['PHP_SELF'], sprintf('remove %s result! examinee="%s", num of times="%d".',
																												QTI_which, $_SERVER['argv'][2], $_SERVER['argv'][3]));
	echo <<< EOB
<script>
parent.c_main.rtop.location.reload(parent.c_main.rtop.location);
parent.c_main.left.document.forms[0].submit();
</script>

EOB;
?>
