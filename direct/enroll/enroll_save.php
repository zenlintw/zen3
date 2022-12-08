<?php
	/**
	 * 儲存指派課程的結果
	 *
	 * @since   2004/08/05
	 * @author  ShenTing Lin
	 * @version $Id: enroll_save.php,v 1.1 2010/02/24 02:38:57 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/username.php');
	require_once(sysDocumentRoot . '/direct/enroll/enroll_lib.php');
	require_once(sysDocumentRoot . '/learn/mycourse/do_function.php');
	require_once(sysDocumentRoot . '/direct/enroll/course_lib.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '1100300100';
	$sysSession->restore();
	if (!aclVerifyPermission(1100300100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 移除暫存檔
	$filename = sysTempPath . '/direct_' . $_COOKIE['idx'] . '.ini';
	@unlink($filename);

	$course = $_POST['course'];
	$csid   = implode(',', $course);
	$member = $_POST['member'];
	$result = array();
	foreach ($member as $val) {
		elective_send($csid, $val, true);
	}
	wmSysLog($sysSession->cur_func, $sysSession->class_id , 0 , 0, 'director', $_SERVER['PHP_SELF'], '儲存指派課程的結果!');
	$theme = "/theme/{$sysSession->theme}/{$sysSession->env}/";
	$js = <<< BOF
	var theme = "{$theme}";

	function showDetail(objName, val) {
		var obj = null;

		obj = document.getElementById("icon1_" + objName);
		if (obj != null) obj.style.display = (val) ? "none" : "";
		obj = document.getElementById("icon2_" + objName);
		if (obj != null) obj.style.display = (val) ? "" : "none";
		obj = document.getElementById("div_" + objName);
		obj.style.overflow = (val) ? "visible" : "hidden";
        
        // Chrome用
        var isSafari = navigator.userAgent.search("Safari") > -1;
        if (typeof(event) != "object" || isSafari) {
            obj.style.height = (val) ? "100%" : "25px";
            obj = document.getElementById("td_" + objName);
            obj.style.height = (val) ? "100%" : "25px";
        }
	}

	function goHelp() {
		var obj = document.getElementById("wiseFm");
		if (obj != null) {
			obj.action = "enroll_help.php";
			obj.submit();
		}
	}
BOF;

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['tabs_enroll_save'][$sysSession->lang], 'tabs1');
		// $colspan = 'colspan="2"';
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1); //, form_id, table_id, form_extra, isDragable);
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('colspan="4"');
						$ary = array(
							array($MSG['msg_step_11'][$sysSession->lang], 'help'  , 1, 'goHelp();'),
							array($MSG['msg_step_2'][$sysSession->lang] , 'member', 0),
							array($MSG['msg_step_3'][$sysSession->lang] , 'course', 0),
							array($MSG['msg_step_4'][$sysSession->lang] , 'review', 0),
							array($MSG['msg_step_5'][$sysSession->lang] , 'result', 1)
						);
						showStep($ary, 'result');
					showXHTML_td_E();
				showXHTML_tr_E();
				showXHTML_tr_B('class="cssTrHead"');
					showXHTML_td('width="30"  align="center" title="' . $MSG['th_serial'][$sysSession->lang] . '"'       , $MSG['th_serial'][$sysSession->lang]);
					showXHTML_td('width="150" align="center" title="' . $MSG['th_username'][$sysSession->lang] . '"'     , $MSG['th_username'][$sysSession->lang]);
					showXHTML_td('width="150" align="center" title="' . $MSG['th_realname'][$sysSession->lang] . '"'     , $MSG['th_realname'][$sysSession->lang]);
					showXHTML_td('align="center" title="' . $MSG['th_enroll_result_title'][$sysSession->lang] . '"', $MSG['th_enroll_result'][$sysSession->lang]);
				showXHTML_tr_E();
				if (is_array($enResult)) {
					$i = 0;
					foreach ($enResult as $key => $val) {
						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td('valign="top"', ++$i);
							showXHTML_td('valign="top"', $key);
							$user = getUserDetailData($key);
							showXHTML_td('valign="top"', $user['realname']);
							showXHTML_td_B('valign="top" height="26px" id="td_' . $key . '"');
								echo '<span id="icon1_' . $key . '">';
								echo '<img border="0" align="left" width="9" height="15"  src="' . $theme . '/plus.gif" alt="' . $MSG['cs_tree_expend'][$sysSession->lang] . '" title="' . $MSG['cs_tree_expend'][$sysSession->lang] . '" onclick="showDetail(\'' . $key . '\', true)">';
								echo '</span>';
								echo '<span id="icon2_' . $key . '" style="display: none;">';
								echo '<img border="0" align="left" width="9" height="15"  src="' . $theme . '/minus.gif" alt="' . $MSG['cs_tree_collect'][$sysSession->lang] . '" title="' . $MSG['cs_tree_collect'][$sysSession->lang] . '" onclick="showDetail(\'' . $key . '\', false)">';
								echo '</span>';

								$cols = 'class="cssTrOdd"';
								echo '<div id="div_' . $key . '" style="height: 25px; overflow: hidden;">';
								showXHTML_table_B('width="360" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
								foreach ($val as $key1 => $val1) {
									$cols = ($cols == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
									showXHTML_tr_B($cols);
										showXHTML_td('width="80"', $key1);
										$csData = getCourseData($key1);
										$lang = getCaption($csData['caption']);
										showXHTML_td('nowrap', divMsg(140, $lang[$sysSession->lang]));
										$msg = '&nbsp;';
										/*
										if ($val1[1] == 'rule') $msg = $MSG['msg_rule'][$sysSession->lang];
										else $msg = $val1[2];
										*/

										if ($val1[0] == 'open') {
											$msg = $MSG['msg_open'][$sysSession->lang];
										} else {
											$msg = '&nbsp;';
											switch (trim($val1[0])) {
												case '%cs_status'     : $msg = $MSG['msg_cs_state'][$sysSession->lang];           break;
												case '%cs_study'      : $msg = $MSG['msg_basic_setting'][$sysSession->lang];      break;
												case '#none'          : $msg = $MSG['msg_rule_none'][$sysSession->lang];          break;
												case '#assistant'     : $msg = $MSG['msg_rule_assistant'][$sysSession->lang];     break;
												case '#instructor'    : $msg = $MSG['msg_rule_instructor'][$sysSession->lang];    break;
												case '#teacher'       : $msg = $MSG['msg_rule_teacher'][$sysSession->lang];       break;
												case '#director'      : $msg = $MSG['msg_rule_director'][$sysSession->lang];      break;
												case '#manager'       : $msg = $MSG['msg_rule_manager'][$sysSession->lang];       break;
												case '#administrator' : $msg = $MSG['msg_rule_administrator'][$sysSession->lang]; break;
												default               :
											}

											switch (trim($val1[1])) {
												case 'yes'  : $msg .= $MSG['msg_cs_selected'][$sysSession->lang];        break;
												case 'deny' : $msg .= $MSG['msg_cs_study_deny'][$sysSession->lang];      break;
												case 'ok'   : $msg .= $MSG['msg_cs_study_ok'][$sysSession->lang];        break;
												case '0'    : $msg .= $MSG['msg_cs_state_close'][$sysSession->lang];     break;
												case '5'    : $msg .= $MSG['msg_cs_state_not_ready'][$sysSession->lang]; break;
												case '9'    : $msg .= $MSG['msg_cs_state_delete'][$sysSession->lang];    break;
												default     : $msg .= '&nbsp;';
											}
										}
										showXHTML_td('nowrap', divMsg(140, $msg,str_replace('<br />','',$msg)));
									showXHTML_tr_E();
								}
								showXHTML_table_E();
								echo '</div>';
								// echo '&nbsp;';
							showXHTML_td_E();
						showXHTML_tr_E();
					}
				}
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
		showXHTML_form_B('action="" method="post" enctype="multipart/form-data" style="display: none;"', 'wiseFm');
			showXHTML_input('hidden', 'wiseguy', '', '', '');
		showXHTML_form_E();
	showXHTML_body_E();
?>
