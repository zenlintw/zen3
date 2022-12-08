<?php
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	$sysSession->cur_func='900200100';
	$sysSession->restore();
	if (!aclVerifyPermission(900200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}

	$owner_id = $sysSession->school_id;
	$owner_nm = $sysSession->school_name;
	$rec_readonly =  1;

	require_once(sysDocumentRoot . '/learn/chatrec/chatrec.inc.php');
?>
