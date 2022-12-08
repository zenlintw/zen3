<?php
	/**
	 * 實際圖片預覽
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/app_course_manage.php');
	
	$picture = base64_decode($_GET['picture']);
	$pictureFileBig5 = iconv('UTF-8', 'BIG5', $picture);
	if(!is_file(sysDocumentRoot.$pictureFileBig5)) {
		$picture = $MSG['msg_read_file_error'][$sysSession->lang];
	} else {
		$picture = "<img src='{$picture}' width='1024' height='468'>";
	}
	
	$caption = base64_decode($_GET['caption']);
	if(strlen($caption)===0) {
		$caption = $MSG['msg_no_caption'][$sysSession->lang];
	}
	
	showXHTML_head_B($MSG['title_manage'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E('');

	showXHTML_body_B('');
		$ary = array();
		$ary[] = array($caption, 'tabs');

		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'mainFm', 'ListTable', '" style="display: inline;"');
			showXHTML_table_B('width="900" border="0" cellspacing="1" cellpadding="3" id="dataTb" class="cssTable"');
				showXHTML_tr_B();
					showXHTML_td('align="center"',$picture);
				showXHTML_tr_E();
				showXHTML_tr_B();
					showXHTML_td_B('align="center"');
						echo '<br>';
						showXHTML_input('button', '', $MSG['btn_close'][$sysSession->lang], '', 'class="button01" onclick="window.close();"');
					showXHTML_td_E('');
				showXHTML_tr_E();
			showXHTML_table_E('');
		showXHTML_tabFrame_E();
		echo '</div>';

	showXHTML_body_E('');
?>
