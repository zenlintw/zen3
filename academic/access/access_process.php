<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/06/16                                                            *
	 *		work for  : 權限控管之 ACL 新增                                                   *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/academic_access.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	$sysSession->cur_func = '100200100';
	$sysSession->restore();
	if (!aclVerifyPermission(100200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

	if (is_array($_POST['fid']) && count($_POST['fid'])){
		switch($_POST['op']){
			case 'remove':
				$fids = implode(',', $_POST['fid']);
				if (ereg('^[0-9]+(,[0-9]+)*$', $fids)){
					$rs = dbGetStMr('WM_acl_list', 'acl_id', "function_id in ($fids)", ADODB_FETCH_ASSOC);
					$acls = '';
					if ($rs) while($fields = $rs->FetchRow()) $acls .= $fields['acl_id'] . ',';
					$acls = substr($acls, 0, -1);
					if ($acls){
						dbDel('WM_acl_member', "acl_id in ($acls)");
						dbDel('WM_acl_list', "acl_id in ($acls)");
					}
					dbDel('WM_acl_function', "function_id in ($fids)");
					wmSysLog($sysSession->cur_func, $sysSession->school_id ,0 ,0, 'manager', $_SERVER['PHP_SELF'], "remove function_id in ($fids) acl_id in ($acls)!");
				}
				break;
			case 'modifyScope':
			case 'modifyPermission':
	// 開始 output HTML
	showXHTML_head_B($MSG['function_maintain'][$sysSession->lang]);
	  showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
	  showXHTML_script('inline', $scr);
	showXHTML_head_E();
	showXHTML_body_B();
	  $ary = $_POST['op'] == 'modifyScope' ? array(array($MSG['msg_chagEffeRang'][$sysSession->lang], 'tabsSet', '')) :
	  										 array(array($MSG['msg_chagDefaLimi'][$sysSession->lang], 'tabsSet', ''));
	  showXHTML_tabFrame_B($ary, 1, 'save_form', 'table_element', 'action="access_process1.php" method="POST" style="display: inline"');
		  showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="760" style="border-collapse: collapse" class="box01"');

		    showXHTML_tr_B('class="bg03 font01"');
		      showXHTML_td('', $MSG['function_id'][$sysSession->lang]);
		      showXHTML_td_B();
		        echo '<ol><li>', implode('</li><li>', $_POST['fid']), '</li></ol>';
		        showXHTML_input('hidden', 'function_id', implode(',', $_POST['fid']));
		        showXHTML_input('hidden', 'op', $_POST['op']);
		        showXHTML_input('hidden', 'attributes', $_POST['attributes']);
		      showXHTML_td_E();
		      showXHTML_td('', $MSG['function_id_hint'][$sysSession->lang]);
		    showXHTML_tr_E();

			if ($_POST['op'] == 'modifyScope'){
		    showXHTML_tr_B('class="bg04 font01"');
		      showXHTML_td('', $MSG['scope'][$sysSession->lang]);
		      showXHTML_td_B();
		        showXHTML_input('checkboxes', 'scope[]', array(1 => $MSG['env1'][$sysSession->lang],
		        					      					   2 => $MSG['env2'][$sysSession->lang],
		        					      					   4 => $MSG['env3'][$sysSession->lang],
		        					      					   8 => $MSG['env4'][$sysSession->lang],
		        					      					  ), array(1,2), '', '<br>');
		      showXHTML_td_E();
		      showXHTML_td('', $MSG['scope_hint'][$sysSession->lang]);
		    showXHTML_tr_E();
			}
			else{
		    showXHTML_tr_B('class="bg04 font01"');
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
		        					      ), array(1,2,4,8,16,32,64), '', '<br>');
		      showXHTML_td_E();
		      showXHTML_td('', $MSG['default_hint'][$sysSession->lang]);
		    showXHTML_tr_E();
			}

		    showXHTML_tr_B('class="bg03 font01"');
		      showXHTML_td_B('colspan="3" align="center"');
				showXHTML_input('submit', '', $MSG['add_function'][$sysSession->lang], '', 'class="button01"');
				showXHTML_input('button', '', $MSG['msg_giveUp'][$sysSession->lang], '', 'class="button01" onclick="location.replace(\'access_maintain.php?' . $_POST['attributes'] . '\');"');
		      showXHTML_td_E();
		    showXHTML_tr_E();
		  showXHTML_table_E();
	  showXHTML_tabFrame_E();
	showXHTML_body_E();
				exit;
				break;
		}
	}
	header('Location: access_maintain.php?' . $_POST['attributes']);
?>
