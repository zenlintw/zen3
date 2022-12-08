<?php
	/**
	 * @author  $Author: small $
	 * @version $Id: username.php,v 1.2 2011-01-20 01:40:51 small Exp $
	 * $State: Exp $
	 */
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

	/**
	 * ���o�ҵ{�N���
	 * @param string  $courseId : �ҵ{�s��
	 * @param boolean $display  : �O�_������X
	 *     true  : ������X
	 *     false : �^��Ū�������G
	 * @return
	 **/
	function getCoursePic($courseId, $display=true) {
		global $sysSession;
		list($pic) = dbGetStSr('CO_course_picture', 'picture', "course_id='{$courseId}'", ADODB_FETCH_NUM);
		if (empty($pic)){
			$filename = sysDocumentRoot . "/theme/{$sysSession->theme}/learn/co_represent.png";
			$pic      = file_get_contents($filename);
		}

		if (!$display) return $pic;

		$len = strlen($pic);
		header('Last-Modified: Thu, 01 Jan 1970 00:00:00 GMT');
		header('Expires: ' . gmdate('r', time()+259200)); // 3�Ѯɮ�
		header('Content-type: image/png');
		header('Content-transfer-encoding: binary');
		header('Content-Disposition: filename=picture.png');
		header('Accept-Ranges: bytes');
		header("Content-Length: {$len}");
		echo $pic;
	}
?>
