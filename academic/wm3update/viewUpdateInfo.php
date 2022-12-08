<?php
	/**
	*  show update infomation about wm3update patches
	*  author: jeff wang
	*  since: 2006-10-05
	*/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/academic/wm3update/lib.php');
	
/*==== function ===*/
	
	
/*==== main ====*/
	// check if http gets arguments is valided.
	if ((!isset($_GET['content'])) || (!isset($_GET['which'])))
	{
		die('arguments error!');
	}

	switch($_GET['content'])
	{
		case 'readme':
			$obj = new WM3UpdateInfo($_GET['which']);
			header('Content-type: text/html');
			header('Content-Disposition: filename="readme.htm"');
			echo $obj->getReadmeContent();
			break;
		case 'filelist':
			$obj = new WM3UpdateInfo($_GET['which']);
			echo $obj->getFilelist();
			break;
		default:
			die('arguments error!');
	}


?>