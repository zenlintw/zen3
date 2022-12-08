<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2004/09/08                                                            *
	 *		work for  : grade property modify                                                         *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *		identifier: $Id: grade_modify3.php,v 1.1 2010/02/24 02:40:27 saly Exp $
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/teach/grade/grade_recal.php');
	
	$sysSession->cur_func = '1400100200';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	if (ereg('^[0-9]+$', $_POST['grade_id'])){
		list($title) = dbGetStSr('WM_grade_list', 'title', "grade_id={$_POST['grade_id']}", ADODB_FETCH_NUM);
		$titles = unserialize($title);
		$titles[$sysSession->lang] = stripslashes($_POST['gradeCaption']);
		dbSet('WM_grade_list',
			  sprintf('title="%s", percent=%.2f', addslashes(serialize($titles)), $_POST['gradePercent'] ),
			  "grade_id={$_POST['grade_id']}"
			 );
    	if ($sysConn->ErrorNo() == 0)
	    {
            // 與三合一系統同步比例及公布日期 start
            if ($sysConn->Affected_Rows())
            {
                list($source, $property) = dbGetStSr('WM_grade_list', 'source, property', "grade_id={$_POST['grade_id']}", ADODB_FETCH_NUM);
            
                $grade_types = array(1 => 'homework', 2 => 'exam', 3 => 'questionnaire');
                dbSet('WM_qti_' . $grade_types[$source] . '_test',
                      sprintf("percent=%.2f", $_POST['gradePercent']),
                      "exam_id={$property}"
                     );
            }
            // 與三合一系統同步比例及公布日期 end
        	
        	reCalculateGrades($sysSession->course_id); //  更新總分平均排名
        	
            echo <<< EOB
<script>
ie = parent.c_main.users_length+parent.c_main.keepTop;
parent.c_main.no_rank = true;
parent.c_main.no_level = true;
for(var i=parent.c_main.keepTop; i<ie; i++){
	if (i>=ie-1) { parent.c_main.no_rank = false; parent.c_main.no_level = false; }
	parent.c_main.calculate(parent.c_main.th_idx, i);
}
</script>
EOB;
        }
	}
	else
		echo '<script>alert("Error!");</script>';
?>
