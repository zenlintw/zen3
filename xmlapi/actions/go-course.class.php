<?php
/**
 * 進入課程上課，記錄最後登入時間，累加上課次數
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

class GoCourseAction extends baseAction
{
    var $courseId = 0;
    var $username = '';
    var $ticket = '';
    
    function main()
    {
        // 驗證 Ticket
        parent::checkTicket();

        global $sysSession;

        $this->courseId = abs(intval($_REQUEST['cid']));
        $this->ticket = mysql_real_escape_string($_GET['ticket']);

        $courseCaption = dbGetOne('`WM_term_course`', '`caption`', "`kind` = 'course' AND `course_id` = {$this->courseId}");
        $courseName = getCaption($courseCaption);
        $courseName = $courseName[$sysSession->lang];

        // 變更session 的 course id
        $sysSession->course_id = $this->courseId;
        $sysSession->restore();
        dbSet('WM_session', "`course_name`='{$courseName}'", "`idx`='{$this->ticket}'");
        $this->username = mysql_real_escape_string($sysSession->username);
        /**
         * TODO
         * 1.前端尚未實作離線回傳進入課程
         * 2.未來如果要實作離線回傳要調整程式順序，否則會發生過去進入的 courseId 先被回傳至 session
        **/
        // 判斷離線回傳的時間不能是未來時間!!避免作弊.
        if (isset($_REQUEST['date']) && $_REQUEST['date']<time()) {
            // 相信 request 傳送來的閱讀時間
            $lastLogin = dbGetCol(
                'WM_term_major', 
                'last_login', 
                sprintf(
                    'course_id=%d and username=\'%s\'',
                    $this->courseId,
                    $this->username
                )
            );
            if (count($lastLogin)>0 && strtotime($lastLogin[0])<$_REQUEST['date']) {
                $date = date('Y-m-d H:i:s', $_REQUEST['date']);
                dbSet(
                'WM_term_major', 
                "login_times=login_times+1, last_login='$date'", 
                sprintf(
                    'course_id=%d and username=\'%s\'', 
                    $this->courseId,
                    $this->username
                )
                );
            } else {
                sprintf('{"code":3,"message":"Timestamp error","data":{}}');
            }
        } else {
            $date = date('Y-m-d H:i:s');
            dbSet(
                'WM_term_major',
                "login_times=login_times+1, last_login='{$date}'", 
                sprintf(
                    'course_id=%d and username=\'%s\'', 
                    $this->courseId, 
                    $this->username
                )
            );
        }

        appSysLog(999999016, $sysSession->course_id , 0 , 1, 'classroom', $_SERVER['PHP_SELF'], 'Go to Course: ' . $sysSession->course_id. 'by app' . $message, $sysSession->username);

        // output
        header('Content-Type: application/json');
        echo sprintf('{"code":0,"message":"success","data":{}}');
        exit();
    }
}