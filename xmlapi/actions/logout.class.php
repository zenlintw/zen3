<?php
/**
 * 取得個人profile
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

class logoutAction extends baseAction
{
    /**
     * 記錄使用者上次登入的時間 -- 提供logout程序呼叫
     *
     * @param string $schoolId
     * @param string $user
     */
    function setLastLoginRec($schoolId, $user)
    {
        global $_SERVER;
        $schoolId = intval($schoolId);
        $user = mysql_real_escape_string($user);

        $ll = dbGetOne(
        	'WM_sch4user', 
        	'`last_login`', 
        	"school_id={$schoolId} AND username='{$user}'"
        );
        $st = intval(time()) - intval($GLOBALS['sysConn']->UnixTimeStamp($ll));
        $msg = 'App User Logout. session total time: ' . $st;
        appSysLog(999999002, $schoolId , 0 , 1, 'other', $_SERVER['PHP_SELF'], $msg, $user);
    }
    
    function main()
    {
        // 驗證Ticket，並取回ticket做後續處理
        $ticket = parent::checkTicket();

        global $sysSession;
        
        $this->setLastLoginRec($sysSession->school_id, $sysSession->username);

        dbDel('WM_session', "`idx` = '{$ticket}'");
        
        // output
        header('Content-Type: application/json');
        echo '{"code":0,"message":"success","data":{}}';
        exit();
    }
}