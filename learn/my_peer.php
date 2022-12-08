<?php
	/**
	 * 【程式功能】我的互評作業
	 * 建立日期：2004/11/09
	 * @author  Wiseguy Liang
	 * @version $Id: my_exam.php,v 1.1 2010/02/24 02:39:05 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/my_exam.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/learn/mycourse/modules/mod_short_link_lib.php');
	require_once(sysDocumentRoot . '/lang/mooc.php');

	$sysSession->cur_func = '2000100100';
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$which = substr(basename($_SERVER['PHP_SELF']), 3, -4);
	$label = 'SYS_04_02_004';
	$sysSession->goto_label = $label;
	$sysSession->restore();

	$exam_ary = checkQTI($sysSession->username, $which);

	$checker = true;

	$js = <<< EOF
var sysGotoLabel = '{$label}';

EOF;

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();

	showXHTML_body_B();

		echo '<center>';
		$ary = array(array($MSG['my_peer'][$sysSession->lang]));
		showXHTML_tabFrame_B($ary, 1, '', 'ListTable');
				showXHTML_table_B('id ="mainTable" width="680" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
					showXHTML_tr_B('class="font01 cssTrHead"');
						showXHTML_td('align="center" nowrap', $MSG['course_no'][$sysSession->lang]);
						showXHTML_td('align="center" nowrap', $MSG['course_name'][$sysSession->lang]);
						showXHTML_td('align="center"'       , $MSG['should_do_homework'][$sysSession->lang]);
						showXHTML_td('align="center"'       , $MSG['not_do_homework'][$sysSession->lang]);
						showXHTML_td('align="center"'       , $MSG['submit_homework'][$sysSession->lang]);
					showXHTML_tr_E();

					if ((count($exam_ary) > 0) && is_array($exam_ary)){
						foreach ($exam_ary as $course_id => $cs_array) {
							$checker ^= true;
							showXHTML_tr_B($checker ? 'class="font01 cssTrOdd"' : 'class="font01 cssTrEvn"');
								// 課程編號
								showXHTML_td('align="left"'  , $course_id);

								// 課程名稱
								showXHTML_td('align="left"'  , $exam_ary[$course_id]['caption']);

								// 應做考試
								showXHTML_td('align="center"', $exam_ary[$course_id]['total_do']);

								// 未做考試
								showXHTML_td('align="center"', $exam_ary[$course_id]['total_undo'] ? $exam_ary[$course_id]['total_undo'] : 0);

								// 做考試
								showXHTML_td_B('align="center"');
									$nEnv = $sysSession->env == 'teach' ? 2 : 1;
									showXHTML_input('button', '', ' Go ', '', 'class="cssBtn" onclick="parent.chgCourse(' .$course_id . ', '.$nEnv.', 1, \''.trim($label).'\');"');
								showXHTML_td_E();
							showXHTML_tr_E();
						}
					}
				showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</center>';
	showXHTML_body_E();
?>
