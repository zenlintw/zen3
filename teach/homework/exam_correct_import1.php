<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/04/11                                                            *
	 *		work for  : 取得某考生對某次測驗的答案卷                                          *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/lang/files_manager.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/quota.php');
	require_once(sysDocumentRoot . '/lib/lib_adjust_char.php');
	require_once(sysDocumentRoot . '/lib/archive_api.php');


	//ACL begin
	if (QTI_which == 'exam') {
		$sysSession->cur_func='1600300100';
	}
	else if (QTI_which == 'homework') {
		$sysSession->cur_func='1700300100';
	}
	else if (QTI_which == 'questionnaire') {
		$sysSession->cur_func='1800300100';
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
	
	if (!isset($_POST['ticket'])) {	// 檢查 ticket 是否存在
	   wmSysLog($sysSession->cur_func, $course_id , $_POST['exam_id'] , 1, 'auto', $_SERVER['PHP_SELF'], 'Access Denied!');
	   die('Access denied.');
	}
	$ticket_head = sysTicketSeed . $course_id . $_POST['exam_id'];
	if (md5($ticket_head) != $_POST['ticket']) {	// 檢查 ticket
	   wmSysLog($sysSession->cur_func, $course_id , $_POST['exam_id'] , 2, 'auto', $_SERVER['PHP_SELF'], 'Fake Ticket!');
	   die('Fake ticket.');
	}
	
	
	if (isset($_POST['op']) && $_POST['op'] == 'uz') {
		
		$arr_type = array('.tar.gz','.tgz','.tar.bz2','.tbz','.tar.Z','.zip','.rar');
		
		$word      = stripslashes($_FILES['uploadz']['name']);
        $filename  = (($w = un_adjust_char($word)) === FALSE) ? $word : $w;
        $ext       = strrchr($_FILES['uploadz']['name'], '.');

        if (pathinfo($filename, PATHINFO_FILENAME ) != 'hw'.$_POST['exam_id']) {
            die('<script type="text/javascript">alert("'.$MSG['msg_error_filename'][$sysSession->lang].'");parent.left.correct_import("'.$_POST['ticket'].'","'.$_POST['exam_id'].'");</script>');	
        }
        
	    if (!in_array($ext,$arr_type)) {
            die('<script type="text/javascript">alert("'.$MSG['msg_error_type'][$sysSession->lang].'");parent.left.correct_import("'.$_POST['ticket'].'","'.$_POST['exam_id'].'");</script>');	
        }
		

		$upload_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09u/hw%09u/',
		  					 $sysSession->school_id,
		  					 $sysSession->course_id,
		  					 QTI_which,
		  					 $_POST['exam_id'],
		  					 $_POST['exam_id']);
		  					 
		$exam_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09u/',
		  					 $sysSession->school_id,
		  					 $sysSession->course_id,
		  					 QTI_which,
		  					 $_POST['exam_id']);  					 
		  					 
		if (!file_exists($upload_path)) {		  					 
            mkdir($upload_path, 0755);
		}
		$arc = new Archive();
		
		$rtn = @$arc->extract_it($_FILES['uploadz']['tmp_name'], $upload_path, $ext);

        $resultMsg =  '['.$word.']' . ($rtn ? $MSG['failure'][$sysSession->lang] : $MSG['batch_import_success'][$sysSession->lang]);
        
        @unlink($_FILES['uploadz']['tmp_name']);
        
        $dir = opendir($upload_path);
	    while (($file = readdir($dir)) !== false)
	    {
		    if ($file!='..' && $file!='.') {

		    	$fi = explode('_',$file);

		    	$mv_dir = $exam_path.$fi[0].'/ref/';
			    if (!file_exists($mv_dir)) {		  					 
		            mkdir($mv_dir, 0755);
				}
				
				$mv_dir = $mv_dir.'000000001';
				
		    	exec("/bin/rm -rf '{$mv_dir}' && cd {$upload_path} &&  /bin/mv '{$file}' '{$mv_dir}'");

		    }
		}
	    closedir($dir);
	    
	    exec("/bin/rm -rf '{$upload_path}'");
	    
	    if (!$rtn) {
	    	$where = sprintf('exam_id=%d and time_id=%d',$_POST['exam_id'], 1);
	    	dbSet('WM_qti_' . QTI_which . '_result', 'status="revised"', $where);
	    }

		die('<script type="text/javascript">alert("'.$resultMsg.'");parent.rtop.location="about:blank";parent.left.location.reload();</script>');
		
	}
	
?>
