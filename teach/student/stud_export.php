<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/07/15                                                            *
	 *		work for  :                                                                       *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *      $Id: stud_export.php,v 1.2 2011-07-27 01:34:32 small Exp $                                                                                          *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/teach_student.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '400400100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
	}

	// 開始 output HTML
	$fields = array(
			'username'             => $MSG['ex_username'][$sysSession->lang],
			'first_name,last_name' => $MSG['realname'][$sysSession->lang],
			'gender'               => $MSG['ex_gender'][$sysSession->lang],
			'birthday'             => $MSG['ex_birthday'][$sysSession->lang],
			'personal_id'          => $MSG['ex_personal_id'][$sysSession->lang],
			'email'                => $MSG['ex_email'][$sysSession->lang],
			'homepage'             => $MSG['ex_homepage'][$sysSession->lang],
			'home_tel'             => $MSG['ex_home_tel'][$sysSession->lang],
			'home_fax'             => $MSG['ex_home_fax'][$sysSession->lang],
			'home_address'         => $MSG['ex_home_address'][$sysSession->lang],
			'office_tel'           => $MSG['ex_office_tel'][$sysSession->lang],
			'office_fax'           => $MSG['ex_office_fax'][$sysSession->lang],
			'office_address'       => $MSG['ex_office_address'][$sysSession->lang],
			'cell_phone'           => $MSG['ex_cell_phone'][$sysSession->lang],
			'company'              => $MSG['ex_company'][$sysSession->lang],
			'department'           => $MSG['ex_department'][$sysSession->lang],
			'title'                => $MSG['ex_title'][$sysSession->lang]
		       );

	showXHTML_head_B($MSG['student_export'][$sysSession->lang]);
	  showXHTML_CSS('include', "/theme/{$sysSession->theme}/teach/wm.css");
	  showXHTML_script('include', '/lib/code.js');
	  $scr = <<< EOB

        function selectAll(idx,mode){
        	var obj = document.getElementById('procTable');
        	var nodes = obj.rows[idx].cells[1].getElementsByTagName('input');
        	for(var i=0; i<nodes.length; i++) {
        	    nodes[i].checked = mode;
        	}

        	if (mode){
        	    var obj = document.getElementById('select_all'+idx);
                obj.checked=true;
            }
        }

        function selectItem(idx,obj){

            var obj = document.getElementById('procTable');
        	var nodes = obj.rows[idx].cells[1].getElementsByTagName('input');

        	var m = 0,cnt=0;
        	for(var i=0; i<nodes.length; i++) {
        	    if (nodes[i].type == "checkbox") m++;

        	    if (nodes[i].checked) cnt++;
        	}

            var obj = document.getElementById('select_all'+idx);
            if (m == cnt)
                obj.checked=true;
            else
                obj.checked=false;
        }

        function submitCheck(){
        	var obj = document.getElementById('procTable');
        	var nodes = obj.rows[0].cells[1].getElementsByTagName('input');
        	var cnt = 0;

        	/*
			 * 選擇匯出學員身份
			 */
        	for(var i=0; i<nodes.length; i++){
        		if (nodes[i].checked) {
        			cnt++;
        		}
        	}

        	if (cnt == 0){
            	alert("{$MSG['error_msg1'][$sysSession->lang]}");
            	return false;
			}

			/*
			 * 選擇所要匯出的欄位
			 */
			var nodes = obj.rows[1].cells[1].getElementsByTagName('input');
        	var cnt = 0;
        	for(var i=0; i<nodes.length; i++){
        		if (nodes[i].checked) {
        			cnt++;
        		}
        	}

        	if (cnt == 0){
            	alert("{$MSG['error_msg'][$sysSession->lang]}");
            	return false;
			}

			/*
			 * 選擇所要匯出的欄位
			 */
			var nodes = obj.rows[2].cells[1].getElementsByTagName('input');
        	var cnt = 0;
        	for(var i=0; i<nodes.length; i++){
        		if (nodes[i].checked) {
        			cnt++;
        		}
        	}

        	if (cnt == 0){
            	alert("{$MSG['error_msg2'][$sysSession->lang]}");
            	return false;
			}

        	var nodes2 = obj.rows[3].cells[1].getElementsByTagName('input');
			if (isIllegalEmails(nodes2[0].value)) {
				alert("{$MSG['input_email'][$sysSession->lang]}");
				return false;
			}
        }

EOB;
	  showXHTML_script('inline', $scr);
	showXHTML_head_E();
	showXHTML_body_B();
	  showXHTML_table_B('border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse"');
	    showXHTML_tr_B();
	      showXHTML_td_B();
	        $ary[] = array($MSG['student_export'][$sysSession->lang], 'tabsSet',  '');
	        showXHTML_tabs($ary, 1);
	      showXHTML_td_E();
	    showXHTML_tr_E();
	    showXHTML_tr_B();
	      showXHTML_td_B('valign="top" ');
		showXHTML_form_B('method="POST" action="stud_export1.php" style="display: inline" onsubmit="return submitCheck();"');

		  showXHTML_table_B('id="procTable" width="760" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse" class="cssTable"');

		     showXHTML_tr_B('class="cssTrEvn"');
		     	showXHTML_td_B();
		      	 	echo $MSG['student_role'][$sysSession->lang];
		      		showXHTML_input('checkbox', '', '', '1', 'id="select_all0" onclick="selectAll(0,this.checked);"');
		      	showXHTML_td_E();
		      	showXHTML_td_B();
			        showXHTML_input('checkbox', 'role[teacher]', $sysRoles['teacher'] ,'teacher','onclick="selectItem(0,this);"');
			        echo $MSG['role_title'][$sysSession->lang];

			        showXHTML_input('checkbox', 'role[instructor]', $sysRoles['instructor'] ,'instructor','onclick="selectItem(0,this);"');
			        echo $MSG['role_title2'][$sysSession->lang];

			        showXHTML_input('checkbox', 'role[assistant]', $sysRoles['assistant'] ,'assistant','onclick="selectItem(0,this);"');
			        echo $MSG['role_title3'][$sysSession->lang] . '<br>';

			        showXHTML_input('checkbox', 'role[student]', $sysRoles['student'] ,'student','onclick="selectItem(0,this);"');
			        echo $MSG['role_title6'][$sysSession->lang];

			        showXHTML_input('checkbox', 'role[auditor]', $sysRoles['auditor'] ,'auditor','onclick="selectItem(0,this);"');
			        echo $MSG['role_title7'][$sysSession->lang];
		      showXHTML_td_E();
		    showXHTML_tr_E();

		    showXHTML_tr_B('class="cssTrOdd"');
		      showXHTML_td_B();
		        echo $MSG['select_fields'][$sysSession->lang];
		        showXHTML_input('checkbox', '', '', '1', 'id="select_all1" onclick="selectAll(1,this.checked);"');
		      showXHTML_td_E();
		      showXHTML_td_B();
		        showXHTML_input('checkboxes', 'fields[]', $fields, array(), 'checked onclick="selectItem(1,this);"', '<br>');
		      showXHTML_td_E();
		    showXHTML_tr_E();

		    showXHTML_tr_B('class="cssTrEvn"');
		    	showXHTML_td_B();
			      	echo $MSG['export_type'][$sysSession->lang];
			      	showXHTML_input('checkbox', '', '', '1', 'id="select_all2" onclick="selectAll(2,this.checked);"');
			    showXHTML_td_E();
			      showXHTML_td_B();

                    showXHTML_input('checkbox', 'ex_file[]', 'html' , 'html',' onclick="selectItem(2,this);"');
                    echo $MSG['file_type2'][$sysSession->lang] . '<br>';

			        showXHTML_input('checkbox', 'ex_file[]', 'xml' , 'xml',' onclick="selectItem(2,this);"');
                    echo $MSG['file_type1'][$sysSession->lang] .'<br>';

			      showXHTML_td_E();
		    showXHTML_tr_E();

		    showXHTML_tr_B('class="cssTrOdd"');
		      showXHTML_td('', $MSG['target_email'][$sysSession->lang]);
		      showXHTML_td_B();
		        showXHTML_input('text', 'email', $sysSession->email, '','size="100" class="cssInput" ');
		        echo '<br>' . $MSG['email_msg'][$sysSession->lang];
		      showXHTML_td_E();
		    showXHTML_tr_E();


		    showXHTML_tr_B('class="cssTrEvn"');
		      showXHTML_td_B('colspan="2" align="center"');
		        showXHTML_input('submit', '', $MSG['sure_export'][$sysSession->lang], '', 'class="button01"');
		      showXHTML_td_E();
		    showXHTML_tr_E();

		  showXHTML_table_E();

		showXHTML_form_E();
	      showXHTML_td_E();
	    showXHTML_tr_E();
	  showXHTML_table_E();
	showXHTML_body_E();

?>