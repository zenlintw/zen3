<?php
   /**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2003/09/15                                                                      *
	*		work for  : 修改教師                                                                        *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       $Id: teacher_modify.php,v 1.1 2010/02/24 02:38:48 saly Exp $                                                                                          *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/teacher_list.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	
	$sysSession->cur_func = '300100300';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$actType      = '';
	$title        = '';
    $teacher_name = '';

    /*
    * 排序
    */
	$sortArr = array('', 'course_id', 'caption');
	$sortby  = min(2, max(1, $_POST['sortby']));
	$order   = $_POST['order'] == 'desc' ? 'desc' : 'asc';

    // 新增教師
	if (empty($_POST['ticket'])) {
		$actType = 'Create';
		$title = $MSG['add_teacher'][$sysSession->lang];
	}

	// 修改教師
	$ticket = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Edit' . $sysSession->username);

	if (trim($_POST['ticket']) == $ticket) {
		$actType = 'Edit';

		$title =  $MSG['title2'][$sysSession->lang];

		if (!preg_match(Account_format, $_POST['username']) ||
			strlen($_POST['username']) < sysAccountMinLen ||
			strlen($_POST['username']) > sysAccountMaxLen)
			header('Location: /academic/teacher/teacher_list.php');

        // 抓取教師的姓名
		list($first_name,$last_name) = dbGetStSr('WM_user_account', 'first_name,last_name', "username='{$_POST['username']}'", ADODB_FETCH_NUM);
		$teacher_name = checkRealname($first_name,$last_name);

		// 抓取教師 教授中課程列表
		$RS2 = dbGetCourses('C.course_id,C.caption', $_POST['username'], intval($_POST['level']), $sortArr[$sortby] . ' ' . $order);
        if ($RS2->RecordCount() == 0){
             header('Location: /academic/teacher/teacher_list.php');
        }
	}

	if (empty($actType)) {
	   wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], '拒絕存取!');
	   die($MSG['illege_access'][$sysSession->lang]);
	}

	$js = <<< BOF

	var ticket1           = "";

	var ticket            = "{$_POST['ticket']}";

    var MSG_SELECT_ALL    = "{$MSG['select_all'][$sysSession->lang]}";
    var MSG_SELECT_CANCEL = "{$MSG['cancel_all'][$sysSession->lang]}";

	window.onload = function(){
      var obj = document.getElementById('mainTable');
      obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[3].cells[0].innerHTML;
    };

    /**
	 * 單獨點課程
	 **/
	function selCourse(obj) {
		var nodes = null, attr = null;
		var isSel = "false";
		var cnt   = 0;
        var m     = 0;

        var obj2  = document.getElementById("actForm");
		var nodes = obj2.getElementsByTagName('input');
		if ((nodes == null) || (nodes.length == 0)) return false;

        for(var i=1; i<nodes.length-1; i++){

		    if ((nodes.item(i).type == 'checkbox') && (nodes.item(i).value != '')){
                m++;
                if (nodes.item(i).checked) cnt++;
		    }
	    }

		// m = (m > 0) ? m - 1 : 0;


	    document.getElementById("ckbox").checked = (m == cnt);

        /*
		 * button 顯示 全選 或 全消 begin
		 */
		var btn1 = document.getElementById("btnSel1");

		if (m == cnt){
            btn1.value = MSG_SELECT_CANCEL;
		}else{
		    btn1.value = MSG_SELECT_ALL;
		}

        var obj = document.getElementById('mainTable');
	    obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[3].cells[0].innerHTML;

	}

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

		selFunc(obj.checked);
	}

	/**
	 * 全選或全消
	 **/
	function selFunc(actType) {

        nodes = document.getElementsByTagName("input");
		for (var i = 0; i < nodes.length; i++) {
		    attr = nodes[i].getAttribute("exclude");
            if ((nodes[i].type == "checkbox") && (attr == null)){
                nodes[i].checked = actType;

                selCourse(nodes[i]);
            }
        }

        var btn1 = document.getElementById("btnSel1");

        /*
        *  全選
        */
        if (actType){
            btn1.value = MSG_SELECT_CANCEL;
        }else{
            btn1.value = MSG_SELECT_ALL;
        }


	    var obj = document.getElementById('mainTable');
	    obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[3].cells[0].innerHTML;


	}
	function checkData(type) {
		var obj = document.actForm;
        var nodes = obj.getElementsByTagName('input');
        var ret = '';
        var msg = '';

        obj.state.value = type;

        if (type == 'M'){
             msg = "{$MSG['title28'][$sysSession->lang]}";
        }else{
             msg = "{$MSG['title8'][$sysSession->lang]}";
        }
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
            obj.course_id.value=ret;
        }

        if (type == 'D'){
            if (confirm("{$MSG['title41'][$sysSession->lang]}")){
                obj.submit();
            }
        }else if (type == 'M'){
            obj.submit();
        }
	}

	function goList() {
		var obj = document.getElementById("actForm");
		obj.action = 'teacher_list.php';
		obj.submit();
	}

	function createTeacher(val) {
		var obj = document.getElementById("actNew");
		if (obj == null) return false;
		obj.ticket.value = ticket1;
		obj.username.value = val;
		obj.submit();
	}

	function selectItem(selAll){
	   var obj = document.getElementById('mainTable');
	   var nodes = obj.getElementsByTagName('input');
	   var m=0;
	   var cnt=0;
	   for(var i=0; i<nodes.length; i++){
		   if (nodes.item(i).type == 'checkbox'){
		       m++;
			   nodes.item(i).checked = selAll;

			    if (selAll){
			        cnt++;
			    }
		    }
	   }

	   /*
		* button 顯示 全選 或 全消 begin
	   */
	   var btn1 = document.getElementById("btnSel1");
	   if (m == cnt){
          btn1.value = MSG_SELECT_CANCEL;
	   }else{
		  btn1.value = MSG_SELECT_ALL;
	   }

       var obj = document.getElementById('mainTable');
	   obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[3].cells[0].innerHTML;

   }

   /*
    * 標題排序
   */
   function chgPageSort(val) {
        var obj = document.getElementById("actForm");
		obj.order.value = obj.order.value == 'asc' ? 'desc' : 'asc';
        obj.sortby.value = val;
		obj.ticket.value = ticket;
        obj.action = 'teacher_modify.php';
        obj.submit();
	}

BOF;
	// 開始呈現 HTML
	showXHTML_head_B($title);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B('');

		showXHTML_table_B('border="0" width="600" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary[] = array($title, 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E();
			showXHTML_tr_E();
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" width="760" id="CGroup" ');
					showXHTML_form_B('method="post" action="teacher_save.php" style="display:inline;"', 'actForm');
					$ticket = md5($sysSession->ticket . $actType . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
					showXHTML_input('hidden', 'ticket', $ticket);
					showXHTML_input('hidden', 'username', $_POST['username']);
					showXHTML_input('hidden', 'old_role', $_POST['level']);
					showXHTML_input('hidden', 'ticket2', $_POST['ticket']);
					showXHTML_input('hidden', 'course_id', '');
					showXHTML_input('hidden', 'state', '');
					showXHTML_input('hidden', 'sortby', $sortby);
					showXHTML_input('hidden', 'order', $order);
					showXHTML_input('hidden', 'page_no', $_POST['page_no']);
		   			showXHTML_input('hidden', 'cond_type', $_POST['cond_type']);
    	   			showXHTML_input('hidden', 'queryTxt', htmlspecialchars(stripslashes(trim($_POST['queryTxt']))), '', '');

					showXHTML_table_B('id ="mainTable" width="600" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
						$col = 'cssTrEvn';
						showXHTML_tr_B('class=" ' . $col . '"');
							showXHTML_td('align="center" nowrap ', $MSG['user_account'][$sysSession->lang]);
							showXHTML_td_B('colspan="2"');
                        echo $_POST['username'];
                        echo '&nbsp;';
                        showXHTML_input('button', '', $MSG['title40'][$sysSession->lang], '', 'id="addBtn1" onclick="createTeacher(\'' . $_POST['username'] . '\');" class="cssBtn"');
							showXHTML_td_E();
						showXHTML_tr_E();
						$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';

   		               showXHTML_tr_B('class=" ' . $col . '"');
							showXHTML_td('align="center" nowrap ', $MSG['real_name'][$sysSession->lang]);
							showXHTML_td_B('colspan="2" ');
                                echo $teacher_name;
							showXHTML_td_E();
						showXHTML_tr_E();
						$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';

						showXHTML_tr_B('class=" ' . $col . '"');
							showXHTML_td('align="center" nowrap ', $MSG['status'][$sysSession->lang]);
							showXHTML_td_B(' colspan="2"');
							  showXHTML_input('select', 'level', array($sysRoles['teacher']    => $MSG['teacher'][$sysSession->lang],
							                                           $sysRoles['assistant']  => $MSG['assistant'][$sysSession->lang],
							                                           $sysRoles['instructor'] => $MSG['instructor'][$sysSession->lang]
							  										  ), $_POST['level']);
							showXHTML_td_E();
						showXHTML_tr_E();
						$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';

	                    showXHTML_tr_B('class=" ' . $col . '"');
							showXHTML_td_B('colspan="3" align="left"');
							    showXHTML_input('button', '', $MSG['select_all'][$sysSession->lang], '', 'class="cssBtn" id="btnSel1" onclick="selected_box();"');
							    showXHTML_input('button', '', $MSG['modify'][$sysSession->lang],     '', 'class="cssBtn" onclick="checkData(\'M\');"');
								showXHTML_input('button', '', $MSG['delete'][$sysSession->lang],     '', 'class="cssBtn" onclick="checkData(\'D\');"');
								showXHTML_input('reset',  '', $MSG['reset'][$sysSession->lang],      '', 'class="cssBtn"');
								showXHTML_input('button', '', $MSG['title6'][$sysSession->lang],     '', 'class="cssBtn" onclick="goList();"');
							showXHTML_td_E();
						showXHTML_tr_E();

						$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';

                  if ($RS2->RecordCount() > 0){   // $RS2 if begin
                     showXHTML_tr_B('class="cssTrHead"');
						      showXHTML_td_B('align="center" nowrap="noWrap"');
							      showXHTML_input('checkbox', '', '', '', 'id = "ckbox" onclick="selectItem(this.checked);" " exclude="true"' . ' title="' . $MSG['select_all'][$sysSession->lang] . '"');
							   showXHTML_td_E();
							   showXHTML_td_B(' align="center" nowrap="noWrap"');
                                    echo '<a class="cssAnchor" href="javascript:;" onclick="chgPageSort(1); return false;" title="' , $MSG['course_id'][$sysSession->lang] , '">',
                                         $MSG['course_id'][$sysSession->lang],
                                         ($sortby == 1 ? (
                                         ' <img src="/theme/' . $sysSession->theme . '/teach/dude07232001' .
                                         ($order == 'desc' ? 'down' : 'up') .
                                         '.gif" valign="absmiddle" border="0">') : ''),
                                         '</a>';
                                showXHTML_td_E();
							   showXHTML_td_B('align="center" nowrap="noWrap"');
                                    echo '<a class="cssAnchor" href="javascript:;" onclick="chgPageSort(2); return false;" title="' , $MSG['title7'][$sysSession->lang] , '">',
                                         $MSG['title7'][$sysSession->lang],
                                         ($sortby == 2 ? (
                                         ' <img src="/theme/' . $sysSession->theme . '/teach/dude07232001' .
                                         ($order == 'desc' ? 'down' : 'up') .
                                         '.gif" valign="absmiddle" border="0">') : ''),
										 '</a>';
                                showXHTML_td_E();
                        showXHTML_td_E();
						   showXHTML_tr_E();

						   while (!$RS2->EOF) { // $RS2 while begin
                        $col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
                        showXHTML_tr_B('class=" ' . $col . '"');
                           showXHTML_td_B('align="center" nowrap ');
                              showXHTML_input('checkbox', 'sel[]', $RS2->fields['course_id'],'',' onclick="selCourse(this)"; ');
                           showXHTML_td_E();

                           showXHTML_td_B('align="center" nowrap ');
                              echo $RS2->fields['course_id'];
                           showXHTML_td_E();

                                 $lang = getCaption($RS2->fields['caption']);
    		                     $csname = $lang[$sysSession->lang];
    		                     if ($csname == ''){
    		                        $csname = $MSG['title31'][$sysSession->lang];
    		                     }
                           showXHTML_td_B('align="left" ');
    		                     echo '<div style="width: 340px; overflow: hidden;" title="' . $csname . '">' . $csname . '</div>';
                           showXHTML_td_E();
                        showXHTML_tr_E();
    		               $RS2->MoveNext();
                     }  // $RS2 while end
                  }  // $RS2 if end

					$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
					showXHTML_tr_B('class=" ' . $col . '"');
						showXHTML_td('colspan="3" align="left"', '&nbsp;');
					showXHTML_tr_E();

					showXHTML_table_E();
				showXHTML_form_E();
				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E();

      showXHTML_form_B('action="teacher_new_course.php" method="post"', 'actNew');
			showXHTML_input('hidden', 'ticket', '', '', '');
			showXHTML_input('hidden', 'username', '', '', '');
	  showXHTML_form_E();

	showXHTML_body_E();
?>
