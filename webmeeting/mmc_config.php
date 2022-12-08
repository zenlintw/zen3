<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/sys_config.php');

// Microsoft Media Server
$MMS_Server_addr     = MMS_Server;
$MMS_Server_port     = MMS_Server_port;
$MMS_Server_API_port = '2519';
$Anicam_enable       = (anicam == 'Y') ? true : false;

// Homemeeting MCU Server
$MMC_Server_addr        = MMC_Server;       // MMC 所在位置
$MCU_Server_port        = MMC_Server_port;  // MCU 所binding的port, 預設是443
$MCU_Server_port1       = '2333';
$MMC_Server_API_Port    = '80';             // 整合的Tinymmc的port,預設是8080
$MMC_Server_API_RootURL = '/TinyMMC/';      // Tinymmc所在的路徑，預設是"/"
$MMC_enable             = (joinet == 'Y') ? true : false;

// Breeze MMS
$Breeze_enable = (breeze == 'Y') ? true : false;

// Site & User info
$WM3_School_ID = $sysSession->school_id;

// 區分是否為小組討論

if (isset($_GET['rid']))
{
	$pos = strpos($_GET['rid'], ' ');
	if ($pos !== false) die('Access Deny!');

}
if (empty($tid) || empty($gid))
{
	$WM3_Meeting_ID    = (isset($_GET['rid'])) ? $_GET['rid'] : $WM3_School_ID . '_C_' . $sysSession->course_id;
	$WM3_Meeting_Owner = $WM3_Meeting_ID;
}
else
{
	$tid = intval($tid);
	$gid = intval($gid);
	$WM3_Meeting_ID    = (isset($_GET['rid'])) ? $_GET['rid'] : $WM3_School_ID . '_C_' . $sysSession->course_id . '_' . $tid . '_' . $gid;
	$WM3_Meeting_Owner = $WM3_Meeting_ID;
}

// $SCUID=$School_ID."_".$cid;
?>