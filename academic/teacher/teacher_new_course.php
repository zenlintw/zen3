<?php
   /**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2003/09/15                                                                      *
	*		work for  : 新增授課教師 的列表                                                                       *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       $Id: teacher_new_course.php,v 1.1 2010/02/24 02:38:48 saly Exp $                                                                                          *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	header('Cache-Control: public');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/teacher_list.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$sysSession->cur_func = '0300100100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$icon_up = '<img src="/theme/default/academic/dude07232001up.gif" border="0" align="absmiddl">';
	$icon_dn = '<img src="/theme/default/academic/dude07232001down.gif" border="0" align="absmiddl">';

	$sysRootAccount = sysRootAccount;	// 設定最高管理員帳號,判斷不允許成為教師

    /*
    * 排序
    */
    $sortby  = min(2,max(1,$_POST['sortby']));
    $sortArr = array('', 'course_id', 'caption');

	$order = $_POST['order'] == 'desc' ? 'desc' : 'asc';
	$tmp2  = $_POST['username'] ? $_POST['username'] : ($_GET['username'] ? $_GET['username'] : '');
	$qtxt  = $_POST['course_name'] ? $_POST['course_name'] : ($_GET['course_name'] ? $_GET['course_name'] : '');
	$qtxt  = stripslashes(trim($qtxt));

	$query2 = $qtxt == '' ? '' : (' and caption like "%' . escape_LIKE_query_str(addslashes(addslashes($qtxt))) . '%" ');
	$query3 = '  and kind ="course" AND status not in (0,9) ';

	list($total_course) = dbGetStSr('WM_term_course', 'count(*)', 'course_id>10000000 '. $query2 . $query3, ADODB_FETCH_NUM);

	$total_page = ceil($total_course / sysPostPerPage);
	$cur_page   = ($_POST['p'] == '') ? 1 : intval($_POST['p']);

	if ($cur_page <= 0)
	{
		$cur_page = 0;
	}
	else if ($cur_page > $total_page)
	{
		$cur_page = 1;
		$limit = ' limit ' . (($cur_page-1)*sysPostPerPage) . ',' . sysPostPerPage;
	}
	else if ($cur_page <= $total_page)
	{
		$limit = ' limit ' . (($cur_page-1)*sysPostPerPage) . ',' . sysPostPerPage;
	}
	$RS = dbGetStMr('WM_term_course','course_id,caption','course_id>10000000 '. $query2 . $query3 . ' order by ' . " $sortArr[$sortby] " . $order . $limit, ADODB_FETCH_ASSOC);
	if ($sysConn->ErrorNo() > 0) die($sysConn->ErrorMsg());

	//  年
	$P = range(0, $total_page);
	$P[0] = $MSG['all'][$sysSession->lang];

	$js = <<< BOF

   var isIE              = (navigator.userAgent.search(' MSIE ') > -1) ? true : false;
   var cur_page          = {$cur_page};
   var total_page        = {$total_page};

   var MSG_SELECT_ALL    = "{$MSG['select_all'][$sysSession->lang]}";
   var MSG_SELECT_CANCEL = "{$MSG['cancel_all'][$sysSession->lang]}";
   var user_temp         = "{$tmp2}";

    window.onload = function(){
      if (total_page > 0){
	      var obj = document.getElementById('mainTable');
	      obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[3].cells[0].innerHTML;
      }


    };

    /**
	 * 單獨點課程
	 **/
	function selCourse(obj) {
		var nodes = null, attr = null;
		var isSel = "false";
		var cnt = 0;
      var m = 0;

      var obj2 = document.getElementById("actForm");
		var nodes = obj2.getElementsByTagName('input');
		if ((nodes == null) || (nodes.length == 0)) return false;

      for(var i=1; i<nodes.length-1; i++){
			if ((nodes.item(i).type == 'checkbox') && (nodes.item(i).value != '')){
         	m++;
            if (nodes.item(i).checked) cnt++;

			}
	   }

		// m = (m > 0) ? m - 1 : 0;
		nowSel = (m == cnt);
	   document.getElementById("ckbox").checked = nowSel;

        /*
		 * button 顯示 全選 或 全消 begin
		 */
		var btn1 = document.getElementById("btnSel1");
		btn1.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;

		if (total_page > 0) {
	      var obj = document.getElementById('mainTable');
	      obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[3].cells[0].innerHTML;
      }

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

		nodes = document.getElementsByTagName("input");
		for (var i = 0; i < nodes.length; i++) {
			attr = nodes[i].getAttribute("exclude");
			if ((nodes[i].type == "checkbox") && (attr == null))
				nodes[i].checked = nowSel;
		}

		if (total_page > 0){
	      var obj = document.getElementById('mainTable');
	      obj.rows[(obj.rows.length-1)].cells[0].innerHTML = obj.rows[3].cells[0].innerHTML;
      }
	}

	function checkData() {
	    var obj = document.getElementById("actForm");

		if (obj.username.value == ''){
	      alert("{$MSG['title12'][$sysSession->lang]}");
	      obj.username.focus();
	      return false;
	    }
	    else if (obj.username.value == '{$sysRootAccount}') {
	    	alert("{$sysRootAccount} {$MSG['title45'][$sysSession->lang]}");
	    	return false;
	    }

        var nodes = obj.getElementsByTagName('input');
        var ret = '';

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
         alert("{$MSG['title13'][$sysSession->lang]}");
         return false;
	   }

       /*
        disable submit button
       */
       var obj2 = document.getElementById("btn_submit");
       obj2.disabled = true;

       var obj3 = document.getElementById('mainTable');
	   obj3.rows[(obj3.rows.length-1)].cells[0].innerHTML = obj3.rows[3].cells[0].innerHTML;

	}

   function QueryCourse() {
      var obj = document.getElementById("course_name");
      var course_name = '';
      course_name = obj.value;

	  var obj = document.getElementById('username');
	  var user_name = '';
	  if (obj.value.length > 0){
	   	 user_name = obj.value;
	  }

      var obj2 = document.getElementById("actQuery");

	   obj2.course_name.value = course_name;
	   obj2.username.value = user_name;

	   obj2.submit();

   }

	function goList() {
		window.location.replace("/academic/teacher/teacher_list.php");
	}

   function page(n){
   	   var obj = null;
   	   var temp_user = '';

   	   obj = document.getElementById("username");
   	   temp_user = obj.value;

	   obj = document.getElementById("actQuery");

	   var tmp = 0;

	   switch(n){
			case -1:
				tmp = 1;

   				break;
   			case -2:
   				tmp = (cur_page-1);

   				break;
   			case -3:
   				tmp = (cur_page+1);

   				break;
   			case -4:
   				tmp = total_page;

   				break;
   			default:
   				var p = parseInt(n);

   				if (p >= 0 && p <= total_page){
   					tmp = p;

   				}
   				break;
	   }

		obj.p.value = tmp;
		obj.username.value = temp_user;

	   	obj.submit();

   }

   /*
    * 標題排序
   */
   function chgPageSort(val) {
        var obj = document.getElementById("actQuery");

		if ((typeof(obj) != "object") || (obj == null)) return false;

		obj.order.value = obj.order.value == 'asc' ? 'desc' : 'asc';

		obj.sortby.value = val;

		obj.username.value = user_temp;

		obj.submit();
	}

		/* 授課教師帳號() */
		function select_teacher(){
			var win = new WinTeacherSelect('setTeacherValue');
			win.run();
		}

		function setTeacherValue(arr)
		{
			user_ids = arr[0];
			document.actForm.username.value = user_ids.replace(/,/ig,';');
		}
BOF;
	// 開始呈現 HTML
	showXHTML_head_B($MSG['add_teacher'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
		showXHTML_script('include', '/lib/popup/popup.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E('');
	showXHTML_body_B('');

		showXHTML_table_B('border="0" width="600" cellspacing="0" cellpadding="0"  id="ListTable"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary[] = array($MSG['add_teacher'][$sysSession->lang], 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');

         $col = 'cssTrEvn';

			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup" ');
					showXHTML_form_B('method="post" action="teacher_save.php" style="display:inline;" onsubmit="return checkData()"', 'actForm');
					$ticket = md5($sysSession->ticket . 'Create' . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
					showXHTML_input('hidden', 'ticket', $ticket, '', '');
					showXHTML_table_B('id ="mainTable" width="600" border="0" cellspacing="1" cellpadding="3" class="cssTable"');

                  showXHTML_tr_B('class="'. $col . '"');
				            showXHTML_td_B('colspan="3" ');
				            	echo $MSG['title34'][$sysSession->lang];
				            	if (strlen($qtxt) == 0)
				            		$show_query = $MSG['title35'][$sysSession->lang];
				            	else
				            		$show_query = htmlspecialchars(stripslashes($qtxt));

                        		showXHTML_input('text', 'course_name', $show_query, '', 'id="course_name" size="20" maxlength="20" width="30" class="cssInput" onclick="this.value=\'\'"');
                        		showXHTML_input('button', '', $MSG['query'][$sysSession->lang], '', 'class="cssBtn" onclick="QueryCourse();"');
				            showXHTML_td_E('');
		            showXHTML_tr_E('');

						$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';

						showXHTML_tr_B('class="'. $col . '"');
    						showXHTML_td_B('colspan="3"');
    						    echo $MSG['user_account'][$sysSession->lang] . '&nbsp;&nbsp;';
                                showXHTML_input('text', 'username', $tmp2, '', 'id="username" size="40" width="30" class="cssInput"');
                                showXHTML_input('button', '', $MSG['title46'][$sysSession->lang], '', 'onclick="select_teacher();" class="cssBtn"');
                                echo $MSG['status'][$sysSession->lang] . '&nbsp;&nbsp;';
                                echo "<select name='level' class='cssInput'>" ,
							        "<option value='teacher'>{$MSG['teacher'][$sysSession->lang]}</option>" ,
                                    "<option value='assistant'>{$MSG['assistant'][$sysSession->lang]}</option>" ,
                                    "<option value='instructor'>{$MSG['instructor'][$sysSession->lang]}</option>" ,
                                    "</select>";
							showXHTML_td_E('');

					    showXHTML_tr_E('');

						$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';

						showXHTML_tr_B('class="'. $col . '"');
						   showXHTML_td('align="center" nowrap colspan="3"', $MSG['title37'][$sysSession->lang]);
						showXHTML_tr_E('');
						$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';

                  if ($RS->RecordCount() > 0){   // $RS if begin
                     showXHTML_tr_B('class="'. $col . '"');
				                showXHTML_td_B('align="left" colspan="3"');
                                    showXHTML_input('button', '', $MSG['select_all'][$sysSession->lang], '', ' id="btnSel1" class="cssBtn" onclick="selected_box();"');
						            echo $MSG['page3'][$sysSession->lang] , '&nbsp;';
						            showXHTML_input('select', '', $P, $cur_page, 'class="cssInput" onchange="page(this.value);" ');
						            echo "&nbsp;";
						            showXHTML_input('button', '', $MSG['first'][$sysSession->lang], '', 'class="cssBtn" ' . ((($cur_page==1) || ($cur_page==0))?          'disabled' : 'onclick="page(-1);"'));

          			                showXHTML_input('button', '', $MSG['prev'][$sysSession->lang] , '', 'class="cssBtn" ' . ((($cur_page==1) || ($cur_page==0))?          'disabled' : 'onclick="page(-2);"'));
          			                showXHTML_input('button', '', $MSG['next'][$sysSession->lang] , '', 'class="cssBtn" ' . ((($cur_page==$total_page)|| ($cur_page==0)) ?'disabled' : 'onclick="page(-3);"'));
          			                showXHTML_input('button', '', $MSG['last'][$sysSession->lang] , '', 'class="cssBtn" ' . ((($cur_page==$total_page)|| ($cur_page==0))?'disabled' : 'onclick="page(-4);"'));
                                    showXHTML_input('submit', '', $MSG['store'][$sysSession->lang], '', 'id="btn_submit" class="cssBtn"');
                                    showXHTML_input('reset', '', $MSG['reset'][$sysSession->lang], '', 'class="cssBtn"');
                                    showXHTML_input('button', '', $MSG['title6'][$sysSession->lang], '', 'onclick="goList();" class="cssBtn"');
                                showXHTML_td_E('');
                     showXHTML_tr_E('');

                     $col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';

                     showXHTML_tr_B('class="cssTrHead"');
                               showXHTML_td_B('align="center"');
							      showXHTML_input('checkbox', '', '', '', ' id = "ckbox" onclick="selected_box();" " exclude="true"' . 'title=' . $MSG['select_all'][$sysSession->lang]);
							   showXHTML_td_E('');
							   showXHTML_td_B(' align="center" nowrap="noWrap" onclick="chgPageSort(1);" title="' . $MSG['course_id'][$sysSession->lang] . '"');
                                    echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                                    echo $MSG['course_id'][$sysSession->lang];
                                    echo ($sortby == 1) ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                                    echo '</a>';
                               showXHTML_td_E('');

							   showXHTML_td_B(' align="center" nowrap="noWrap" onclick="chgPageSort(2);" title="' . $MSG['course_name'][$sysSession->lang] . '"');
                                    echo '<a class="cssAnchor" href="javascript:;" onclick="return false;">';
                                    echo $MSG['course_name'][$sysSession->lang];
                                    echo ($sortby == 2) ? ($order == 'desc' ? $icon_dn : $icon_up) : '';
                                    echo '</a>';
                                showXHTML_td_E('');
                        showXHTML_td_E('');
						   showXHTML_tr_E('');
                        $i = 0;
                        while (!$RS->EOF) { // $RS2 while begin

                            $col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
                            showXHTML_tr_B('class=" ' . $col . '"');
                                showXHTML_td_B('align="center"');
                                    showXHTML_input('checkbox', 'sel[]', $RS->fields['course_id'],'',' onclick="selCourse(this)"; ');
                                showXHTML_td_E('');
                                $lang = getCaption($RS->fields['caption']);
    		                    $csname = $lang[$sysSession->lang];
    		                    if ($csname == ''){
    		                       $csname = $MSG['title31'][$sysSession->lang];
   		                    }
    		                    showXHTML_td_B('align="left" ');
    		                     echo $RS->fields['course_id'];
                                showXHTML_td_E('');
                                showXHTML_td_B('align="left" ');
    		                     echo '<div style="width: 220px; overflow: hidden;" title="' . $csname . '">' . $csname . '</div>';
                                showXHTML_td_E('');
                            showXHTML_tr_E('');
    		               $RS->MoveNext();
    		               $i++;
                        }  // $RS while end
                  }else{
                     showXHTML_tr_B('class="'. $col . '"');
							showXHTML_td('align="center" nowrap  colspan="3"', $MSG['title36'][$sysSession->lang]);
					showXHTML_tr_E('');
                  }  // $RS if end

               $col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';

               showXHTML_tr_B('class="'. $col . '"');
                  showXHTML_td_B('align="left" colspan="3"');
                  showXHTML_td_E('');
      		   showXHTML_tr_E();

					showXHTML_table_E('');
					showXHTML_form_E('');
				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');

      showXHTML_form_B('action="teacher_new_course.php" method="post"', 'actQuery');
			showXHTML_input('hidden', 'ticket', '', '', '');
			showXHTML_input('hidden', 'course_name', htmlspecialchars(stripslashes($qtxt)), '', '');
			showXHTML_input('hidden', 'username', '', '', '');
			showXHTML_input('hidden', 'sortby', $sortby, '', '');
	        showXHTML_input('hidden', 'order', $order, '', '');
	        showXHTML_input('hidden', 'p', '', '', '');
		showXHTML_form_E('');

	showXHTML_body_E('');
?>
