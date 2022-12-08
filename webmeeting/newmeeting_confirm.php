<?php
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/sys_config.php');
require_once(sysDocumentRoot . '/webmeeting/global.php');
require_once(sysDocumentRoot . '/webmeeting/include/hit_settings.php');    // for encryption related settings, like DEFAULT_PUBLIC_KEY_PATH
require_once(sysDocumentRoot . '/webmeeting/include/hit_jnjxml.php');
require_once(sysDocumentRoot . '/webmeeting/include/hit_recording.php');   // for checking reuse meetingId - IsMeetingIdUsed()
require_once(sysDocumentRoot . '/webmeeting/include/hit_integration.php');
#========== main =================

$online_meeting_info = get_online_meeting($_POST['ip'], $_POST['api_port'], $_POST['api_path'], $_POST['ownerId']);
if (strcmp($online_meeting_info,'0') != 0)
{
	header("Location: /webmeeting/oh_set.php");
	exit;
}

buildMeetingChatroom($_POST['cid'], $_POST['meetingTitle'], $_POST['meetingId'], $_POST['CU_Teacher_ID'], 'joinnet');

//setMeetingInfo($ownerId, $meetingId, $meetingTitle);

// Configure setting in the JNJ file
$jnj = new JnjData();

$jnj->setEncryptionInfo(
    DEFAULT_KEY_DIR.DEFAULT_PRIVATE_KEY,
    DEFAULT_KEY_DIR.DEFAULT_PUBLIC_KEY,
    DEFAULT_SITE_ID,
    DEFAULT_PASS_PHRASE
);

$meetingTitle = $_POST['meetingTitle'];
$ownerName    = $_POST['ownerName'];

$jnj->setMeetingMode(
    $_POST['ownerId'],
    $_POST['meetingId'],
    $_POST['duration'],
    $meetingTitle,
    $_POST['maxGuest'],
    $_POST['autoExtension'],
    $_POST['recording'],
    $_POST['preparation']
);

$jnj->setOwnerInfo(
    $_POST['ownerId'],
    $ownerName,
    $_POST['ownerEmail'],
    $_POST['diskQuota']
);

$jnj->setMcuInfo(
    $_POST['ip'],
    $_POST['ip2'],
    $_POST['portm'],
    $_POST['portm2']
);

$jnj->setPassword(
    $_POST['password']
);

// Launch JoinNet if user clicked "Launch JoinNet" button
if ($_POST['task'] == "launch"){
    $errCode = $jnj->launchJoinnet();
    print $jnj->errorMessage;
    exit();
}

//----------------------------------------------------
// Note: Codes below this point are for display/information only.
// It is not required for launching JoinNet
//----------------------------------------------------
$warnings = array();

// Format the JNJ Content for displaying in browser
$errCode = $jnj->GetJnjFile($jnjFile);
if ($errCode) {
    $warnings[] = $jnj->errorMessage;
}

// escape HTML characters
$formattedJnjFile = htmlspecialchars($jnjFile);
// replace \n with <br>
$formattedJnjFile = str_replace("\n", '<br>', $formattedJnjFile);


// Get the user info in plain (un-encrypted) XML format
$userInfo = '';
$jnj->GetUserInfo($userInfo);

// Format the XML for displaying in browser
$formattedUserInfo = preg_replace("/\n[\r\n\s]+/", '<br>', htmlspecialchars($userInfo));

// Check input data and give simple warnings to users

if ($meetingId == '')
    $warnings[] = 'Meeting ID is not specified.';

if ($ownerId == '')
    $warnings[] = 'Owner ID is not specified.';

if ($ownerName == '')
    $warnings[] = 'Owner Name is not specified.';

if ($ip == '')
    $warnings[] = 'MCU IP address is not specified.';

if ($portm == '')
    $warnings[] = 'MCU port is not specified.';

if ($meetingId == '')
    $warnings[] = 'Meeting Id is not specified.';
else
    if (IsMeetingIdUsed($ownerId, $meetingId))
        $warnings[] = "Meeting Id [$meetingId] is already in use.  Try to use a different ID.";

?>
<html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>New Meeting (2/2)</title>
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <form method="get" name="frm1">

        <?php
            // Print warning messages if there are any
            if (count($warnings)){
                print "<div class='warning'>";
                print "<ul><li>".implode("</li><li>", $warnings)."</li></ul>";
                print "</div>";
            }
        ?>

        <div class="content">
            <input type="button" value="< Back" class="bttn" onclick="location.href='newmeeting.php';">
            <input type="submit" value="Launch JoinNet..." class="bttn" name="btnLaunch" >

            <table cellspacing="0">
                <tr>
                    <td nowrap class="subject">User-Info</td>
                </tr>
                <tr>
                    <td nowrap><?php print $formattedUserInfo; ?></td>
                </tr>
                <tr>
                    <td nowrap class="subject">JNJ File with User-Info Encrypted</td>
                </tr>
                <tr>
                    <td nowrap><?php print $formattedJnjFile; ?></td>
                </tr>
            </table>


            <input type="hidden" name="task"            value="launch">
            <input type="hidden" name="meetingId"       value="<?php print htmlspecialchars($meetingId); ?>">
            <input type="hidden" name="meetingTitle"    value="<?php print htmlspecialchars($meetingTitle); ?>">
            <input type="hidden" name="maxGuest"        value="<?php print htmlspecialchars($maxGuest); ?>">
            <input type="hidden" name="duration"        value="<?php print htmlspecialchars($duration); ?>">
            <input type="hidden" name="autoExtension"   value="<?php print htmlspecialchars($autoExtension); ?>">
            <input type="hidden" name="recording"       value="<?php print htmlspecialchars($recording); ?>">
            <input type="hidden" name="password"        value="<?php print htmlspecialchars($password); ?>">
            <input type="hidden" name="ownerName"       value="<?php print htmlspecialchars($ownerName); ?>">
            <input type="hidden" name="ownerId"         value="<?php print htmlspecialchars($ownerId); ?>">
            <input type="hidden" name="ownerEmail"      value="<?php print htmlspecialchars($ownerEmail); ?>">
            <input type="hidden" name="diskQuota"       value="<?php print htmlspecialchars($diskQuota); ?>">
            <input type="hidden" name="ip"              value="<?php print htmlspecialchars($ip); ?>">
            <input type="hidden" name="ip2"             value="<?php print htmlspecialchars($ip2); ?>">
            <input type="hidden" name="portm"           value="<?php print htmlspecialchars($portm); ?>">
            <input type="hidden" name="portm2"          value="<?php print htmlspecialchars($portm2); ?>">
        </div>
    </form>
<script>
    document.frm1.btnLaunch.focus();
</script>
</body>
</html>
