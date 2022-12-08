<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2004/05/7                                                                      *
	*		work for  : 老師/助教/講師列表                                                                                               *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       $Id: teacher_list.php,v 1.1 2010/02/24 02:38:48 saly Exp $                                                                                          *
	**************************************************************************************************/


	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
        require_once(sysDocumentRoot . '/lang/course_manage.php');
	require_once(sysDocumentRoot . '/lang/teacher_list.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
    
	$sysSession->cur_func = '300100600';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	// 設定車票 (set ticket)
	setTicket();

	$ticket_create = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Create' . $sysSession->username);
	$ticket_edit   = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Edit'   . $sysSession->username);
	$ticket_delete = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Delete' . $sysSession->username);
	$ticket_login  = md5($sysSession->ticket    . $_COOKIE['idx']          . $sysSession->school_id         . $sysSession->school_name);

	//  每頁有幾筆
	$page_num = sysPostPerPage;

	$icon_up = '<img src="/theme/' . $sysSession->theme . '/teach/dude07232001up.gif" border="0" align="absmiddl">';
	$icon_dn = '<img src="/theme/' . $sysSession->theme . '/teach/dude07232001down.gif" border="0" align="absmiddl">';

	$lang = strtolower($sysSession->lang);

	if ($_SERVER['PHP_SELF'] == '/academic/teacher/teacher_list.php')
	{
		$tmp_colspan = "6";
		$hidden_login = false;
		$form_action = 'teacher_list.php';
	}
	else
	{
		$tmp_colspan = "5";
		$hidden_login = true;
		$form_action = 'teacher_show_list.php';
	}

	$cond_type = min(3, max(0, $_POST['cond_type']));
	$orderby   = $_POST['oby1'] == 'desc' ? 'desc' : 'asc';
	$sortArr   = array('', 'b.username', 'a.first_name '.$orderby.',a.last_name', 'level');
	$sortby    = min(3, max(1, $_POST['sby1']));

	$sqls = $Sqls['get_all_teacher_level'];

	if ($_SERVER['PHP_SELF'] == '/academic/teacher/teacher_list.php')
	{
		$sqls .= ' and c.status not in (0,9) ';
	}
	else
	{
		$sqls .= ' and c.status not in (9) ';
	}

	$query_txt = $_POST['queryTxt'] == $MSG['query_teacher'][$sysSession->lang] ? '' : trim($_POST['queryTxt']);
	$query_txt1 = stripslashes($query_txt);

	if ($query_txt != '')
	{
		$query_txt = escape_LIKE_query_str(addslashes($query_txt));

		switch ($cond_type){
			case 0:
				$query = " and a.username like '%" . $query_txt . "%'  ";
				break;
			case 1:
				if ($sysSession->lang == 'Big5' || $sysSession->lang == 'GB2312')
						$query = sprintf(' and CONCAT(a.last_name, a.first_name) like "%%%s%%"', $query_txt);
					else
						$query = sprintf(' and CONCAT(a.first_name, a.last_name) like "%%%s%%"', $query_txt);
				break;
			case 2:
				$query = " and a.email like '%" . $query_txt . "%'  ";
				break;

			case 3:  // 課程名稱
				if (! empty($query_txt)){
					if ($_SERVER['PHP_SELF'] == '/academic/teacher/teacher_list.php'){
						$query1 = "course_id>10000000 and caption like '%" . $query_txt . "%'  " .
									" and kind ='course' AND status not in (0,9) ";
					}else{
						$query1 = "course_id>10000000 and caption like '%" . $query_txt . "%'  " .
									" and kind ='course' AND status not in (9) ";
					}
					$RS = dbGetStMr('WM_term_course','course_id',$query1, ADODB_FETCH_ASSOC);

					$str_courses = '';

					if ($RS->RecordCount() > 0){
						while ($RS1 = $RS->FetchRow()){
							$str_courses .= $RS1['course_id'] . ',';
						}
						$str_courses = substr($str_courses, 0, -1);
						$query = ' and c.course_id in (' . $str_courses . ') ';
					}else{
						// 查無符合關鍵字 的課程名稱
						$query = ' and c.course_id in (0) ';
					}
				}
				break;
		}
	}

	// 顯示使用
	$sqls .= $query . ' group by b.username,level order by ' . $sortArr[$sortby] . ' ' . $orderby;
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	$RS = $sysConn->Execute($sqls);
	$total_item = $RS->RecordCount();
	$total_page = ceil($total_item / $page_num);
	if ($_POST['page_no'] == ''){
		if ($total_page > 0){
			$cur_page = 1;
		}else{
			$cur_page = 0;
		}

		if ($cur_page < 0 || $cur_page > $total_page) $cur_page = 1;
	    $limit_begin = (($cur_page-1) * $page_num);

	}else{
	    if (($_POST['page_no'] >  0)){
		    $cur_page = intval($_POST['page_no']);

			if ($cur_page < 0 || $cur_page > $total_page) $cur_page = 1;
		    $limit_begin = (($cur_page-1)* $page_num);

		}else if ($_POST['page_no'] == 0){
			$cur_page = 0;
		}
	}

	if ($cur_page > 0){
    	$RS = $sysConn->SelectLimit($sqls,$page_num,$limit_begin);
	}else if ($cur_page == 0){
		$RS = $sysConn->Execute($sqls);
	}

	$js = <<< BOF
 // //////////////////////////////////////////////////////////////////////////
	var theme             = "{$sysSession->theme}";
	var ticket1           = "";
	var ticket2           = "{$ticket_edit}";
	var ticket3           = "{$ticket_delete}";

	var lang              = "{$lang}";
	var nowIdx            = 0, maxIdx = 0;
	var nowIdx2           = 0;
	var listNum           = 10;   // 每頁列出幾筆資料

	var orderby           = "{$orderby}";

	var cur_page          = {$cur_page};

	var total_page        = {$total_page};

	var msg_explode       = "{$MSG['explode'][$sysSession->lang]}";
    var msg_close_explode = "{$MSG['close_explode'][$sysSession->lang]}";
    var form_action       = "{$form_action}";

    var queryTxt          = "{$query_txt1}";

// //////////////////////////////////////////////////////////////////////////
	function chgPageSort(val) {
		var obj = document.delForm;

		obj.sby1.value = val;

		if (orderby == 'asc'){
			obj.oby1.value = 'desc';
		}else{
			obj.oby1.value = 'asc';
		}

		if (queryTxt == '') obj.queryTxt.value = '';

		obj.action = form_action;

		obj.submit();
	}
// //////////////////////////////////////////////////////////////////////////
	function editTeacher(val,val2) {
      var type = '';

      switch (val2){
         case "{$MSG['teacher'][$sysSession->lang]}":
               type = 'Teacher';
               break;
         case "{$MSG['assistant'][$sysSession->lang]}":
               type = 'Assistant';
               break;
         case "{$MSG['instructor'][$sysSession->lang]}":
               type = 'Instructor';
               break;
      }

		var obj = document.getElementById("actForm");
		if (obj == null) return false;
		obj.ticket.value = ticket2;
		obj.username.value = val;
		obj.level.value = val2;
		obj.submit();
	}


// //////////////////////////////////////////////////////////////////////////

	function act(val) {

		var obj = document.delForm;

		switch(val){
    		case -1:
    			obj.page_no.value = 1;
    			break;
    		case -2:
    			obj.page_no.value = (cur_page-1);
    			break;
    		case -3:
    			obj.page_no.value = (cur_page+1);
    			break;
    		case -4:
    			obj.page_no.value = (total_page);
    			break;
    		default:
    			obj.page_no.value = parseInt(val);
		}

		if (queryTxt == '') obj.queryTxt.value = '';

		obj.action = form_action;

		obj.submit();
	}
// //////////////////////////////////////////////////////////////////////////
	function createTeacher() {
		var obj = document.getElementById("actNew");
		if (obj == null) return false;
		obj.ticket.value = ticket1;
		obj.submit();
	}
// //////////////////////////////////////////////////////////////////////////
	/**
	 * 將 授課列表 的 內容 展開 / 收攏
	 * idx: 第幾筆資料
	 * type: 是要展開 (1) 或 收攏 (0)
	 * content : 教師 授課的 內容
	 **/
    function ta_explode(idx,type){

        var obj = document.getElementById("rec"+idx);

        /*
         *展開 收攏的 小圖片
        */
        var obj2 = document.getElementById("imgsrc"+idx);

        if (type == 1) {  //  open

			obj.style.display = "block";
            obj2.src='/theme/' + theme + '/academic/minus.gif';
            obj2.title=msg_close_explode;
            obj2.onclick = function () {
                ta_explode(idx, 0);
            };

        }else if (type == 0){
            obj.style.display = "none";
            obj2.src ='/theme/' + theme + '/academic/plus.gif';
            obj2.title=msg_explode;
            obj2.onclick = function () {
                ta_explode(idx, 1);
            };

        }

    }
// //////////////////////////////////////////////////////////////////////////
    /**
    * 查詢 keyword
    */
    function QueryTA(){
		var obj = document.delForm;
		obj.action = form_action;
		obj.submit();

	}

// //////////////////////////////////////////////////////////////////////////
	/**
    * 顯示修課記錄
    */
	function ShowDetail(user, val,role) {

		var obj = document.getElementById("CourFm");

		if ((typeof(obj) != "object") || (obj == null)) return false;
		obj.user.value = user;
		obj.msgtp.value = val;
		obj.user_role.value = role;
		obj.submit();

	}

// //////////////////////////////////////////////////////////////////////////
	function LoginTeacher(val) {

        var obj = document.getElementById("actLogin");
		if (obj == null) return false;
        obj.username.value = val;
        obj.submit();
   	}
// //////////////////////////////////////////////////////////////////////////

	window.onload = function () {
		var obj = null;

		var obj = document.getElementById('TeacherList');
	   	obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[1].cells[0].innerHTML;
	};

BOF;

	// 開始呈現 HTML
	showXHTML_head_B($MSG['teacher_list_title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B('');

		showXHTML_table_B('width="650" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					if ($_SERVER['PHP_SELF'] == '/academic/teacher/teacher_list.php'){
				      $title = $MSG['teacher_list_title'][$sysSession->lang];
				    }else{
				      $title = $MSG['title39'][$sysSession->lang];
				    }
					$ary[] = array($title, 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup" ');

					showXHTML_form_B('style="display:inline;" method="post" ', 'delForm');
						showXHTML_input('hidden', 'ticket', '', '', '');
						showXHTML_input('hidden', 'sby1', '', '', 'id="sby1"');
					    showXHTML_input('hidden', 'oby1', $orderby, '', 'id="oby1"');
					    showXHTML_input('hidden', 'page_no', '', '', '');
					    showXHTML_input('hidden', 'cond_type', $cond_type, '', '');


						showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="TeacherList" class="cssTable"');
							showXHTML_tr_B('class="cssTrHead"');
								showXHTML_td_B('colspan="' . $tmp_colspan . '" ');
									showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0"');
										//  查詢教師
										showXHTML_tr_B('');
											echo $MSG['teacher_query'][$sysSession->lang];

											if ($query_txt == '')
												$show_txt = $MSG['query_teacher'][$sysSession->lang];
											else
												$show_txt = htmlspecialchars($query_txt1);

									      	showXHTML_input('text', 'queryTxt', $show_txt, '', 'id="queryTxt" class="cssInput" onclick="this.value=\'\'"');

									      	$role_array = array(0=>$MSG['user_account'][$sysSession->lang],
									      			    		1=>$MSG['real_name'][$sysSession->lang],
									      						2=>'Email',
									      						3=>$MSG['course_name'][$sysSession->lang]
									      						);


											showXHTML_input('select', 'cond_type', $role_array, $cond_type, 'size="1" class="cssInput" ');

									      	echo '&nbsp;';
									      	showXHTML_input('button', '', $MSG['query'][$sysSession->lang], '', 'style="24" class="cssBtn" onclick="QueryTA()"');
									      	echo '&nbsp;&nbsp;';

									      	if ($_SERVER['PHP_SELF'] == '/academic/teacher/teacher_list.php'){
										  		showXHTML_input('button', '', $MSG['add_teacher'][$sysSession->lang], '', 'id="addBtn1" onclick="createTeacher();" class="cssBtn"');
										  	}
										showXHTML_tr_E('');
									showXHTML_table_E('');
								showXHTML_td_E('');
							showXHTML_tr_E('');

							showXHTML_tr_B('class="cssTrEvn"');
								showXHTML_td_B('colspan="' . $tmp_colspan . '"  id="toolbar1"');

									echo $MSG['page3'][$sysSession->lang] , '&nbsp;';

									$P = range(0, $total_page);
									$P[0] = $MSG['all'][$sysSession->lang];

            			    		showXHTML_input('select', '', $P, $cur_page, 'size="1" onchange="act(this.value);" class="cssInput"');

									showXHTML_input('button', 'firstBtn1', $MSG['first'][$sysSession->lang], '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==1) || ($cur_page==0))?          'disabled="true" ' : 'onclick="act(-1);"'));

									showXHTML_input('button', 'prevBtn1', $MSG['prev'][$sysSession->lang], '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==1) || ($cur_page==0))?          'disabled="true" ' : 'onclick="act(-2);"'));

									showXHTML_input('button', 'nextBtn1', $MSG['next'][$sysSession->lang], '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==0) ||  ($cur_page==$total_page))?          'disabled="true" ' : 'onclick="act(-3);"'));

									showXHTML_input('button', 'lastBtn1', $MSG['last'][$sysSession->lang], '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==0) ||  ($cur_page==$total_page))?          'disabled="true" ' : 'onclick="act(-4);"'));

								showXHTML_td_E('');
							showXHTML_tr_E('');

							showXHTML_tr_B('class="cssTrHead"');

							    showXHTML_td_B(' align="center" id="user_account" nowrap="noWrap" title="' . $MSG['user_account'][$sysSession->lang] . '"');
	                                echo '<a class="cssAnchor" href="javascript:chgPageSort(1);" >';
	                                echo $MSG['user_account'][$sysSession->lang];
	                                echo ($sortby == 1) ? ($orderby == 'desc' ? $icon_dn : $icon_up) : '';
	                                echo '</a>';
	                            showXHTML_td_E('');

	                            showXHTML_td_B(' align="center" id="real_name" nowrap="noWrap" title="' . $MSG['real_name'][$sysSession->lang] . '"');
	                                echo '<a class="cssAnchor" href="javascript:chgPageSort(2);" >';
	                                echo $MSG['real_name'][$sysSession->lang];
	                                echo ($sortby == 2) ? ($orderby == 'desc' ? $icon_dn : $icon_up) : '';
	                                echo '</a>';
	                            showXHTML_td_E('');

	                            showXHTML_td_B('align="center" id="msg_status" nowrap="noWrap" title="' . $MSG['status'][$sysSession->lang] . '"');
	                                echo '<a class="cssAnchor" href="javascript:chgPageSort(3);" >';
	                                echo $MSG['status'][$sysSession->lang];
	                                echo ($sortby == 3) ? ($orderby == 'desc' ? $icon_dn : $icon_up) : '';
	                               echo '</a>';
	                            showXHTML_td_E('');

								showXHTML_td('align="center" nowrap ', $MSG['teach_course_name'][$sysSession->lang]);

								if ($_SERVER['PHP_SELF'] == '/academic/teacher/teacher_list.php'){

								   showXHTML_td('align="center" nowrap ', $MSG['title32'][$sysSession->lang]);
								   showXHTML_td('align="center" nowrap ', $MSG['modify'][$sysSession->lang]);

							    }else{
									showXHTML_td('align="center" nowrap ', $MSG['title44'][$sysSession->lang]);
								}

							showXHTML_tr_E('');

							$serial_no = 0;

							if ($RS->RecordCount() > 0){
								// while begin
								while ($RS1 = $RS->FetchRow()){

									$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';

									showXHTML_tr_B($col);

										showXHTML_td('', $RS1['username']);

										showXHTML_td_B('');
                                            // Bug#1263 真實姓名的顯示不按照個人語系，而按照個人姓名的設定 by Small 2006/12/28
                                            $realname = checkRealname($RS1['first_name'],$RS1['last_name']);

											echo '<div style="width: 100px; overflow:hidden;" title="' . htmlspecialchars($realname) . '">' . $realname . '</div>';

										showXHTML_td_E('');

										showXHTML_td('align="center"', $MSG[array_search($RS1['level'], $sysRoles)][$sysSession->lang]);

										showXHTML_td_B('align="left"');
											// 抓取 老師 教授的課程的 名稱 及 身份
/*
									        $query1 = " and d.role&" . $RS1['level'] . " ";
									        $cour_sqls = str_replace(array('%query1%', '%username%'),
									                                 array($query1, $RS1['username']),
									                                 $Sqls['get_teacher_name_status']);

									        if ($_SERVER['PHP_SELF'] == '/academic/teacher/teacher_list.php'){
									        	$cour_sqls .= ' and c.status not in (0,9)';
									        }else{
									    		$cour_sqls .= ' and c.status != 9';
									    	}

									        $C_RS = $sysConn->Execute($cour_sqls);
*/
//											$C_RS = dbGetCourses('C.caption, C.status', $RS1['username'], $RS1['level']);
											$C_RS = $sysConn->Execute(sprintf('select C.caption, C.status from WM_term_major AS M inner join WM_term_course AS C on C.course_id=M.course_id where M.username="%s" AND C.kind="course" AND ((M.role&%s and (C.status between 0 and 5))) ORDER BY case C.status when 0 then 8 else C.status end asc, C.course_id', $RS1['username'], $RS1['level'], ($RS1['level'] & ($sysRoles['assistant']|$sysRoles['instructor']|$sysRoles['teacher']))));
                                                                                        
											$total_course_name = '';
    
                                                                                        // 課程狀態
                                                                                        $CourseStatusList = array(
                                                                                            5 => $MSG['param_prepare'][$sysSession->lang],
                                                                                            1 => $MSG['param_open_a'][$sysSession->lang],
                                                                                            2 => $MSG['param_open_a_date'][$sysSession->lang],
                                                                                            3 => $MSG['param_open_n'][$sysSession->lang],
                                                                                            4 => $MSG['param_open_n_date'][$sysSession->lang],
                                                                                            0 => $MSG['param_close'][$sysSession->lang]
                                                                                        );

									        if ($C_RS){
									        	while ($C_RS1 = $C_RS->FetchRow()){
													$lang = getCaption($C_RS1['caption']);
    												$csname = $lang[$sysSession->lang];
													$total_course_name .= $csname;
                                                                                                        $courseStatus = $CourseStatusList[$C_RS1['status']];
                                                                                                        $isCourseStatus = preg_match('/^(.*)（.*）$/', $courseStatus, $match);
                                                                                                        if ($isCourseStatus) {
                                                                                                            $courseStatus = $match[1];
                                                                                                        } else {
                                                                                                            $courseStatus = $CourseStatusList[$C_RS1['status']];
                                                                                                        }
                                                                                                        $total_course_name .= ' (' . $courseStatus . ')<br>';
									        	}
									        }

									        $total_course_name = substr($total_course_name,0,-4);

									        $total_course_name1 = str_replace('<br>',"\r\n",$total_course_name);

											echo '<img id="imgsrc' . $serial_no . '" title="' . $MSG['explode'][$sysSession->lang] . '" src="/theme/' . $sysSession->theme . '/academic/plus.gif" width="9" height="15" align="absmiddle" ' .
			                                	 ' onclick="ta_explode(' . $serial_no . ',1);">' .
			                                	 '<div style="width: 300px; overflow:hidden;" title="' . htmlspecialchars($total_course_name1) . '">' .
			                                     '<span id="rec' . $serial_no . '" style="display: none;">' .
									              $total_course_name . 
									             '</span>' .
									             '</div>';

        								showXHTML_td_E('');

										if ($_SERVER['PHP_SELF'] == '/academic/teacher/teacher_list.php'){

											showXHTML_td_B('align="center"');
												showXHTML_input('button', 'LoginBtn', $MSG['title33'][$sysSession->lang], '', 'onclick="LoginTeacher(\'' . $RS1['username'] . '\')"');
											showXHTML_td_E('');

											showXHTML_td_B('align="center"');
												showXHTML_input('button', 'ModifyBtn', $MSG['modify'][$sysSession->lang], '', 'onclick="editTeacher(\'' . $RS1['username'] . '\',\'' . $RS1['level']  . '\')"');
											showXHTML_td_E('');

										}else{
											showXHTML_td_B('align="center"');
												echo '<a href="javascript:;" onclick="ShowDetail(\'' . $RS1['username'] . '\',2,\'' . $RS1['level']  . '\');return false;"><img src="/theme/' . $sysSession->theme . '/academic/icon_folder.gif" width="16" height="16" border="0" alt="' . $MSG['show_detail'][$sysSession->lang] . '"></a>';
											showXHTML_td_E('');
										}
									showXHTML_tr_E('');

									$serial_no++;
								}
							}else{

								showXHTML_tr_B('class="cssTrEvn"');
									showXHTML_td_B('align="center" colspan="' . $tmp_colspan . '$tmp_colspan"  id="toolbar2"');
										echo $MSG['no_keyword'][$sysSession->lang];
									showXHTML_td_E('');
								showXHTML_tr_E('');

							}

							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';

							showXHTML_tr_B($col);
								showXHTML_td_B('colspan="' . $tmp_colspan . '$tmp_colspan"  id="toolbar2"');
								showXHTML_td_E('');
							showXHTML_tr_E('');
						showXHTML_table_E('');
					showXHTML_form_E('');

				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');

		// 修改授課
		showXHTML_form_B('action="teacher_modify.php" method="post"', 'actForm');
			showXHTML_input('hidden', 'ticket', '', '', '');
			showXHTML_input('hidden', 'username', '', '', '');
			showXHTML_input('hidden', 'level', '', '', '');
			showXHTML_input('hidden', 'page_no', $cur_page, '', '');
    	    showXHTML_input('hidden', 'cond_type', $cond_type, '', '');
    	    showXHTML_input('hidden', 'queryTxt', htmlspecialchars($query_txt1), '', '');
		showXHTML_form_E('');

		// 新增授課
		showXHTML_form_B('action="teacher_new_course.php" method="post"', 'actNew');
			showXHTML_input('hidden', 'ticket', '', '', '');
		showXHTML_form_E('');

		// 登入
		showXHTML_form_B('action="/academic/teach_relogin.php" method="post"', 'actLogin');
			showXHTML_input('hidden', 'username', '', '', '');
			showXHTML_input('hidden', 'ticket', $ticket_login, '', '');
		showXHTML_form_E('');

		//  老師修課記錄
        showXHTML_form_B('action="teacher_course.php" method="post" enctype="multipart/form-data" style="display:none"', 'CourFm');
    	    showXHTML_input('hidden', 'msgtp', '', '', '');
    	    showXHTML_input('hidden', 'user', '', '', '');
    	    showXHTML_input('hidden', 'user_role', '', '', '');
    	    showXHTML_input('hidden', 'page_no', $cur_page, '', '');
    	    showXHTML_input('hidden', 'cond_type', $cond_type, '', '');
    	    showXHTML_input('hidden', 'queryTxt', htmlspecialchars($query_txt1), '', '');
    	    showXHTML_input('hidden', 'back_href', $form_action, '', '');
        showXHTML_form_E();

	showXHTML_body_E('');
?>
