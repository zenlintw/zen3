<?php
/**
 * 行事曆
 *
 * 建立日期：2004/04/01
 * @author  KuoYang Tsao
 * @copyright 2004 SUNNET
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

/*** 環境變數 ***/
$calEnv = 'academic';

/*** 是否唯讀 ***/
$calLmt = 'N';
require_once(sysDocumentRoot . '/learn/newcalendar/calendar.php');