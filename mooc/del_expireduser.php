<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/mooc/models/user.php');
require_once(sysDocumentRoot . '/lib/login/login.inc');
require_once(sysDocumentRoot . '/lib/username.php');

$rsUser = new user();
$users = $rsUser->getExpiredUsers(3);

if (count($users) >= 1) {
    $r = $rsUser->delExpiredTmpUsers($users);
} else {
    $r = '0';
}
echo 'delete users: ' . $r;