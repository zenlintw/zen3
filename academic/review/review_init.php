<?php
	/**
	 * 修課審核
	 *
	 * @since   2003/08/01
	 * @author  ShenTing Lin
	 * @version $Id: review_init.php,v 1.1 2010/02/24 02:38:41 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/review.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	if (!aclVerifyPermission(1100500100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}
	
	// 各個身分
	$roles = array(
		'none'          => $MSG['role_none'][$sysSession->lang],
		'assistant'     => $MSG['role_assistant'][$sysSession->lang],
		'instructor'    => $MSG['role_instructor'][$sysSession->lang],
		'teacher'       => $MSG['role_teacher'][$sysSession->lang],
		'director'      => $MSG['role_director'][$sysSession->lang],
		'manager'       => $MSG['role_manager'][$sysSession->lang],
		'administrator' => $MSG['role_administrator'][$sysSession->lang],
		// 'other'         => $MSG['role_other'][$sysSession->lang],
	);

	function parseXMLRole($val, $id="WM_START") {
		if (!$dom = domxml_open_mem($val)) return false;
		$xptr = xpath_new_context($dom);
		$to   = xpath_eval($xptr, "//activity[@id='{$id}']/to");
		$ary  = $to->nodeset;
		if (count($ary) <= 0) return false;
		$role = array();
		$role['account'] = $ary[0]->get_attribute('account');
		$role['email']   = $ary[0]->get_attribute('email');
		return $role;
	}

	function getRole($val) {
		global $roles;

		$val = trim($val);
		$result = array();
		if (empty($val)) return array('none', '');

		$rest = substr($val, 1);
		if (array_key_exists($rest, $roles)) {
			$result = array($rest, '');
		} else {
			$result = array('other', $val);
		}
		return $result;
	}

?>
