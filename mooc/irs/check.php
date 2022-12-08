<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

// 取得 URL 參數
$action = trim($_GET['action']);

switch ($action) {
    case 'start':
        // TODO: 可將驗證部分加到此頁面
        // 導向開始作答頁面
        header('LOCATION: exam_start.php?goto=' . trim($_GET['goto']));
        break;
    default:
        header('LOCATION: /mooc/irs/message.php?goto='.sysNewEncode(serialize(array()), 'wm5IRS'));
}
