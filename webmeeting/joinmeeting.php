<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
$tid = $_SERVER['argv'][0];
$gid = $_SERVER['argv'][1];
require_once(sysDocumentRoot . '/webmeeting/global.php');
require_once(sysDocumentRoot . '/webmeeting/include/hit_integration.php');

$online_meeting_info = get_online_meeting($MMC_Server_addr, $MMC_Server_API_Port, $MMC_Server_API_RootURL, $WM3_Meeting_Owner);
if (strcmp($online_meeting_info,'0') == 0) exit;
list($meetingId, $ownerName) = explode(':',$online_meeting_info);

// 更新討論次數
dbSet('WM_term_major','dsc_times=dsc_times+1',"username='{$sysSession->username}' and course_id='{$sysSession->course_id}'");

$ownerId   = $o_mowner->ID;
$duration  = $o_minfo->Duration;
$password  = '';
$ip        = $o_mserver->IP;
$portm     = $o_mserver->Portm;
$portm2    = $o_mserver->Portm2;
$guestId   = $sysSession->username;
$guestName = getRealnameByUsername($guestId);
$invited   = 1;
$task      = 'launch';

?>
<html>
<body onload="document.forms[0].submit();">
<form method="post"  name="frm" action="/webmeeting/joinmeeting_confirm.php">
<input type="hidden" name="task"      value="launch">
<input type="hidden" name="meetingId" value="<? echo $meetingId;?>">
<input type="hidden" name="ownerName" value="<? echo $ownerName;?>">
<input type="hidden" name="ownerId"   value="<? echo $ownerId;?>">
<input type="hidden" name="duration"  value="0">
<input type="hidden" name="password"  value="">
<input type="hidden" name="ip"        value="<? echo $ip; ?>">
<input type="hidden" name="portm"     value="<? echo $portm; ?>">
<input type="hidden" name="portm2"    value="<? echo $portm2; ?>">
<input type="hidden" name="guestId"   value="<? echo $guestId; ?>">
<input type="hidden" name="guestName" value="<? echo $guestName; ?>">
<input type="hidden" name="invited"   value="1">
</form>