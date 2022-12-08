<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/co_meeting/module/meeting.php');
$action = $_POST['action'];
switch($action)
{
    //設定密碼
    case 'setUserPwd':
        $password = trim($_POST['password']);
        if(!empty($password))
        {
            dbSet("CO_meeting_user","password='{$password}'","username='{$sysSession->username}'");
            echo "<script>alert('密碼變更成功');opener.location.reload();location='/co_meeting/co_meeting_pwd.php'</script>";
        }
        else
        {
            echo "<script>alert('密碼變更失敗');location='/co_meeting/co_meeting_pwd.php'</script>";
        }
        break;
    //建立會議
    case 'createMeeting':
        $meeting = new Meeting();
        $topic = $_POST['name'];
        $duration = intval($_POST['duration'])*60;
        $begin = date("Y-m-d H:i:s");
        $end   = date("Y-m-d H:i:s",time()+$duration);
        $code = $meeting->createMeeting($sysSession->course_id,$topic,$begin,$end);
        if($code == '1')
        {
            echo "<script>alert('建立會議成功');location='/co_meeting/co_meeting_setting.php'</script>";
        }
        else
        {
            $errorMsg = "建立會議失敗\\rerrorCode=>" . $code;
            echo "<script>alert('{$errorMsg}');location='/co_meeting/co_meeting_setting.php'</script>";
        }
        break;
    default:
        die('error action');
}
