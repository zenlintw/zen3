<?php
    /**
     * 登出
     * $Id: logout.php,v 1.1 2010/02/24 02:38:55 saly Exp $
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/login/login.inc');
    require_once(sysDocumentRoot . '/online/msg_backup.php');
#=========== main ================
    // add by jeff : 2006-04-26, 當此視窗是agent所開啟的，登出就關閉視窗
    if (substr($_COOKIE['idx'], 0, 5) == 'agent') {
print <<< EOM
        <html>
        <script language="javascript">window.close();</script>
        </html>
EOM;
        exit;
    }

    // 紀錄上站停留時間
    updateSiteAccTime($_COOKIE['idx']);
    // 記錄此次上站log
    setLastLoginRec($sysSession->school_id, $sysSession->username);
    // 備份線上傳訊
    msg_backup();
    // 刪除討論室的 Session
    dbDel('WM_chat_session', "`idx`='{$_COOKIE['idx']}' AND `username`='{$sysSession->username}'");
    // 刪除 Session
    removeExpiredSessionIdx($_COOKIE['idx']);
    //刪除ftp認證資料
    removeExpiredFtpAuth($sysSession->username);
    /* [MOOC](B) #57892 清除 sso idx 及代登入過的學校 session 2014/12/19 By Spring */
    if (isset($_COOKIE["sIdx"])) {
        // 清除sIdx
        $fdm = explode('.', $_SERVER['SERVER_NAME']);
        $dm = str_replace($fdm[0].'.', '', $_SERVER['SERVER_NAME']);
        setcookie ('sIdx', $sysSession->school_id.$_COOKIE['idx'], time() - 3600, '/', $dm, false);
        // 清除 sso 到其他學校所儲存的 session
        $rsSch = dbGetStMr(sysDBname.'.`WM_school`', 'distinct(`school_id`)', '1');
        if ($rsSch) {
            while (!$rsSch->EOF) {
                $userschools[] = $rsSch->fields['school_id'];
                $rsSch->MoveNext();
            }
        }
        foreach ($userschools as $v) {
            dbDel(sysDBprefix.$v.'.WM_session', "idx='{$_COOKIE['idx']}'");
        }
    }
    /* [MOOC](E) #57892 */

    /* #82791 清除 保持登入的 persist_idx */
    if (isset($_COOKIE["persist_idx"])&&!empty($_COOKIE["persist_idx"])) {
        // 清除 persist_idx
        setcookie('persist_idx', '', time()-3600, '/', '', $http_secure);
        // 清除 WM_persist_login
        dbDel('WM_persist_login', sprintf("persist_idx='%s'",mysql_escape_string($_COOKIE["persist_idx"])));
    }
    /* #82791 End */
    
    if (isset($_COOKIE["cal_alert"])&&!empty($_COOKIE["cal_alert"])) {
    	setcookie('cal_alert', '', time()-3600, '/', '', $http_secure);
    }

    // 清除 Cookie
    $t = time() - 3600;
    foreach(array('idx',
                  'forum_sortby',
                  'forum_qsortby',
                  'forum_search',
                  'forum_qsearch',
                  'search_type',
                  'qsearch_type',
                  'search_keyword',
                  'qsearch_keyword',
                  'rows_page',
                  'Qrows_page',
                  'SQrows_page',
                  'forum_extras',
                  'Ticket') as $cookie) if (isset($_COOKIE[$cookie])) setcookie ($cookie, '', $t);
                  
    setcookie('wm_learning_hash_clean', 'N', time() + 86400, '/');

    // 導到首頁
    if (defined('sysEnableMooc') && (sysEnableMooc > 0)) {
            header('Location: /mooc/index.php');
    } else {
            header('Location: /');
    }
