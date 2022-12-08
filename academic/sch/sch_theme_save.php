<?php
	/**
	 * 選擇範本
	 *
	 * @since   2005/06/07
	 * @author  ShenTing Lin
	 * @version $Id: sch_theme_save.php,v 1.1 2010/02/24 02:38:42 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/file_api.php');
	require_once(sysDocumentRoot . '/lib/lib_ini.php');
	require_once(sysDocumentRoot . '/academic/sch/sch_theme_lib.php');
	require_once(sysDocumentRoot . '/lang/sch_theme.php');

	/**
	 * 備份原來的設定檔
	 *     保留十次的備份，編號越小的越新
	 **/
	function backupFile($fname) {
		@unlink("{$fname}.bk9");
		for ($i = 8; $i >= 0; $i--) {
			@rename("{$fname}.bk{$i}", "{$fname}.bk" . ($i + 1));
		}
		@rename($fname, "{$fname}.bk0");
	}

	$target = sysDocumentRoot . "/base/{$sysSession->school_id}/theme/learn/";
	// 取得上傳夾檔
	backupFile($target . '/logo.gif');
	$ret = save_upload_file($target, 0, 0);
	if (!empty($ret)) {
		$f = explode("\t", $ret);
		@rename($target . $f[1], $target . 'logo.gif');
	}
	// 複製對應的檔案
	$theme = intval($_POST['theme']);
		// 要複製的檔案列表
	$files = array(
		'help.gif'        ,
		'sysbar.css'      , 'wm.css'          ,
		'my_title1.gif'   , 'my_title2.gif'   ,
		'my_title3.gif'   , 'my_title4.gif'   ,
		'title_off_01.gif', 'title_off_02.gif',
		'title_off_03.gif',
		'title_on_01.gif' , 'title_on_02.gif' ,
		'title_on_03.gif' ,
		'mleft.gif'       , 'mright.gif'      ,
		'sleft.gif'       , 'sright.gif'      ,
		'1.gif'           , '1-1.gif'         ,
		'2.gif'           , '2-1.gif'         ,
		'3.gif'           , '3-1.gif'         ,
		'4.gif'           , '4-1.gif'         ,
		'5.gif'           , '5-1.gif'         ,
		'6.gif'           , '6-1.gif'         ,
		'7.gif'           , '7-1.gif'
	);
	if (empty($ret)) $files[] = 'logo.gif';
	$source = sysDocumentRoot . '/theme/default/' . themeMap($theme) . '/';
	foreach ($files as $val) {
		backupFile($target . $val);
		@copy($source . $val, $target . $val);
	}

	$theme = intval($_POST['theme']);
	if (empty($theme)) $theme = 1;
	$filename = sysDocumentRoot . "/base/{$sysSession->school_id}/theme/theme.ini";
	@touch($filename);
	$objAssoc = new assoc_data();
	$objAssoc->has_sections = false;
	$objAssoc->setStorePath($filename);
	$objAssoc->restore();   // 恢復原本的資料
	$theme = $objAssoc->setValues('', 'learn', $theme);
	$objAssoc->store();   // 儲存

	showXHTML_head_B($MSG['title_theme'][$sysSession->lang]);
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['theme_suit'][$sysSession->lang], 'tabs1'); //, action);
		// $ary[] = array($MSG['theme_detail'][$sysSession->lang], 'tabs2'); //, action);
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'actFm', '', 'action="sch_theme_logo.php" method="post" enctype="multipart/form-data" style="display: inline;"', false);
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				showXHTML_tr_B('class="cssTrHelp"');
					showXHTML_td('', $MSG['msg_save_success'][$sysSession->lang]);
				showXHTML_tr_E();
				showXHTML_tr_B('class="cssTrEvn"');
					showXHTML_td_B('align="center"');
						showXHTML_input('button', 'btnOK', $MSG['btn_return_suite'][$sysSession->lang], '', 'onclick="window.location.replace(\'/academic/sch/sch_theme.php\')" class="cssBtn"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();
?>
