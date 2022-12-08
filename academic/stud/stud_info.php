<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                       *
	*		Creation  : 2003/09/23                                                                    *
	*		work for  :  基本資料 & 修課記錄 &　學習成果                                                                      *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	*       $Id: stud_info.php,v 1.1 2010/02/24 02:38:45 saly Exp $                                                                                          *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	
	$ACADEMIC_DELETE_MEMBER = true;
	$uri_target = 'stud_remove.php?msgtp=2';
	
	require_once(sysDocumentRoot . '/academic/stud/stud_query1.php');
?>
