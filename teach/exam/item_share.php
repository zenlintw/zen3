<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *              Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *              Programmer: Wiseguy Liang                                                         *
	 *              Creation  : 2004/03/02                                                            *
	 *              work for  : Item share                                                            *
	 *              work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');

	//ACL begin
	if (QTI_which == 'exam') {
		$sysSession->cur_func='1600100800';
	}
	else if (QTI_which == 'homework') {
		$sysSession->cur_func='1700100800';
	}
	else if (QTI_which == 'questionnaire') {
		$sysSession->cur_func='1800100100';
	}
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}
	//ACL end

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	// 判斷 ticket 是否正確 (開始)
	$ticket = md5($_POST['gets'] . sysTicketSeed . $course_id . $_COOKIE['idx']);
	if ($ticket != $_POST['ticket']) {
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'Illegal Access!');
	   die('Illegal Access !');
	}
	// 判斷 ticket 是否正確 (結束)
	if (!ereg('^[A-Z0-9_,]+$', $_POST['lists'])) {
	   wmSysLog($sysSession->cur_func, $course_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], 'ID format error:' . $_POST['lists']);
	   die('ID format error !'); // 判斷 ident 序列格式
	}

	chkSchoolId('WM_qti_share_item');
	$sqls = "insert ignore into WM_qti_share_item select NULL,'" . QTI_which .  "',Q.* from WM_qti_" . QTI_which .
			"_item as Q where ident in ('" . str_replace(',', "','", $_POST['lists']) .  "')";
	$sysConn->Execute($sqls);
	$count = $sysConn->Affected_Rows();
	if ($count){
		if ($topDir == 'academic')
			$source = sprintf(sysDocumentRoot . '/base/%05d/%s/Q/', $sysSession->school_id, QTI_which);
		else
			$source = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/Q/', $sysSession->school_id, $sysSession->course_id, QTI_which);
		$target = sprintf(sysDocumentRoot . '/base/%05d/QTI_share/%s/', $sysSession->school_id, QTI_which);
		if (!is_dir($target)) exec("mkdir -p '$target'");
		foreach(explode(',', $_POST['lists']) as $id){
			if (is_dir($source . $id)){
				if (!is_dir($target . $id)) mkdir($target . $id, 0770);  // 若分享目錄不存在就 mkdir()
				chdir($source . $id) AND exec("cp * '{$target}{$id}/'"); // 切換到來源目錄並複製所有檔案
			}
		}
	}
	wmSysLog($sysSession->cur_func, $course_id , 0 , 0, 'auto', $_SERVER['PHP_SELF'], 'Item share ident in ' . $_POST['lists']);
	printf("
<script>
alert('{$MSG['thanks_for_share'][$sysSession->lang]}');
//location.replace('item_maintain.php');
</script>", $count);
?>
