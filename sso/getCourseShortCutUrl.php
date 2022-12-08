<?php 
/**
 * ���o���s�ҵ{�����|
 */
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/username.php');
require_once("functions.php");
require_once("security.php");

/* main */
if (!isset($_GET["user"])) die("Error:Need User");
if (!isset($_GET["course_id"])) die("Error:Need Course");
$user = mysql_escape_string($_GET["user"]);
$sch_course_id = intval($_GET["course_id"]);	//�հȨt��-�ҵ{R�ѧO�X

//�T�{�ϥΪ̬O�_�s�b
if (checkUsername($user) != 2){
	die("Error:No User");
}

//���o�հȨt�ι�����LMS���ҵ{�s��
$row = dbGetStSr("CO_course","WM_course_id",sprintf("subj_id=%d",$sch_course_id),ADODB_FETCH_ASSOC);
//�Y�䤣�즹�����ҵ{
if (count($row) == 0){
	die("Error:No Course");
}
$course_id = $row["WM_course_id"];

//���ϥΪ̬O�_���סA�B�iŪ��
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
