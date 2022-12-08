<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/06/13 $Id: access_acllist.php,v 1.1 2010/02/24 02:38:13 saly Exp $                                                           *
	 *		work for  : 權限控管之 ACL 管理                                                   *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/academic_access.php');

#==== Function =====
	function default_permission_view($val){
		static $default_permission = array(   1 => 'E',
											  2 => 'V',
											  4 => 'R',
											  8 => 'W',
											 16 => 'O',
											 32 => 'U',
											 64 => 'D',
											128 => 'M',
											256 => 'A'
										  );
		$ret = '';
		foreach($default_permission as $k => $v) $ret .= ($val & $k) ? $v : '.';
		return $ret;
	}

	function deleteAcls($arr)
	{
		if (count($arr) == 0) return;
		$where = sprintf("acl_id in (%s)",implode(",", $arr));
		dbDel('WM_acl_list', $where);
	}
#==== Main =========

	$sysSession->cur_func='100200100';
	$sysSession->restore();
	if (!aclVerifyPermission(100200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	if (isset($_POST['op']))
	{
		switch($_POST['op'])
		{
			case 'delete':
				deleteAcls($_POST['acls']);
				break;
		}
	}



#=== 開始 output HTML ===
	showXHTML_head_B($MSG['function_maintain'][$sysSession->lang]);
	  showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");

	  $altmsg = $MSG['select_one_item_to_delete'][$sysSession->lang];
	  $scr = <<< EOB

	  function deleteAclItems()
	  {
	  		var hasChecked = false;
	  		for(i=0; i<document.save_form.elements.length; i++)
	  		{
	  			if (document.save_form.elements[i].type== "checkbox")
	  			{
	  				if (document.save_form.elements[i].checked )
	  				{
	  					hasChecked = true;
	  					break;
	  				}
	  			}
	  		}

	  		if (hasChecked == false)
	  		{
	  			alert("$altmsg");
	  			return;
	  		}

	  		document.save_form.op.value = "delete";
	  		document.save_form.submit();
	  }
EOB;
	  showXHTML_script('inline', $scr);
	showXHTML_head_E();
	showXHTML_body_B('onload="toolbar2.innerHTML = toolbar1.innerHTML;"');
	  $ary = array(array($MSG['acl_list'][$sysSession->lang], 'tabsSet', ''));
	  showXHTML_tabFrame_B($ary, 1, 'save_form', 'table_element', 'action="" method="POST" style="display: inline"');
		showXHTML_input('hidden', 'op', '', '', '');
	  	showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="760" style="border-collapse: collapse" class="cssTable"');

	  	  showXHTML_tr_B('class="cssTrEvn font01"');
	  	    showXHTML_td_B('id="toolbar1" colspan="5"');
	  	      showXHTML_input('button', '', $MSG['go_back_list'][$sysSession->lang], '', 'class="cssBtn" onclick="history.back();"');
	  	      showXHTML_input('button', '', $MSG['remove'][$sysSession->lang], '', ' onClick="deleteAclItems();"');
	  	      // showXHTML_input('button', '', $MSG['export'][$sysSession->lang], '', '');
	  	      // showXHTML_input('button', '', $MSG['import'][$sysSession->lang], '', '');
	  	    showXHTML_td_E();
	  	  showXHTML_tr_E();

	  	  showXHTML_tr_B('class="cssTrHead font01"');
	  	    showXHTML_td('width="50"', $MSG['serial_no'][$sysSession->lang]);
	  	    showXHTML_td('width="100"', $MSG['acl_serialno'][$sysSession->lang]);
	  	    showXHTML_td('width="310"', $MSG['acl_title'][$sysSession->lang]);
	  	    showXHTML_td('width="200"', $MSG['acl_actno'][$sysSession->lang]);
	  	    showXHTML_td('width="100"', $MSG['acl_access'][$sysSession->lang]);
	  	  showXHTML_tr_E();

		  $i = 1;
	  	  $rs = dbGetStMr('WM_acl_list', '*', 'function_id=' . $_GET['fid'], ADODB_FETCH_ASSOC);
		  if ($rs) while($fields = $rs->FetchRow()){
	  	    showXHTML_tr_B('class="cssTr font01"');
	  	      showXHTML_td('', $i++);
	  	      showXHTML_td_B();
	  	        showXHTML_input('checkbox', 'acls[]', $fields['acl_id'], '', '');
	  	        printf('<a href="javascript:;" onclick="this.href=\'access_acl.php?acl_id=%d&ticket=%s&%s\';" title="%s">%08d</a>', $fields['acl_id'], md5(sysTicketSeed . $fields['acl_id'] . $_GET['fid'] . $_COOKIE['idx']), $_SERVER['QUERY_STRING'], $MSG['msg_contAndModi'][$sysSession->lang], $fields['acl_id']);
	  	      showXHTML_td_E();

   		  	  if (strpos($fields['caption'], 'a:') === 0)
		  	  	$captions = unserialize($fields['caption']);
		  	  else
		  	  	$captions[$sysSession->lang] = $fields['caption'];
	  	      showXHTML_td('nowrap', '<span style="width: 310px; overflow: hidden">' . $captions[$sysSession->lang] . '</span>');
	  	      showXHTML_td('', sprintf('%d -> %d', $fields['unit_id'], $fields['instance']));
	  	      showXHTML_td('', default_permission_view(aclPermission2Bitmap($fields['permission'])));
	  	    showXHTML_tr_E();
		  }

	  	  showXHTML_tr_B('class="cssTrEvn font01"');
	  	    showXHTML_td('id="toolbar2" colspan="5"', '');
	  	  showXHTML_tr_E();

		showXHTML_table_E();
	  showXHTML_tabFrame_E();
	showXHTML_body_E();
?>
