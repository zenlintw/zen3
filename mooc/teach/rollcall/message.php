<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/lang/rollcall.php');

    $goto = sysNewDecode($_GET['goto'],'wm5IRS');
    if ($goto === false){
        header('HTTP/1.1 403 Forbidden');
        exit;
    }

    $gotoData = unserialize($goto);
    $msgCode = intval($gotoData['code']);
    if ($msgCode <= 0){
        header('HTTP/1.1 403 Forbidden');
        exit;
    }

    $showMessage = '';
    switch ($msgCode) {
        case 1:
            // Qrcode錯誤，顯示重掃的訊息
            $showMessage = $MSG['errmsg_rescan_qrcode'][$sysSession->lang];
            break;

        case 2:
            // 登入者非本課學員身份
            $showMessage = $MSG['errmsg_not_member'][$sysSession->lang];
            break;

        case 3:
            // 登入者非正式生，但為教師、助教或旁聽生
            $showMessage = $MSG['errmsg_not_student'][$sysSession->lang];
            break;

        case 4:
            // 點名時間已結束
            $showMessage = $MSG['errmsg_rollcall_expired'][$sysSession->lang];
            break;

        case 7:
            // 已報名過，無法再報到
            $showMessage = $MSG['errmsg_not_write'][$sysSession->lang];
            break;

        default:
            // 未被定義的訊息編號，一律Forbidden
            header('HTTP/1.1 403 Forbidden');
            exit;
    }

    $smarty->assign('showMessage', $showMessage);
    $smarty->display('teach/rollcall/message.tpl');
