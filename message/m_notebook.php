<?php
/**
 * 讀取筆記
 *
 * 建立日期：2015/4/30
 * @author  cch
 * @version $Id: read.php,v 1.1 2015/4/30 kurt Exp $
 * @copyright 2015 SUNNET
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/editor.php');
require_once(sysDocumentRoot . '/lib/file_api.php');
require_once(sysDocumentRoot . '/lang/mooc_upload.php');
require_once(sysDocumentRoot . '/lang/mooc_notebook.php');
require_once(sysDocumentRoot . '/mooc/models/notebook.php');

require_once(sysDocumentRoot . '/message/lib.php');

if (!aclVerifyPermission(2200200200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
{
}

if (isset($_POST['fid']) === false) {
    die('Access deny !');
}

$smarty->assign('fid', $_POST['fid']);
$smarty->assign('id', $_POST['id']);
$smarty->assign('fname', htmlspecialchars(urldecode($_POST['fname'])));
$smarty->assign('userPath', getUserViewPath());
$smarty->assign('cticket', $_COOKIE['idx']);
$smarty->assign('msg', $MSG);

// FCK Editor
$oEditor = new wmEditor;
$oEditor->setEditor('rtnFckeditor');
$oEditor->setValue('');
$oEditor->addContType('isHTML', 1);
$oEditor->addUploadFun();
$htmleditorName = 'notebook-mod-content';// 給予要變成編輯器的元素名稱
$bw = $oEditor->chkBrowser();
$headers = apache_request_headers();
$isIE11 = preg_match('/Trident\/(\d+)/', $headers['User-Agent'], $regs) && intval($regs[1])> 6;

// 給js lib用
$smarty->assign('htmleditorname', $htmleditorName); // 編輯器名稱
$smarty->assign('bw0', $bw[0]); // 判斷 MSIE
$smarty->assign('setUploadFun', $oEditor->setUploadFun);
$smarty->assign('isIE11', $isIE11);// 判斷 IE11

$content = $oEditor->generate($htmleditorName, '100%', 375);
$smarty->assign('notebook_mod_content', $content);  // 編輯器html tag

$smarty->display('common/tiny_header.tpl');
$smarty->display('notebook/notebook.tpl');
$smarty->display('common/tiny_footer.tpl');