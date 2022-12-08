<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2004/09/08                                                            *
	 *		work for  : grade property modify                                                         *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *		identifier: $Id: grade_modify2.php,v 1.1 2010/02/24 02:40:27 saly Exp $
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/grade.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/teach/grade/grade_recal.php');	// 重新計算成績
		
	$sysSession->cur_func = '1400100200';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	if (ereg('^[0-9]+$', $_POST['grade_id'])){
		$change = false;
		foreach(array('Big5', 'GB2312', 'en', 'EUC-JP', 'user_define') as $lang)
		{
			$_POST['title'][$lang] = stripslashes($_POST['title'][$lang]);
		}
		
		if (isSet($_POST['rdoPublish']) && $_POST['rdoPublish'] == 'no') 
		{
			$publish_begin = $publish_end = '0000-00-00 00:00:00';
		}
		else
		{
			$publish_begin = (isSet($_POST['chk_begin']) && isSet($_POST['publish_begin'])) ? $_POST['publish_begin'] . ':00' : '1970-01-01 00:00:00';
			$publish_end   = (isSet($_POST['chk_end'])   && isSet($_POST['publish_end']  )) ? $_POST['publish_end']   . ':00' : '9999-12-31 00:00:00';
		}
        
		dbSet('WM_grade_list',
			  sprintf('title="%s", percent=%.2f, publish_begin="%s", publish_end="%s"',
			  		  addslashes(serialize($_POST['title'])),
			  		  $_POST['percent'],
			  		  $publish_begin,
			  		  $publish_end
			  		 ),			  
			 " grade_id={$_POST['grade_id']}"
			 );
			 
		if ($sysConn->ErrorNo())
		{
			$result_msg = addslashes($MSG['modify_grade_error'][$sysSession->lang] . $sysConn->ErrorMsg());
			wmSysLog($sysSession->cur_func, $sysSession->course_id , $_POST['grade_id'] , 1, 'auto', $_SERVER['PHP_SELF'], $result_msg);
		}
		else
		{
			if ($sysConn->Affected_Rows())
			{
                // 與三合一系統同步比例及公布日期 start
                $grade_types = array(1 => 'homework', 2 => 'exam', 3 => 'questionnaire', 3 => 'peer');

                list($source, $property)       = dbGetStSr('WM_grade_list', 'source, property', "grade_id={$_POST['grade_id']}", ADODB_FETCH_NUM);
                list($begin_time, $close_time,$announce_type) = dbGetStSr('WM_qti_' . $grade_types[$source] . '_test',
                                                           'begin_time, close_time, announce_type',
                                                           "exam_id={$property}", ADODB_FETCH_NUM);

                $t = sprintf('percent=%.2f,announce_type="', $_POST['percent']);
                switch($publish_begin)
                {
					case '0000-00-00 00:00:00':
						$t .= 'never"';
						dbSet('WM_qti_' . $grade_types[$source] . '_test', $t, "exam_id={$property}");
						break;
                    case '1970-01-01 00:00:00':
                    case $begin_time:
						$t .= 'now"';
                        break;
                    case $close_time:
						$t .= 'close_time"';
                        break;
                    default:
						$t .= 'user_define", announce_time="' . $publish_begin . '"';
                        break;
                }
				/*
				if($announce_type!='never')
					dbSet('WM_qti_' . $grade_types[$source] . '_test', $t, "exam_id={$property}");
				*/
                // 與三合一系統同步比例及公布日期 end
                $change = true;

				//$result_msg = $MSG['modify_complete'][$sysSession->lang];
			}
			else
				$result_msg = $MSG['no_modify'][$sysSession->lang];
			
		}
		
		
                if (is_array($_POST['fields']) && count($_POST['fields']) && (list($isThisCourseGrade) = dbGetStSr('WM_grade_list', 'count(*)', "course_id={$sysSession->course_id} and grade_id={$_POST['grade_id']}", ADODB_FETCH_NUM))) {

                    $students = $sysConn->GetCol('select username from WM_term_major where course_id=' . $sysSession->course_id . ' and role & ' . $sysRoles['student']);
                    foreach ($_POST['fields'] as $username => $grade) {
                        if (!preg_match('/^-?\d{1,5}(\.\d+)?$/', $grade[0]))
                            $grade[0] = '';
                        if (in_array($username, $students)) {
                            dbSet('WM_grade_item', "score='{$grade[0]}',comment='{$grade[1]}'", "grade_id={$_POST['grade_id']} and username='$username'");
                            if ($sysConn->ErrorNo()) {
                                $errMsg = $sysConn->ErrorMsg();
                                wmSysLog($sysSession->cur_func, $sysSession->course_id, $_POST['grade_id'], 1, 'auto', $_SERVER['PHP_SELF'], $errMsg);
                                die($errMsg);
                            } else {
                                // MIS#20398 by Small 2011/3/7
                                // elseif ($sysConn->Affected_Rows() == 0 && $grade[0] != '')
                                if ($sysConn->Affected_Rows()) {
                                    $change = true;
                                } else if ($sysConn->Affected_Rows() == 0 && ($grade[0] != '' || $grade[1] != '')) {
                                    $grade[0] = ($grade[0] == '') ? 0 : $grade[0];
                                    dbNew('WM_grade_item', 'grade_id,username,score,comment', "{$_POST['grade_id']}, '$username', {$grade[0]}, '{$grade[1]}'");
                                    if ($sysConn->Affected_Rows())
                                        $change = true;
                                }
                            }
                        }
                    }

                    unset($students);
                }
                if ($change) {
                    $result_msg = $MSG['modify_complete'][$sysSession->lang];
                    wmSysLog($sysSession->cur_func, $sysSession->course_id, $_POST['grade_id'], 0, 'auto', $_SERVER['PHP_SELF'], $result_msg);
                }
	}
	reCalculateGrades($sysSession->course_id);
	
	// header('Content-type: text/html; charset=UTF-8');
	echo <<< EOB
	<script>
		alert('{$result_msg}');
		location.replace('grade_maintain.php');
	</script>
EOB;
?>
