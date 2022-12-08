<?php
	/**
	 * 列出檔案
	 *
	 * @since   2012/08/20
	 * @author  ShenTing Lin
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

    if (isset($_GET['name'])) {
        $filename = strip_tags($_GET['name']);
        $filePath = '/base/%05d/door/APP/wmmedia/cover/%s';
    }

	$filename = preg_replace(
		array('/\.\.+/', '/[\/\\\\]{2,}/'),
		array('', '/'),
		$filename
	);
	
	$url = sprintf(
		$filePath,
		$sysSession->school_id, $filename
	);
	header('Location: ' . $url);