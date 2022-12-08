<?php
    /**
     * 複習快通車
     *
     * PHP 5.3.28+, MySQL 5.1.59+, Apache 2.2.21+
     *
     * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
     * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
     * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
     *
     * @package     WM5
     * @author      Spring
     * @copyright   2015- SunNet Tech. INC.
     * @since       2015-03-04
     *
     */

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    require_once(sysDocumentRoot . '/mooc/models/user.php');
//    require_once(sysDocumentRoot . '/lib/editor.php');
    require_once(sysDocumentRoot . '/lang/mooc_note.php');

    // 快照本（TODO：暫時和 筆記本→筆記管理→寫筆記 共用編號）
    $sysSession->cur_func = '2600200100';
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }

//    $rsUser = new user();
    // $rsUser->setNoteByNoteId('1', $sysSession->username, '電路學-電學-0:32', '你好2');
    // $rsUser->addNoteByUsername('spring', '10000002', 'I_SCO_456465456_123123131', '測試-測試1-0:30', 'http://wm5-trunk-spring.sun.net.tw/', '00:30', '', $aId = '1234');
//    $note = $rsUser->getNoteByUsername($sysSession->username);
    // 使用者資料夾
    $userNoteDir = sprintf('/user/%1s/%1s/%s/note/',
                        substr($sysSession->username, 0, 1), substr($sysSession->username, 1, 1), $sysSession->username);
    $rsSnapshot = new snapshot();
    
    $note_course = $rsSnapshot-> getNoteCourseByUsername($sysSession->username);
    
    // 取得選單名稱
    define('SYSBAR_MENU', 'personal');
    define('SYSBAR_LEVEL', 'personal');
    require_once(sysDocumentRoot . '/academic/sysbar/main/sysbar_lib.php');
    $curTitle = getSysbarTitle(SYSBAR_MENU, SYSBAR_LEVEL, 'SYS_06_01_013');
    $smarty->assign('curTitle', $curTitle);    
    
    // FCK Editor
//    $oEditor = new wmEditor;
//    $oEditor->setEditor('rtnFckeditor');
//    $oEditor->setValue(($content) ? $content : '&nbsp;');
//    $oEditor->addContType('isHTML', 1);
//    $oEditor->addUploadFun();
//    $content = $oEditor->generate('content', 500, 350);
//    $smarty->assign('content', $content);

    // 'write.php?bTicket=' + bTicket  張貼
    $bticket = md5(sysTicketSeed . 'Board' . $_COOKIE['idx'] . $bid);
    // 'edit.php?bTicket=' + bTicket  編輯
    $ticket = md5(sysTicketSeed . 'read' . $_COOKIE['idx'] . $bid);

    $smarty->assign('bTicket', $bticket);
    $smarty->assign('ticket', $ticket);
    if ($note_course['code'] == 1) {
        $smarty->assign('noteCs', $note_course['data']);
    }

//    $smarty->assign('note', $note);
    $smarty->assign('noteDir', $userNoteDir);

    $smarty->display('m_quick_review.tpl');