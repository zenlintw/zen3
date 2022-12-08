<?php
	/**
	 * 編輯簽名檔
	 *
	 * 建立日期：2003/03/04
	 * @author  ShenTing Lin
	 * @version $Id: tagline.php,v 1.1 2010/02/24 02:39:10 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/personal.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/mooc/models/school.php');  //使用 getSchoolStudentMooc
	
	$sysSession->cur_func='400400300';
	$sysSession->restore();
	if (!aclVerifyPermission(400400300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$rsSchool = new school();
	
	$maxTagline = 3;   // 設定一個人最多可以有幾個簽名檔

	$RS = dbGetStMr('WM_user_tagline', 'serial, title, ctype, tagline', "username='{$sysSession->username}'", ADODB_FETCH_ASSOC);

	showXHTML_head_B($MSG['tabs_personal'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('include', 'lib.js');
	showXHTML_head_E('');
	showXHTML_body_B('');
		showXHTML_table_B('width="760" align="center" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary = array();
					$ary[] = array($MSG['tabs_personal'][$sysSession->lang]       , 'tabsSet'     , 'doFunc(1)');
					$ary[] = array($MSG['tabs_tagline'][$sysSession->lang]        , 'tabsTag'     , '');
						// student_mooc 為 0 時，才顯示我的學習中心
						if ($rsSchool->getSchoolStudentMooc($sysSession->school_id) == 0) {
							$ary[] = array($MSG['tabs_mycourse_manage'][$sysSession->lang], 'tabsMyCourse', 'doFunc(3)');
						}
					showXHTML_tabs($ary, 2);
				showXHTML_td_E('');
			showXHTML_tr_E('');
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup"');

					showXHTML_form_B('action="tagline1.php" method="post" enctype="multipart/form-data" style="display:inline;" onsubmit="return true;"', 'setForm');
					setTicket();
					$ticket = md5($sysSession->username . $sysSession->ticket . $sysSession->school_id);
					showXHTML_input('hidden', 'ticket', $ticket, '', '');
					showXHTML_table_B('width="760" align="center" border="0" cellspacing="1" cellpadding="3" id="MySet" class="cssTable"');
						showXHTML_tr_B('class="cssTrHead"');
							showXHTML_td_B('colspan="4"');
								echo $MSG['msg_tagline'][$sysSession->lang];
							showXHTML_td_E('');
						showXHTML_tr_E('');

						showXHTML_tr_B('class="cssTrHead"');
							showXHTML_td('align="center"', $MSG['tagline_id'][$sysSession->lang]);
							showXHTML_td('align="center" colspan="2"', $MSG['tagline_content'][$sysSession->lang]);
							showXHTML_td('align="center"', $MSG['tagline_help'][$sysSession->lang]);
						showXHTML_tr_E('');

						$ctype = array(
							0 => 'Text',
							1 => 'Html'
						);
						$cnt = 1;
						while (!$RS->EOF) {
							if ($cnt > $maxTagline) break;
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td_B('align="center" valign="top" rowspan="3"');
									showXHTML_input('hidden', 'serial[]', $RS->fields['serial'], '', '');
									echo $cnt;
								showXHTML_td_E('');
								showXHTML_td('align="right" valign="top"', $MSG['tagline_title'][$sysSession->lang]);
								showXHTML_td_B();
									showXHTML_input('text', 'title[]', $RS->fields['title'], '', 'size="85" maxlength="250"');
								showXHTML_td_E();
								showXHTML_td('valign="top"', '&nbsp;');
							showXHTML_tr_E('');

							showXHTML_tr_B($col);
								showXHTML_td('align="right" valign="top"', $MSG['tagline_content'][$sysSession->lang]);
								showXHTML_td_B('');
									$content = $RS->fields['tagline'];
									showXHTML_input('textarea', 'tagline[]', $content, '', 'rows="7" cols="85"');
								showXHTML_td_E('');
								showXHTML_td('valign="top"', $MSG['msg_help_content'][$sysSession->lang]);
							showXHTML_tr_E('');

							showXHTML_tr_B($col);
								showXHTML_td('align="right" valign="top"', $MSG['msg_type'][$sysSession->lang]);
								showXHTML_td_B('');
									$def = ($RS->fields['ctype'] == 'text') ? 0 : 1;
									showXHTML_input('radio', 'isHTML' . $RS->fields['serial'], $ctype, $def, '');
								showXHTML_td_E('');
								showXHTML_td('valign="top"', $MSG['msg_help_type'][$sysSession->lang]);
							showXHTML_tr_E('');

							$cnt++;
							$RS->MoveNext();
						}  // End while(!$RS->EOF)

						for (; $cnt <= $maxTagline; $cnt++) {
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							showXHTML_tr_B($col);
								showXHTML_td_B('align="center" valign="top" rowspan="3"');
									showXHTML_input('hidden', 'serial[]', "-{$cnt}", '', '');
									echo $cnt;
								showXHTML_td_E('');
								showXHTML_td('align="right" valign="top"', $MSG['tagline_title'][$sysSession->lang]);
								showXHTML_td_B();
									showXHTML_input('text', 'title[]', '', '', 'size="85" maxlength="250"');
								showXHTML_td_E();
								showXHTML_td('valign="top"', '&nbsp;');
							showXHTML_tr_E('');

							showXHTML_tr_B($col);
								showXHTML_td('align="right" valign="top"', $MSG['tagline_content'][$sysSession->lang]);
								showXHTML_td_B('');
									showXHTML_input('textarea', 'tagline[]', '', '', 'rows="7" cols="85"');
								showXHTML_td_E('');
								showXHTML_td('valign="top"', $MSG['msg_help_content'][$sysSession->lang]);
							showXHTML_tr_E('');

							showXHTML_tr_B($col);
								showXHTML_td('align="right" valign="top"', $MSG['msg_type'][$sysSession->lang]);
								showXHTML_td_B('');
									showXHTML_input('radio', 'isHTML' . "-{$cnt}", $ctype, 0, '');
								showXHTML_td_E('');
								showXHTML_td('valign="top"', $MSG['msg_help_type'][$sysSession->lang]);
							showXHTML_tr_E('');
						}

						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td_B('colspan="4" align="center"');
								showXHTML_input('submit', '', $MSG['btn_save'][$sysSession->lang], '', 'class="cssBtn"');
								showXHTML_input('reset' , '', $MSG['reset'][$sysSession->lang], '', 'class="cssBtn"');
							showXHTML_td_E('');
						showXHTML_tr_E('');
					showXHTML_table_E('');
					showXHTML_form_E('');

				showXHTML_td_E('');
			showXHTML_tr_E('');
		showXHTML_table_E('');
	showXHTML_body_E('');
?>
