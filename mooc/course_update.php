<?php
    /**
     * 課程停用
     */
        require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
        require_once(sysDocumentRoot . '/lib/acl_api.php');
        include_once(sysDocumentRoot . '/lib/lib_stud_rm.php');
        require_once(sysDocumentRoot . '/mooc/common/common.php');
        require_once(sysDocumentRoot . '/lang/mooc.php');
        
    // 未登入    
	if ($sysSession->username == 'guest') {
	    header("LOCATION: /mooc/login.php");
	    exit;
	}

	// 停用課程id
	$arr_courseid = $_POST['selectCourseId'];
	
	foreach ($arr_courseid as $key => $value) {
		$isTeacher = false;
	    $isTeacher = aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'], $value);
	    // 判斷是否為此門課程老師
	    if ($isTeacher) {
	    	dbSet('WM_term_course', sprintf("status=%d", 5), sprintf("course_id=%d ",  $value));
	    }
	}
	
	echo 1 ;
