<?php
class AccountCenter_Auth
{
    /**
     * @var 前端傳入的實際帳號
     */
    var $originalUsername;
    /**
     * @var 前端傳入的實際密碼
     */
    var $originalPassword;
    /**
     * @var MD5過的密碼
     */
    var $md5Password;
    /**
     * @var 避免SQL Injection而處理的帳號
     */
    var $avoidSIUsername;
    /**
     * @var 避免SQL Injection而處理的密碼
     */
    var $avoidSIPassword;

    /**
     * 驗證帳號密碼
     * 1.將原始資料進行設定與轉換
     * 2.驗證帳號是否過期
     * 3.排除公司帳號不用進校務系統驗證，直接進行WMPro平台驗證
     * 4.如果有校務系統透通，先進行校務系統驗證，驗證失敗，再進行WMPro平台驗證
     * 5.$encrypt變數是用於WMPro平台驗證上，判斷密碼是否需要進行MD5編碼
     *
     * @param string $username 使用者帳號
     * @param string $password 使用者密碼
     * @param string $encrypt 加密方式
     * @return boolean true:驗證成功,false:驗證失敗
     */
    function auth ($username, $password, $encrypt = '') {
        // 將原始資料進行設定與轉換
        $this->_setAuthInfo($username, $password, $encrypt);

        // 判斷帳號是否過期，如果過期則逕自回傳 false，不需再去驗證帳密(要實作isUserAccountExpired)
        if (!$this->isUserAccountExpired()) {
            return false;
        }

        // 排除公司帳號不用進校務系統驗證(改用login.conf)
        $usersValidByWM3 = array('root', 'sunnet');
        // 公司帳號直接進行WMPro平台驗證
        if (in_array($username, $usersValidByWM3)) {
            return $this->_isExistWMPro();
        }

        // @TODO 要實作SSO驗證function
        if ($this->_isExistSSO()) {
          return true;
        }
              
        return $this->_isExistWMPro();
    }
    /**
     * 記錄原始帳密與避免SQL Injection的帳密
     *
     * @private
     * @param string $username 帳號
     * @param string $password 密碼
     * @param string $encrypt 加密方式     
     **/
    function _setAuthInfo ($username, $password, $encrypt) {
        $this->originalUsername = $username;
        if ($encrypt === '') {
          // 明碼密碼
          $this->originalPassword = $password;
          $this->md5Password = md5($password);
        } else if ($encrypt === 'md5') {
          // md5的密碼
          $this->md5Password = $password;
        } else {
          // @TODO: 密碼解密
          $this->md5Password = md5($password);
        }
        
        $this->avoidSIUsername = mysql_real_escape_string($username);
        $this->avoidSIPassword = mysql_real_escape_string($this->md5Password);
    }
    /**
     * 驗證帳號使用期限
     * 1.利用已經轉換過的帳號$avoidSIUsername來進行驗證
     *
     * @private
     * @return boolean true:可用,false:不可用
     **/
    function _isUserAccountExpired () {
        // @TODO 實作驗證帳號使用期限
    }
    /**
     * 檢查帳密是否存在WMPro平台
     * 1.利用已經轉換過的帳號$avoidSIUsername來進行驗證
     * 2.利用已經轉換過的密碼$avoidSIPassword來進行驗證
     * 3.判斷$encrypt的值來決定是否對密碼進行MD5編碼
     *
     * @private
     * @return boolean true:存在,false:不存在
     **/
    function _isExistWMPro () {
        // @TODO 實作帳密檢查
    }

    /**
     * 比對SSO帳號、密碼是否正確 (校務系統驗證要寫在這裡面)
     *
     * @private
     * @return boolean true:存在,false:不存在
     **/
    function _isExistSSO () {
        // @TODO 校務透通
    }
}