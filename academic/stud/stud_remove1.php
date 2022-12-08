<?php
    /**
     * 刪除帳號 - 顯示設定的結果
     * $Id: stud_remove1.php,v 1.1 2010/02/24 02:38:45 saly Exp $
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/username.php');
    require_once(sysDocumentRoot . '/lang/stud_account.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');

    $sysSession->cur_func = '400300600';
    $sysSession->restore();
    if (!aclVerifyPermission(400300600, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }

    $ticket = md5($sysSession->ticket . 'Delete' . $sysSession->school_id . $sysSession->username);

    if (trim($_POST['ticket']) != $ticket) {
        wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 1, 'manager', $_SERVER['PHP_SELF'], '拒絕存取!');
        die($MSG['access_deny'][$sysSession->lang]);
    }


    /**
     * 安全性檢查
     *     1. 身份的檢查
     *     2. 權限的檢查
     *     3. .....
     **/

    // 設定車票
    setTicket();

    $header    = str_replace('%', '%%', trim($_POST['header']));
    $tail    = str_replace('%', '%%', trim($_POST['tail']));
    $first    = intval($_POST['first']);
    $last    = intval($_POST['last']);
    $len    = intval($_POST['len']);

    $fmt1 = "{$header}%0{$len}d{$tail}";
    $fmt2 = '<span class="color01">' . $header . '</span><span class="color02">%0' .
        $len .'d</span><span class="color03">' . $tail . '</span>';

    $succ = 0;
    $msg = array();
    $cnt = 0;
    for($i = $first; $i <= $last; $i++) {
        $temp = sprintf($fmt1, $i);
        $res = checkUsername($temp);
        if ($res == 0) {
            list($user_count) = dbGetStSr('WM_user_account', 'count(*)',"username='{$temp}'", ADODB_FETCH_NUM);
            if ($user_count > 0) {
                    $res = 2;
            }
        }
        $msg[$cnt][0] = sprintf($fmt2, $i);

        switch ($res)
        {
            case 2 :
                $msg[$cnt][1] = '&nbsp;';
                $succ++;
                break;
            case 0 :
                $msg[$cnt][1] = "<span class=\"color04\">{$MSG['account_not_exist'][$sysSession->lang]}</span>";
                break;
            case 1 :
                $msg[$cnt][1] = "<span class=\"color04\">{$MSG['system_reserved'][$sysSession->lang]}</span>";
                break;
            case 3 :
                $msg[$cnt][1] = "<span class=\"color04\">{$MSG['format_not_match'][$sysSession->lang]}</span>";
                break;
            case 4 :
                $msg[$cnt][1] = "<span class=\"color04\">{$MSG['system_reserved'][$sysSession->lang]}</span>";
                break;
        }
        $cnt++;
    }


    $js = <<< BOF
        function checkdata(){
            if (confirm("{$MSG['title60'][$sysSession->lang]}")){
                document.getElementById('btn_submit').disabled = true;
                document.getElementById('btn_submit2').disabled = true;
                return true;
            }else{
                return false;
            }
        }
BOF;
    showXHTML_head_B($MSG['delete_account'][$sysSession->lang]);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
    showXHTML_script('inline', $js);
    showXHTML_head_E();
    showXHTML_body_B();
    $arry[] = array($MSG['del_serial_account'][$sysSession->lang], 'delTable1');
        showXHTML_table_B('border="0" cellspacing="0" cellpadding="0"');
            showXHTML_tr_B();
                showXHTML_td_B();
                    showXHTML_tabs($arry, 1);
                showXHTML_td_E();
            showXHTML_tr_E();
            showXHTML_tr_B();
                showXHTML_td_B('valign="top" ');
                    // 刪除連續帳號
                    showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="delTable1" class="cssTable"');
                        showXHTML_tr_B('class="cssTrHead"');
                            showXHTML_td('colspan="2" ', $MSG['del_list'][$sysSession->lang]);
                        showXHTML_tr_E();
                        showXHTML_tr_B('class="cssTrEvn"');
                            if ($succ > 0) {
                                showXHTML_form_B('action="stud_remove2.php" method="post" onSubmit="return checkdata();"', 'delFm');
                                showXHTML_td_B('colspan="2"');
                                    showXHTML_input('hidden', 'header', $header, '', '');
                                    showXHTML_input('hidden', 'tail'  , $tail  , '', '');
                                    showXHTML_input('hidden', 'first' , $first , '', '');
                                    showXHTML_input('hidden', 'last'  , $last  , '', '');
                                    showXHTML_input('hidden', 'len'   , $len   , '', '');
                                    $ticket = md5($sysSession->username . 'AutoDelete' . $sysSession->ticket. $sysSession->school_id);
                                    showXHTML_input('hidden', 'ticket', $ticket, '', '');
                                    showXHTML_input('submit', '', $MSG['ok_del'][$sysSession->lang], '', 'id="btn_submit" class="cssBtn"');
                                    showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'stud_remove.php\');"');
                                showXHTML_td_E('&nbsp;');
                                showXHTML_form_E();
                            } else {
                                showXHTML_td_B('colspan="2" ');
                                    echo $MSG['no_del_account'][$sysSession->lang];
                                    showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'stud_remove.php\');"');
                                showXHTML_td_E('&nbsp;');
                            }
                        showXHTML_tr_E();
                        for ($i = 0; $i < $cnt; $i++) {
                            $col = ($col == 'class="cssTrOdd"') ? 'class="cssTrEvn"' : 'class="cssTrOdd"';
                            showXHTML_tr_B($col);
                                showXHTML_td('', $msg[$i][0]);
                                showXHTML_td('', $msg[$i][1]);
                            showXHTML_tr_E();
                        }
                        $col = ($col == 'class="cssTrOdd"') ? 'class="cssTrEvn"' : 'class="cssTrOdd"';
                        showXHTML_tr_B($col);
                            if ($succ > 0) {
                                showXHTML_form_B('action="stud_remove2.php" method="post"', 'delFm');
                                showXHTML_td_B('colspan="2"');
                                    showXHTML_input('hidden', 'header', $header, '', '');
                                    showXHTML_input('hidden', 'tail'  , $tail  , '', '');
                                    showXHTML_input('hidden', 'first' , $first , '', '');
                                    showXHTML_input('hidden', 'last'  , $last  , '', '');
                                    showXHTML_input('hidden', 'len'   , $len   , '', '');
                                    $ticket = md5($sysSession->username . 'AutoDelete' . $sysSession->ticket. $sysSession->school_id);
                                    showXHTML_input('hidden', 'ticket', $ticket, '', '');
                                    showXHTML_input('submit', '', $MSG['ok_del'][$sysSession->lang], '', 'id="btn_submit2" class="cssBtn"');
                                    showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'stud_remove.php\');"');
                                showXHTML_td_E('&nbsp;');
                                showXHTML_form_E();
                            } else {
                                showXHTML_td_B('colspan="2" ');
                                    echo $MSG['no_del_account'][$sysSession->lang];
                                    showXHTML_input('button', '', $MSG['cancel'][$sysSession->lang], '', 'class="cssBtn" onclick="window.location.replace(\'stud_remove.php\');"');
                                showXHTML_td_E('&nbsp;');
                            }
                        showXHTML_tr_E();
                    showXHTML_table_E();

                showXHTML_td_E();
            showXHTML_tr_E();
        showXHTML_table_E();
    showXHTML_body_E();
?>
