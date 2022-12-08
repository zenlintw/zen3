<?php
/**
 * 儲存管理者
 *
 * @since   2003/10/14
 * @author  ShenTing Lin
 * @version $Id: sysop_save.php,v 1.1 2010/02/24 02:38:46 saly Exp $
 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/academic/sys/lib.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/common.php');
require_once(sysDocumentRoot . '/lib/username.php');

$sysSession->cur_func = '100400200';
$sysSession->restore();
if (!aclVerifyPermission(100400200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
    if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
        sysFail();
    }
    
    /**
     * 1. 是否具備管理者的身份
     * 2. 是，但是是一般管理者，則檢查是否具備該校管理者的身份
     * 3. 否，踢掉
     **/
    $permit = array(
        $sysRoles['manager'] => 0,
        $sysRoles['administrator'] => 1,
        $sysRoles['root'] => 2
    );
    $level  = intval(getAllSchoolTopAdminLevel($sysSession->username));
    if ($level < $sysRoles['administrator'])
        $level = intval(getAdminLevel($sysSession->username));
    if ($level <= 0)
        sysFail();
    
    // 一般管理者不可以跨校
    if ($level < $sysRoles['administrator'])
        $sid = intval($sysSession->school_id);
    else
        $sid = intval(getNodeValue($dom, 'sid')); // 取得學校編號
    $osid = intval(getNodeValue($dom, 'osid')); // 取得學校編號
    
    $sname    = getAllSchoolName();
    $username = getNodeValue($dom, 'username'); // 取得帳號
    $res      = checkUsername($username);
    if (($res != 2) && ($res != 4)) {
        wmSysLog($sysSession->cur_func, $sysSession->school_id, 0, 1, 'manager', $_SERVER['PHP_SELF'], '查無此人!');
        die($MSG['username_not_exist'][$sysSession->lang]);
    }
    // 檢查帳號存不存在
    $RS    = dbGetStSr('WM_all_account', 'first_name, last_name', "username='{$username}'", ADODB_FETCH_ASSOC);
    $uname = checkRealname($RS['first_name'], $RS['last_name']);
    $uname = htmlspecialchars($uname);
    
    $permita = array(
        0 => $sysRoles['manager'],
        1 => $sysRoles['administrator'],
        2 => $sysRoles['root']
    );
    
    $allow_ip = trim(getNodeValue($dom, 'ip')); // 取得可登入的 IP
    $permit   = getNodeValue($dom, 'permit'); // 取得等級
    $mode     = trim(getNodeValue($dom, 'mode')); // 取得編輯或新增模式
    
    if ($level < $sysRoles['administrator']) {
        // 一般管理者不可新增它校的管理者與權限比他大的
        $permit = 0;
        $sid    = intval($sysSession->school_id);
    } else if ($level < $sysRoles['root']) {
        if ($permit >= 2)
            $permit = 0;
    }
    // 設定 root 的權限永遠為最高管理者
    if ($username == sysRootAccount)
        $permit = 2;
    
    if (empty($allow_ip))
        $allow_ip = '127.0.0.1';
    
    if ($mode == 'add') {
        // 新增管理者
        dbNew('WM_manager', '`username`, `school_id`, `level`, `allow_ip`', "'{$username}', {$sid}, {$permita[$permit]}, '{$allow_ip}'");
        if ($sysConn->Affected_Rows() <= 0) {
            wmSysLog('100400100', $sysSession->school_id, 0, 2, 'manager', $_SERVER['PHP_SELF'], $username . '管理者已存在，不須新增!');
            die($MSG['msg_add_fail'][$sysSession->lang]);
        }
        wmSysLog('100400100', $sysSession->school_id, 0, 0, 'manager', $_SERVER['PHP_SELF'], '新增管理者' . $username);
    } else {
        // 修改
        dbSet('WM_manager', "`school_id`={$sid}, `level`={$permita[$permit]}, `allow_ip`='{$allow_ip}'", "`username`='{$username}' AND `school_id`={$osid}");
        if ($sysConn->Affected_Rows() <= 0) {
            wmSysLog('100400200', $sysSession->school_id, 0, 3, 'manager', $_SERVER['PHP_SELF'], $username . '不需更新!');
            die($MSG['msg_update_fail'][$sysSession->lang]);
        }
        wmSysLog('100400200', $sysSession->school_id, 0, 0, 'manager', $_SERVER['PHP_SELF'], '修改管理者' . $username);
    }
    
    $xmlStrs = <<< BOF
	<sysop>
		<uname>{$username}</uname>
		<name>{$uname}</name>
		<sid>{$sid}</sid>
		<sname>{$sname[$sid]}</sname>
		<permit>{$permit}</permit>
		<limit_ip>{$allow_ip}</limit_ip>
	</sysop>
BOF;
    header("Content-type: text/xml");
    echo '<manifest>', $xmlStrs, '</manifest>';
    
} else {
    sysFail();
}