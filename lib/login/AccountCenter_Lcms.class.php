<?php
/**
 * 提供 App login.class.php 呼叫來介接 WM 驗證
 *
 *
 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
 *
 * @author      sj <sj@sun.net.tw>
 * @copyright   2012 SunNet Tech. INC.
 * @version     1.0
 * @since       2012-11-11
 */ 
class AccountCenter_Lcms
{
    /**
     * 驗證帳號使用期限
     * 
     * @param string $username : 使用者帳號
     * @return boolean 驗證結果 (true:可用；false：不可用)
     **/
    function isUserAccountExpired($username)
    {
        global $sysSession, $sysConn;
        $schoolId = (isset($sysSession))? $sysSession->school_id : 10001;
        $where = '((ISNULL(begin_time)) || (begin_time  = "0000-00-00") || (begin_time <= CURDATE())) AND ' .
                 '((ISNULL(expire_time)) || (expire_time = "0000-00-00") || (expire_time >= CURDATE())) AND '.
                 "school_id={$sysSession->school_id} AND username='{$username}'";
        list($isExpire) = dbGetStSr('WM_sch4user', 
                                    'count(*)', 
                                    $where, ADODB_FETCH_NUM);
        return ($isExpire === 1 || $isExpire === '1');
    }

    /**
     * 驗證帳號密碼
     *
     * @param string $username : 使用者帳號
     * @param string $password : 使用者密碼
     * @return boolean 帳號密碼驗證成功或失敗
     */
    function auth ($username, $password)
    {
        // 客製登入可改寫這裡，自行設計驗證方式，預設用 WM 登入
        global $sysConn;

        // 判斷帳號是否過期，如果過期則逕自回傳 false，不需再去驗證帳密
        if (!$this->isUserAccountExpired($username)) {
            return false;
        }
        
        $row = dbGetStSr(
            'WM_user_account',
            'count(*) AS count',
            sprintf(
                "username='%s' AND password='%s' AND enable='Y'",
                mysql_real_escape_string($username),
                mysql_real_escape_string($password)
            )
        );
        return ($row['count'] === '1');
    }
}
