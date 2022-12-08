<?php
	/**
	 * 實際圖片預覽
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/app_course_manage.php');

	$activityIds = base64_decode(trim($_GET['activityIds']));
	
	$aryActivity = explode(',',$activityIds);
	
	for($i=0;$i<count($aryActivity);$i++) {
		$j = $i+1;
		$activityId = $aryActivity[$i];
		$newPermute = $j;
		dbSet('CO_activities',"permute={$newPermute}","act_id={$activityId}");
	}
	
	echo '<script>window.close()</script>';
?>
