<?php 
/**
 * 取得使用者所修的校務系統的課程有在LMS開課，且使用者也是選課學生
 */
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/username.php');
require_once("functions.php");

/* main */
if (!isset($_GET["user"])) die("Error:Need User");
if (!isset($_GET["courses"])) die("Error:Need Course");
$user = mysql_escape_string($_GET["user"]);
$sch_course_ids = explode(",",$_GET["courses"]);	//校務系統-課程R識別碼
if (count($sch_course_ids) == 0)  die("Error:Need Course");
for($i=0,$size=count($sch_course_ids); $i<$size; $i++){
	$sch_course_ids[$i] = intval($sch_course_ids[$i]);
}

$lms_sch_course_ids = array();	//有在LMS開課,且有$user選課的
$rs = dbGetStMr("WM_term_major AS T1 INNER JOIN `CO_course` AS T2 ON T1.course_id = T2.WM_course_id","T1.course_id, T1.role, T2.subj_id",sprintf("T1.username = '%s' and T2.subj_id in (%s)",$user,implode(",",$sch_course_ids)),ADODB_FETCH_ASSOC);
if ($rs){
	while($row=$rs->FetchRow()){
		if (!isAllowRead($row["role"], $row["course_id"])) continue;
		$lms_sch_course_ids[] = $row["subj_id"];
	}
}
echo implode(",",$lms_sch_course_ids);
?>
