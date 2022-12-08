<?php
    /**
     * 課程編輯
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
    
	if (isset($_GET['id'])) {
		// 編輯課程id
		$courseid = $_GET['id'];
	
		$isTeacher = aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'], $courseid);
	
	    if ($isTeacher){
	    	$sysSession->course_id = $courseid;
	    	$sysSession->goto_label = 'SYS_02_02_003';
	    	$sysSession->restore();
	    	header("Location:/teach/index.php");
	    }
	} else {
		$sysSession->goto_label = 'SYS_06_01_003';
		$sysSession-> course_id = 0;
	    $sysSession->restore();
	    header("Location:/learn/index.php");
	}
    
