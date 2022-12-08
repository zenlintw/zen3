<?php
	/**
	 * 新的我的課程
	 *
	 * @since   2004/09/16
	 * @author  ShenTing Lin
	 * @version $Id: mycourse.php,v 1.1 2010/02/24 02:39:08 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	if (isset($_GET['lang'])) $sysSession->lang = trim($_GET['lang']);
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/learn/mycourse/mycourse_lib.php');
	require_once(sysDocumentRoot . '/lang/mycourse.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	
	define('MYCOURSE_MODULE', true);
	// $sysSession->cur_func='2500100100';
	// $sysSession->restore();
	
	if (!aclVerifyPermission(2500100100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$isEdit = ($sysSession->username != 'guest');
	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	if ($isEdit) {
		showXHTML_script('include', '/learn/mycourse/mycourse_lib.js');
		showXHTML_script('include', '/lib/xmlextras.js');
	}
	showXHTML_head_E();
	showXHTML_body_B('');
		showXHTML_table_B('align="center" width="' . $defWidth . '" border="0" cellspacing="0" cellpadding="0"');
			showXHTML_tr_B();
				showXHTML_td_B('colspan="3"');
					$defSize = $defWidth;
					$ary = $myConfig->assoc_ary['MyConfig_Head'];
					$dir = sysDocumentRoot . '/learn/mycourse/modules/';
					foreach ($ary as $key => $val) {
						$visible = $myConfig->getValues($val, 'visibility');
						if ($visible != 'visible') continue;
						include_once($dir . $modTable[$val]);
					}
				showXHTML_td_E();
			showXHTML_tr_E();
			showXHTML_tr_B();
				showXHTML_td_B('width="' . $defLSize . '" align="right" valign="top"');
					$isEdit = ($sysSession->username != 'guest');
					$msg = ($isEdit) ? $MSG['msg_reposition_here'][$sysSession->lang] : '&nbsp;';
					showXHTML_mytitle_postit('lt', $msg, " width: {$defLSize}px;");
					echo '<div id="lt"></div>';
					$defSize = $defLSize;
					$ary = $myConfig->assoc_ary['MyConfig_Col1'];
					$dir = sysDocumentRoot . '/learn/mycourse/modules/';
					foreach ($ary as $key => $val) {
						$visible = $myConfig->getValues($val, 'visibility');
						if ($visible != 'visible') continue;
						include_once($dir . $modTable[$val]);
					}
					echo '<div id="lb"></div>';
				showXHTML_td_E();
				showXHTML_td('width="25"', '&nbsp;');
				showXHTML_td_B('width="' . $defRSize . '" align="left" valign="top"');
					$isEdit = ($sysSession->username != 'guest');
					$msg = ($isEdit) ? $MSG['msg_reposition_here'][$sysSession->lang] : '&nbsp;';
					showXHTML_mytitle_postit('rt', $msg);
					echo '<div id="rt"></div>';
					$defSize = $defRSize;
					$ary = $myConfig->assoc_ary['MyConfig_Col2'];
					$dir = sysDocumentRoot . '/learn/mycourse/modules/';
					foreach ($ary as $key => $val) {
						$visible = $myConfig->getValues($val, 'visibility');
						if ($visible != 'visible') continue;
						include_once($dir . $modTable[$val]);
					}
					echo '<div id="rb"></div>';
				showXHTML_td_E();
			showXHTML_tr_E();
		showXHTML_table_E();
	showXHTML_body_E();
	$myConfig->store();   // 儲存設定值
?>
