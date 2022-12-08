<?php 
/**
 * 取得直連課程的捷徑
 */
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/username.php');
require_once("functions.php");
require_once("security.php");

/* main */
if (!isset($_GET["user"])) die("Error:Need User");
if (!isset($_GET["course_id"])) die("Error:Need Course");
$user = mysql_escape_string($_GET["user"]);
$sch_course_id = intval($_GET["course_id"]);	//校務系統-課程R識別碼

//確認使用者是否存在
if (checkUsername($user) != 2){
	die("Error:No User");
}

//取得校務系統對應的LMS的課程編號
$row = dbGetStSr("CO_course","WM_course_id",sprintf("subj_id=%d",$sch_course_id),ADODB_FETCH_ASSOC);
//若找不到此對應課程
if (count($row) == 0){
	die("Error:No Course");
}
$course_id = $row["WM_course_id"];

//此使用者是否有修，且可讀取
$role = getUserMajorRole($user, $course_id);
if (empty($role))
{
	die("Error:No Major");
}
	
if (!isAllowRead($role, $course_id))
{
	die("Error:No Allow");
}

$objCrypt = new DataCrypt();
echo "http://elearning.ksu.edu.tw/sso/autologin.php?encdata=".$objCrypt->encRequest(sprintf("%s,%d,%d",$user,$course_id,time()));
?>
