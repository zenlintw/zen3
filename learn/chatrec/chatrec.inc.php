<?php
/** include 此頁者, 需在外面先設好
 *    1. $owner_id    : 符合 WM_bbs_boards 之 $owner_id 規則
 *    2. $owner_nm    : 本版 owner 之名稱 ( 學校, 課程, 班級, 小組 )
 */
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/lib_forum.php');
require_once(sysDocumentRoot . '/forum/lib_rightcheck.php');
require_once(sysDocumentRoot . '/learn/chatrec/lib_chat_records.php');

$sysSession->cur_func = '900200100';
$sysSession->restore();
if (!aclVerifyPermission(900200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

if (!dbGetRecordBoard($owner_id, $result)) {
    echo 'System Error!';
    wmSysLog($sysSession->cur_func, $sysSession->course_id, 0, 1, 'auto', $_SERVER['PHP_SELF'], 'System Error');
    exit();
}

$sysSession->board_id       = $result['board_id'];
$sysSession->news_board     = 0; // 含時間(開啟及關閉)欄位類型之討論版
$sysSession->board_readonly = $rec_readonly;
$sysSession->page_no        = '';
$sysSession->post_no        = '';
$sysSession->q_page_no      = '';
$sysSession->q_post_no      = '';
$sysSession->news_nodes     = '';

$sysSession->board_ownerid   = $owner_id;
$sysSession->board_ownername = $owner_nm;

// 是否具刊登權限(含修改, 刪除)
$sysSession->q_right = ChkRight($result['board_id']);
$sysSession->b_right = $sysSession->q_right; // 目前兩者一樣

list($bname, $default_order) = dbGetStSr('WM_bbs_boards', 'bname, default_order', 'board_id = ' . $sysSession->board_id, ADODB_FETCH_NUM);
$sysSession->sortby   = $default_order;
$sysSession->q_sortby = $default_order;
$bname                = unserialize($bname);
dbSet('WM_session', "board_name='" . addslashes($bname[$sysSession->lang]) . "',q_path=''", "idx='{$_COOKIE['idx']}'");

// 回存 SESSION
$sysSession->restore();

// 清除 Cookie 所存搜尋條件
ClearForumCookie();

// 讀出 extras 值到 cookie 中
loadExtras2Cookie($sysSession->board_id);

//header('Location:/forum/index.php');
header('Location:/forum/m_node_list.php?xbid=' . sysNewEncode($sysSession->board_id, $_COOKIE['idx'], true));