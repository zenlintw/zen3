<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	*                                                                                                 *
	*		Programmer: cch                                                                           *
	*		Creation  : 201/2/10                                                                      *
	*		work for  : 顯示 課程代表圖                                                               *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	*       $Id: showpic.php,v 1.1 2014/2/10 02:38:44 saly Exp $                                      *	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/co_course.php');

	getCoursePic(trim($_GET['a']));
?>