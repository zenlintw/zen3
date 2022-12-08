<?php
	/**
	 * 學校統計資料 - 使用者人數統計
	 * 建立日期：2004/08/16
	 * @author  Amm Lee
	 * @version $Id: sch_user_statistics.php,v 1.1 2010/02/24 02:38:43 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
	require_once(sysDocumentRoot . '/mooc/common/common.php');
	require_once(sysDocumentRoot . '/lang/sch_statistics.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/Week2YearMonthDay.php');
	require_once(sysDocumentRoot . '/lang/teach_statistics.php');
	require_once(sysDocumentRoot . '/mooc/models/statistics.php');
	require_once(sysDocumentRoot . '/lib/course.php');
	
	$sysSession->cur_func = '1500200100';
	$sysSession->restore();
	if (!aclVerifyPermission(1500200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$course_rang = !isset($_POST['ck_course_rang'])?1:max(min(3,intval($_POST['ck_course_rang'])),1);
	
	$countCount = 0;
	if ($course_rang == 1) { //全校課程
	    $countCount = dbGetOne('WM_term_course RIGHT JOIN `WM_review_sysidx` ON `WM_review_sysidx`.`discren_id` = `WM_term_course`.`course_id`','count(*)','`course_id` != 10000000 AND `kind` = "course" AND `status` < 9');
	    $title_name = $MSG['title58'][$sysSession->lang];
	}else if ($course_rang == 2) { //單一課程群組
	    if ($_POST['single_group_id'] == 10000000){	// 未分組課程
	        chkSchoolId('WM_term_course');
	        $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	        $data = $sysConn->GetCol('select B.course_id ' .
    	        'from WM_term_course as B ' .
    	        'left join WM_term_group as G ' .
                'RIGHT JOIN `WM_review_sysidx` ON `WM_review_sysidx`.`discren_id` = `WM_term_course`.`course_id`'.
    	        'on B.course_id=G.child ' .
    	        'where G.child is NULL and B.kind="course" and B.status != 9');
	        $cour_str   = is_array($data) && count($data) ? implode(',', $data) : '';
	        $countCount = count($data);
	        $title_name = stripslashes(str_replace('%CGROUP%',$MSG['title74'][$sysSession->lang],$MSG['title97'][$sysSession->lang]));
	    }else{		//  某群組 底下的所有課程
	        $group_id   = intval($_POST['single_group_id']);
	        $data       = @array_keys(getAllCourseInGroup($group_id));
	        $cour_str   = is_array($data) && count($data) ? implode(',', $data) : '';
	        $countCount = count($data);
	        $title_name = stripslashes(str_replace('%CGROUP%',$_POST['single_group'],$MSG['title97'][$sysSession->lang]));
	    }
	}else if ($course_rang == 3) { // 某個課程
	    // html 標題
	    $title_name = $MSG['title104'][$sysSession->lang] . $_POST['single_course'];
	    $cour_str = $_POST['single_course_id'];
	    $countCount = 1;
	}
	
	if ($course_rang > 1) {
	    // 取得 課程底下的 老師 助教 講師 正式生 旁聽生 資料
	    $RS = dbGetStMr('WM_term_major','distinct username','course_id in (' . $cour_str . ')', ADODB_FETCH_ASSOC);
	    $users = array();
	    if ($RS && $RS->RecordCount() > 0){
	        while($RS1 = $RS->FetchRow()){
	            if (! in_array("'" . $RS1['username'] . "'",$users)){
	                $users[] = "'" . $RS1['username'] . "'";
	            }
	        }
	        $users1 = implode(',',$users);
	    }
	}

	// 課程身份
	$role = array(
    	'auditor'    => 0,
    	'student'    => 0,
    	'assistant'  => 0,
    	'instructor' => 0,
    	'teacher'    => 0
	);
	
	$gender = array();
	if ($course_rang == 1){
	    //  性別
	    $gender = dbGetAssoc('WM_user_account', 'gender,count(*) as num', "username != 'root' group by gender", ADODB_FETCH_ASSOC);
	    // 取得 課程底下所有成員的角色資料
	    $subtable = 'select username, BIT_OR(role) as all_perms from WM_term_course as T1 INNER JOIN WM_term_major as T2 on T1.course_id=T2.course_id RIGHT JOIN `WM_review_sysidx` ON `WM_review_sysidx`.`discren_id` = T1.`course_id` where `kind` = "course" AND T1.status < 9 group by username';
	    $RS = $sysConn->Execute("SELECT all_perms,count(*) as ct FROM ({$subtable}) as T1 group by all_perms");
	    if ($RS->RecordCount() > 0){
	        while($RS1 = $RS->FetchRow()){
	            foreach ($role as $key => $val) {
	                if ($RS1['all_perms'] & $sysRoles[$key]) {
	                    $role[$key] += $RS1['ct'];
	                }
	            }
	        }
	    }
	}else if (strlen($users1) > 0){
	    //  性別
	    $gender = dbGetAssoc('WM_user_account', 'gender,count(*) as num', 'username in ('.$users1.') group by gender', ADODB_FETCH_ASSOC);
	    // 取得 課程底下所有成員的角色資料
	    $subtable = 'select username, BIT_OR(role) as all_perms from WM_term_major where course_id in (' . $cour_str . ') group by username'; 
	    $RS = $sysConn->Execute("SELECT all_perms,count(*) as ct FROM ({$subtable}) as T1 group by all_perms");
	    if ($RS->RecordCount() > 0){
	        while($RS1 = $RS->FetchRow()){
	            foreach ($role as $key => $val) {
	                if ($RS1['all_perms'] & $sysRoles[$key]) {
	                    $role[$key] += $RS1['ct'];
	                }
	            }
	        }
	    }
	}
	
	if (array_key_exists('', $gender)) {
	    $gender['NOT_MARKED'] = $gender[""];
	    unset($gender[""]);
	}
        
        // 性別轉數字，無該性別自動轉0
	foreach (array('M', 'F', 'N') as $v) {
		$gender_sort[$v] = (int)$gender[$v];
	}
	
	
	// assign
	$smarty->assign('post', $_POST);
	$smarty->assign('MSG', $MSG);
	$smarty->assign('sysSession', $sysSession);
	$smarty->assign('courseRange', $course_rang);
	$smarty->assign('genderP', $gender_sort);
	$smarty->assign('roleP', $role);
	$smarty->assign('title_name', $title_name);
	$smarty->assign('courseCount', $countCount);
        $smarty->assign('msg', $MSG);
	
	
	foreach ($gender_sort as $key => $value){
		$x_gender[]=$key;
		$y_gender[]=$value;
	}
	foreach ($role as $key => $value){
		$x_role[]=$key;
		$y_role[]=$value;
	}
	
	$smarty->assign('action_gender', "user_gender_graph.php");
	$smarty->assign('action_role', "user_role_graph.php");
	$smarty->assign('x_gender', implode(",",$x_gender));
	$smarty->assign('y_gender', implode(",",$y_gender));
	$smarty->assign('x_role', implode(",",$x_role));
	$smarty->assign('y_role', implode(",",$y_role));
        
	$smarty->display('academic/stat/sch_user_statistics.tpl');
	exit;
	$js = <<< EOF

	function user_role(val){

		var check_value = '';

		var obj = document.getElementById('queryForm');

		val = parseInt(val);

		switch (val){
			case 1:
				obj.condition[0].checked = true;
				obj.condition[2].disabled = true;

				obj.single_all[0].checked = false;
				obj.single_all[1].checked = false;
				obj.single_all[2].checked = false;
				obj.single_all[3].checked = false;

				obj.single_all[0].disabled = true;
				obj.single_all[1].disabled = true;
				obj.single_all[2].disabled = true;
				obj.single_all[3].disabled = true;
				break;
			case 2:
				obj.single_all[0].disabled = '';
				obj.single_all[1].disabled = '';
				obj.single_all[2].disabled = true;
				obj.single_all[3].disabled = true;
				if (!obj.single_all[0].checked && !obj.single_all[1].checked)
				obj.single_all[0].checked = true;

				obj.condition[2].disabled = '';

				break;
			case 3:
				obj.single_all[0].disabled = true;
				obj.single_all[1].disabled = true;
				obj.single_all[2].disabled = '';
				obj.single_all[3].disabled = '';
				if (!obj.single_all[2].checked && !obj.single_all[3].checked)
				obj.single_all[2].checked = true;

				obj.condition[2].disabled = '';

				break;
		}
	}

	function check_data(){

		var obj = document.getElementById('queryForm');

		if (obj.target_member[0].checked) {
			if ((obj.condition[0].checked == false) && (obj.condition[1].checked == false)){
				alert("{$MSG['title173'][$sysSession->lang]}");
				return false;
			}
		}

		if (obj.target_member[1].checked) {
			if (obj.single_all[0].checked){
				if (obj.single_group_id.value == ''){
					alert("{$MSG['title151'][$sysSession->lang]}");
					obj.single_group.focus();
					return false;
				}
			}

			if (obj.single_all[1].checked){
				if (obj.single_course_id.value == ''){
					alert("{$MSG['title152'][$sysSession->lang]}");
					obj.single_course.focus();
					return false;
				}
			}
		}

		if (obj.target_member[2].checked) {
			if (obj.single_all[2].checked){
				if (obj.single_cgroup_id.value == ''){
					alert("{$MSG['title153'][$sysSession->lang]}");
					obj.single_cgroup.focus();
					return false;
				}
			}

			if (obj.single_all[3].checked){
				if (obj.single_class_id.value == ''){
					alert("{$MSG['title154'][$sysSession->lang]}");
					obj.single_class.focus();
					return false;
				}
			}
		}

		obj.action = 'sch_user_statistics1.php';

		window.onunload = function () {};

		obj.submit();

	}

	function age_rang(){
		var obj = document.getElementById('age_interval');

		var txt = '';

		txt = '&nbsp;<select name="age_rang">' +
			  '<option value="10">10</option>'+
			  '<option value="5">5</option>'+
			  '<option value="3">3</option>'+
			  '</select>';

		obj.innerHTML = txt;

	}

	function select_cgroup(){
		var ret = showDialog('pickCGroup.php',true,window,true,0,0,'250px','250px','scrollbars=1');

		if (!ret)
		return;

	}

	function select_class(){
		var obj = document.getElementById('single_cgroup_id');
		var nodes = document.getElementsByTagName('input');

		if (obj.value.length == 0) {
			alert("{$MSG['title93'][$sysSession->lang]}");

			obj = document.getElementById('single_cgroup_id');
			obj.focus();
			return false;
		}

		var gd = obj.value;

		var ret = showDialog('pickClass.php?gd='+gd,true,window,true,0,0,'250px','250px','scrollbars=1');
	}

	// 顯示班級名稱於 repost_course 中
	function showClassCaption(idx,caption) {

		var field = document.getElementById(idx);
		if(!field) return;

		field.value = caption;
	}

	// 顯示課程名稱於 repost_course 中 (course_name)
	function showCourseCaption(idx,caption) {

		var field = document.getElementById(idx);
		if(!field) return;

		field.value = caption;
	}

	function select_group(){
		var ret = showDialog('pickGroup.php',true,window,true,0,0,'250px','250px','scrollbars=1');

		if (!ret)
		return;

	}

	function select_course(){
		var obj = document.getElementById('single_group_id');
		var nodes = document.getElementsByTagName('input');

		if (obj.value.length == 0) {
			alert("{$MSG['title64'][$sysSession->lang]}");
			obj = document.getElementById('single_group');
			obj.focus();
			return false;
		}

		var gd = obj.value;
		var ret = showDialog('pickCourse.php?gd='+gd,true,window,true,0,0,'250px','250px','scrollbars=1');

	}

	function mod_role(val){
		var disable_state = '';
		if (val == 1){
			disable_state = 'true';
		}else{
			disable_state = '';
		}

		var obj = document.getElementById('queryForm');

		var check_value = '';

		val = parseInt(val);

		if (obj.target_member[0].checked){
			check_value = parseInt(obj.target_member[0].value);
		}else if (obj.target_member[1].checked){
			check_value = parseInt(obj.target_member[1].value);
		}else if (obj.target_member[2].checked){
			check_value = parseInt(obj.target_member[2].value);
		}

		switch (check_value){
			case 1:

				// role disable
				obj.condition[2].disabled = true;
				break;
		}

	}
	var orgload = window.onload;
	window.onload = function () {
		orgload();

		user_role(1);

		age_rang();
	};
EOF;

	showXHTML_head_B($MSG['title5'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('include', '/lib/common.js');
	showXHTML_script('include', 'sch_statistics.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E('');

	showXHTML_body_B('');

		$ary[] = array($MSG['title5'][$sysSession->lang], 'tabs');
		showXHTML_tabFrame_B($ary, 1, 'queryForm', 'ListTable', 'action="" method="POST" style="display: inline;"', false);
				showXHTML_table_B('id ="mainTable" width="680" border="0" cellspacing="1" cellpadding="3" class="cssTable" ');
					showXHTML_tr_B('class="cssTrEvn"');
						showXHTML_td('nowrap', $MSG['title76'][$sysSession->lang]);
						showXHTML_td_B('nowrap colspan="2"');
							showXHTML_input('radio', 'target_member', array(1 => $MSG['title78'][$sysSession->lang]), 1, 'onClick="user_role(this.value);"');
							echo '<br>';

							showXHTML_input('radio' , 'target_member'  , array(2 => $MSG['title59'][$sysSession->lang]), 1, 'onClick="user_role(this.value);"');
							showXHTML_input('text'  , 'single_group'   , '', '', 'id="single_group" class="cssInput" size="20"');
							showXHTML_input('hidden', 'single_group_id', '', '', 'id="single_group_id" class="cssInput" size="20"');
							showXHTML_input('button','btnImp',$MSG['title60'][$sysSession->lang],'','class="cssBtn" onclick="select_group()"');
							echo '<br>&nbsp;&nbsp;';

							showXHTML_input('radio', 'single_all', array(1 => $MSG['title61'][$sysSession->lang]));
							echo '<br>&nbsp;&nbsp;';
							showXHTML_input('radio', 'single_all', array(2 => $MSG['title62'][$sysSession->lang]));
							showXHTML_input('text', 'single_course', '', '', 'id="single_course" class="cssInput" size="20"');
							showXHTML_input('hidden', 'single_course_id', '', '', 'id="single_course_id" class="cssInput" size="20"');
							showXHTML_input('button','btnImp',$MSG['title63'][$sysSession->lang],'','class="cssBtn" onclick="select_course()"');
							echo '<p>';

							showXHTML_input('radio' , 'target_member', array(3 => $MSG['title90'][$sysSession->lang]), 1, 'onClick="user_role(this.value);"');
							showXHTML_input('text'  , 'single_cgroup', '', '', 'id="single_cgroup" class="cssInput" size="20"');
							showXHTML_input('hidden', 'single_cgroup_id', '', '', 'id="single_cgroup_id" class="cssInput" size="20"');
							showXHTML_input('button','btnImp',$MSG['title60'][$sysSession->lang],'','class="cssBtn" onclick="select_cgroup()"');
							echo '<br>' . '&nbsp;&nbsp;';

							showXHTML_input('radio', 'single_all', array(3 => $MSG['title91'][$sysSession->lang]));
							echo '<br> &nbsp;&nbsp;';

							showXHTML_input('radio', 'single_all', array(4 => $MSG['title92'][$sysSession->lang]));
							showXHTML_input('text', 'single_class', '', '', 'id="single_class" class="cssInput" size="20"');
							showXHTML_input('hidden', 'single_class_id', '', '', 'id="single_class_id" class="cssInput" size="20"');
							showXHTML_input('button','btnImp',$MSG['title96'][$sysSession->lang],'','class="cssBtn" onclick="select_class()"');

						showXHTML_td_E();
					showXHTML_tr_E();
					showXHTML_tr_B('class="cssTrOdd"');
						showXHTML_td('nowrap', $MSG['title79'][$sysSession->lang]);
						showXHTML_td_B('nowrap');
						  showXHTML_input('radio', 'condition', array(1 => $MSG['title80'][$sysSession->lang],
						  											  2 => $MSG['title81'][$sysSession->lang] . '<span id="age_interval"></span>',
						  											  3 => $MSG['title82'][$sysSession->lang])
						  											  ,1,'', "<br>\n");
						showXHTML_td_E();
						showXHTML_td('', $MSG['title83'][$sysSession->lang]);
					showXHTML_tr_E();

					showXHTML_tr_B('class="cssTrEvn"');
						showXHTML_td_B('colspan="3" nowrap align="center"');
						  	showXHTML_input('button', '', $MSG['title11'][$sysSession->lang], '', 'class="cssBtn" onclick="check_data();"');
							showXHTML_input('reset','btnImp',$MSG['title22'][$sysSession->lang],'','onclick="user_role(1);"');
						showXHTML_td_E();
					showXHTML_tr_E();

				showXHTML_table_E();
		showXHTML_tabFrame_E();
	showXHTML_body_E('');

?>