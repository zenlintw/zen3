<?php
	/**
	 * 我的辦公室
	 *
	 * @since   2003/06/12
	 * @author  ShenTing Lin
	 * @version $Id: teacher.php,v 1.2 2010-08-23 02:31:52 small Exp $
	 * @copyright 2003 SUNNET
	 * @備註 : 此支程式專供/learn/mycourse/index.php中所引用
	 **/

	if (!aclVerifyPermission(2500200200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$icon_up = '<img src="/theme/'.$sysSession->theme.'/learn/dude07232001up.gif" border="0" align="absmiddl">';
	$icon_dn = '<img src="/theme/'.$sysSession->theme.'/learn/dude07232001down.gif" border="0" align="absmiddl">';

	$sortby_arr = array('course_id', 'caption', 'teach_status', 'teach_level', 'teach_students', 'teach_st_begin', 'teach_st_end' , 'teach_homework', 'teach_exam');
	$order_arr  = array('asc', 'desc');

	$sortby = $_POST['sortby'];
	$order  = empty($_POST['order'])  ? getSetting('cour_office_order')  : $_POST['order'];

	foreach($sortby_arr as $k => $v)
	{
		if ($v == $sortby)	
		{
			$sortby_idx = $k;
			break;
		}
	}
	
	function getSortLink($msg, $type) {
		global $icon_up, $icon_dn, $sortby, $order;
		$rtn = '<a class="cssAnchor" href="javascript:;" onclick="return false;">' . $msg;
		if ($type == $sortby)
			$rtn .= $order == 'desc' ? $icon_dn : $icon_up;
		return $rtn . '</a>';
	}

	$courses = array();
	// 取出群組中教授的課程編號
	$group_id = intval(getSetting('group_id'));
	if (($status == 0) || ($status == 2)) {
		$group_id = 10000000;
		saveSetting('group_id', $group_id);
		saveSetting('page_no', '1');
	}

         // 課程名稱
    $course_name = ($_POST['course_name'] == $MSG['msg_course1'][$sysSession->lang]) ? '' : trim(strip_tags(stripslashes($_POST['course_name'])));
    $smarty->assign('course_name', $course_name);
//	$students = $sysConn->GetAssoc('select M1.course_id,count(*)
//									from WM_term_major as M1
//									left join WM_term_major as M2
//									on M1.course_id=M2.course_id and (M2.role & ' . ($sysRoles['student'] | $sysRoles['auditor']) . ')
//									where M1.username="' . $sysSession->username . '" and (M1.role & ' . ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']) . ')
//									group by M1.course_id');
    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	if ($group_id <= 10000000) {
		$RS = dbGetCourses('C.course_id, C.caption, C.status, C.st_begin, C.st_end, M.role & ' . ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']) . ' as level',
						   $sysSession->username,
						   $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant'],
		                   'C.course_id desc');
		if ($RS)
		{
		    $unsubmit_homeworks = array();
			if ($es = $sysConn->GetCol('SELECT M1.course_id
										FROM WM_term_major AS M1, WM_qti_homework_test AS T, WM_qti_homework_result AS R
										WHERE M1.username = "' . $sysSession->username . '"
										AND (M1.role & ' . ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']) . ')
										AND M1.course_id = T.course_id
										AND T.exam_id = R.exam_id
										AND R.status = "submit"
										GROUP BY M1.course_id, T.exam_id'))
                $unsubmit_homeworks = array_count_values($es);

		    $unsubmit_exams = array();
			if ($es = $sysConn->GetCol('SELECT M1.course_id
										FROM WM_term_major AS M1, WM_qti_exam_test AS T, WM_qti_exam_result AS R
										WHERE M1.username = "' . $sysSession->username . '"
										AND (M1.role & ' . ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']) . ')
										AND M1.course_id = T.course_id
										AND T.exam_id = R.exam_id
										AND R.status = "submit"
										GROUP BY M1.course_id, T.exam_id'))
                $unsubmit_exams = array_count_values($es);

			while (!$RS->EOF) {
				if (!empty($RS->fields['course_id'])) {
                                        $RSS = dbGetStSr('WM_term_major', 'count(*) as cnt', "course_id={$RS->fields['course_id']} and role&({$sysRoles['student']} | {$sysRoles['auditor']})", ADODB_FETCH_ASSOC);
					$courses[] = array(
							$RS->fields['course_id'], fetchTitle($RS->fields['caption']),
							$RS->fields['status']	, $RS->fields['level'],
							intval($RSS['cnt']),
							intval($students[$RS->fields['course_id']]),
							$RS->fields['st_begin'], $RS->fields['st_end'],
							intval($unsubmit_homeworks[$RS->fields['course_id']]),
							intval($unsubmit_exams[$RS->fields['course_id']])
						);
				}
				$RS->MoveNext();
			}
		}


	} else {
		$sqls = str_replace('%USERNAME%', $sysSession->username, $Sqls['get_group_teacher_term']);
		$sqls = str_replace('%GROUP_ID%', $group_id,             $sqls);

		$RS = $sysConn->Execute($sqls);
		if ($RS)
		{
			while (!$RS->EOF) {
				if (!empty($RS->fields['course_id'])) {

					// 學員人數
					// MIS#17719 聯合-我的課程:學員人數統計值有時不會顯示 by Small
					// $RSS = dbGetStSr('WM_term_major', 'count(*)', "course_id={$RS->fields['course_id']}", ADODB_FETCH_ASSOC);
					$RSS = dbGetStSr('WM_term_major', 'count(*) as cnt', "course_id={$RS->fields['course_id']} and role&({$sysRoles['student']} | {$sysRoles['auditor']})", ADODB_FETCH_ASSOC);

					// 正式生
					$CS_RS = dbGetStMr('WM_term_major','username','course_id=' . $RS->fields['course_id'] . ' and role & ' . $sysRoles['student'], ADODB_FETCH_ASSOC);
					$cour_stud      = '';
					$stud_cond      = '';	// 正式生查詢條件
					$hw_uncorrect   = 0;	// 未做作業
					$exam_uncorrect = 0;	// 未做測驗

					// if begin
					if ($CS_RS){
						if ($CS_RS->RecordCount() > 0){
							while ($CS_RS1 = $CS_RS->FetchRow()){
								$cour_stud .= "'" . $CS_RS1['username'] . "',";
							}
							$cour_stud = substr($cour_stud,0,-1);

							$stud_cond = ' and R.examinee in (' . $cour_stud . ')';

							// 作業 (begin)
							$hw_type = 'homework';
							$hw_sqls = str_replace('%COURSEID%', $RS->fields['course_id'], $Sqls['cour_hwork_exam_times']);
							$hw_sqls = str_replace('%TYPE%', $hw_type,$hw_sqls);
							$hw_sqls .= $stud_cond;

							$hw_uncorrect = $sysConn->GetOne($hw_sqls);
							// 作業 (end)

							// 測驗 (begin)
							$exam_type = 'exam';
							$exam_sqls = str_replace('%COURSEID%', $RS->fields['course_id'], $Sqls['cour_hwork_exam_times']);
							$exam_sqls = str_replace('%TYPE%', $exam_type,$exam_sqls);
							$exam_sqls .= $stud_cond;

							$exam_uncorrect = $sysConn->GetOne($exam_sqls);
							// 測驗 (end)
						}
					}
					// if end

					$courses[] = array(
							$RS->fields['course_id'], fetchTitle($RS->fields['caption']),
							$RS->fields['status']	, $RS->fields['level'],
							// MIS#17719 聯合-我的課程:學員人數統計值有時不會顯示 by Small
							// $RSS[0]					, $RS->fields['st_begin'],
							$RSS['cnt']					, $RS->fields['st_begin'],
							$RS->fields['st_end']	, $hw_uncorrect,
							$exam_uncorrect
						);
				}
				$RS->MoveNext();
			}
		}


	}
	
	if (!empty($course_name)) {
    	$new_course = array();
	    foreach ($courses as $key => $val) {
	        if (strpos($courses[$key][1], htmlspecialchars($course_name)) !== false) {
	            $new_course[] = $val;
	        } 
	    } 
	    unset($courses);
	    $courses = $new_course;
    }
    

	// 用來對課程作排序
    function cmp($a, $b) {
		global $sortby_idx, $order;

		if ($a[$sortby_idx] == $b[$sortby_idx]) return 0;
		if ($order == 'desc')
			return $a[$sortby_idx] > $b[$sortby_idx] ? -1 : 1;
		else
			return $a[$sortby_idx] > $b[$sortby_idx] ? 1 : -1;
	}

	// 用來對課程作排序
	function cmpCaption($a, $b) {
	    global $sortby_idx, $order, $sysSession;
	     
	    $cpA = unserialize($a[$sortby_idx]);
	    $cpB = unserialize($b[$sortby_idx]);
	
	    if ($cpA[$sysSession->lang] == $cpB[$sysSession->lang]) return 0;
	    if ($order == 'desc')
	        return $cpA[$sysSession->lang] > $cpB[$sysSession->lang] ? -1 : 1;
	    else
	        return $cpA[$sysSession->lang] > $cpB[$sysSession->lang] ? 1 : -1;
	}
	
	// 對課程作排序
	if (in_array($sortby, $sortby_arr) && in_array($order, $order_arr)) {
	    if ($sortby_arr[$sortby_idx] == 'caption') {
	        usort($courses, 'cmpCaption');
	    }else{
	        usort($courses, 'cmp');
	    }
		$courses = array_values($courses); 	// 重建keys
	}

	$cnt = count($courses);

	// 計算全部的課程數
	$total_course = $cnt;
	// 計算總共分幾頁
	$total_page = ceil($total_course / $lines);
	// 產生下拉換頁選單
	$all_page = range(0, $total_page);
	$all_page[0] = $MSG['page_all'][$sysSession->lang];
	// 設定下拉換頁選單顯示第幾頁
	$setting_no = intval(getSetting('page_no'));
	//$setting_no = empty($setting_no) ? $total_page : $setting_no;
	$page_no = isset($_POST['page']) ? intval($_POST['page']) : $setting_no;
	if (($page_no < 0) || ($page_no > $total_page))
		$page_no = $total_page;
	if ($page_no == 0) $page_no = 1;
	saveSetting('page_no', $page_no);  // 回存設定

	$js = <<< BOF
	var total_count = {$total_course};
	var page_size = {$lines};
	var total_page={$total_page};
	var page_no = {$page_no};

	// 用來排序的function
	function chgPageSort(sortby) {
		var obj = document.getElementById('actFm');

		if ((typeof(obj) != "object") || (obj == null)) return false;
		if (obj.sortby.value != sortby) {
			obj.order.value = 'asc';
			obj.sortby.value = sortby;
		}
		else
			obj.order.value = obj.order.value == 'asc' ? 'desc' : 'asc';
		obj.submit();
	}
	
	function CancelQuery(){
		obj  = document.getElementById("actFm");
		obj.course_name.value = '';
		
		window.onunload = function () {};
		obj.submit();
	}
	
	/**
	 * 搜尋課程
	 *
	 **/
	function queryCourse() {
		var obj = null;
		// course name keyword
		obj = document.getElementById("cour_keyword");
		var cour_key = (obj == null) ? "" : obj.value;

		obj  = document.getElementById("actFm");
		obj.course_name.value = cour_key;
		obj.isquery.value = "true";
		window.onunload = function () {};
		obj.submit();
	}
	
BOF;
	
	if ($page_no == 0) {
	    $begin = 0;
	    $end   = $cnt;
	} else {
	    $begin = intval($page_no - 1) * $lines;
	    $end   = intval($page_no) * $lines;
	    if ($begin < 0)  $begin = 0;
	    if ($end > $cnt) $end   = $cnt;
	}
	
	switch($sysSession->env) {
	    case 'teach'  : $nEnv = 2; break;
	    case 'direct' : $nEnv = 3; break;
	    case 'acdemic': $nEnv = 4; break;
	    default: $nEnv = 1;
	}
	$smarty->assign('nEnv', $nEnv);
	
	$datalist = array();
	for ($i = $begin; $i < $end; $i++) {
	    $val = $courses[$i];
	    if ($val[2] != 0)
	    {

	        switch ($val[2]) {
	            case 0 :
	                $msg_status = $MSG['msg_cs_close'][$sysSession->lang];
	                break;
	            case 1 : case 2 : case 3 : case 4 :
	                $msg_status = $MSG['msg_cs_open'][$sysSession->lang];
	                break;
	            case 5 :
	                $msg_status = $MSG['msg_cs_prepare'][$sysSession->lang];
	                break;
	        }
	        $val[2] = $msg_status;
	        $val[3] = $teacher_level[$val[3]];
	    }
	    $datalist[] = $val;
	}

	$smarty->assign('sort', $sortby);
	$smarty->assign('order', $order);
	$smarty->assign('inlineTeacherJS', $js);
	$smarty->assign('datalist', $datalist);
