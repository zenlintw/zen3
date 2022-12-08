<?php

require_once('include/hit_settings.php');   // for encryption related settings, like DEFAULT_PUBLIC_KEY_PATH
require_once('include/hit_recording.php');  // for checking meetingId - IsMeetingIdUsed()
require_once('include/hit_jnjxml.php');

//$ownerName = iconv("big5","UTF-8",$ownerName);
//$guestName = iconv("big5","UTF-8",$guestName);

// Fill in the user-info data structure
$jnj = new JnjData();
$jnj->setMeetingMode($_POST['ownerId'], $_POST['meetingId'], $_POST['duration']);
$jnj->setOwnerInfo($_POST['ownerId'], $_POST['ownerName']);
$jnj->setGuestInfo($_POST['guestId'], $_POST['guestName'], $_POST['invited']);
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
$formattedJnjFile = str_replace("\n", '<br>', $formattedJnjFile);


// Get the user info in plain (un-encrypted) XML format
$userInfo = '';
$jnj->GetUserInfo($userInfo);

// Format the XML for displaying in browser
$formattedUserInfo = preg_replace("/\n[\r\n\s]+/", '<br>', htmlspecialchars($userInfo));

// Save form inputs, so we can fill in values to the form when users click BACK button
SaveSessionValue("previousJoinInput", $HTTP_GET_VARS);



if ($meetingId == '')
    $warnings[] = 'Meeting ID is not specified.';

if ($ownerId == '')
    $warnings[] = 'Owner ID is not specified.';

if ($ip == '')
    $warnings[] = 'MCU IP address is not specified.';

if ($portm == '')
    $warnings[] = 'MCU port is not specified.';


?>
<html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Join Meeting (2/2)</title>
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <?php print BuildHtmlNav('join'); ?>
    <form method="get" name="frm1">

        <?php
            if (count($warnings)){
                print "<div class='warning'>";
                print "<ul><li>".implode("</li><li>", $warnings)."</li></ul>";
                print "</div>";
            }
        ?>

        <div class="content">
            <input type="button" value="< Back" class="bttn" onclick="location.href='joinmeeting.php';">
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
            <input type="hidden" name="ownerId"         value="<?php print htmlspecialchars($ownerId); ?>">
            <input type="hidden" name="ownerName"       value="<?php print htmlspecialchars($ownerName); ?>">
            <input type="hidden" name="duration"        value="<?php print htmlspecialchars($duration); ?>">
            <input type="hidden" name="password"        value="<?php print htmlspecialchars($password); ?>">
            <input type="hidden" name="guestId"         value="<?php print htmlspecialchars($guestId); ?>">
            <input type="hidden" name="guestName"       value="<?php print htmlspecialchars($guestName); ?>">
            <input type="hidden" name="invited"         value="<?php print htmlspecialchars($invited); ?>">
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
