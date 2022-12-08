<?php
/*
 * jQuery File Upload Plugin PHP Example 5.14
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

// error_reporting(E_ALL | E_STRICT);
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');

switch ($sysSession->env) {
    case 'academic':
        // 檢查是否具有一般管理者, 進階管理者, root的權限
        if (!aclCheckRole($sysSession->username, ($sysRoles['manager'] | $sysRoles['administrator'] | $sysRoles['root']), $sysSession->school_id, false)) {
            header('HTTP/1.0 403 Forbidden');
            exit;
        }
        break;
    case 'teach':
        // 檢查是否具有教師、助教、講師的權限(需先進入某一課程後，才能透過網址列切換)
        if (preg_match('/[\d]{8}/', $sysSession->course_id) && !aclCheckRole($sysSession->username, ($sysRoles['teacher'] | $sysRoles['assistant'] | $sysRoles['instructor']), $sysSession->course_id, false)) {
            header('HTTP/1.0 403 Forbidden');
            exit;
        // 校務問卷
        } else if (preg_match('/[\d]{5}/', $sysSession->course_id)) {
            $forGuest = aclCheckWhetherForGuestQuest($sysSession->course_id, $_GET['serial_number']);
            if (!$forGuest && !aclCheckRole($sysSession->username, ($sysRoles['teacher'] | $sysRoles['assistant'] | $sysRoles['instructor'] | $sysRoles['student'] | $sysRoles['auditor']), 0, false)) {
                header('HTTP/1.0 403 Forbidden');
                exit;
            }
        }
        break;
    case 'direct':
        // 檢查是否具有導師的權限(需先事先進入導師辦公室過，才能透過網址列直接切換)
        if (!aclCheckRole($sysSession->username, ($sysRoles['director']), $sysSession->class_id, false)) {
            header('HTTP/1.0 403 Forbidden');
            exit;
        }
        break;
    case 'learn':
        $forGuest = aclCheckWhetherForGuestQuest($sysSession->course_id, $_GET['serial_number']);
        if (!$forGuest && !aclCheckRole($sysSession->username, ($sysRoles['teacher'] | $sysRoles['assistant'] | $sysRoles['instructor'] | $sysRoles['student'] | $sysRoles['auditor']), 0, false)) {
            header('HTTP/1.0 403 Forbidden');
            exit;
        }
        break;
    default:
        header('HTTP/1.0 403 Forbidden');
        exit;
        break;
}

define('ValidRoleCheck', 1);

require('UploadHandler.php');
$upload_handler = new UploadHandler();