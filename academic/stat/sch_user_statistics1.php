<?php
	/**
	 * �Ǯղέp��� - �ϥΪ̤H�Ʋέp
	 * �إߤ���G2004/08/16
	 * @author  Amm Lee
	 * @version $Id: sch_user_statistics1.php,v 1.1 2010/02/24 02:38:43 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/sch_statistics.php');

	$sysSession->cur_func = '1500200100';
	$sysSession->restore();
	if (!aclVerifyPermission(1500200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	function setClass($class_id) {
		global $class_ids, $ClassGpTree;
		if (is_array($ClassGpTree[$class_id]) && count($ClassGpTree[$class_id])) {
			foreach($ClassGpTree[$class_id] as $child) {
				$class_ids[] = $child;
				setClass($child);
			}
		}
	}

	switch ($_POST['target_member']){
		case 1:		// �Ҧ��w�g���U���H
			// html ���D
			$title_name = $MSG['title78'][$sysSession->lang];

			break;
		case 2:		// �Y�ӽҵ{�s��
			switch ($_POST['single_all']){
				case 1:
					if ($_POST['single_group_id'] == 10000000){	// �����սҵ{
						chkSchoolId('WM_term_course');
						$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
						$data = $sysConn->GetCol('select B.course_id ' .
						                         'from WM_term_course as B ' .
						                         'left join WM_term_group as G ' .
						                         'on B.course_id=G.child ' .
						                         'where G.child is NULL and B.kind="course" and B.status != 9');
						$cour_str   = is_array($data) && count($data) ? implode(',', $data) : '';
						$title_name = stripslashes(str_replace('%CGROUP%',$MSG['title74'][$sysSession->lang],$MSG['title97'][$sysSession->lang]));
					}else{		//  �Y�s�� ���U���Ҧ��ҵ{
						$group_id   = intval($_POST['single_group_id']);
						$data       = array_keys(getAllCourseInGroup($group_id));
						$cour_str   = is_array($data) && count($data) ? implode(',', $data) : '';
						$title_name = stripslashes(str_replace('%CGROUP%',$_POST['single_group'],$MSG['title97'][$sysSession->lang]));
					}
					break;
				case 2:		// �Y�ӽҵ{
					// html ���D
					$title_name = stripslashes(str_replace(array('%CGROUP%', '%CNAME%'),
						                                   array($_POST['single_group'], $_POST['single_course']),
						                                   $MSG['title98'][$sysSession->lang] ) );
					$cour_str = $_POST['single_course_id'];
					break;
			}

			// ���o �ҵ{���U�� �Ѯv �U�� ���v ������ ��ť�� ���
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
			break;
		case 3:		// �Y�ӯZ�Ÿs��
			switch ($_POST['single_all']){
				case 3:		// �s�դ��Ҧ��Z��
					// html ���D
					$title_name = stripslashes(str_replace('%CGROUP%',$_POST['single_cgroup'],$MSG['title99'][$sysSession->lang]));

					// ������l�`�I�����`�I�Ҧ����	(begin)
					$ClassGpTree = array();
					$RS = dbGetStMr('`WM_class_group`', '*', '`child` != 0 order by `parent`, `permute`', ADODB_FETCH_ASSOC);
					if ($RS->RecordCount() > 0){
						while ($RS1 = $RS->FetchRow()) {
							$ClassGpTree[$RS1['parent']][$RS1['permute']] = $RS1['child'];
						}
					}

					$group_id  = intval($_POST['single_cgroup_id']);
					$class_ids = array();
					$class_ids[] = $group_id;
					setClass($group_id);
					break;
				case 4:		// �Y�@�Z��
					// html ���D
					$title_name = stripslashes(str_replace(array('%CGROUP%', '%CNAME%'),
					                                       array($_POST['single_cgroup'], $_POST['single_class']),
					                                       $MSG['title100'][$sysSession->lang] ) );
					$class_ids[]	= intval($_POST['single_class_id']);
					break;
			}

			$RS = dbGetStMr('WM_class_member','distinct username','class_id in (' . implode(',',$class_ids) . ')', ADODB_FETCH_ASSOC);
			$users = array();
			if ($RS->RecordCount() > 0){
				while($RS1 = $RS->FetchRow()){
					if (! in_array("'" . $RS1['username'] . "'",$users)){
						$users[] = "'" . $RS1['username'] . "'";
					}
				}
				$users1 = implode(',', $users);
			}

			break;
	}

	$js = <<< EOF
		/* �p�G�O Mozilla/Firefox �h�[�W outerHTML/innerText ���䴩 */
		if (navigator.userAgent.indexOf(' Gecko/') != -1)
		{
			HTMLElement.prototype.__defineSetter__('outerHTML', function(s){
			   var range = this.ownerDocument.createRange();
			   range.setStartBefore(this);
			   var fragment = range.createContextualFragment(s);
			   this.parentNode.replaceChild(fragment, this);
			});

			HTMLElement.prototype.__defineGetter__('outerHTML', function() {
			   return new XMLSerializer().serializeToString(this);
			});

			HTMLElement.prototype.__defineGetter__('innerText', function() {
			  return this.innerHTML.replace(/<[^>]+>/g, '');
			});
		}

		/*
		 * Export data
		*/
		function displayDialog(name,sel_type){
			var obj            = document.getElementById(name);
			/*
			 * ��ܮإ�����  = [���ʪ����y��] + [�� Frame ���e��] - [��ܮؼe��(480)] �A���� 10 �� pixel
			 */
			obj.style.left     = document.body.scrollLeft + document.body.offsetWidth - 460;
			/*
			 * ��ܮؤW�t���  = [���ʪ��W�y��] �U�� 10 �� pixel
			 */
			obj.style.top      = document.body.scrollTop  + 30;
			obj.style.display  = '';
			obj.sel_type.value = sel_type;
		}

		function hiddenDialog(name){
			document.getElementById(name).style.display = 'none';
		}

		function checkExport(){

			var csv_content = '',htm_content = '',xml_content = '',col='';
			var obj2        = document.getElementById('exportForm');
			var obj         = document.getElementById('mainTable');
			var total_rows  = 0;

			if (obj2.sel_type.value == 'today'){
				total_rows = obj.rows.length -1;
			}else{
				total_rows = obj.rows.length -2;
			}

			for (var i = 0; i < total_rows; i++) {

				col = col == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"';

				if (i == 0){
					csv_content += obj.rows[i].cells[0].innerHTML + "<br>";

					htm_content += '<tr '+ col + '>'+ obj.rows[i].cells[0].outerHTML+ "<tr>";

					xml_content += '<summary>'+obj.rows[i].cells[0].innerHTML + '</summary>';
				}else if (obj2.sel_type.value == 'today'){
					csv_content += obj.rows[i].cells[0].innerHTML + ',' + "<br>";

					htm_content += '<tr '+ col + '><td>'+ obj.rows[i].cells[0].innerHTML + '</td></tr>';

					xml_content += '<record>'+obj.rows[i].cells[0].innerHTML + '</record>';

				}else {
					csv_content += obj.rows[i].cells[0].innerHTML + ',' + obj.rows[i].cells[1].innerHTML + "<br>";

					htm_content += '<tr '+ col + '><td>'+ obj.rows[i].cells[0].innerHTML + '</td><td>'+obj.rows[i].cells[1].innerHTML + "</td><tr>";

					xml_content += '<record><date_range>'+obj.rows[i].cells[0].innerHTML + '</date_range><data>' + obj.rows[i].cells[1].innerHTML + "</data></record>";
				}
			}

			var obj2 = document.getElementById('exportForm');

			var nodes = obj2.getElementsByTagName('input');

			var sel_count = 0;

			for (var i = 0 ; i < nodes.length ; i++){
				if ((nodes[i].type == "checkbox") && (nodes[i].checked)){
					switch (nodes[i].value){
						case 'csv':
							obj2.csv_content.value = csv_content;
							sel_count++;
							break;
						case 'htm':
							obj2.htm_content.value = htm_content;
							sel_count++;
							break;
						case 'xml':
							obj2.xml_content.value = xml_content;
							sel_count++;
							break;
					}
				}
			}

			if (sel_count > 0){
				hiddenDialog('exportTable');
				obj2.submit();
				document.getElementById('btn_export').disabled = false;
			}
			else {
				alert("{$MSG['no_select_data_format'][$sysSession->lang]}");
			}

		}
EOF;

	showXHTML_head_B($MSG['title5'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	showXHTML_script('include', '/lib/dragLayer.js');
	showXHTML_script('include', 'sch_statistics.js');
	showXHTML_script('inline', $js);
	showXHTML_head_E('');

	showXHTML_body_B('');

	showXHTML_table_B('width="500" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
		showXHTML_tr_B('');
			showXHTML_td_B('');
				$ary[] = array($MSG['title5'][$sysSession->lang], 'tabs');
				showXHTML_tabs($ary, 1);
			showXHTML_td_E('');
		showXHTML_tr_E('');

		showXHTML_tr_B('');
			showXHTML_td_B('valign="top" id="CGroup" ');
				showXHTML_table_B('id ="mainTable" width="500" border="0" cellspacing="1" cellpadding="3" class="cssTable"');

					switch ($_POST['condition']){
						case 1:		//  �ʧO
							if ($_POST['target_member'] == 1){
								$RS = dbGetStMr('WM_user_account', 'gender,count(*) as num', '1 group by gender', ADODB_FETCH_ASSOC);
							}else if (strlen($users1) > 0){
								$RS = dbGetStMr('WM_user_account', 'gender,count(*) as num', 'username in ('.$users1.') group by gender', ADODB_FETCH_ASSOC);
							}
							$gender['M'] = 0;
							$gender['F'] = 0;

							if ($RS) while ($row = $RS->FetchRow()) {
								$gender[$row['gender']] = $row['num'];
							}

							$total_count = array_sum($gender);

							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td_B('align="center" colspan="2"');
									echo $sysSession->school_name, $title_name,
									     str_replace('%TOTAL_PEOPLE%','<font color="red">' . $total_count . '</font>',$MSG['title84'][$sysSession->lang]);
								showXHTML_td_E('');
							showXHTML_tr_E('');

							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td('align="center" ', $MSG['title85'][$sysSession->lang]);
								showXHTML_td('align="center" ', $MSG['title86'][$sysSession->lang]);
							showXHTML_tr_E('');

							// �̤j��
							$max_value = max($gender);
							foreach ($gender as $key => $value){
								$x_scale[] = $key;
								$y_scale[] = $value;

								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td_B('align="center" ');
										switch ($key){
											case 'M':
												echo $MSG['title87'][$sysSession->lang];
												break;
											case 'F':
												echo $MSG['title88'][$sysSession->lang];
												break;
										}
									showXHTML_td_E('');
									showXHTML_td('align="center" ', $value);
								showXHTML_tr_E('');
							}

							if (count($x_scale) > 0){
								$str_x_scale = implode(',',$x_scale);
							}
							if (count($y_scale) > 0){
								$str_y_scale = implode(',',$y_scale);
							}

							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td('align="right" ', $MSG['title17'][$sysSession->lang]);
								showXHTML_td('align="left"', '<img src="/theme/default/academic/graph.gif" border="1" width="20" onclick="viwGraph(\'user_gender_graph.php\',\'' . $str_x_scale . '\',\'' . $str_y_scale . "'," . $max_value . ",'" . $choice_date . "'" . ');" style="cursor: pointer">');
							showXHTML_tr_E('');

							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td_B('align="center" colspan="2"');
									showXHTML_input('button','btnImp',$MSG['export'][$sysSession->lang],'','onclick="this.disabled=true; displayDialog(\'exportTable\',\'gender\');" id="btn_export"');

							break;
						case 2:		//  �~�ֶ��Z

							$add_value = intval($_POST['age_rang']) - 1;

							for($i=0;$i <= 100;$i+$add_value){
								$temp = $i+$add_value;
								if ($temp < 100){
									$key = $i . '~' . $temp;
								}else{
									$key = $i . '~';
								}
								$birth_range[$key] = 0;
								$i = $temp+1;
							}

							$sqls = 'birthday is not null ';
							if (($_POST['target_member'] == 2) || ($_POST['target_member'] == 3)){
								if (strlen($users1) > 0){
									$sqls .= ' and username in (' . $users1 . ') ';
								}
								$RS = dbGetCol('WM_user_account',
											   'birthday',
											   $sqls);
							}else if ($_POST['target_member'] == 1){
								$RS = dbGetCol('WM_user_account',
											   'birthday',
											   $sqls);
							}

							$this_year = intval(date('Y'));

							$num = count($birth_range);

							if ($RS){	// if begin
								foreach($RS as $birthday){		// while begin
									if (empty($birthday)) continue;

									$temp = explode('-',$birthday);
									//  user ��ڪ��~��
									$birth_year = $this_year - intval($temp[0]);

									foreach ($birth_range as $key => $value){		// foreach begin
										$temp1 = explode('~',$key);
										$small = intval($temp1[0]);
										$big   = intval($temp1[1]);
										if (empty($big)){
											if (($birth_year >= $small)){
												$birth_range[$key]++;
											}
										}else{
											if (($birth_year >= $small) && ($birth_year <= $big)){
												$birth_range[$key]++;
											}
										}
									}		// foreach end
								}		// while end
							}		//  if end

							$total_count = array_sum($birth_range);

							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td_B('align="center" colspan="2"');
									echo $sysSession->school_name, $title_name,
									     str_replace('%TOTAL_PEOPLE%','<font color="red">' . $total_count . '</font>',$MSG['title84'][$sysSession->lang]);
								showXHTML_td_E('');
							showXHTML_tr_E('');

							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td('align="center" ', $MSG['title89'][$sysSession->lang]);
								showXHTML_td('align="center" ', $MSG['title86'][$sysSession->lang]);
							showXHTML_tr_E('');

							$max_value = max($birth_range);

							foreach ($birth_range as $key => $value){		// foreach begin
								$x_scale[] = $key;
								$y_scale[] = $value;
								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td('align="center" ', $key);
									showXHTML_td('align="center" ', $value);
								showXHTML_tr_E('');
							}
							if (count($x_scale) > 0){
								$str_x_scale = implode(',',$x_scale);
							}
							if (count($y_scale) > 0){
								$str_y_scale = implode(',',$y_scale);
							}
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td('align="right" ', $MSG['title17'][$sysSession->lang]);
								showXHTML_td('align="left"', '<img src="/theme/default/academic/graph.gif" border="1" width="20" onclick="viwGraph(\'user_birth_graph.php\',\'' . $str_x_scale . '\',\'' . $str_y_scale . "'," . $max_value . ",''" . ');" style="cursor: pointer">');
							showXHTML_tr_E('');

							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td_B('align="center" colspan="2"');
										showXHTML_input('button','btnImp',$MSG['export'][$sysSession->lang],'','onclick="this.disabled=true; displayDialog(\'exportTable\',\'birthday\');" id="btn_export"');
							break;
						case 3:		// ����

							// �x�s �b��
							$user_exist = array();

							// �x�s ����
							$role = array();

							if ($_POST['target_member'] == 2){		// �Y�ӽҵ{

								// ���o �Z�ũ��U�� �Ѯv �U�� �������
								$RS = dbGetStMr('WM_term_major','distinct username,role&' . ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']) . ' as level','course_id in (' . $cour_str . ') and role & ' . ($sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']), ADODB_FETCH_ASSOC);

								if ($RS->RecordCount() > 0){
									while($RS1 = $RS->FetchRow()){
										$level = array_search($RS1['level'], $sysRoles);
										$role[$level] = $role[$level] + 1;

										if (! in_array("'" . $RS1['username'] . "'",$user_exist)){
											$user_exist[] = "'" . $RS1['username'] . "'";
										}
									}
								}

								$sum_role  = ($sysRoles['student'] | $sysRoles['auditor']);
        						$cond_role = '(role & ' . $sum_role . ')';
								$cond_user = '';
        						if (count($user_exist) > 0){
        							$cond_user = ' and username not in (' . implode(',',$user_exist) . ')';
								}

								$RS = dbGetStMr('WM_term_major','distinct username,' . $cond_role . ' as role','course_id in (' . $cour_str . ') and ' . $cond_role . $cond_user, ADODB_FETCH_ASSOC);

								if ($RS->RecordCount() > 0){
									while($RS1 = $RS->FetchRow()){
										$str_role = aclBitmap2Roles(intval($RS1['role']));
										$role[$str_role] = $role[$str_role] + 1;
									}

								}


							}else if ($_POST['target_member'] == 3){		// �Y�ӯZ��
								// ���o �Z�ũ��U�� �ɮv �U�� �������
								$RS = dbGetStMr('WM_class_member','distinct username,role&' . ($sysRoles['director'] | $sysRoles['assistant']) . ' as role','class_id in (' . implode(',',$class_ids) . ') and role&' . ($sysRoles['director'] | $sysRoles['assistant']), ADODB_FETCH_ASSOC);

								if ($RS->RecordCount() > 0){
									while($RS1 = $RS->FetchRow()){
										$str_role = aclBitmap2Roles(intval($RS1['role']));
										$role[$str_role] = $role[$str_role] +1;

										if (! in_array("'" . $RS1['username'] . "'",$user_exist)){
											$user_exist[] = "'" . $RS1['username'] . "'";
										}
									}
								}

								$cond_user = '';
        						if (count($user_exist) > 0){
        							$cond_user = ' and username not in (' . implode(',',$user_exist) . ')';
								}

								$RS = dbGetStMr('WM_class_member','distinct username,role','class_id in (' . implode(',',$class_ids) . ') ' . $cond_user, ADODB_FETCH_ASSOC);

								if ($RS->RecordCount() > 0){
									while($RS1 = $RS->FetchRow()){
										$str_role = aclBitmap2Roles(intval($RS1['role']));
										$role[$str_role] = $role[$str_role] +1;

									}
								}

							}

							$total_count = array_sum($role);

							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td_B('align="center" colspan="2"');
									echo $sysSession->school_name, $title_name,
									     str_replace('%TOTAL_PEOPLE%','<font color="red">' . $total_count . '</font>',$MSG['title84'][$sysSession->lang]);
								showXHTML_td_E('');
							showXHTML_tr_E('');

							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td('align="center" ', $MSG['title94'][$sysSession->lang]);
								showXHTML_td('align="center" ', $MSG['title86'][$sysSession->lang]);
							showXHTML_tr_E('');

							$max_value = max($role);

							foreach ($role as $key => $value){		// foreach begin
								// ���̤j��
								$x_scale[] = $key;
								$y_scale[] = $value;
								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td('align="center" ', $MSG[$key][$sysSession->lang]);
									showXHTML_td('align="center" ', $value);
								showXHTML_tr_E('');

							}	// foreach end

								if (count($x_scale) > 0){
									$str_x_scale = implode(',',$x_scale);
								}
								if (count($y_scale) > 0){
									$str_y_scale = implode(',',$y_scale);
								}
								$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td('align="right" ', $MSG['title17'][$sysSession->lang]);
									showXHTML_td('align="left"', '<img src="/theme/default/academic/graph.gif" border="1" width="20" onclick="viwGraph(\'user_role_graph.php\',\'' . $str_x_scale . '\',\'' . $str_y_scale . "'," . $max_value . ",''" . ');" style="cursor: pointer">');
								showXHTML_tr_E('');

							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
								showXHTML_tr_B($col);
									showXHTML_td_B('align="center" colspan="2"');
										showXHTML_input('button','btnImp',$MSG['export'][$sysSession->lang],'','onclick="this.disabled=true; displayDialog(\'exportTable\',\'role\');" id="btn_export"');

							break;
					}

							showXHTML_input('button','btnImp',$MSG['print'][$sysSession->lang],'','onclick="javascript:window.print();"');
							showXHTML_input('button','btnImp',$MSG['title95'][$sysSession->lang],'','onclick="do_fun(4);"');
						showXHTML_td_E('');
					showXHTML_tr_E('');
				showXHTML_table_E('');
			showXHTML_td_E('');
		showXHTML_tr_E('');
	showXHTML_table_E('');

	//  �Ϫ�
    	showXHTML_form_B('action="" method="post" target="viewGraphWin" enctype="multipart/form-data" style="display:none"', 'GraphFm');
	    	showXHTML_input('hidden', 'x_scale', '', '', '');
	    	showXHTML_input('hidden', 'y_scale', '', '', '');
	    	showXHTML_input('hidden', 'max_val', '', '', '');
    		showXHTML_input('hidden', 'type', '', '', '');
	    	showXHTML_input('hidden', 'period_date', '', '', '');
    	showXHTML_form_E();

		// �ץX
		$ary = array(array($MSG['title19'][$sysSession->lang], '', ''));
		showXHTML_tabFrame_B($ary, 1, 'exportForm', 'exportTable', 'action="sch_cour_export.php" method="POST" style="display: inline" target="empty"', true);
		  showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" class="cssTable"');

		    showXHTML_tr_B('class="cssTrEvn"');
		      showXHTML_td('', $MSG['title20'][$sysSession->lang]);
		      showXHTML_td_B();
		        showXHTML_input('checkboxes', 'type_kinds[]', array('csv' => 'Excel (.csv)',
		        													'htm' => 'HTML table (.htm)',
		        													'xml' => 'XML (.xml)'), array('csv'), '', '<br>');
		      showXHTML_td_E();
		    showXHTML_tr_E();
		    showXHTML_tr_B('class="cssTrOdd"');
		      showXHTML_td('', $MSG['title21'][$sysSession->lang]);
		      showXHTML_td_B();
		        showXHTML_input('text', 'download_name', 'user_stat.zip', '', 'maxlength="60" size="40" class="box02"');
		      showXHTML_td_E();
		    showXHTML_tr_E();
		    showXHTML_tr_B('class="cssTrEvn"');
		      showXHTML_td_B('colspan="2" align="center"');
		      	showXHTML_input('hidden', 'sel_type', '', '', '');
		        showXHTML_input('hidden', 'csv_content', '', '', '');
		  		showXHTML_input('hidden', 'htm_content', '', '', '');
    	  		showXHTML_input('hidden', 'xml_content', '', '', '');
    	  		showXHTML_input('hidden', 'function_name', $MSG['title5'][$sysSession->lang], '', '');
		        showXHTML_input('hidden', 'adv_file', 'user_stat', '', '');
		        showXHTML_input('button', '', $MSG['title11'][$sysSession->lang], '', 'class="cssBtn" onclick="checkExport();"');
		        showXHTML_input('button', '', $MSG['title22'][$sysSession->lang], '', 'class="cssBtn" onclick="hiddenDialog(\'exportTable\'); document.getElementById(\'btn_export\').disabled = false;"');
		      showXHTML_td_E();
		    showXHTML_tr_E();
		  showXHTML_table_E();
		showXHTML_tabFrame_E();

	showXHTML_body_E('');
?>
