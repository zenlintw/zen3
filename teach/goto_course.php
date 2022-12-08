<?php
/**
* 教師環境的切換課程
* $Id: goto_course.php,v 1.1 2010/02/24 02:40:26 saly Exp $
**/

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
$sysSession->env = 'teach';
$sysSession->restore();

$getSysbar = true;

$envWork = $sysSession->env;

$envRead = 'teach';

// 清除三合一試題管理之搜尋條件
setcookie('QuestionItemQueryConds', '', time() - 3600, '/teach/homework/');
setcookie('QuestionItemQueryConds', '', time() - 3600, '/teach/exam/');
setcookie('QuestionItemQueryConds', '', time() - 3600, '/teach/questionnaire/');

require_once(sysDocumentRoot . '/academic/goto.php');