<?php
/**
 * LCMS 登入驗證
 *
 *
 * LICENSE: 本程式原始碼檔案，為旭聯科技股份有限公司所有。非經旭聯書面授權
 * 則嚴禁抄襲、篡改、散布、公開部份或全部內容。若取得旭聯書面授權書，亦得遵
 * 照書中所限制之使用範圍使用之，否則仍以侵權論究。
 *
 * @category    xmlapi
 * @package     WM25
 * @subpackage  WebServices
 * @author      Jeff Wang <jeff@sun.net.tw>
 * @copyright   2011 SunNet Tech. INC.
 * @version     1.0
 * @since       2012-12-28
 */

include_once(dirname(__FILE__).'/action.class.php');
include_once(dirname(__FILE__).'../../../lib/acl_api.php');

class LcmsLoginAction extends baseAction
{
    var $username;
    var $password;
    var $md;

    var $userIP;
    var $userHost;
    var $sessionId;

    var $respondeCode = 0;
    var $responseMsg = '';
    var $responseData = null;

    function LcmsLoginAction()
    {
        parent::baseAction();

        // 修正 php4 的 bug，當query string value 有 %26 會被自動切割的白痴問題
        if (strpos($_SERVER['REQUEST_URI'], '?')) {
            parse_str(substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], '?')+1), $_GET);
        }

        // 解密
        $de = _3desDecode(($_GET['en']));
        $obj = json_decode($de);

        if (isset($obj->username)) {
            $this->username = $obj->username;
        } else if ($obj->user) {
            // 為了相容以前的設定，只好同時判斷username與user
            $this->username = $obj->user;
        }

        if (isset($obj->md)) {
            $this->md = $obj->md;
        } else {
            $this->md = '';
        }

        // 預設沒有傳md參數，所以都會md5加密
        if ($this->md === '1') {
            $this->password = $obj->password;
        } else {
            $this->password = md5($obj->password);
        }

        $this->setUserIP();
    }

    function setUserIP()
    {
        global $_SERVER;
        //設定使用者的ip
        $this->userIP = explode(
            ',',
            ($_SERVER['HTTP_X_FORWARDED_FOR']?$_SERVER['HTTP_X_FORWARDED_FOR']:$_SERVER['REMOTE_ADDR'])
        );
        $this->userHost = $this->userIP[0];
    }

    function output()
    {
         if ($this->respondeCode != 0) {
             return sprintf('{"code":%d,"message":"%s", "data":{}}',$this->respondeCode, $this->responseMsg);
         }

         $jsonTemplate =  '{"code":0,"message":"%s","data":{"session_data":';
         $jsonTemplate .= '{"ticket":"%s","username":"%s",';
         $jsonTemplate .= '"wm_login_url":"http://%s%s/xmlapi/validTicket.php?ticket=%s"}}}';
         return sprintf(
             $jsonTemplate,
             $this->responseMsg, $this->sessionId,
             $this->username,
             $_SERVER['HTTP_HOST'],(($_SERVER['SERVER_PORT']=='80')?'':':'.$_SERVER['SERVER_PORT']),
             $this->sessionId
        );
    }

    function getUserData($username)
    {
        global $sysConn;
        $row = dbGetStSr(
            'WM_user_account',
            '*',
            sprintf("username='%s'", mysql_real_escape_string($username))
        );
        return $row;
    }

    function main()
    {
        global $sysSession;

        // 使用者帳密驗證
        $customAuthAdapter = dirname(__FILE__) . '/../../lib/login/AccountCenter_Lcms.class.php';
        if (!file_exists($customAuthAdapter)) {
            $this->respondeCode = 503;
            $this->responseMsg = "LOGIN FAIL (Auth not implement.)";
            print_r($this->output());
            // 砍掉 initialize.php 產生的 Guest
            $this->removeGuestSession();
            return;
        } else {
            include($customAuthAdapter);
            $auth = new AccountCenter_Lcms();
            if (!$auth->auth($this->username, $this->password)) {
                $this->respondeCode = 403;
                $this->responseMsg = "LOGIN FAIL (Expired or NOT EXISTS.)";
                print_r($this->output());
                // 砍掉 initialize.php 產生的 Guest
                $this->removeGuestSession();
                return;
            }
        }

        //取得使用者的資料, 以陣列回傳
        $userData = $this->getUserData($this->username);
        if (!isset($userData['username'])) {
            $this->respondeCode = 403;
            $this->responseMsg = "LOGIN FAIL (NOT EXISTS.)";
            print_r($this->output());
            // 砍掉 initialize.php 產生的 Guest
            $this->removeGuestSession();
            return;
        }

        // 判斷是否為老師相關角色：課程助教、講師、教師
        $sysRoles = array('guest' =>  1, // 參觀者
            'senior'           =>     2, // 學長
            'paterfamilias'    =>     4, // 家長
            'superintendent'   =>     8, // 長官/督學
            'auditor'          =>    16, // 旁聽生
            'student'          =>    32, // 正式生
            'assistant'        =>    64, // 課程助教
            'instructor'       =>   128, // 講師
            'class_instructor' =>   256, // 班級助理
            'teacher'          =>   512, // 教師 (通常比講師多具有教材管理編修權)
            'director'         =>  1024, // 導師 (學生人員管理)
            'manager'          =>  2048, // 一般管理者
            'administrator'    =>  4096, // 超級管理者
            'root'             =>  8192, // 最高管理者 (一機只有一人)
            'all'              => 16127  // 所有之 mask
           );

        // md = 1供給 lms/first 使用
        if ($this->md === '1') {
            $teach = aclCheckRole($this->username, $sysRoles['root']);
        // 其他狀況: 僅給WM老師登入用
        } else {
            $teach = aclCheckRole($this->username, $sysRoles['assistant'] | $sysRoles['instructor'] | $sysRoles['class_instructor'] | $sysRoles['teacher']) || aclCheckRole($this->username, $sysRoles['director']);
        }

        if ($teach === '0' || $teach === false) {
            $this->respondeCode = 403;
            $this->responseMsg = "LOGIN FAIL (Not teacher.)";
            print_r($this->output());
            // 砍掉 initialize.php 產生的 Guest
            $this->removeGuestSession();
            return;
        }

        // 建立個人ini
        // 砍掉 initialize.php 產生的 Guest
        $this->removeGuestSession();
        $this->sessionId = $sysSession->init($userData);
        $_COOKIE['idx'] = $this->sessionId;

        // 設定school hash
        $skey = md5($_SERVER['HTTP_HOST'].$sysSession->school_id);
        $schoolHash = substr($skey, 0, 17) . $sysSession->school_id . substr($skey, -10);
        setcookie('school_hash', $schoolHash, 0, '/');

        // 登入寫 Log
        $msg = 'App User Login';
        wmSysLog(999999001, $sysSession->school_id , 0 , 1, 'other', $_SERVER['PHP_SELF'], $msg, $userData['username']);

        dbSet(
            'WM_sch4user',
            "`login_times`=`login_times`+1, `last_login`=NOW(), `last_ip`='".wmGetUserIp()."'",
            sprintf("school_id=%d AND username='%s'",$sysSession->school_id,$userData['username'])
        );

        // 設定cookie
        setcookie('idx', $this->sessionId, 0, '/');

        // output
        header('Content-Type: application/json');
        print_r($this->output());
        exit();
    }

    /**
     * 砍掉 initialize.php 產生的 Guest
     *
     * @return void
     */
    function removeGuestSession()
    {
        if (!empty($_COOKIE['idx']) && strlen($_COOKIE['idx']) === 32) {
            dbDel('WM_session', "idx='{$_COOKIE['idx']}'");
        }
    }
}