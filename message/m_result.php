<?php
    /**
     * 搜尋結果
     *
     * 建立日期：2015/5/25
     * @author  cch
     * @version $Id: read.php,v 1.1 2015/5/25 kurt Exp $
     * @copyright 2015 SUNNET
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/username.php');
    require_once(sysDocumentRoot . '/lib/editor.php');
    require_once(sysDocumentRoot . '/lang/mooc_notebook.php');
    require_once(sysDocumentRoot . '/mooc/models/notebook.php');
    
    require_once(sysDocumentRoot . '/lang/msg_center.php');
    require_once(sysDocumentRoot . '/message/lib.php');

//    $_POST['fid'] = 'USER_5101666892878';
//    $_POST['fname'] = '1234';
    
    if (!aclVerifyPermission(2200200200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
    }
    
    if (isset($_POST['keyword']) === false) {
        die('Access deny !');
    }
    
    $smarty->assign('fid', $_POST['fid']);
    $smarty->assign('fname', $_POST['fname']);
    $smarty->assign('userPath', getUserViewPath());
    $smarty->assign('cticket', $_COOKIE['idx']);
    $smarty->assign('msg', $MSG);

    $smarty->display('common/tiny_header.tpl');
    $smarty->display('notebook/result.tpl');
    $smarty->display('common/tiny_footer.tpl');