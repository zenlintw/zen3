<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *      Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	 *                                                                                                *
	 *      Programmer: Saly Lin                                                                 *
	 *      Creation  : 2004/03/8                                                                    *
	 *      work for  :                                                                               *
	 *      work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
	require_once(sysDocumentRoot . '/lib/lstable.php');
	require_once(sysDocumentRoot . '/lang/teach_course.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$sysSession->cur_func = '900100300';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	function divMsg($width=100, $caption='&nbsp;', $title='', $without_title=false) {
		if (empty($title)) $title = $caption;
		return $without_title ? ('<div style="width: ' . $width . 'px; overflow:hidden;">' . $caption . '</div>') : ('<div style="width: ' . $width . 'px; overflow:hidden;" title="' . $title . '">' . $caption . '</div>');
	}

	function showGrouping($val) {
		global $sysSession;
		$lang = getCaption($val);
		return divMsg(120, htmlspecialchars_decode($lang[$sysSession->lang]));
	}

	function showSubject($val, $act) {
		global $sysSession;
		$lang = getCaption($val);
		return divMsg(500, '<a href="javascript:;" onclick="return false;" class="cssAnchor">' . htmlspecialchars_decode($lang[$sysSession->lang]) . '</a>', strip_tags(htmlspecialchars_decode($lang[$sysSession->lang])) . '" onclick="goBoard(\'' . sysEncode($act) . '\')');
	}

	$js = <<< EOB

	/**
	 * 設定議題討論版
	 * @param string val : 議題討論版編號
	 **/
	function setSubject(val) {
		var obj = document.getElementById("editFm");
		if ((typeof(obj) != "object") || (obj == null)) return false;
		obj.bid.value = val;
		obj.submit();
	}

	function goBoard(val) {
		if ((typeof(parent.c_sysbar) == "object") && (typeof(parent.c_sysbar.goBoard) == "function")) {
			parent.c_sysbar.goBoard(val);
		}
	}

	function goto_group() {
		window.location.replace("cour_subject.php");
	}

EOB;

	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_script('inline', $js);
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array(
					array($MSG['subject_title'][$sysSession->lang], 'tabsSet1',  "goto_group();"),
					array($MSG['subject_title1'][$sysSession->lang], 'tabsSet2',  '')
				);
		echo '<div align="center">';
                $display_css['table'] = 'width="1000"';
		showXHTML_tabFrame_B($ary, 2, 'muteFm', '', 'onsubmit="return false;" method="post" enctype="multipart/form-data" style="display:inline"', null, null, $display_css);
			showXHTML_input('hidden', 'nodeids', '', '', 'id="nodeids"');

			$myTable = new table();
			$myTable->extra = 'width="1000" border="0" cellspacing="1" cellpadding="3" id="dataTabs" class="cssTable"';

			// 工具列
			$btns = new toolbar();
			$btns->add_input('button', '', $MSG['btm_modify'][$sysSession->lang], '', 'class="cssBtn" onclick="setSubject(\'%0\')"');
			
			$myTable->add_field($MSG['grouping_times'][$sysSession->lang], '', '', '%2'    , 'showGrouping' , 'nowrap="noWrap"');
			$myTable->add_field($MSG['title_subject'][$sysSession->lang] , '', '', '%1 %0' , 'showSubject'  , 'nowrap="noWrap"');
			$myTable->add_field($MSG['title_action'][$sysSession->lang]  , '', '', $btns   , ''             , 'align="center"' );

			$tab    = 'WM_student_separate as S,WM_student_group as G,WM_bbs_boards as B';
			$fields = ' G.`board_id`, `bname`, `team_name`';
			$where  = " G.team_id=S.team_id and G.course_id=S.course_id and G.course_id={$sysSession->course_id} and B.board_id = G.board_id order by G.team_id,G.group_id";

			$myTable->set_sqls($tab, $fields, $where);
			$myTable->show();

		showXHTML_tabFrame_E();
		echo '</div>';

		showXHTML_form_B('action="cour_group_subject_property.php" method="post" enctype="multipart/form-data" style="display:none"', 'editFm');
			showXHTML_input('hidden', 'bid', '', '', '');
			showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . 'setBoard' . $_COOKIE['idx']), '', '');
		showXHTML_form_E();

	showXHTML_body_E();
?>
