<?php
/**************************************************************************************************
 *
 *		Wisdom Master 5(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
 *
 *		Programmer: cch
 *              SA        : 914
 *		Creation  : 2016/12/2
 *		work for  : 下載教材
 *		work on   : Apache 2.2.21, MySQL 5.1.59 , PHP 5.3.28
 *
 **************************************************************************************************/

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/lang/learn_path.php');

// 網頁權限控制
$sysSession->cur_func = '1900200100';
$sysSession->restore();
if (!aclVerifyPermission(1900200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

// 下載預覽區
$key = md5(sysTicketSeed . htmlspecialchars($_COOKIE['idx']));
$realpath = trim(sysNewDecode(htmlspecialchars(rawurldecode($_GET['path'])), $key, true));
if (preg_match('/^\/base\/\d+\/course\/\d{8}\/content\/.+\.[a-zA-Z0-9]+$/', $realpath)) {
    $smarty->assign('filename', basename($realpath));
    
    $smarty->assign('candown', true);
    if(strstr($_SERVER['HTTP_USER_AGENT'],'iPad') || strstr($_SERVER['HTTP_USER_AGENT'],'iPhone')) {
    	$arr_ext = array('flv','wmv','swf','wma','zip','rar','tar','7z');
        $ext = pathinfo(basename($realpath), PATHINFO_EXTENSION);
        if (in_array($ext,$arr_ext)) {
        $smarty->assign('candown', false);
        }
    } 
    
    if (file_exists(sysDocumentRoot . $realpath) === true) {
        $smarty->assign('filesize', FileSizeConvert(filesize(sysDocumentRoot . $realpath)));
        $smarty->assign('path', htmlspecialchars(rawurldecode($_GET['path'])));
    } else {
        $smarty->assign('filesize', '');
        $smarty->assign('message', sprintf($MSG['msg_file_not_exist'][$sysSession->lang], basename($realpath)));
    }

    require_once(sysDocumentRoot . '/lib/Mobile_Detect.php');
    // 判斷使用者是否使用行動裝置
    $detect = new Mobile_Detect;
    if($detect->isMobile() && !$detect->isTablet()){
        // 輸出
        $smarty->display('common/tiny_header.tpl');
        $smarty->display('phone/learn/download_materials.tpl');
        $smarty->display('common/tiny_footer.tpl');

    }else{
        // 輸出
        $smarty->display('common/tiny_header.tpl');
        $smarty->display('learn/download_materials.tpl');
        $smarty->display('common/tiny_footer.tpl');
    }


} else {
    die('Access denied.');
}