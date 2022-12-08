<?php
    /**
     * 訂閱討論版文章
     *
     * 建立日期：2004/04/30
     * @author  KuoYang Tsao
     * @copyright 2003 SUNNET
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/forum.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');

    $sysSession->cur_func = '900200300';
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
    }

    // 取討論版編號
    $std = "/^([0-9]+)$/";
    if (strlen($_POST['bid']) === 10 && preg_match($std, $_POST['bid']) === 1) {
        $bid = $_POST['bid'];
    } else {
        $bid = $sysSession->board_id;
    }

    list($count) = dbGetStSr('WM_bbs_order', 'count(*)', "board_id = {$bid} and username = '{$sysSession->username}'", ADODB_FETCH_NUM);
    if ($count > 0) {
        dbDel('WM_bbs_order', "board_id = {$bid} and username = '{$sysSession->username}'");
        $MsgTitle = $MSG['unsubscribe'][$sysSession->lang];
        $js_update= 'false'; // 顯示取消訂閱
    } else {
        dbNew('WM_bbs_order', 'board_id, username', "{$bid}, '{$sysSession->username}'");
        $MsgTitle = $MSG['subscribe'][$sysSession->lang];
        $js_update= 'true'; // 顯示訂閱
    }

    if ($sysConn->Affected_Rows() > 0) {
    $MsgAction = $MSG['success_to'][$sysSession->lang];

    wmSysLog($sysSession->cur_func, $sysSession->class_id , $bid , 0, 'auto', $_SERVER['PHP_SELF'], $MsgTitle . $MsgAction);

    $js = <<< BOF
      var parWin = dialogArguments;
      parWin.displaySubscribeButton({$js_update});
BOF;
    } else {
        $MsgAction = $MSG['fail_to'][$sysSession->lang];
        wmSysLog($sysSession->cur_func, $sysSession->class_id , $bid , 1, 'auto', $_SERVER['PHP_SELF'], $MsgTitle . $MsgAction);
        $js = "";
    }

    $MsgNote  = $MsgAction . $MsgTitle . "[" . $sysSession->board_name ."]";

    showXHTML_head_B($MsgNote);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    showXHTML_script('inline', $js);
    showXHTML_head_E('');
    showXHTML_body_B('');
    $ary = array();
    $ary[] = array($MsgTitle, '');
    // $colspan = 'colspan="2"';
    showXHTML_tabFrame_B($ary, 1); //, '', table_id, form_extra, isDragable);
        showXHTML_table_B('width="300" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
            showXHTML_tr_B('class="cssTrEvn"');
                showXHTML_td('',$MsgNote);
            showXHTML_tr_E('');
            showXHTML_tr_B('class="cssTrOdd"');
                showXHTML_td_B('align="center"');
                showXHTML_input('button','btnClose',$MSG['close_window'][$sysSession->lang],'','onClick="window.close()"');
                showXHTML_td_E('');
            showXHTML_tr_E('');
        showXHTML_table_E('');
    showXHTML_tabFrame_E();
    showXHTML_body_E('');