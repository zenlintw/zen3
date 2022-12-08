<?php
/**
 * 提供 App login.class.php 呼叫來介接 WM 驗證
 *
 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
 *
 */ 
class AccountCenter_APP
{
    // 前端收到的實際帳密
    var $originalUsername;
    var $originalPassword;
    // 避免SQL Injection而處理的帳密
    var $avoidSIUsername;
    var $avoidSIPassword;

    /**
     * 驗證帳號使用期限
     *
     * @return boolean 驗證結果 (true:可用；false：不可用)
     **/
    function isUserAccountExpired()
    {
        global $sysSession;

        $schoolId = (isset($sysSession))? $sysSession->school_id : 10001;
        $where = '((ISNULL(begin_time)) || (begin_time  = "0000-00-00") || (begin_time <= CURDATE())) AND ' .
                 '((ISNULL(expire_time)) || (expire_time = "0000-00-00") || (expire_time >= CURDATE())) AND '.
                 "school_id={$schoolId} AND username='{$this->avoidSIUsername}'";
        list($isExpire) = dbGetStSr('WM_sch4user', 
                                    'count(*)', 
                                    $where, ADODB_FETCH_NUM);
        return ($isExpire === 1 || $isExpire === '1');
    }

    /**
     * 檢查WMPRO自己的帳密
     *
     * @return boolean 存在與否
     **/
    function WMProCheckExist () {
        $row = dbGetStSr(
            'WM_user_account',
            'count(*) AS count',
            sprintf(
                "username='%s' AND password='%s' AND enable='Y'",
                $this->avoidSIUsername,
                md5($this->avoidSIPassword)
            )
        );

        return ($row['count']==='1');
    }

    /**
     * 比對SSO帳號、密碼是否正確 (校務系統驗證要寫在這裡面)
     *
     * @return boolean 帳密是否正確 (true:帳密正確；false:帳密錯誤)
     **/
    function ssoCheckExist()
    {
        // 校務系統驗證失敗後，仍然要回WMPRO驗證
        return $this->WMProCheckExist();
    }

    /**
     * 將處理帳號與密碼，記錄原始帳密與避免SQL Injection的帳密
     * @private
     * @param string $username 帳號
     * @param string $password 密碼
     **/
    function setAuthInfo ($username, $password)
    {
        $this->originalUsername = $username;
        $this->originalPassword = $password;
        $this->avoidSIUsername = mysql_real_escape_string($username);
        $this->avoidSIPassword = mysql_real_escape_string($password);
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
       $this->setAuthInfo($username, $password);

        // 判斷帳號是否過期，如果過期則逕自回傳 false，不需再去驗證帳密
        if (!$this->isUserAccountExpired()) {
            return false;
        }

        // 不需要取得校務帳號的平台帳號
        $UsersValidedByWM3 = array('root', 'sunnet');

        if (!in_array($username, $UsersValidedByWM3)) {
            // 校務系統驗證
            return $this->ssoCheckExist();
        } else {
            // WMPRO平台驗證
            return $this->WMProCheckExist();
        }
    }
}
