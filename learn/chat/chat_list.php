<?php
/**
 * 學生端的聊天室列表
 *
 * @since   2003/12/25
 * @author  ShenTing Lin
 * @version $Id: chat_list.php,v 1.1 2010/02/24 02:39:05 saly Exp $
 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
$sysSession->cur_func = '2000200100';
$sysSession->restore();
if (!aclVerifyPermission(2000200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

$owner_id = $sysSession->course_id;
require_once(sysDocumentRoot . '/learn/chat/chat_main_list.php');
