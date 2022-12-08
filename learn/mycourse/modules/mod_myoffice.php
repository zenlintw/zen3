<?php
	/**
	 * 我的辦公室
	 *
	 * @since   2004/08/31
	 * @author  ShenTing Lin
	 * @version $Id: mod_myoffice.php,v 1.1 2010/02/24 02:39:09 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	if (!defined('MYCOURSE_MODULE') || MYCOURSE_MODULE === false) {
		include_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
		include_once(sysDocumentRoot . '/lib/interface.php');
		include_once(sysDocumentRoot . '/learn/mycourse/mycourse_lib.php');
		include_once(sysDocumentRoot . '/lib/acl_api.php');
	}
	require_once(sysDocumentRoot . '/lib/common.php');
	
	$sysSession->cur_func = '700700100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	// 檢查是不是老師
	$isTeach = aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']);
	if ($isTeach) {
		$isEdit = ($sysSession->username != 'guest');
		$lines = 3;
		$id = 'MyOffice';
		// 主要視窗大小的設定 (Begin)
		$wd = $defSize - 10;
		$dd = intval($wd) - 15;
		$Ld = $defLSize - 25;
		$Rd = $defRSize - 15;
		// 主要視窗大小的設定 (End)
		$id = showXHTML_mytitle_B($id, $MSG['tabs_myoffice'][$sysSession->lang], $wd, $isEdit);
			showXHTML_table_B('width="100%" border="0" cellspacing="1" cellpadding="3" class="cssTable" id="tab_' . $id . '"');
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('nowrap="nowrap"');
						echo '<div align="left" id="div_' . $id . '" style="width: ' . $dd . 'px; overflow: hidden; padding: 10px 0px 0px 15px;">';
							$theme = (empty($sysSession->theme)) ? '/theme/default/learn/' : "/theme/{$sysSession->theme}/{$sysSession->env}/";
							$img = '<img src="' . $theme . 'my_dot1.gif" width="12" height="12" border="0" align="absmiddle">';
							$RS = dbGetCourses('C.course_id, C.caption',
											   $sysSession->username,
											   $sysRoles['teacher'] | $sysRoles['instructor'] | $sysRoles['assistant']);
							$cnt = 0;
							$total = 0;
							if ($RS) {
								$total = $RS->RecordCount();
								while (!$RS->EOF) {
									if (!empty($RS->fields['caption'])) {
										$allow = checkIPLimit($sysSession->username, 'teach', $RS->fields['course_id']);
										if ($allow) {
											$lang = getCaption($RS->fields['caption']);
											$nEnv = $sysSession->env == 'teach' ? 2 : 1;
											echo $img . '<a href="javascript:;" onclick="parent.chgCourse(' . $RS->fields['course_id'] . ', '.$nEnv.',2); return false;" class="cssAnchor" title="' .$lang[$sysSession->lang] . '">' . $lang[$sysSession->lang] . '</a><br>';
											$cnt++;
										}
										if ($cnt >= $lines) break;
									}
									$RS->MoveNext();
								} // End while (!$RS->EOF)
							}
							if ($cnt <= 0) {
								echo '<div style="padding: 0px 0px 10x 0px;">' . $MSG['msg_no_myoffice'][$sysSession->lang] . '</div>';
							} else if ($total > $lines) {
								showXHTML_mytitle_more('onclick="mod_' . $id . '_more(); return false;"');
							}
						echo '</div>';
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
			$msg = ($isEdit) ? $MSG['msg_reposition_here'][$sysSession->lang] : '&nbsp;';
			showXHTML_mytitle_postit($id, $msg);
		showXHTML_mytitle_E();

		showXHTML_form_B('action="index.php" method="post" enctype="multipart/form-data" style="display:none;"', 'fm_' . $id);
			showXHTML_input('hidden', 'tabs', '2', '', '');
		showXHTML_form_E('');

		$js = <<< BOF
		// 若要 resize，則 function name 必須為 mod_{id}_resize
		function mod_{$id}_resize() {
			if (dragID != "{$id}") return false;
			var nodes = null;
			var objName = "{$id}";
			var obj = document.getElementById("div_" + objName);
			var isSmall = false;
			if ((typeof(obj) != "object") || (obj == null)) return false;
			isSmall = (parseInt(curSize) <= {$defLSize});
			obj.style.width = isSmall ? "{$Ld}px" : "{$Rd}px";
		}

		function mod_{$id}_more() {
			var obj = document.getElementById("fm_{$id}");
			if (obj != null) obj.submit();
		}
BOF;
		showXHTML_script('inline', $js);
	}

?>
