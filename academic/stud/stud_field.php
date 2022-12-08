<?php
    /**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: amm lee                                                         *
	 *		Creation  : 2004/01/08                                                            *
	 *		work for  : 匯出人員資料 (第三步驟 -> 選擇匯出內容 )                                                                      *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.4                                *
	 *      $Id: stud_field.php,v 1.1 2010/02/24 02:38:44 saly Exp $                                                                                          *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/stud_export.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$sysSession->cur_func = '400400200';
	$sysSession->restore();
	if (!aclVerifyPermission(400400200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$export_data = array();

	if ($_POST['course_id'] != '')
	{
		if (intval($_POST['course_id']) == 10000000)
		{
			$course_data = intval($_POST['course_id']);
			$check_role = $course_data;
		}
		else
		{
			$export_data = preg_split('/\D+/', $_POST['course_id'], -1, PREG_SPLIT_NO_EMPTY);
			$export_data = array_unique($export_data);
			$course_data = implode(',', $export_data);
			$course_name = array();

			$RS = dbGetStMr('WM_term_course', 'caption', 'course_id in (' . $course_data . ') and status<9', ADODB_FETCH_ASSOC);
			while (!$RS->EOF)
			{
				$lang = getCaption($RS->fields['caption']);
				$course_name[] = $lang[$sysSession->lang];
				$RS->MoveNext();
			}
			$course_name = implode(',', $course_name);
		}
	}
	else if ($_POST['class_id'] != '')
	{
		if (intval($_POST['class_id']) == 1000000)
		{
			$class_data = intval($_POST['class_id']);
			$check_role = $class_data;
		}
		else
		{
			$export_data = preg_split('/\D+/', $_POST['class_id'], -1, PREG_SPLIT_NO_EMPTY);
			$export_data = array_unique($export_data);
			$class_data = implode(',', $export_data);
			$class_name = array();

			$RS = dbGetStMr('WM_class_main', 'caption', 'class_id in (' . $class_data . ') ', ADODB_FETCH_ASSOC);
			while (!$RS->EOF)
			{
				$lang = getCaption($RS->fields['caption']);
				$class_name[] = $lang[$sysSession->lang];
				$RS->MoveNext();
			}
			$class_name = implode(',', $class_name);
		}
	}

	$js = <<< BOF

    function chkData(){

        var inputs = document.getElementsByTagName('input');

        var role_num = 0;
        var field_num = 0;
        var file_num = 0;
        var action_file = 'stud_field1.php';

        for(var i = 0; i < inputs.length; i++){
            if ((inputs[i].type.toLowerCase() == 'checkbox') && (inputs[i].checked)){

                if (inputs[i].name.indexOf('role') != -1){
                    role_num++;
                }else if (inputs[i].name.indexOf('field') != -1){
                    field_num++;
                }else if (inputs[i].name.indexOf('ex_file') != -1){
                    file_num++;
                }
            }else if ((inputs[i].type.toLowerCase() == 'radio') && (inputs[i].checked)){

                    switch (parseInt(inputs[i].value)){
                        case 1:
                            action_file = 'stud_field1.php';
                            break;
                        case 2:
                            action_file = 'stud_field2.php';
                            break;
                    }
            }
        }

BOF;
	// 課程代碼 或 班級代碼 不為 10000000,1000000 (全校)
	if (! in_array($check_role, array(10000000, 1000000)))
	{
		$js .= <<< BOF
		if (role_num == 0){
			alert("{$MSG['title51'][$sysSession->lang]}");
			return false;
		}
BOF;
	}

	$js .= <<< BOF
		if (field_num == 0)
		{
			alert("{$MSG['title52'][$sysSession->lang]}");
			return false;
		}

		if (file_num == 0)
		{
			alert("{$MSG['title53'][$sysSession->lang]}");
			return false;
		}

		var obj = document.getElementById('post1');

		if (obj.send_email.value.length == 0)
		{
			alert("{$MSG['title61'][$sysSession->lang]}");
			return false;
		}

		obj.action = action_file;
		return true;
	}

	/*
	 * select all or cancel
	 * param : idx  ( select_all+idx => id name)
	 *         mode (checkbox type)
	 */
	function selectAll(idx, mode)
	{
		var obj = document.getElementById('mainTable');
		var nodes = obj.rows[idx].cells[1].getElementsByTagName('input');

		for(var i = 0; i < nodes.length; i++)
		{
			nodes[i].checked = mode;
		}
		return false;

		if (mode)
		{
			var obj = document.getElementById('select_all' + idx);
			obj.checked = true;
		}
	}

	function selectItem(idx, obj)
	{
		var obj = document.getElementById('mainTable');
		var nodes = obj.rows[idx].cells[1].getElementsByTagName('input');
		var m = 0, cnt = 0;

		for(var i = 0; i < nodes.length; i++)
		{
			if (nodes[i].type == "checkbox") m++;
			if (nodes[i].checked) cnt++;
		}

		var obj = document.getElementById('select_all' + idx);
		if (m == cnt)
			obj.checked = true;
		else
			obj.checked = false;
	}

BOF;

    // 開始呈現 HTML
	showXHTML_head_B($MSG['title'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('inline', $js,false);
	showXHTML_head_E('');
	showXHTML_body_B('');

	showXHTML_table_B('border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary[] = array($MSG['title16'][$sysSession->lang], 'tabs');
					showXHTML_tabs($ary, 1);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_form_B('method="post" action="" enctype="multipart/form-data" onsubmit="return chkData();"', 'post1');
					showXHTML_td_B('valign="top" id="CGroup" ');
						$ticket = md5($sysSession->ticket . 'export_data' . $sysSession->school_id . $sysSession->school_name . $sysSession->username);
						showXHTML_input('hidden', 'ticket', $ticket, '', '');
						showXHTML_input('hidden', 'course_data', $course_data, '', '');
						showXHTML_input('hidden', 'class_data', $class_data, '', '');

						showXHTML_table_B('id ="mainTable" width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable"');

							$col = 'cssTrEvn';
							showXHTML_tr_B('class=" ' . $col . '"');
								showXHTML_td(' colspan="2"', $MSG['title10'][$sysSession->lang]);
							showXHTML_tr_E('');

							$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
							showXHTML_tr_B('class=" ' . $col . '" ');
								showXHTML_td('nowrap', empty($course_data) ? $MSG['title7'][$sysSession->lang] : $MSG['title6'][$sysSession->lang]);
								showXHTML_td_B('');
									if ($course_data != '')
									{
										if (intval($_POST['course_id']) == 10000000)
											echo $sysSession->school_name;
										else
											echo $course_name;
									}
									else if ($class_data != '')
									{
										if (intval($_POST['class_id']) == 1000000)
											echo $sysSession->school_name;
										else
											echo $class_name;
									}
    							showXHTML_td_E('');
    						showXHTML_tr_E('');

							$cs_id_array = array(10000000, 1000000);

							if ($_POST['course_id'] != '')
							{
								$temp_id = intval($_POST['course_id']);
							}
							else if ($_POST['class_id'] != '')
							{
								$temp_id = intval($_POST['class_id']);
							}

							// 身份別 begin
							if (! in_array($temp_id, $cs_id_array))
								$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
							$extra = in_array($temp_id, $cs_id_array) ? 'style="display:none"' : '';
							showXHTML_tr_B('class=" ' . $col . '" ' . $extra . '');
								showXHTML_td_B('align="right" valign="top" nowrap');
									echo $MSG['title11'][$sysSession->lang];
									showXHTML_input('checkbox', '', '', '1', 'id="select_all2" onclick="selectAll(2,this.checked);"');
								showXHTML_td_E('');

								showXHTML_td_B('');
									// 班級有導師和助教， (WM_class_director || WM_class_member)
									// 課程有教師、講師和助教 (WM_term_teacher || WM_term_major)
									if ($course_data != '')
									{
										showXHTML_input('checkbox', 'role[teacher]', $sysRoles['teacher'] , 'teacher', 'onclick="selectItem(2,this);"');
										echo $MSG['title12'][$sysSession->lang] . '&nbsp;';

										showXHTML_input('checkbox', 'role[instructor]', $sysRoles['instructor'] , 'instructor', 'onclick="selectItem(2,this);"');
										echo $MSG['title14'][$sysSession->lang] . '&nbsp;';

										showXHTML_input('checkbox', 'role[assistant]', $sysRoles['assistant'] , 'assistant', 'onclick="selectItem(2,this);"');
										echo $MSG['title15'][$sysSession->lang] . '<br>';

										showXHTML_input('checkbox', 'role[student]', $sysRoles['student'] , 'student', 'onclick="selectItem(2,this);"');
										echo $MSG['title19'][$sysSession->lang];

										showXHTML_input('checkbox', 'role[auditor]', $sysRoles['auditor'] , 'auditor', 'onclick="selectItem(2,this);"');
										echo $MSG['title20'][$sysSession->lang];
									}

									if ($class_data != '')
									{
										showXHTML_input('checkbox', 'role[director]', $sysRoles['director'] , 'director', 'onclick="selectItem(2,this);"');
										echo $MSG['title13'][$sysSession->lang] . '&nbsp;';

										showXHTML_input('checkbox', 'role[assistant]', $sysRoles['assistant'] , 'assistant', 'onclick="selectItem(2,this);"');
										echo $MSG['role_assistant'][$sysSession->lang] . '&nbsp;';

										showXHTML_input('checkbox', 'role[student]', $sysRoles['student'] , 'student', 'onclick="selectItem(2,this);"');
										echo $MSG['role_student'][$sysSession->lang];
									}
								showXHTML_td_E('');
							showXHTML_tr_E('');
							// 身份別 end
							$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
							showXHTML_tr_B('class=" ' . $col . '"');
								showXHTML_td_B('align="right" valign="top" nowrap');
									echo $MSG['title21'][$sysSession->lang];
									showXHTML_input('checkbox', '', '', '1', 'id="select_all3" onclick="selectAll(3,this.checked);"');
								showXHTML_td_E('');

								showXHTML_td_B('  ');
									showXHTML_input('checkbox', 'field[]', '1' , '', 'checked="checked" onclick="selectItem(3,this);"');
									echo $MSG['title22'][$sysSession->lang] . '&nbsp;';

									showXHTML_input('checkbox', 'field[]', '2' , '', 'checked="checked" onclick="selectItem(3,this);"');
									echo $MSG['title23'][$sysSession->lang] . '<br>';

									showXHTML_input('checkbox', 'field[]', '3' , '', 'checked="checked" onclick="selectItem(3,this);"');
									echo $MSG['title24'][$sysSession->lang] . '&nbsp;';

									showXHTML_input('checkbox', 'field[]', '4' , '', 'checked="checked" onclick="selectItem(3,this);"');
									echo $MSG['title25'][$sysSession->lang] . '<br>';

									showXHTML_input('checkbox', 'field[]', '5' , '', 'checked="checked" onclick="selectItem(3,this);"');
									echo $MSG['title26'][$sysSession->lang] . '&nbsp;';

									showXHTML_input('checkbox', 'field[]', '6' , '', 'checked="checked" onclick="selectItem(3,this);"');
									echo $MSG['title27'][$sysSession->lang] . '&nbsp;';

									showXHTML_input('checkbox', 'field[]', '7' , '', 'checked="checked" onclick="selectItem(3,this);"');
									echo $MSG['title28'][$sysSession->lang] . '&nbsp;';

									showXHTML_input('checkbox', 'field[]', '8' , '', 'checked="checked" onclick="selectItem(3,this);"');
									echo $MSG['title29'][$sysSession->lang] . '<br>';

									showXHTML_input('checkbox', 'field[]', '9' , '', 'checked="checked" onclick="selectItem(3,this);"');
									echo $MSG['title30'][$sysSession->lang] . '&nbsp;';

									showXHTML_input('checkbox', 'field[]', '10' , '', 'checked="checked" onclick="selectItem(3,this);"');
									echo $MSG['title31'][$sysSession->lang] . '&nbsp;';

									showXHTML_input('checkbox', 'field[]', '11' , '', 'checked="checked" onclick="selectItem(3,this);"');
									echo $MSG['title32'][$sysSession->lang] . '<br>';

									showXHTML_input('checkbox', 'field[]', '12' , '', 'checked="checked" onclick="selectItem(3,this);"');
									echo $MSG['title33'][$sysSession->lang] . '&nbsp;';

									showXHTML_input('checkbox', 'field[]', '13' , '', 'checked="checked" onclick="selectItem(3,this);"');
									echo $MSG['title34'][$sysSession->lang] . '&nbsp;';

									showXHTML_input('checkbox', 'field[]', '14' , '', 'checked="checked" onclick="selectItem(3,this);"');
									echo $MSG['title35'][$sysSession->lang] . '<br>';

									showXHTML_input('checkbox', 'field[]', '15' , '', 'checked="checked" onclick="selectItem(3,this);"');
									echo $MSG['title36'][$sysSession->lang] . '<br>';

									showXHTML_input('checkbox', 'field[]', '16' , '', 'checked="checked" onclick="selectItem(3,this);"');
									echo $MSG['title37'][$sysSession->lang] . '<br>';

									showXHTML_input('checkbox', 'field[]', '17' , '', 'checked="checked" onclick="selectItem(3,this);"');
									echo $MSG['title38'][$sysSession->lang] . '<br>';
								showXHTML_td_E('');
							showXHTML_tr_E('');

							$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
							showXHTML_tr_B('class=" ' . $col . '"');
								showXHTML_td_B('align="right" valign="top" nowrap');
									echo $MSG['title39'][$sysSession->lang];
									showXHTML_input('checkbox', '', '', '1', 'id="select_all4" onclick="selectAll(4,this.checked);"');
								showXHTML_td_E('');

								showXHTML_td_B(' ');
									showXHTML_input('checkbox', 'ex_file[]', 'xml' , 'xml', 'onclick="selectItem(4,this);"');
									echo $MSG['title41'][$sysSession->lang] . '<br>';

									showXHTML_input('checkbox', 'ex_file[]', 'html' , 'html', 'onclick="selectItem(4,this);"');
									echo $MSG['title42'][$sysSession->lang] . '<br>';
								showXHTML_td_E('');
							showXHTML_tr_E('');

							$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
							showXHTML_tr_B('class=" ' . $col . '"');
								showXHTML_td('align="right" valign="top" nowrap', $MSG['title54'][$sysSession->lang]);
								showXHTML_td_B(' ');
									if ($course_data != '')
									{
										if (intval($_POST['course_id']) == 10000000)
										{
											$sel = array('1' => $MSG['title55'][$sysSession->lang]);
										}
										else
										{
											$sel = array(
												'1' => $MSG['title55'][$sysSession->lang],
												'2' => $MSG['title56'][$sysSession->lang]
											);
										}
									}
									else if ($class_data != '')
									{
										if (intval($_POST['class_id']) == 1000000)
										{
											$sel = array('1' => $MSG['title55'][$sysSession->lang]);
										}
										else
										{
											$sel = array(
												'1' => $MSG['title55'][$sysSession->lang],
												'2' => $MSG['title56'][$sysSession->lang]
											);
										}
									}
									// showXHTML_input('checkbox', 'ex_file[]', 'html' , 'html');
									showXHTML_input('radio', 'classify', $sel, '1', '', '<br>');
								showXHTML_td_E('');
							showXHTML_tr_E('');

							$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
							showXHTML_tr_B('class=" ' . $col . '"');
								showXHTML_td('align="right" valign="top" nowrap', $MSG['title43'][$sysSession->lang]);
								showXHTML_td_B(' ');
									showXHTML_input('text', 'send_email', $sysSession->email, '', 'id="send_email" size="100" width="100" class="cssInput" ');
									echo '<br>' . $MSG['title44'][$sysSession->lang];
								showXHTML_td_E('');
							showXHTML_tr_E('');

							$col = ($col == 'cssTrOdd') ? 'cssTrEvn' : 'cssTrOdd';
							showXHTML_tr_B('class=" ' . $col . '"');
								showXHTML_td_B('align="center" colspan="2"');
									showXHTML_input('submit', '', $MSG['title8'][$sysSession->lang], '', ' class="cssBtn" ');
									showXHTML_input('reset', '', $MSG['btn_reset'][$sysSession->lang], '', ' class="cssBtn"');
									if ($_POST['course_id'] != '')
									{
										$show_msg = $MSG['title63'][$sysSession->lang];
										$go_href = 'course_set.php';
									}
									else if ($_POST['class_id'] != '')
									{
										$show_msg = $MSG['title64'][$sysSession->lang];
										$go_href = 'class_group.php';
									}
									showXHTML_input('button', '', $MSG['btn_prv'][$sysSession->lang], '', ' onclick="window.location.href=\'' . $go_href . '\'" class="cssBtn"');
									showXHTML_input('button', '', $show_msg, '', ' onclick="window.location.href=\'stud_export.php\'" class="cssBtn"');
								showXHTML_td_E('');
							showXHTML_tr_E('');
						showXHTML_table_E('');

					showXHTML_td_E('');
				showXHTML_form_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
	showXHTML_body_E('');
?>
