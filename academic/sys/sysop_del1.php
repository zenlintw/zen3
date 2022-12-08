<?php
/**
 * 刪除管理者
 *
 * @since   2003/10/15
 * @author  ShenTing Lin
 * @version $Id: sysop_del1.php,v 1.1 2010/02/24 02:38:45 saly Exp $
 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/academic/sys/lib.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/common.php');
require_once(sysDocumentRoot . '/lib/username.php');

$sysSession->cur_func = '100400300';
$sysSession->restore();
if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

// $sysConn->debug = true;
/**
 * 安全性檢查
 *     1. 身份的檢查
 *     2. 權限的檢查
 *     3. .....
 **/

// 安全與權限檢查

// 設定車票
setTicket();

// 取出操作此功能的管理者的等級
$level = intval(getAllSchoolTopAdminLevel($sysSession->username));
if ($level < $sysRoles['administrator'])
    $level = intval(getAdminLevel($sysSession->username));
if ($level <= 0) {
    wmSysLog($sysSession->cur_func, $sysSession->school_id, 0, 1, 'manager', $_SERVER['PHP_SELF'], '不具備管理者的權限!');
    die($MSG['not_sysop'][$sysSession->lang]);
}

$sqls  = '';
$sysop = array();
$user  = array();

// 建立刪除人員列表 (Begin)
$i = -1;
foreach ($_POST['ckUname'] as $val) {
    if (!preg_match('/^([\w-]+),(\d+)$/', $val, $ulist))
        continue;
    array_shift($ulist);
    
    $i++;
    // 建立資料
    $sqls      = "`username`='{$ulist[0]}' AND `school_id`={$ulist[1]}";
    $RS        = dbGetStSr('WM_manager', '*', $sqls, ADODB_FETCH_ASSOC);
    $sysop[$i] = $RS;
    
    $user[$RS['username']] = $RS['username'];
    $canDel                = true;
    
    // 設定取出管理者的條件
    switch ($level) {
        case $sysRoles['root']: // 最高管理者
            if ($ulist[0] == sysRootAccount) {
                $sysop[$i]['result'] = $MSG['msg_root'][$sysSession->lang];
                $canDel              = false;
            }
            break;
        
        case $sysRoles['administrator']: // 進階管理者
            if ($ulist[0] == sysRootAccount) {
                $sysop[$i]['result'] = $MSG['msg_root'][$sysSession->lang];
                $canDel              = false;
            }
            break;
        
        default: // case 2048: 一般管理者
            if ($ulist[0] == sysRootAccount) {
                $sysop[$i]['result'] = $MSG['msg_root'][$sysSession->lang];
                $canDel              = false;
            }
            if ($ulist[1] != intval($sysSession->school_id)) {
                $sysop[$i]['result'] = $MSG['msg_del_deny'][$sysSession->lang];
                $canDel              = false;
            }
    }
    // 刪除管理者
    if ($canDel) {
        if (($level > $sysop[$i]['level']) || ($sysSession->username == $sysop[$i]['username'])) {
            dbDel('WM_manager', $sqls);
            wmSysLog($sysSession->cur_func, $sysSession->school_id, 0, 0, 'manager', $_SERVER['PHP_SELF'], "刪除管理者name={$ulist[0]}, school={$ulist[1]} success!");
            $sysop[$i]['result'] = $MSG['msg_del_success'][$sysSession->lang];
        } else {
            wmSysLog($sysSession->cur_func, $sysSession->school_id, 0, 1, 'manager', $_SERVER['PHP_SELF'], "刪除管理者name={$ulist[0]}, school={$ulist[1]} fail!");
            $sysop[$i]['result'] = $MSG['msg_del_fail'][$sysSession->lang];
        }
    }
}
// 建立刪除人員列表 (End)

$userlist = '"' . implode('", "', $user) . '"';
$RS       = dbGetStMr('WM_all_account', 'username, first_name, last_name', "username IN ({$userlist})", ADODB_FETCH_ASSOC);
if ($RS) {
    while (!$RS->EOF) {
        $user[$RS->fields['username']] = checkRealname($RS->fields['first_name'], $RS->fields['last_name']);
        $RS->MoveNext();
    }
}

$js = <<< BOF
    window.onload = function () {
            document.getElementById("tb2").innerHTML = document.getElementById("tb1").innerHTML;
    }
BOF;
showXHTML_head_B($MSG['title'][$sysSession->lang]);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/academic/wm.css");
    showXHTML_script('inline', $js);
showXHTML_head_E();

showXHTML_body_B();
    $ary   = array();
    $ary[] = array(
        $MSG['tabs_del_admin'][$sysSession->lang],
        'tabs1'
    );
    showXHTML_tabFrame_B($ary, 1, 'adminModify', null, 'action="sysop_del1.php" method="post" onsubmit="return delAdmin();" style="display: inline;"', false);
        $colspan = ($level == $sysRoles['manager']) ? 6 : 8;
        showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="box01" id="tabAction"');
            showXHTML_tr_B('class="cssTrEvn"');
            showXHTML_td_B('align="center" nowrap="nowrap" colspan="' . $colspan . '" id="tb1" class="font01"');
            showXHTML_input('button', 'bd', $MSG['btn_return'][$sysSession->lang], '', 'class="button01" onclick="location.replace(\'./sysop.php\')"');
            showXHTML_td_E('');
            showXHTML_tr_E('');

            showXHTML_tr_B('class="cssTrHead font01"');
            showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_number'][$sysSession->lang]);
            showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_username'][$sysSession->lang]);
            showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_name'][$sysSession->lang]);
            if ($level >= $sysRoles['administrator']) {
                showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_school_id'][$sysSession->lang]);
                showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_school_name'][$sysSession->lang]);
            }
            showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_permit'][$sysSession->lang]);
            showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_limit_ip'][$sysSession->lang]);
            showXHTML_td('nowrap="nowrap" align="center"', $MSG['th_result'][$sysSession->lang]);
            showXHTML_tr_E('');

            $sname = getAllSchoolName();
            $idx   = 0;
            foreach ($sysop as $val) {
                $col = ($col == 'class="cssTrEvn font01"') ? 'class="cssTrOdd font01"' : 'class="cssTrEvn font01"';
                showXHTML_tr_B($col);
                showXHTML_td('nowrap="nowrap" align="center"', ++$idx);
                showXHTML_td_B('nowrap="nowrap"');
                echo $val['username'];
                showXHTML_td_E('');
                showXHTML_td('nowrap="nowrap"', $user[$val['username']]);
                if ($level >= $sysRoles['administrator']) {
                    showXHTML_td('nowrap="nowrap" align="center"', $val['school_id']);
                    showXHTML_td('nowrap="nowrap"', $sname[$val['school_id']]);
                }
                showXHTML_td('nowrap="nowrap"', $sopLevel[$val['level']]);
                showXHTML_td('', nl2br($val['allow_ip']));
                showXHTML_td('nowrap="nowrap"', $val['result']);
                showXHTML_tr_E('');
            }

            $col = ($col == 'class="cssTrEvn font01"') ? 'class="cssTrOdd font01"' : 'class="cssTrEvn font01"';
            showXHTML_tr_B($col);
            showXHTML_td_B('align="center" nowrap="nowrap" colspan="' . $colspan . '" id="tb2" class="cssInput"');
            showXHTML_td_E('');
            showXHTML_tr_E('');

        showXHTML_table_E('');
    showXHTML_tabFrame_E();

showXHTML_body_E();