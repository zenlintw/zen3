<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2004/06/15                                                            *
	 *		work for  :                                             *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/academic_access.php');

	$sysSession->cur_func = '100200100';
	$sysSession->restore();
	if (!aclVerifyPermission(100200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}

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



	if (ereg('^[0-9]+$', $_GET['fid'])){
		$func = dbGetStSr('WM_acl_function', '*', 'function_id=' . $_GET['fid'], ADODB_FETCH_ASSOC);
		if (!$func || $sysConn->ErrorNo() ) {
		   wmSysLog($sysSession->cur_func, $sysSession->school_id , 0, 1, 'manager',$_SERVER['PHP_SELF'], $MSG['msg_noFuncId'][$sysSession->lang]);
		   die('<script>alert("'.$MSG['msg_noFuncId'][$sysSession->lang].'"); history.back(); </script>');
		}
	}
	else {
		wmSysLog($sysSession->cur_func, $sysSession->school_id , 0, 2, 'manager',$_SERVER['PHP_SELF'], $MSG['msg_notCorrFuncId'][$sysSession->lang]);
		die('<script>alert("'.$MSG['msg_notCorrFuncId'][$sysSession->lang].'"); history.back(); </script>');
	}

	// 如果是修改 ACL
	if (ereg('^[0-9]+$', $_GET['acl_id'])){
		$ticket = md5(sysTicketSeed . $_GET['acl_id'] . $_GET['fid'] . $_COOKIE['idx']);
		if ($ticket != $_GET['ticket']) die('Fake ACL_ID.');

	$acl = dbGetStSr('WM_acl_list', '*', "acl_id={$_GET['acl_id']}", ADODB_FETCH_ASSOC);
		if ($acl === FALSE) die('ACL not found.');
		$perm_bitmaps = aclPermission2Bitmap($acl['permission']);
		$onload = sprintf('onload="fetchElement(%d, %d);"', $acl['unit_id'], $acl['instance']);

		$rs = dbGetStMr('WM_acl_member', 'member', "acl_id={$_GET['acl_id']}", ADODB_FETCH_ASSOC);
		$extra_member = '';
		$roles = 0;
		if ($rs) while($field = $rs->FetchRow()){
			if (strpos($field['member'], '#') === 0)
				$roles += $GLOBALS['sysRoles'][substr($field['member'], 1)];
			else
				$extra_member .= $field['member'] . "\n";
		}
		$caption = $MSG['msg_modiACL'][$sysSession->lang];
	}
	else{
		$caption = $MSG['msg_creaACL'][$sysSession->lang];
	}


	list($x, $scope) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF']); // 取得第一層目錄名
	showXHTML_head_B($caption);
	showXHTML_CSS('include', sprintf('/theme/%s/%s/wm.css', $sysSession->theme, $scope));
	showXHTML_script('include', '../../lib/xmlextras.js');
	showXHTML_script('inline',"

	function fetchElement(department_id, element_id){
		if (department_id.toString().search(/^[0-9]{6,8}$/) !== 0) return;
		var xmlHttp = XmlHttp.create();
		if (element_id == null)
			xmlHttp.open('POST', 'access_fetchElement.php?' + department_id, false);
		else
			xmlHttp.open('POST', 'access_fetchElement.php?' + department_id + '+' + element_id, false);
		xmlHttp.send();
		elementSelect.innerHTML = xmlHttp.responseText;
	}

", false);
	showXHTML_head_E();
	showXHTML_body_B($onload);
	  aclGenerateAclControlPanel($func, '', $acl, $roles, $extra_member, false);
	showXHTML_body_E();
?>
