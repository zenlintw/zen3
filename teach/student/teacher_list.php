<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Saly Lin                                                                         *
	*		Creation  : 2004/05/7                                                                      *
	*		work for  : 老師/助教/講師列表                                                                                               *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       $Id: teacher_list.php,v 1.1 2010/02/24 02:40:31 saly Exp $                                                                                          *
	**************************************************************************************************/


	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/teacher_settutor.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');

	$sysSession->cur_func = '300100600';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 設定車票 (set ticket)
	setTicket();

	$ticket_create = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Create' . $sysSession->username);
	$ticket_edit   = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Edit'   . $sysSession->username);
	$ticket_delete = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Delete' . $sysSession->username);

	//  每頁有幾筆
	$page_num = max(sysPostPerPage, 1);

	$icon_up = '<img src="/theme/default/teach/dude07232001up.gif" border="0" align="absmiddl">';
	$icon_dn = '<img src="/theme/default/teach/dude07232001down.gif" border="0" align="absmiddl">';

	$lang = strtolower($sysSession->lang);

	$tmp_colspan = 5;

	$_POST['sby1'] = intval($_POST['sby1']);
	switch ($_POST['sby1'])
	{
		case 2 :
			$sortby = 'a.first_name, a.last_name';
			break;
		case 3 :
			$sortby = 'level';
			break;
		default:
			$_POST['sby1'] = 1;
			$sortby = 'b.username';
	}
	$_POST['oby1'] = trim($_POST['oby1']);
	$orderby = (in_array($_POST['oby1'], array('asc', 'desc'))) ? $_POST['oby1'] : 'asc';

	$self_level = aclCheckRole($sysSession->username, ($sysRoles['teacher']|$sysRoles['instructor']|$sysRoles['assistant']), $sysSession->course_id, true) &
				  ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']);
	$self_level = array_search($self_level, $sysRoles);

	$sqls = str_replace('%COURSE_ID%', $sysSession->course_id, $Sqls['get_course_teacher_level']);

	if ($_POST['cond_type'] != ''){

		$type = intval($_POST['cond_type']);

		$query_txt = trim($_POST['queryTxt']);
		$query_txt1 = htmlspecialchars(stripslashes($query_txt));

		if ($query_txt != '' && $query_txt != $MSG['query_teacher'][$sysSession->lang]){
    		$query_txt = escape_LIKE_query_str(addslashes($query_txt));

			switch ($type){
				case 0:
					$query = " and a.username like '%" . $query_txt . "%' ";
					break;
				case 1:
				    $query = ' and if(a.first_name REGEXP "^[0-9A-Za-z _-]$" && a.last_name REGEXP "^[0-9A-Za-z _-]$", concat(a.first_name, " ", a.last_name), concat(a.last_name, a.first_name)) LIKE "%' . $query_txt . '%" ';
					break;
				case 2:
					$query = " and a.email like '%" . $query_txt . "%' ";
					break;
			}
		}
	}

	// 顯示使用
	$sqls .= "{$query} group by b.username,level order by {$sortby} {$orderby}";
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	$RS = $sysConn->Execute($sqls);
	$total_item  = $RS ? $RS->RecordCount() : 0;
	$total_page  = max(1, ceil($total_item / $page_num));
	$cur_page    = isSet($_POST['page_no']) ? max(0, min($_POST['page_no'], $total_page)) : min(1, $total_page);
	$limit_begin = (($cur_page-1) * $page_num);
   	if ($cur_page != 0) $RS = $sysConn->SelectLimit($sqls,$page_num,$limit_begin);

	$js = <<< BOF
 // //////////////////////////////////////////////////////////////////////////
	var theme = "{$sysSession->theme}";
	var ticket2 = "{$ticket_edit}";
	var ticket3 = "{$ticket_delete}";

	var lang = "{$lang}";
	var nowIdx = 0, maxIdx = 0;
	var nowIdx2 = 0;
	var listNum = 10;   // 每頁列出幾筆資料

	var cur_page = {$cur_page};
	var total_page = {$total_page};
	var queryTxt = "{$query_txt}";

 // //////////////////////////////////////////////////////////////////////////

	function checkData(type) {
		var obj = document.delForm;
        var nodes = obj.getElementsByTagName('input');
        var ret = '';
        var msg = '';

		obj.state.value = type;
		msg = "{$MSG['title8'][$sysSession->lang]}";

		if (obj == null) return false;

		//  檢查是否有勾選資料 (begin)
	    for(var i=1; i<nodes.length-1; i++){
		   if (nodes.item(i).type == 'checkbox' && nodes.item(i).checked){
			   if (nodes.item(i).value != ''){

			      ret += (nodes.item(i).value + ',');
			   }
			}
	    }
		//  檢查是否有勾選資料 (end)

        if (ret.length == 0){
            alert(msg);
            return false;
	    }else{
		    ret = ret.replace(/,$/, '');
            obj.user_id.value=ret;
            obj.ticket.value = ticket3;
        }

        if (confirm("{$MSG['title41'][$sysSession->lang]}")){
        	obj.action = 'teacher_save.php';
            obj.submit();
        }

	}

	var MSG_SELECT_ALL    = "{$MSG['select_all'][$sysSession->lang]}";
	var MSG_SELECT_CANCEL = "{$MSG['cancel_all'][$sysSession->lang]}";
    /**
	 * 同步全選或全消的按鈕與 checkbox
	 * @version 1.0
	 **/
	var nowSel = false;
	function selected_box() {
		var obj  = document.getElementById("ckbox");
		var btn1 = document.getElementById("btnSel1");
		if ((obj == null) || (btn1 == null) ) return false;
		nowSel = !nowSel;

		obj.checked = nowSel;
		btn1.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;

		nodes = document.getElementsByTagName("input");
		for (var i = 0; i < nodes.length; i++) {
			attr = nodes[i].getAttribute("exclude");
			if ((nodes[i].type == "checkbox") && (attr == null))
				nodes[i].checked = nowSel;
      }

	   var obj = document.getElementById('TeacherList');
	   obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[1].cells[0].innerHTML;
	}

	function chgPageSort(val) {
		var obj = document.delForm;

		if (obj.sby1.value == val)
		{
			obj.oby1.value = (obj.oby1.value == "asc") ? "desc" : "asc";
		}
		else
		{
			obj.sby1.value = val;
		}

		obj.action = "teacher_list.php";
		obj.submit();
	}

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
		obj.role.value = val2;
		obj.submit();
	}

	function selUser(val){
		var obj = null,nodes = null;
		var total_num = 0,cnt = 0,attr = null;

		obj = document.getElementById("TeacherList");
		nodes = obj.getElementsByTagName("input");

		for (var i = 0; i < nodes.length; i++) {
			attr = nodes[i].getAttribute("exclude");
			if ((nodes[i].type == "checkbox") && (attr == null)) {
				total_num++;
				if (nodes[i].checked) cnt++;
			}
		}

		nowSel = (total_num == cnt);
		document.getElementById("ckbox").checked = nowSel;

		var btn1 = document.getElementById("btnSel1");
		btn1.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;

		var obj = document.getElementById('TeacherList');
		obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[1].cells[0].innerHTML;
	}

	function QueryTA(){
		var obj = document.delForm;
		obj.action = 'teacher_list.php';
		obj.submit();

	}

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
		obj.action = 'teacher_list.php';
		obj.submit();
	}

	window.onload = function () {
		var obj = document.getElementById('TeacherList');
	   	obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[1].cells[0].innerHTML;
	};

BOF;

    // SHOW_PHONE_UI 常數定義於 /mooc/teach/studnet/teacher_list.php
    if (defined('SHOW_PHONE_UI') && SHOW_PHONE_UI === 1) {
        $datas = array();
        if ($RS->RecordCount() > 0){
            while ($users = $RS->FetchRow())
            {
                $users['level'] = array_search($users['level'], $sysRoles);
                $users['realname'] = checkRealname($users['first_name'],$users['last_name']);
                $users['role'] = $MSG[$users['level']][$sysSession->lang];
                $datas[] = $users;
            }
        }

        // assign
        $smarty->assign('inlineJS', $js);
        $ticket = md5($sysSession->ticket . 'Create' . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
        $smarty->assign('ticket', $ticket);
        $smarty->assign('self_level', $self_level);
        $smarty->assign('datalist', $datas);

        $smarty->display('common/tiny_header.tpl');
        $smarty->display('common/course_header.tpl');
        $smarty->display('phone/teach/student/teacher_list.tpl');
        $smarty->display('common/tiny_footer.tpl');
        exit;
    }

	// 開始呈現 HTML
	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/teach/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();

		showXHTML_table_B('width="650" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B();
				showXHTML_td_B();
					$ary[] = array($MSG['title39'][$sysSession->lang], 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E();
			showXHTML_tr_E();
			showXHTML_tr_B();
				showXHTML_td_B('valign="top" id="CGroup" ');

					showXHTML_form_B('style="display:inline;" method="post" ', 'delForm');
						showXHTML_input('hidden', 'ticket' , '', '', '');
						showXHTML_input('hidden', 'sby1'   , $_POST['sby1'], '', 'id="sby1"');
					    showXHTML_input('hidden', 'oby1'   , $orderby, '', 'id="oby1"');
					    showXHTML_input('hidden', 'user_id', '', '', '');
					    showXHTML_input('hidden', 'state'  , '', '', '');
					    showXHTML_input('hidden', 'page_no', '', '', '');

						showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" id="TeacherList" class="cssTable"');
							showXHTML_tr_B('class="cssTrHead"');
								showXHTML_td_B('colspan="' . $tmp_colspan . '" ');
									showXHTML_table_B('width="100%" border="0" cellspacing="0" cellpadding="0"');
										//  查詢教師
										showXHTML_tr_B();
											echo $MSG['teacher_query'][$sysSession->lang];

											if ($query_txt == '') $query_txt1 = $MSG['query_teacher'][$sysSession->lang];
									      	showXHTML_input('text', 'queryTxt', $query_txt1, '', 'id="queryTxt" class="cssInput" onclick="this.value=\'\'"');
                                                                                echo '&nbsp;';
									      	$role_array = array(0=>$MSG['user_account'][$sysSession->lang],
									      			    		1=>$MSG['real_name'][$sysSession->lang],
									      						2=>'Email');

											if (strlen($type) == 0) $type = 0;

											showXHTML_input('select', 'cond_type', $role_array, $type, 'size="1" class="cssInput" ');

									      	echo '&nbsp;';
									      	showXHTML_input('button', '', $MSG['query'][$sysSession->lang], '', 'style="24" class="cssBtn" onclick="QueryTA()"');
									      	echo '&nbsp;';
										  	showXHTML_input('button', '', $MSG['add_teacher'][$sysSession->lang], '', 'id="addBtn1" onclick="location.replace(\'teacher_new_course.php\')" class="cssBtn"');
										showXHTML_tr_E();
									showXHTML_table_E();
								showXHTML_td_E();
							showXHTML_tr_E();

							showXHTML_tr_B('class="cssTrEvn"');
								showXHTML_td_B('colspan="' . $tmp_colspan . '"  id="toolbar1"');
									showXHTML_input('button', '', $MSG['select_all'][$sysSession->lang], '', ' id="btnSel1" class="cssBtn" onclick="selected_box();"');

									echo $MSG['page3'][$sysSession->lang] , '&nbsp;';

									$P = range(0, $total_page);
									$P[0] = $MSG['all'][$sysSession->lang];

            			    		showXHTML_input('select', '', $P, $cur_page, 'size="1" onchange="act(this.value);" class="cssInput"');
									showXHTML_input('button', 'firstBtn1', $MSG['first'][$sysSession->lang] , '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==1) || ($cur_page==0))?                     'disabled="true" ' : 'onclick="act(-1);"'));
									showXHTML_input('button', 'prevBtn1' , $MSG['prev'][$sysSession->lang]  , '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==1) || ($cur_page==0))?                     'disabled="true" ' : 'onclick="act(-2);"'));
									showXHTML_input('button', 'nextBtn1' , $MSG['next'][$sysSession->lang]  , '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==0) ||  ($cur_page==$total_page))?          'disabled="true" ' : 'onclick="act(-3);"'));
									showXHTML_input('button', 'lastBtn1' , $MSG['last'][$sysSession->lang]  , '', 'id="firstBtn1" class="cssBtn" ' . ((($cur_page==0) ||  ($cur_page==$total_page))?          'disabled="true" ' : 'onclick="act(-4);"'));
									showXHTML_input('button', ''         , $MSG['delete'][$sysSession->lang], '', 'class="cssBtn" onclick="checkData(\'D\');"');
								showXHTML_td_E();
							showXHTML_tr_E();

							showXHTML_tr_B('class="cssTrHead"');
	//							showXHTML_td('align="center" nowrap ', $MSG['teach_course_name'][$sysSession->lang]);
	                            showXHTML_td_B('align="left" nowrap="noWrap" ');
									showXHTML_input('checkbox', '', '', '', 'id = "ckbox" onclick="selected_box();" " exclude="true"' . 'title=' . $MSG['select_all'][$sysSession->lang]);
								showXHTML_td_E();

								showXHTML_td_B(' align="center" id="user_account" nowrap="noWrap" title="' . $MSG['user_account'][$sysSession->lang] . '"');
									echo '<a class="cssAnchor" href="javascript:chgPageSort(1);" >',
									      $MSG['user_account'][$sysSession->lang],
									      ($_POST['sby1'] == 1 ? ($orderby == 'desc' ? $icon_dn : $icon_up) : ''),
									     '</a>';
								showXHTML_td_E();

								showXHTML_td_B(' align="center" id="real_name" nowrap="noWrap" title="' . $MSG['real_name'][$sysSession->lang] . '"');
									echo '<a class="cssAnchor" href="javascript:chgPageSort(2);" >',
									     $MSG['real_name'][$sysSession->lang],
									     ($_POST['sby1'] == 2 ? ($orderby == 'desc' ? $icon_dn : $icon_up) : ''),
									     '</a>';
								showXHTML_td_E();

								showXHTML_td_B('align="center" id="msg_status" nowrap="noWrap" title="' . $MSG['status'][$sysSession->lang] . '"');
									echo '<a class="cssAnchor" href="javascript:chgPageSort(3);" >',
									     $MSG['status'][$sysSession->lang],
									     ($_POST['sby1'] == 3 ? ($orderby == 'desc' ? $icon_dn : $icon_up) : ''),
									     '</a>';
								showXHTML_td_E();

								showXHTML_td('align="center" nowrap ', $MSG['modify'][$sysSession->lang]);
							showXHTML_tr_E();

							if ($RS->RecordCount() > 0){
								while ($RS1 = $RS->FetchRow()){
									$RS1['level'] = array_search($RS1['level'], $sysRoles);
									$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';

									showXHTML_tr_B($col);
										showXHTML_td_B('width="10"');
											switch ($RS1['level']){
												case 'teacher':
													echo '&nbsp;';
													break;
												case 'instructor':
												case 'assistant':
													if ($self_level == 'teacher'){
														showXHTML_input('checkbox', 'ckUname[]', $RS1['username'] . '@' . $RS1['level'], '', 'onclick="selUser(this.checked)"');
													}
													break;
											}
										showXHTML_td_E();
										showXHTML_td('', $RS1['username']);
										showXHTML_td('', checkRealname($RS1['first_name'],$RS1['last_name']));
										showXHTML_td('align="center"', $MSG[$RS1['level']][$sysSession->lang]);
										showXHTML_td_B('align="center"');
											switch ($RS1['level']){
												case 'teacher':
													echo '&nbsp;';
													break;
												case 'instructor':
												case 'assistant':
													if ($self_level == 'teacher'){
														showXHTML_input('button', 'ModifyBtn', $MSG['modify'][$sysSession->lang], '', 'onclick="editTeacher(\'' . $RS1['username'] . '\',\'' . $RS1['level']  . '\')"');
													}
													break;
											}
										showXHTML_td_E();
									showXHTML_tr_E();


								}
							}else{
								showXHTML_tr_B('class="cssTrEvn"');
									showXHTML_td('align="center" colspan="' . $tmp_colspan . '"  id="toolbar2"', $MSG['no_keyword'][$sysSession->lang]);
								showXHTML_tr_E();
							}

							showXHTML_tr_B('class="cssTrEvn"');
								showXHTML_td_B('colspan="' . $tmp_colspan . '"  id="toolbar2"');
								showXHTML_td_E();
							showXHTML_tr_E();
						showXHTML_table_E();
					showXHTML_form_E();

				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E();

		showXHTML_form_B('action="teacher_modify.php" method="post"', 'actForm');
			showXHTML_input('hidden', 'ticket'  , '', '', '');
			showXHTML_input('hidden', 'username', '', '', '');
			showXHTML_input('hidden', 'role'    , '', '', '');
			showXHTML_input('hidden', 'page_no' , $cur_page, '', '');
		showXHTML_form_E();

	showXHTML_body_E();
?>
