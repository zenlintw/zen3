<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/06/13                                                            *
	 *		work for  : 權限控管之 ACL 管理                                                   *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *      $Id: access_modifyfunction.php,v 1.1 2010/02/24 02:38:13 saly Exp $:                                                                                          *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/academic_access.php');

	$sysSession->cur_func = '100200200';
	$sysSession->restore();
	if (!aclVerifyPermission(100200200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	$rs = dbGetStSr('WM_acl_function', '*', 'function_id=' . intval($_GET['fid']), ADODB_FETCH_ASSOC);
	if (!$rs) header('access_maintain.php?' . substr(strstr($_SERVER['QUERY_STRING'], '&'))) AND exit;

	// 開始 output HTML
	showXHTML_head_B($MSG['function_maintain'][$sysSession->lang]);
	  showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	  showXHTML_script('inline', $scr);
	showXHTML_head_E();
	showXHTML_body_B();
	  $ary = array(array($MSG['mod_function'][$sysSession->lang], 'tabsSet', ''));
	  showXHTML_tabFrame_B($ary, 1, 'save_form', 'table_element', 'action="access_process1.php" method="POST" style="display: inline"');
		  showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="760" style="border-collapse: collapse" class="cssTable"');

		    showXHTML_tr_B('class="cssTrEvn font01"');
		      showXHTML_td('', $MSG['function_id'][$sysSession->lang]);
		      showXHTML_td_B();
		      	echo $rs['function_id'];
		        showXHTML_input('hidden', 'function_id', $rs['function_id']);
		        showXHTML_input('hidden', 'op', 'modifyFunction');
		        showXHTML_input('hidden', 'attributes', substr(strstr($_SERVER['QUERY_STRING'], '&'), 1));
		      showXHTML_td_E();
		      showXHTML_td('', $MSG['function_id_hint'][$sysSession->lang]);
		    showXHTML_tr_E();

		    showXHTML_tr_B('class="cssTrOdd font01"');
		      showXHTML_td('', $MSG['func_caption'][$sysSession->lang]);
		      showXHTML_td_B();
		        showXHTML_input('text', 'caption', htmlspecialchars(locale_conv($rs['caption'])), '', 'class="cssInput" size="50" maxlength="254"');
		      showXHTML_td_E();
		      showXHTML_td('', $MSG['func_caption_hint'][$sysSession->lang]);
		    showXHTML_tr_E();

		    showXHTML_tr_B('class="cssTrEvn font01"');
		      showXHTML_td('', $MSG['scope'][$sysSession->lang]);
		      showXHTML_td_B();
		        showXHTML_input('checkboxes', 'scope[]', array(1 => $MSG['env1'][$sysSession->lang],
		        					       2 => $MSG['env2'][$sysSession->lang],
		        					       4 => $MSG['env3'][$sysSession->lang],
		        					       8 => $MSG['env4'][$sysSession->lang],
		        					      ), aclScope2Array($rs['scope']), '', '<br>');
		      showXHTML_td_E();
		      showXHTML_td('', $MSG['scope_hint'][$sysSession->lang]);
		    showXHTML_tr_E();

		    showXHTML_tr_B('class="cssTrOdd font01"');
		      showXHTML_td('', $MSG['default_permission'][$sysSession->lang]);
		      showXHTML_td_B();
		        showXHTML_input('checkboxes', 'default[]', array(  1 => $MSG['enable'][$sysSession->lang]    ,
		                                                           2 => $MSG['visible'][$sysSession->lang]   ,
		                                                           4 => $MSG['readable'][$sysSession->lang]  ,
		                                                           8 => $MSG['writable'][$sysSession->lang]  ,
		                                                          16 => $MSG['modifiable'][$sysSession->lang],
		                                                          32 => $MSG['uploadable'][$sysSession->lang],
		                                                          64 => $MSG['removable'][$sysSession->lang] ,
		                                                         128 => $MSG['manageable'][$sysSession->lang],
		                                                         256 => $MSG['assignable'][$sysSession->lang]
		        					      ), aclPermission2Array($rs['default_permission']), '', '<br>');
		      showXHTML_td_E();
		      showXHTML_td('', $MSG['default_hint'][$sysSession->lang]);
		    showXHTML_tr_E();

		    showXHTML_tr_B('class="cssTrEvn font01"');
		      showXHTML_td_B('colspan="3" align="center"');
				showXHTML_input('submit', '', $MSG['mod_btn'][$sysSession->lang], '', 'class="cssBtn"');
				showXHTML_input('button', '', $MSG['cancel_btn'][$sysSession->lang], '', 'class="cssBtn" onclick="location.replace(\'access_maintain.php?' . $_POST['attributes'] . '\');"');
		      showXHTML_td_E();
		    showXHTML_tr_E();

		  showXHTML_table_E();
	  showXHTML_tabFrame_E();
	showXHTML_body_E();

?>
