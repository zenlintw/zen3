<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

    // 清除 course_id
    $sysSession->course_id = '';
    $sysSession->course_name = '';
    $sysSession->restore();
    dbSet('WM_session', 'course_id=0, course_name=""', "idx='{$_COOKIE['idx']}'");

    header('Location: /learn/index.php');