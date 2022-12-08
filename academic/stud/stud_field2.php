<?php
 /**************************************************************************************************
 *                                                                                                *
 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
 *                                                                                                *
 *		Programmer: amm lee                                                         *
 *		Creation  : 2004/01/08                                                            *
 *		work for  : 匯出人員資料 (第四步驟 -> 匯出 => 班級 或 課程 )                                                                                 *
 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.4                                *
 *      $Id: stud_field2.php,v 1.1 2010/02/24 02:38:44 saly Exp $                                                                                          *
 **************************************************************************************************/

	define('EXPORT_TYPE', 'multi');
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/academic/stud/stud_field1.php');
?>