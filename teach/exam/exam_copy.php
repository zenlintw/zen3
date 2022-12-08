<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wing                                	                          *
	 *		Creation  : 2005/10/17                                                            *
	 *		work for  : Copy exam 				                                  *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/quota.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	
	//ACL begin
	if (QTI_which == 'exam') {
		$sysSession->cur_func = '1600200100';
	}
	else if (QTI_which == 'homework') {
		$sysSession->cur_func = '1700200100';
	}
	else if (QTI_which == 'questionnaire') {
		$sysSession->cur_func = '1800200100';
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
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'Access Denied!');
	   die('Access denied.');					
	}
	$ticket = md5(sysTicketSeed . $course_id . $_POST['referer']);			// 產生 ticket
	
	if ($ticket != $_POST['ticket']) {	// 檢查 ticket
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], 'Fake Ticket!');
	   die('Fake ticket.');			
	}
	if (!ereg('^[0-9]+(,[0-9]+)*$', $_POST['lists'])) {	// 檢查 lists
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 3, 'auto', $_SERVER['PHP_SELF'], 'Fake lists!');
	   die('Fake lists.');	
	}
	
	// 如果是要拷貝給其它授課課程
	chkSchoolId('WM_term_major');
	if (is_array($_POST['target_courses']) && count($_POST['target_courses']))
	{
	    $own_courses = $sysConn->GetCol('select M.course_id ' .
										'from WM_term_major as M inner join WM_term_course as C ' .
										'on M.course_id = C.course_id ' .
										'where M.username="' . $sysSession->username .
										'" and M.role&' .
										($sysRoles['assistant'] | $sysRoles['instructor'] | $sysRoles['teacher']) .
										' and C.status between 1 and 5 and ' .
										'(isnull(C.st_begin) or C.st_begin<=CURDATE()) and (isnull(C.st_end) or C.st_end>=CURDATE()) and ' .
										'C.quota_used < C.quota_limit');
        $target_courses = array_intersect($own_courses, $_POST['target_courses']);
	}
	else
	    $target_courses = false;

    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	$rs = $sysConn->GetArray('select * from WM_qti_' . QTI_which . '_test where exam_id in (' . $_POST['lists'] . ')');
    $failure = 0;
    $include_items = array();
    $copied = array();
    $isPossibleNulls = array('begin_time','close_time','announce_time','item_cramble','random_pick','notice');
    
	if (is_array($rs) && count($rs))
	{
	    // 開始拷貝試卷
	    foreach ($rs as $record)
	    {
			// 隨機出題不能複製
			if (strpos($record['content'], '<wm_immediate_random_generate_qti') !== FALSE) {
				echo '<script language="javascript">alert("' . $MSG['immediate_random_generate_test'][$sysSession->lang] . '"); location.href = "exam_maintain.php";</script>';
				exit;
			}
			
	        $record['exam_id'] = 'NULL';
	    
			$title = unserialize($record['title']);
			$lang['Big5']   	 = empty($title['Big5'])		? '' : ('COPY_' . trim($title['Big5']));
			$lang['GB2312'] 	 = empty($title['GB2312'])		? '' : ('COPY_' . trim($title['GB2312']));
			$lang['en']     	 = empty($title['en'])			? '' : ('COPY_' . trim($title['en']));
			$lang['EUC-JP'] 	 = empty($title['EUC-JP'])		? '' : ('COPY_' . trim($title['EUC-JP']));
			$lang['user_define'] = empty($title['user_define'])	? '' : ('COPY_' . trim($title['user_define']));
			$record['title'] = serialize($lang);
			if (QTI_which === 'homework') {
                            $record['create_time'] = date('Y-m-d H:i:s');
			}

			$record['sort'] = 0;

            if (QTI_which == 'exam' || (sysEnableAppISunFuDon && QTI_which == 'questionnaire' && $topDir == 'teach'))

            if (sysEnableAppISunFuDon && 
                (QTI_which == 'exam' || QTI_which == 'questionnaire') && 
                $topDir == 'teach' && 
                intval($record['type']) === 5
            ) {
                $record['publish'] = 'prepare';
                $record['begin_time'] = '0000-00-00 00:00:00';
                $record['close_time'] = '9999-12-31 00:00:00';
            }

			if (is_array($target_courses) && count($target_courses))
			{
		        foreach ($isPossibleNulls as $possibleNull)
		            if ($record[$possibleNull] == '') $record[$possibleNull] = 'NULL';

		    	foreach ($target_courses as $target_course)
			    {
			        $record['course_id'] = $target_course;

					if ($sysConn->AutoExecute('WM_qti_' . QTI_which . '_test', $record, 'INSERT'))
				        $copied[$target_course][] = $sysConn->Insert_ID(); // 蒐集新建試卷
					else
                        $failure++;
				}
				
				// 蒐集所含題目
				if (preg_match_all('/<item [^>]*\bid="(\w+)"/U', $record['content'], $regs, PREG_PATTERN_ORDER))
				{
				    $include_items = array_merge($include_items, $regs[1]);
				}
				elseif (strpos($record['content'], '<wm_immediate_random_generate_qti') !== false)
				{
				    $include_items = array_merge($include_items, $sysConn->GetCol('select ident from WM_qti_' . QTI_which . '_item where course_id=' . $course_id));
				}
			}
			else
			{
			    $failure += $sysConn->AutoExecute('WM_qti_' . QTI_which . '_test', $record, 'INSERT') ? 0 : 1;
			}
		}
	}

	// 複製到別門課，必須複製題目
	if (is_array($target_courses) && count($include_items))
	{
	    /* 取得夾檔位置 begin */
		if (!defined('QTI_env'))
			list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
		else
			$topDir = QTI_env;

		if ($topDir == 'academic')
		{
			$from_uri = sprintf('%s/base/%05d/%s/Q/',
                                 sysDocumentRoot,
			  					 $sysSession->school_id,
			  					 QTI_which);
		}
		else
		{
			$from_uri = sprintf('%s/base/%05d/course/%08d/%s/Q/',
                                 sysDocumentRoot,
			  					 $sysSession->school_id,
			  					 $course_id,
			  					 QTI_which);
		}
		/* 取得夾檔位置 end */

	
		// 拷貝夾檔
	    $include_items = array_unique($include_items);
	    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
        $items = $sysConn->GetArray(sprintf('select * from WM_qti_%s_item where ident in ("%s")', QTI_which, implode('","', $include_items)));

		if (is_array($items) && count($items))
		{
			$t = split('[. ]', microtime());
			$ident = sprintf('WM_ITEM1_%010d_%%08d_%d_', sysSiteUID, $t[2]);
			$count = intval(substr($t[1],0,6));
			$possibleNulls = array('version', 'volume', 'chapter', 'paragraph', 'section', 'answer', 'attach');

		    foreach ($target_courses as $target_course)
		    {
				$olds = array(); // 舊題號
				$news = array(); // 新題號

				if ($topDir == 'academic')
				{
					$save_uri = sprintf('%s/base/%05d/%s/Q/',
                                         sysDocumentRoot,
					  					 $sysSession->school_id,
					  					 QTI_which);
				}
				else
				{
					$save_uri = sprintf('%s/base/%05d/course/%08d/%s/Q/',
                                         sysDocumentRoot,
					  					 $sysSession->school_id,
					  					 $target_course,
					  					 QTI_which);
				}
				if (!is_dir($save_uri)) exec('mkdir -p ' . $save_uri);
				if (!is_dir($save_uri)) continue;
				
			    foreach ($items as $item)
			    {
			        $old_id			   = $item['ident'];
			        $item['ident']     = sprintf($ident, $target_course) . ($count++);
			        $item['course_id'] = $target_course;
			        $item['content']   = str_replace($old_id, $item['ident'], $item['content']);
			        foreach ($possibleNulls as $possibleNull)
			            if ($item[$possibleNull] == '') $item[$possibleNull] = 'NULL';

			        if ($sysConn->AutoExecute('WM_qti_' . QTI_which . '_item', $item, 'INSERT'))
			        {
			            if ($item['attach']) @chdir($from_uri) and @exec("cp -Rf '{$old_id}' '{$save_uri}/{$item['ident']}'"); // 拷貝夾檔
						$olds[] = $old_id;
						$news[] = $item['ident'];
					}
					else
						echo ($sysConn->ErrorNo() . ':' . $sysConn->ErrorMsg());
				}
				
				// 將新建的試卷之 XML 裡的舊題號換成新題號
			    $contents = $sysConn->GetAssoc('select exam_id,content from WM_qti_' . QTI_which . '_test where exam_id in (' . implode(',', $copied[$target_course]) . ')');
			    $fields = array('content' => '');
			    foreach ($contents as $k => $v)
			    {
			        if (strpos($record['content'], '<wm_immediate_random_generate_qti') !== false) continue;
			        $fields['content'] = str_replace($olds, $news, $v);
			        $sysConn->AutoExecute('WM_qti_' . QTI_which . '_test', $fields, 'UPDATE', 'exam_id=' . $k);
				}

				getCalQuota($target_course, $quota_used, $quota_limit);
				setQuota($target_course, $quota_used);

			    unset($olds, $news, $quota_used, $quota_limit);
		    }
		}
	}

	if($failure < 1)
		$msg = $MSG['copy_sucess'][$sysSession->lang];
	else
		$msg = $MSG['copy_failure'][$sysSession->lang];

	$referer = ($_POST['referer']?"?".$_POST['referer']:'');
	wmSysLog($sysSession->cur_func, $course_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], 'copy ' . QTI_which . ':' . $_POST['lists']);

	$js = <<< BOF

	window.onload = function () {
		alert("{$msg}");
		window.location.replace("exam_maintain.php$referer");
	};

BOF;
	showXHTML_script('inline', $js);
?>
