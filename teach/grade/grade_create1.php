<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/07/31                                                            *
	 *		work for  : grade manage                                                          *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/grade.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/teach/grade/grade_recal.php');	// 重新計算成績
		
	$sysSession->cur_func = '1400100100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$source_types = array('homework', 'exam', 'questionnaire');

    chkSchoolId('WM_grade_list');
	if ( ereg('^[123]$', $_POST['source']) ){			// 從三合一匯入
		$source_kind = $source_types[$_POST['source']-1];
		$source_id = intval($_POST['import_' . $source_kind]);

		$sqls = 'insert into WM_grade_list (course_id,title,source,property,percent,publish_begin,publish_end) ' .
				"select course_id,title,{$_POST['source']},exam_id,percent," .
				"if (announce_type='user_define', announce_time, if(announce_type='never', '0000-00-00 00:00:00', if(announce_type='close_time', close_time, begin_time)))," .
				"if (announce_type='never', '0000-00-00 00:00:00', NULL) " .
				" from WM_qti_{$source_kind}_test where exam_id=$source_id";
		$sysConn->Execute($sqls);
		if ($sysConn->ErrorNo())	{
		    $errMsg = $sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg();
		    wmSysLog($sysSession->cur_func, $sysSession->course_id , $source_id , 1, 'auto', $_SERVER['PHP_SELF'], $errMsg);
			die($errMsg);
		}
		else
			$new_grade_id = $sysConn->Insert_ID();
		
		wmSysLog($sysSession->cur_func, $sysSession->course_id , $source_id , 0, 'auto', $_SERVER['PHP_SELF'], 'create new grade list grade id = ' . $new_grade_id);
		
		if ($source_kind == 'exam')
		{
			$sqls = 'insert IGNORE into WM_grade_item (grade_id,username,score,comment) ';
			$what_kind_calculate = $sysConn->GetOne("select count_type from WM_qti_{$source_kind}_test where exam_id=$source_id");
			switch($what_kind_calculate)
			{
				case 'max':
				case 'min':
				case 'average':
					if ($what_kind_calculate == 'average') $what_kind_calculate = 'avg';
					$sqls .= "select $new_grade_id,R.examinee,{$what_kind_calculate}(R.score),R.comment ";
					break;
				case 'last':
					$sqls .= "select $new_grade_id,R.examinee,SUBSTRING(MAX(CONCAT(LPAD(R.time_id,6,'0'),R.score)),7) as score,SUBSTRING(MAX(CONCAT(LPAD(R.time_id,6,'0'),R.comment)),7) as comment ";
					break;
				case 'first':
				default:
					$sqls .= "select $new_grade_id,R.examinee,SUBSTRING(MIN(CONCAT(LPAD(R.time_id,6,'0'),R.score)),7) as score,SUBSTRING(MIN(CONCAT(LPAD(R.time_id,6,'0'),R.comment)),7) as comment ";
					break;
			}
			$sqls .= "from WM_term_major as M inner join WM_qti_{$source_kind}_result as R
					  on R.exam_id=$source_id and R.examinee=M.username and (R.status='revised' or R.status='publish')
					  where M.course_id={$sysSession->course_id} and M.role & {$sysRoles['student']}
					  group by R.examinee";
		}
		else
			$sqls = "insert IGNORE into WM_grade_item (grade_id,username,score,comment)
					 select $new_grade_id,R.examinee,R.score,R.comment
					 from WM_term_major as M inner join WM_qti_{$source_kind}_result as R
					 on R.exam_id=$source_id and R.examinee=M.username and (R.status='revised' or R.status='publish')
					 where M.course_id={$sysSession->course_id} and M.role & {$sysRoles['student']}";

		$sysConn->Execute($sqls);
		$sysConn->ErrorNo() and (die($sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg()));
                wmSysLog($sysSession->cur_func, $sysSession->course_id , $source_id , 0, 'auto', $_SERVER['PHP_SELF'], $MSG['import'][$sysSession->lang] . $source_kind);
	}
	elseif($_POST['source'] == 9){					// 自訂新成績
		if ($_POST['importType'] == '2')
		{
			if (!is_uploaded_file($_FILES['importfile']['tmp_name'])){
				die("<script>alert('{$MSG['incorrect upload file'][$sysSession->lang]}'); history.back();</script>");
			}
			$lang = ($_POST['file_format'] ? $_POST['file_format'] : $sysSession->lang);	// 設定匯入檔案所使用的語系
			$lists = file($_FILES['importfile']['tmp_name']) AND unlink($_FILES['importfile']['tmp_name']);
			$students_of_course = $sysConn->GetCol("select username from WM_term_major where course_id={$sysSession->course_id} and role&" . $sysRoles['student']);
			include_once(sysDocumentRoot .'/lib/interface.php');
		// 開始 output HTML
		  showXHTML_head_B('');
		    showXHTML_CSS('include', "/theme/{$sysSession->theme}/teach/wm.css");
		  showXHTML_head_E();
		  showXHTML_body_B('');
		    echo "<center>\r\n";
			showXHTML_tabFrame_B(array(array($MSG['confirm_upload'][$sysSession->lang])), 1, 'uploadForm', null, 'action="grade_create1.php" method="POST" style="display: inline"', false);
			$forGroup = $_POST['which'];
			$titles = $_POST['title'];
			unset($_POST['title'], $_POST['fields'], $_POST['which'], $_POST['import_homework'],
			      $_POST['import_exam'], $_POST['import_questionnaire']);

			if (is_array($titles)) foreach($titles as $k => $v)printf("\t\t\t<input type=\"hidden\" name=\"title[%s]\", value=\"%s\">\r\n", $k, $v);
			foreach($_POST as $k => $v) printf("\t\t\t<input type=\"hidden\" name=\"%s\", value=\"%s\">\r\n", $k, $v);
			showXHTML_table_B('id ="mainTable" border="0" cellpadding="3" cellspacing="1" width="760" style="border-collapse: collapse" class="box01"');
			showXHTML_tr_B('class="bg02 font01"');
			  showXHTML_td('', $MSG['student'][$sysSession->lang]);
			  showXHTML_td('', $MSG['score'][$sysSession->lang]);
			  showXHTML_td('', $MSG['comment'][$sysSession->lang]);
			  showXHTML_td('', '');
			showXHTML_tr_E();
			$i=0;
			$line1 = true;
			if (!is_array($lists)) $lists = array();
			foreach($lists as $grades)
			{
				//	去除UTF-8的檔頭 Begin
				if ($line1) {
					if ($lang == 'UTF-8' && strtolower(bin2hex(substr($grades, 0 , 3))) == 'efbbbf')
						$grades = substr($grades, 3);
					$line1 = false;
				}
				//	去除UTF-8的檔頭 End
				$grades = ($lang == 'Big5' || $lang == 'GB2312') ? iconv($lang, 'UTF-8', trim($grades)) : trim($grades);
				list($id, $score, $comments) = preg_split('/(,| +)/', $grades, 3);
				$comments = htmlspecialchars($comments, ENT_QUOTES, 'UTF-8');
				if ($forGroup)
				{
					$students = dbGetCol('WM_student_div', 'username',
										 sprintf('course_id=%d and group_id=%d and team_id=%d', $sysSession->course_id, $id, $forGroup));
					foreach($students as $id)
					{
						printf("\t\t\t\t<tr class=\"bg0%d\"><td>%s</td><td><input type=\"hidden\" name=\"fields[%s][0]\" value=\"%.2f\">%.2f</td><td><input type=\"hidden\" name=\"fields[%s][1]\" value=\"%s\">%s</td><td></td></tr>\r\n", $i+3, $id, $id, $score, $score, $id, $comments, $comments);
						$i = ++$i%2;
					}
				}
				else
				{
					if (in_array($id, $students_of_course))
					{
						printf("\t\t\t\t<tr class=\"bg0%d font01\"><td>%s</td><td><input type=\"hidden\" name=\"fields[%s][0]\" value=\"%.2f\">%.2f</td><td><input type=\"hidden\" name=\"fields[%s][1]\" value=\"%s\">%s</td><td></td></tr>\r\n", $i+3, $id, $id, $score, $score, $id, $comments, $comments);
					}
					else
					{
						printf("\t\t\t\t<tr class=\"bg0%d font01\" style=\"color: gray\"><td>%s</td><td>%.2f</td><td>%s</td><td style=\"color: red\">%s</td></tr>\r\n", $i+3, $id, $score, $comments, $MSG['not_student'][$sysSession->lang]);
					}
					$i = ++$i%2;
				}
			}
			    showXHTML_tr_B(sprintf('class="bg0%d"', $i+3));
			      showXHTML_td_B('align="center" colspan="4"');
			        showXHTML_input('hidden', 'importType', '1');
			        showXHTML_input('submit', null, $MSG['sure_upload'][$sysSession->lang]  , null, 'class="cssBtn"');
			        showXHTML_input('button', null, $MSG['cancel_upload'][$sysSession->lang], null, 'class="cssBtn" onclick="location.replace(\'grade_maintain.php\');"');
			      showXHTML_td_E();
			    showXHTML_tr_E();
			  showXHTML_table_E();
			showXHTML_tabFrame_E();
			echo "</center>\r\n";
		  showXHTML_body_E();
		  exit;
		}
		else
		{
			if (!is_array($_POST['title'])  || implode('', $_POST['title']) == '' ||
				!is_array($_POST['fields']) || empty($_POST['fields'])
			   )
			{
				header('Location: grade_maintain.php'); exit;
			}

			$title = $sysConn->qstr(serialize($_POST['title']));
			$percent = floatval($_POST['percent']);
			if (isSet($_POST['rdoPublish']) && $_POST['rdoPublish'] == 'no') 
			{
				$publish_begin = $publish_end = '0000-00-00 00:00:00';
			}
			else
			{
				$publish_begin = (isSet($_POST['chk_begin']) && isSet($_POST['publish_begin'])) ? $_POST['publish_begin'] . ':00' : '1970-01-01 00:00:00';
				$publish_end   = (isSet($_POST['chk_end'])   && isSet($_POST['publish_end']  )) ? $_POST['publish_end']   . ':00' : '9999-12-31 00:00:00';
			}
			dbNew('WM_grade_list', 'course_id,title,source,percent,publish_begin,publish_end',
			      "{$sysSession->course_id},{$title},9,$percent,'$publish_begin','$publish_end'");
			if ($sysConn->ErrorNo()) {
				$errMsg = $sysConn->ErrorNo() . ': ' . $sysConn->ErrorMsg();
		    	wmSysLog($sysSession->cur_func, $sysSession->course_id , $source_id , 1, 'auto', $_SERVER['PHP_SELF'], $errMsg);
				die($errMsg);
			}
			else
				$new_grade_id = $sysConn->Insert_ID();

			if ($_POST['which'])	// 小組成績
			{
				foreach($_POST['fields'] as $group_id => $grade){
					if ($grade[0] == '' && $grade[1] == '') continue;
					$rs = dbGetStMr('WM_student_div', 'username', sprintf('course_id=%d and group_id=%d and team_id=%d', $sysSession->course_id, $group_id, $_POST['which']), ADODB_FETCH_NUM);
					while(list($u) = $rs->FetchRow())
						dbNew('WM_grade_item', 'grade_id,username,score,comment', "$new_grade_id, '$u', '{$grade[0]}', '{$grade[1]}'");
				}
			}
			else					// 個人成績
			{
				foreach($_POST['fields'] as $username => $grade){
					if ($grade[0] == '' && $grade[1] == '') continue;
					dbNew('WM_grade_item', 'grade_id,username,score,comment', "$new_grade_id, '$username', '{$grade[0]}', '{$grade[1]}'");
				}
			}
		}
                wmSysLog($sysSession->cur_func, $sysSession->course_id , $new_grade_id , 0, 'auto', $_SERVER['PHP_SELF'], $MSG['set_self'][$sysSession->lang]);
	}
	
	reCalculateGrades($sysSession->course_id);
	echo <<< EOB
<script>location.replace('grade_maintain.php');</script>
EOB;
?>
