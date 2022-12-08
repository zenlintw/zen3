<?php
	/**
	 * 設定個人資料
	 *
	 * 建立日期：2003/03/05
	 * @author  ShenTing Lin
	 * @version $Id: tagline1.php,v 1.1 2010/02/24 02:39:10 saly Exp $
	 * @copyright 2003 SUNNET
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
	require_once(sysDocumentRoot . '/lang/personal.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/mooc/models/school.php');  //使用 getSchoolStudentMooc
	
	$sysSession->cur_func='400400300';
	$sysSession->restore();
	if (!aclVerifyPermission(400400300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	// 檢查 ticket 是不是吻合
	$ticket = md5($sysSession->username . $sysSession->ticket . $sysSession->school_id);
	if ($ticket != trim($_POST['ticket'])) {
		echo 'Access deny.';
		wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'others', $_SERVER['PHP_SELF'], '拒絕存取!');
	    exit();
	}
	
	$rsSchool = new school();
	
	setTicket();

	showXHTML_head_B($MSG['tabs_personal'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/learn/wm.css");
	showXHTML_script('include', 'lib.js');
	showXHTML_head_E();
	showXHTML_body_B('');
		showXHTML_table_B('width="760" align="center" border="0" cellspacing="0" cellpadding="0" id="ListTable"');
			showXHTML_tr_B('');
				showXHTML_td_B('');
					$ary[] = array($MSG['tabs_personal'][$sysSession->lang], 'tabsSet', 'doFunc(1)');
					$ary[] = array($MSG['tabs_tagline_save'][$sysSession->lang], 'tabsTag');
					// student_mooc 為 0 時，才顯示我的學習中心
					if ($rsSchool->getSchoolStudentMooc($sysSession->school_id) == 0) {
						$ary[] = array($MSG['tabs_mycourse_manage'][$sysSession->lang], 'tabsMyCourse',  'doFunc(3)');
					}
					showXHTML_tabs($ary, 2);
				showXHTML_td_E();
			showXHTML_tr_E();
			showXHTML_tr_B('');
				showXHTML_td_B('valign="top" id="CGroup"');
					showXHTML_table_B('width="760" align="center" border="0" cellspacing="1" cellpadding="3" id="MySet" class="cssTable"');
						showXHTML_tr_B('class="cssTrHead"');
							showXHTML_td_B('colspan="3"');
								echo $sysSession->realname . '(' . $sysSession->username . ') > ' . $MSG['msg_tagline_update'][$sysSession->lang] . ' > ' . $MSG['tag_update_success'][$sysSession->lang];
							showXHTML_td_E();
						showXHTML_tr_E();

						showXHTML_tr_B('class="cssTrHead"');
							showXHTML_td('align="center"', $MSG['tagline_id'][$sysSession->lang]);
							showXHTML_td('align="center" colspan="2"', $MSG['tagline_content'][$sysSession->lang]);
						showXHTML_tr_E();

						$ctypeary = array(
							'text' => 'Text',
							'html' => 'Html'
						);

						foreach ($_POST['serial'] as $key => $val) {
							$val = intval($val);
							$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
							// $col = ($col == 'bg04') ? 'bg03' : 'bg04';
							$content = rtrim($_POST['tagline'][$key]);
							$layout  = stripslashes($content);
							$title   = htmlspecialchars(rtrim(strip_tags($_POST['title'][$key])));
							$ctype   = ($_POST['isHTML' . $val] == '1') ? 'html' : 'text';
							if ($ctype == 'html') {
								$content = strip_scr($layout);
							} else {
								$patterns = array("/(http:\/\/[^\s]+)/","/([\w\d]+(\.[_\w\d]+)*@[\w\d]+(\.[\w\d]+){1,4})/");
								$replace = array("<a href=\"\\1\" target=\"_blank\" class=\"cssAnchor\">\\1</a>","<a href=\"mailto:\\1\">\\1</a>");
								$content = nl2br(preg_replace($patterns, $replace, htmlspecialchars($layout, ENT_QUOTES)));
								// $layout = '<pre>' . preg_replace($patterns, $replace, htmlspecialchars($layout, ENT_QUOTES)) . '</pre>';
							}

							$showResult = false;
							if (($val < 0) || empty($val)) {
								if (!empty($title) || !empty($content)) {
									$showResult = true;
									dbNew('WM_user_tagline', 'username, title, ctype, tagline', "'{$sysSession->username}', '{$title}', '{$ctype}', '{$content}'");
									wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 0, 'others', $_SERVER['PHP_SELF'], 'new tagline');
								}
							} else {
								$showResult = true;
								dbSet('WM_user_tagline', "title='{$title}', ctype='{$ctype}', tagline='{$content}'", "serial={$val} AND username='{$sysSession->username}'");
								wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 0, 'others', $_SERVER['PHP_SELF'], 'update tagline');
							}

							if ($showResult) {
								showXHTML_tr_B($col);
									showXHTML_td('align="center" valign="top" rowspan="3"', intval($key) + 1);
									showXHTML_td('align="right" valign="top"', $MSG['tagline_title'][$sysSession->lang]);
									showXHTML_td('', $title);
								showXHTML_tr_E();
								showXHTML_tr_B($col);
									showXHTML_td('align="right" valign="top"', $MSG['tagline_content'][$sysSession->lang]);
									showXHTML_td('', $content);
								showXHTML_tr_E();
								showXHTML_tr_B($col);
									showXHTML_td('align="right" valign="top"', $MSG['msg_type'][$sysSession->lang]);
									showXHTML_td('', $ctypeary[$ctype]);
								showXHTML_tr_E();
							}
						}

						$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
						showXHTML_tr_B($col);
							showXHTML_td_B('colspan="3" align="center"');
								showXHTML_input('button', '', $MSG['return_tagline'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'tagline.php\')"');
							showXHTML_td_E();
						showXHTML_tr_E();
					showXHTML_table_E();
				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E();
	showXHTML_body_E();
?>
