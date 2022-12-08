<?php
/**
 * 教材檔案總管
 *
 * 建立日期：2002/08/26
 * @author  Wiseguy
 * @version $Id: filemanager.php,v 1.1 2010/02/24 02:38:20 saly Exp $
 **/

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
$sysSession->cur_func = '1200200100';
$sysSession->restore();
if (!aclVerifyPermission(1200200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

dbSet('WM_auth_ftp', "home='" . sprintf('%s/base/%05d/content/%06d/', sysDocumentRoot, $sysSession->school_id, $_SERVER['argv'][0]) . "'", "userid='{$sysSession->username}'");

define('basePath', sprintf('%s/base/%05d/content/%06d/', sysDocumentRoot, $sysSession->school_id, $_SERVER['argv'][0]));
require_once(sysDocumentRoot . '/teach/files/manager.php');