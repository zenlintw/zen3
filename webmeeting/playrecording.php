<?php

header("content-type: text/html; charset=UTF-8");
header("Cache-Control: private");

//require_once("include/hit_common.php");
require_once('include/hit_recording.php');
require_once('include/hit_jnjxml.php');
require_once('include/hit_settings.php');

// Configure setting in the JNJ file
$jnj = new JnjData();
$jnj->SetPlaybackMode($_POST['ownerId'], $_POST['meetingId'], $_POST['recordingId']);
$jnj->SetOwnerInfo($_POST['ownerId'], "dummy");
$jnj->setMcuInfo($_POST['ip'], '', $_POST['portm'], $_POST['portm2']);
$jnj->setPassword($_POST['password']);
$jnj->setEncryptionInfo(
    DEFAULT_KEY_DIR.DEFAULT_PRIVATE_KEY,
    DEFAULT_KEY_DIR.DEFAULT_PUBLIC_KEY,
    DEFAULT_SITE_ID,
    DEFAULT_PASS_PHRASE
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
$formattedJnjFile = str_replace("\n", "<br>", $formattedJnjFile);


// Get the user info in plain (un-encrypted) XML format
$userInfo = '';
$jnj->GetUserInfo($userInfo);

// Format the XML for displaying in browser
$formattedUserInfo = preg_replace("/\n[\r\n\s]+/", '<br>', htmlspecialchars($userInfo));

// Save form inputs, so we can fill in values to the form when users click BACK button
SaveSessionValue("previousPlayInput", $_GET);

if (trim($meetingId) == '')
    $warnings[] = 'Meeting ID is not specified.';
else
    if (!IsMeetingIdUsed($ownerId, $meetingId))
        $warnings[] = "Meeting [$meetingId] is not found.";

if (trim($ownerId) == '')
    $warnings[] = 'Owner ID is not specified.';

if (trim($ip) == '')
    $warnings[] = 'MCU IP address is not specified.';

if (trim($portm) == '')
    $warnings[] = 'MCU port is not specified.';

?>
<html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Play Recording (2/2)</title>
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <?php print BuildHtmlNav('play'); ?>

    <form method="get" name="frm1">

        <?php
            if (count($warnings)){
                print "<div class='warning'>";
                print "<ul><li>".implode("</li><li>", $warnings)."</li></ul>";
                print "</div>";
            }
        ?>
        <div class="content">

            <input type="button" value="< Back" class="bttn" onclick="location.href='playrecording.php';">
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

            <input type="hidden" name="task" value="launch">
            <input type="hidden" name="meetingId"       value="<?php print htmlspecialchars($meetingId); ?>">
            <input type="hidden" name="ownerId"         value="<?php print htmlspecialchars($ownerId); ?>">
            <input type="hidden" name="recordingId"     value="<?php print htmlspecialchars($recordingId); ?>">
            <input type="hidden" name="password"        value="<?php print htmlspecialchars($password); ?>">
            <input type="hidden" name="ip"              value="<?php print htmlspecialchars($ip); ?>">
            <input type="hidden" name="portm"           value="<?php print htmlspecialchars($portm); ?>">
            <input type="hidden" name="portm2"          value="<?php print htmlspecialchars($portm2); ?>">
        </div>
    </form>

    <script>
        document.frm1.btnLaunch.focus();
    </script>
</body>
</html>
