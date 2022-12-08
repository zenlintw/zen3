<?

//////////////////////////////////////////////////////////////////////////////
//
//  hit_jnjxml.php
//
//  Contains tools for generating JNJ file and launching JoinNet
//
//////////////////////////////////////////////////////////////////////////////
    
require_once(dirname(__FILE__) . '/hit_encryption.php');

define('HIT_JNJ_TYPE_TEST_CONNECTION_TO_MCU',  9);
define('HIT_JNJ_TYPE_SPECIFIED_IN_USERINFO' , 13);

Class JnjData
{
    
    var $errorMessage;
    
    
    // ID of the meeting
    //   Usually, web application will generate a unique value for Meeting ID. 
    //   Meeting ID is used by MCU to identify the meeting. Also, it is used
    //   to determine where MCU will store meeting data, for example
    //   {recording-root-dir}/_user/{owner-id}/{meeting-id} 
    var $meetingId;         
        
    // Title of the meeting
    //   This information is written to the recording XML file for information
    //   purpose only. It is not used by JoinNet nor MCU.         
    var $meetingTitle;      
        
    // Name of the recording file
    //   MCU uses this value to determine which recording will be played when
    //   a meeting contains multiple recording JNR files. This value should
    //   be the name of JNR file without file extension. 
    var $recordingFile;     

    // Whether the meeting will be recorded
    //   Default is Yes. A recording JNR file will be generated by MCU if
    //   the meeting is recorded.
    //   0=No, 1=Yes(default)
    var $recording;         
    
    // What instruction Joinnet should take upon receiving the JNJ file
    //   Choices are meeting, playback, download, delete, checkmessage
    //   For maximum compatibility, specify the command in all lowercase
    var $command;           
    
    // Length of the meeting
    //   Default is 0. Maximum is 600.
    //   Note: When duration is equal to zero, the meeting will be considered
    //   person based. Otherwise, it will be considered event based.
    var $duration;
    
    // Whether the meeting can continue beyond the specified duration
    //   If Auto-Extension is enabled, MCU will allow the 
    //   meeting to continue after the Duaration has passed, as long as 
    //   there are active participants in the meeting room.  Note: MCU
    //   may terminate the meeting during the extension period if there 
    //   is a new meeting which requires more connections
    //   0=No, 1=Yes(default)
    var $autoExtension;
    
    // Number of guests (outside connections) allowed
    //   Default is 10. People who are not connected from the "local range"
    //   are consider as guests. Local range is specified in the MCU license.
    //   Note: the actual maximum allowance is restricted by the MCU license
    //         and the current usage in MCU. 
    var $maxGuest;          

    // Specify that the owner enters the meeting for preparation purpose only
    //   Only the meeting's owner can enter in preparation mode.  In
    //   preparation mode, owner can upload slides and make annotations.
    //   Meeting is not consider to start and no participates are allowed 
    //   to enter.
    //   0=No(default), 1=Yes
    var $preparation;       

    // Startup page to be shown in Joint Web Browser
    //   If this is not specify, default setting in MCU's configm.ini file will be used.
    var $jointBrowsingUrl;
        
    // Is this meeting reserved?
    //   Each MCU has a limited number of connections.  Web application
    //   can control the number of reserved connections for the meeting.
    //   0=No(default), 1=Yes
    var $guaranteed;        
    // If the meeting is reserved, how many seats are reserved for invited guests
    var $guaranteedInvited;     
    // If the meeting is reserved, how many seats are reserved for uninvited guests
    var $guaranteedUninvited;   


    // IP address of MCU server
    var $mcuIp;             
    // IP address of backup servers
    //   Joinnet will attempt to connect to these servers if the primary server (at $mcuIp) is down
    //   multiple addresses can be entered with comma separated.
    var $backupIp;          
    // Preferred/primary connection port at MCU server. Common setting is 2333
    var $port1;             
    // Alternate/secondary connection port at MCU server.  Common setting is 443
    //   Joinnet will attempt to connect to this port if it cannot talk to MCU using the preferred port ($port1) 
    var $port2;             
    
    
    // Horizontal size of Joinnet video window (in pixel)
    var $videoWidth;        
    // Vertical size of Joinnet video window (in pixel)
    var $videoHeight;       


    // ID of the meeting's owner
    //   Owner ID defines where MCU will store the meeting data. Meeting data
    //   is stored in owner's directory, for example 
    //   {recording-root-dir}/_user/{owner-id}/{meeting-id}
    var $ownerId;           

    // Name of the meeting's owner
    //   It will be displayed in JoinNet and recorded in meeting XML file
    var $ownerName;         
    
    // Email address of the meeting's owner
    //   If there are any system messages, MCU will send them to this address
    var $ownerEmail;
    
    // Maximum disk usage for the owner (in MByte)
    //   Default is 50MB. Before MCU starts a new meeting, it checks the disk
    //   usage of meeting's owner. If the owner has exceed his/her quota, MCU
    //   will refuse to start a new meeting and it will send a warning message
    //   to the owner.
    var $diskQuota;         
    
    // ID of the participant
    //   When an ID is specified, MCU can use this to identify and control
    //   this user in the meeting.  For example, if two people try to enter
    //   a meeting using the same ID, only one of them will be able to get in.
    //   Also, if a person has been blocked by the owner during a meeting, 
    //   he/she will not be able to join again. 
    var $guestId;           
    
    // Name of the participant
    //   It will be displayed in JoinNet and recorded in meeting XML file.
    var $guestName;         
    
    // Whether this participant holds a ticket
    //   The followings apply to a person who holds a ticket:
    //   - his video won't be seen by other participants during question mode
    //   - his name won't be shown in control panel except token holder
    //   Note: this option is obsolete and is no longer supported
    //   0=No(default) 1=Yes
    var $ticket;            
        
    // Whether this participant is invited.        
    //   When an un-invited person joins a meeting, MCU will prompt the 
    //   meeting's owner for authorization. 
    //   0=No(default) 1=Yes
    var $invited;           

    // The time JNJ file is created (UTC time in seconds)
    //   MCU uses this to check for the freshness of the request in JNJ file.
    //   The file is considered fresh if the time is within 5 minutes.
    //   Timestamp is not checked if "password" is present
    var $timeStamp;         
        
    // Password required for entering the meeting
    //   A password can be used to enhance security. If it is specified, 
    //   JoinNet will ask users to enter password prior entering the meeting.
    //   To prevent illicit JNJ file sharing, the generated JNJ file will be
    //   valided for 5 minutes if password is not specified
    var $password;          

    // Group settings are for experimental and is not supported
    var $groupName;         
    var $groupDiskQuota;
    var $maxGroupGuest;
    
    
    // The type of JNJ file
    // Note: not all types are supported by HIT
    //	0 - normal meeting
    //	1 - goto meeting
    //	2 - playback
    //	3 - download
    //	4 - web office
    //	5 - web office message manager
    //	6 - delete recording file
    //	7 - e classroom
    //	8 - test wizard including GC
    //	9 - test wizard only MCU  (supported)
    //	10 - web casting meeting
    //	11 - web casting playback
    //	12 - chat room (supported)
    var $codeType;


    // These parameters are Public-Key cryptography settings for 
    // encrypting the user-info entry when creating JNJ file    
    // see JnjHelper's contructor for explanations
    var $privateKeyPath;
    var $publicKeyPath;
    var $siteId;
    var $passPhrase;




    //--------------------------------------------------------------------
    // DESC:    Constructor 
    //--------------------------------------------------------------------
    function JnjData()
    {
        $this->reset();
        $this->errorMessage = '';
    }


    //--------------------------------------------------------------------
    // DESC:    reset 
    // PARAM:   Reset all the settings in user info 
    //--------------------------------------------------------------------
    function reset(){
        $this->meetingId      = '';
        $this->meetingTitle   = '';
        $this->recordingFile  = '';
        $this->command        = '';
        $this->ownerId        = '';
        $this->guestId        = '';
        $this->groupName      = '';
        $this->ownerEmail     = '';
        $this->ownerName      = '';
        $this->guestName      = '';
        $this->diskQuota      = '';
        $this->groupDiskQuota = '';
        $this->maxGuest       = '';
        $this->maxGroupGuest  = '';
        $this->invited        = '';
        $this->ticket         = '';
        $this->timeStamp      = '';
        $this->recording      = '';
        $this->duration       = '';
        $this->preparation    = '';
        $this->guaranteed     = '';
        $this->guaranteedInvited      = '';
        $this->guaranteedUninvited    = '';
        $this->mcuIp          = '';
        $this->backupIp       = '';
        $this->port1          = '';
        $this->port2          = '';
        $this->videoWidth     = '';
        $this->videoHeight    = '';
        $this->codeType       = '';
        $this->privateKeyPath = '';
        $this->publicKeyPath  = '';
        $this->siteId         = '';
        $this->passPhrase     = '';
    }






    //--------------------------------------------------------------------
    // NAME:    setPassword 
    // DESC:    Specify a password that user must be entered before
    //            Joinnet joins a meeting, playback, downloads, etc...
    // PARAM:   $password [in]
    //            password in plain text
    // NOTE:    password will be hashed using SHA1 during the creation of
    //              JNJ file
    //--------------------------------------------------------------------
    function setPassword($password){
        $this->password = $password;
    }
    
    
    //--------------------------------------------------------------------
    // NAME:    setOwnerInfo 
    // DESC:    Set owner related information in user-info
    //          This function is applied for Meeting mode.
    // PARAM:   $ownerId [in]
    //            ID of the meeting's owner
    //            Owner ID defines where MCU will store/get meeting data.
    //            Meeting data is stored in owner's directory, for example 
    //            {recording-root-dir}/_user/{owner-id}/{meeting-id}
    //          $ownerName [in]
    //            Name of the meeting's owner
    //          $ownerEmail [in]
    //            Email address of the meeting's owner.  MCU will send
    //            any system message to this address.
    //          $diskQuota [in]
    //            Maximum disk usage for the owner (in MByte)
    //            Default is 50MB.  If the owner has exceed his/her quota, 
    //            MCU will refuse to start a new meeting and it will send 
    //            a error message to the owner.  This parameter is not
    //            used for playback or download activities.
    //--------------------------------------------------------------------
    function setOwnerInfo($ownerId, $ownerName, $ownerEmail='', $diskQuota=''){
        $this->ownerId    = $ownerId;
        $this->ownerName  = $ownerName;
        $this->ownerEmail = $ownerEmail;        
        $this->diskQuota  = $diskQuota;
    }
        
        
    //--------------------------------------------------------------------
    // NAME:    setGuestInfo 
    // DESC:    Set guest related information in user-info
    //          This function is applied for Meeting mode.
    // PARAM:   $guestId [in]
    //            ID of the participate who will enter the meeting
    //          $guestName [in]
    //            Name of the participate
    //          $isInvited [in]
    //            whether the participate is invited
    //            0=No(default) 1=Yes
    //          $hasTicket [in]
    //            whether the participate enters meeting using a ticket
    // NOTE:
    //   When an un-invited person joins a meeting, MCU will prompt the 
    //   meeting's owner for authorization. 
    //
    //   The followings apply to a person who holds a ticket:
    //   - his video won't be seen by other participants during question mode
    //   - his name won't be shown in control panel except token holder
    //   Note: this option is obsolete and is no longer supported
    //--------------------------------------------------------------------
    function setGuestInfo($guestId, $guestName, $isInvited='', $hasTicket=''){        
        $this->guestId   = $guestId;
        $this->guestName = $guestName;
        $this->invited   = $isInvited;
        $this->hasTicket = $hasTicket;
    }

    //--------------------------------------------------------------------
    // NAME:    setVideoWindowSize 
    // DESC:    Set the size of video window in Joinnet
    //          This function is applied for Meeting mode.
    // PARAM:   $width [in]
    //            horizontal size in pixel
    //          $height [in]
    //            vertical size in pixel
    //--------------------------------------------------------------------
    function setVideoWindowSize($width, $height){
        $this->videoWidth  = $width;
        $this->videoHeight = $height;
    }
    
    
    //--------------------------------------------------------------------
    // NAME:    setMcuInfo 
    // DESC:    Set server related information in user-info
    // PARAM:   $primaryIp [in]
    //            Main server that holds meeting service
    //          $backupIp [in]
    //            Alternate servers that Joinnet can be connect to if the 
    //            main server  is down.  Multiple addresses can be entered 
    //            with comma separated.  Use '' (empty string) if there
    //            is no backup servers.
    //          $preferredPort [in]
    //            Primary connection port listened by MCU server
    //            Common setting is 2333
    //          $alternatePort [in]
    //            Secondary connection port listened by MCU server
    //            Common setting is 443 (standard HTTPS port)
    //--------------------------------------------------------------------
    function setMcuInfo($primaryIp, $backupIp, $preferredPort, $alternatePort){
        $this->mcuIp    = $primaryIp;
        $this->backupIp = $backupIp;
        $this->port1    = $preferredPort;
        $this->port2    = $alternatePort;
    }
    
    
    //--------------------------------------------------------------------
    // NAME:    setReservationInfo 
    // DESC:    Set reservation information in user-info
    //          This function is applied for Meeting mode.
    // PARAM:   $guaranteedInvited [in]
    //            number of seats are reserved for invited guests
    //          $guaranteedUninvited [in]
    //            number of seats are reserved for uninvited guests
    //--------------------------------------------------------------------
    function setReservationInfo($guaranteedInvited, $guaranteedUninvited){
        $this->guaranteed          = '1';
        $this->guaranteedInvited   = $guaranteedInvited;
        $this->guaranteedUninvited = $guaranteedUninvited;
    }




    //--------------------------------------------------------------------
    // DESC:    SetEncryptionInfo 
    // DESC:    Set public-Key cryptography configuration for
    //          encrypting the user-info entry when creating JNJ file
    //
    // PARAM:   privateKeyPath [in]
    //              Path of the private key which will be used for encryption
    //          publicKeyPath [in]
    //              Path of the public key which will be used for encryption
    //          siteId [in]
    //              Identifier to be included in the encrypted string.
    //              Receipent use this to identify who sends the message and 
    //              to determine which public key should be used to decrypt 
    //              the message
    //          passPhrase [in]
    //              Password to access the private key
    //--------------------------------------------------------------------
    function SetEncryptionInfo($privateKeyPath, $publicKeyPath, $siteId, $passPhrase)
    {
        $this->privateKeyPath = $privateKeyPath;
        $this->publicKeyPath  = $publicKeyPath;
        $this->siteId         = $siteId;
        $this->passPhrase     = $passPhrase;
    }

    //--------------------------------------------------------------------
    // NAME:    setMeetingMode 
    // DESC:    Prepare a JNJ file for launching/joining a meeting
    // PARAM:   $ownerId [in]
    //            ID of the person who will own the meeting
    //          $meetingId [in]
    //            ID of the meeting
    //          $duration [in]
    //            Default is 0. Maximum is 600. Note: When duration is 
    //            equal to zero, the meeting will be considered person 
    //            based. Otherwise, it will be considered event based.
    //          $meetingTitle [in]
    //            Title of the meeting
    //          $maxGuest [in]
    //            Default is 10
    //            Number of guests (outside connections) allowed
    //          $autoExtend [in]
    //            0=No, 1=Yes(default)
    //            Whether the meeting can continue beyond the specified 
    //            duration
    //          $willBeRecorded [in]
    //            0=No, 1=Yes(default)
    //            A recording JNR file will be generated by MCU if
    //            the meeting is recorded.
    //          $preparationMode [in]
    //            0=No(default), 1=Yes
    //            In preparation mode, owner can upload slides and make 
    //            annotations.  Meeting is not consider to start and no 
    //            participates are allowed to enter.
    //--------------------------------------------------------------------
    function setMeetingMode(   $ownerId,
                                $meetingId, 
                                $duration='', 
                                $meetingTitle='', 
                                $maxGuest='', 
                                $autoExtend='', 
                                $willBeRecorded='', 
                                $preparationMode='',
                                $jointBrowsingUrl='')
    {
        $this->codeType      = HIT_JNJ_TYPE_SPECIFIED_IN_USERINFO;
        $this->command       = 'meeting';
        $this->ownerId       = $ownerId;
        $this->meetingId     = $meetingId;
        $this->meetingTitle  = $meetingTitle;
        $this->maxGuest      = $maxGuest;
        $this->duration      = $duration;
        $this->autoExtension = $autoExtend;
        $this->recording     = $willBeRecorded;
        $this->preparation   = $preparationMode;
        $this->jointBrowsingUrl = $jointBrowsingUrl;

        // Note: the following dummy parameters are for back compatibility
        $this->ownerName     = 'undefined-owner-name';
    }    
    
    //--------------------------------------------------------------------
    // NAME:    setPlaybackMode 
    // DESC:    Prepare a JNJ file for playing a recorded meeting
    // PARAM:   $ownerId [in]
    //            ID of the owner who owns the recording
    //          $meetingId [in]
    //            ID of the meeting that is recorded
    //          $recordingFile [in]
    //            Name of JNR recording file without file extension.
    //            MCU uses this value to determine which recording will 
    //            be played when a meeting contains multiple recording 
    //            JNR files.
    //--------------------------------------------------------------------
    function setPlaybackMode($ownerId, $meetingId, $recordingFile=''){
        $this->codeType      = HIT_JNJ_TYPE_SPECIFIED_IN_USERINFO;
        $this->command       = 'playback';
        $this->ownerId       = $ownerId;
        $this->meetingId     = $meetingId;
        $this->recordingFile = $recordingFile;

        // Note: the following dummy parameters are for back compatibility
        $this->ownerName     = 'undefined-owner-name';
    }



    //--------------------------------------------------------------------
    // NAME:    SetCheckMessage
    // DESC:    Prepare a JNJ file for checking messages
    // PARAM:   $ownerId [in]
    //            ID of the owner who wants to check message
    //--------------------------------------------------------------------
    function setCheckMessageMode($ownerId){
        $this->codeType      = HIT_JNJ_TYPE_SPECIFIED_IN_USERINFO;
        $this->command = 'checkmessage';
        $this->ownerId = $ownerId;

        // Note: the following dummy parameters are for back compatibility
        $this->ownerName = "undefined-owner-name";
        $this->meetingId = "undefined-meeting-id";
    }


    //--------------------------------------------------------------------
    // NAME:    setDownloadMode 
    // DESC:    Prepare a JNJ file for downloading a recorded meeting
    // PARAM:   $meetingId [in]
    //            ID of the meeting that is recorded
    //          $recordingFile [in]
    //            Name of JNR recording file without file extension.
    //            MCU uses this value to determine which recording will 
    //            be played when a meeting contains multiple recording 
    //            JNR files.
    //--------------------------------------------------------------------
    function setDownloadMode($ownerId, $meetingId, $recordingFile){
        $this->codeType      = HIT_JNJ_TYPE_SPECIFIED_IN_USERINFO;
        $this->command       = 'download';
        $this->ownerId       = $ownerId;
        $this->meetingId     = $meetingId;
        $this->recordingFile = $recordingFile;

        // Note: the following dummy parameters are for back compatibility
        $this->ownerName     = 'undefined-owner-name';
    }
    
    
    //--------------------------------------------------------------------
    // NAME:    setTestWizardMode 
    // DESC:    Prepare a JNJ file for launching JoinNet in Test Wizard mode
    //--------------------------------------------------------------------
    function setTestWizardMode(){
        $this->codeType = HIT_JNJ_TYPE_TEST_CONNECTION_TO_MCU;
    }




    //--------------------------------------------------------------------
    // NAME:    getUserInfo
    // DESC:    Return user-info in XML format which can then be encrypted 
    //            and insert to a JNJ file.
    // PARAM:   userInfo [out]
    // RETURN:  0 = operation is success
    //--------------------------------------------------------------------
    function getUserInfo(&$userInfo){

        // Title of the meeting
        $meetingTitle = '';
        if ($this->meetingTitle !== '')
            $meetingTitle = '<meetingtitle>'.htmlspecialchars($this->meetingTitle).'</meetingtitle>';


        // Password
        $password = '';
        if ($this->password !== ''){
            $hash = '';
            $encryptor = new EncryptionTool();
            $errCode = $encryptor->GetBase64Sha1($hash, $this->password);
            if ($errCode) {
                $this->errorMessage = $encryptor->errorMessage . " Unable to encrypt password.";
                return $errCode;
            }
            
            // note: password is encrypted before putting it in the XML file
            $password = '<password>'.$hash.'</password>';
        }
        
        // Owner related
        $ownerId = '';
        if ($this->ownerId !== '')
            $ownerId = 'id="'.htmlspecialchars($this->ownerId).'"';

        $diskQuota = '';
        if ($this->diskQuota !== '')
            $diskQuota = 'diskquota="'.htmlspecialchars($this->diskQuota).'"';

        $maxGuest = '';
        if ($this->maxGuest !== '')
            $maxGuest = 'maxoutconnection="'.htmlspecialchars($this->maxGuest).'"';

        $ownerEmail = '';
        if ($this->ownerEmail !== '')
            $ownerEmail = 'email="'.htmlspecialchars($this->ownerEmail).'"';

        $ownerName = htmlspecialchars($this->ownerName);
        $meetingId = htmlspecialchars($this->meetingId);
        $timeStamp = time();


        // Guest related
        $guestInfo = '';
        if ($this->guestName !== '' || $this->guestId !== '' || $this->invited !== '' || $this->ticket !== ''){

            $guestId = '';
            if ($this->guestId !== '')
                $guestId = 'id="'.htmlspecialchars($this->guestId).'"';

            $invited = '';
            if ($this->invited !== '')
                $invited = 'invited="'.htmlspecialchars($this->invited).'"';

            $ticket = '';
            if ($this->ticket !== '')
                $ticket = 'ticket="'.htmlspecialchars($this->ticket).'"';

            $guestName = htmlspecialchars($this->guestName);

            $guestInfo = "<guest $guestId $invited $ticket>$guestName</guest>";
        }

        // Group related
        $groupInfo = '';
        if ($this->groupName !== '' || $this->groupDiskQuota !== '' || $this->maxGroupGuest !== ''){

            $groupDiskQuota = '';
            if ($this->groupDiskQuota !== '')
                $groupDiskQuota = 'diskquota="'.htmlspecialchars($this->groupDiskQuota).'"';

            $maxGroupGuest = '';
            if ($this->maxGroupGuest !== '')
              $maxGroupGuest = 'maxguest="'.htmlspecialchars($this->maxGroupGuest).'"';

            $groupName = htmlspecialchars($this->groupName);

            $groupInfo = "<group $groupDiskQuota $maxGroupGuest>$groupName</group>";
        }


        // Command + parameters
        $command = '';
        if ($this->command !== '' || $this->duration !== '' || $this->recordingfile !='' || $this->preparation !== ''){

            $recording = '';
            if ($this->recording !== '')
                $recording = 'recording="'.htmlspecialchars($this->recording).'"';

            $autoExtension = '';
            if ($this->autoExtension !== '')
                $autoExtension = 'autoextension="'.htmlspecialchars($this->autoExtension).'"';

            $duration = '';
            if ($this->duration !== '')
                $duration = 'duration="'.htmlspecialchars($this->duration).'"';

            $recordingFile = '';
            if ($this->recordingFile !== '')
                $recordingFile = 'file="'.htmlspecialchars($this->recordingFile).'"';

            $preparation = '';
            if ($this->preparation !== '')
                $preparation = 'preparationmode="'.htmlspecialchars($this->preparation).'"';

            $jointBrowsingUrl = '';
            if ($this->jointBrowsingUrl !== '')
                $jointBrowsingUrl = 'default_joint_browsing_page="'.htmlspecialchars($this->jointBrowsingUrl).'"';

            $command = htmlspecialchars($this->command);

            // seat guarantee/reservation settings
            $guaranteeInfo = '';
            if ($this->guaranteed !== ''){

                $guaranteedInvited = '';
                if ($this->guaranteedInvited !== '')
                    $guaranteedInvited = 'invited="'.htmlspecialchars($this->guaranteedInvited).'"';

                $guaranteedUninvited = '';
                if ($this->guaranteedUninvited !== '')
                    $guaranteedUninvited = 'uninvited="'.htmlspecialchars($this->guaranteedUninvited).'"';

                $guaranteeInfo = "<guaranteed $guaranteedInvited $guaranteedUninvited>{$this->guaranteed}</guaranteed>";
            }
        }

        $userInfo = <<<END
<?xml version="1.0" encoding="utf-8" ?>
<jnj>
    <owner $ownerId $diskQuota $maxGuest $ownerEmail>$ownerName</owner>
    <meetingid>$meetingId</meetingid>
    <timestamp>$timeStamp</timestamp>
    $meetingTitle
    $password
    $guestInfo
    $groupInfo
    <command $recording $duration $autoExtension $recordingFile $jointBrowsingUrl $preparation>{$command}{$guaranteeInfo}</command>
</jnj>
END;

        return 0;
    }



//<? dummy comment to overcome EmEditor's display bug




    //--------------------------------------------------------------------
    // NAME:    getEncryptedUserInfo
    // DESC:    Retrieve encrypted user-info
    // PARAM:   encryptedInfo [out]
    //              Encrypted user-info entry in following format
    //              <site-id>|<session-key>|<encrypted-user-info>
    // RETURN:  0 = operation is success
    //--------------------------------------------------------------------
    function getEncryptedUserInfo(&$encryptedUserInfo)
    {
        $encryptedUserInfo = '';
        
        // Obtain user-info in XML format
        if ($errCode = $this->getUserInfo($userInfo))
            return $errCode;

        // Encrypt user-info
        $encryptor = new EncryptionTool();
        $errCode = $encryptor->PkeEncrypt(
            $encryptedUserInfo,
            $userInfo, 
            $this->privateKeyPath, 
            $this->publicKeyPath, 
            $this->siteId, 
            $this->passPhrase 
       );
        if ($errCode){
            $this->errorMessage = $encryptor->errorMessage;
        }
        
        return $errCode;
    }



    //--------------------------------------------------------------------
    // NAME:    getJnjFile
    // DESC:    Build a completed a JNJ file for launching JoinNet.  
    // Note:    Before calling this function, caller should have called
    //          other configuration functions such as setMeetingMode()
    //          and setMcuInfo().
    // PARAM:   jnjFile [out]
    //              Content of the JNJ file
    // RETURN:  0 = operation is success
    //--------------------------------------------------------------------
    function getJnjFile(&$jnjFile)
    {
        $newLine = "\r\n";
        $port2Entry     = $this->port2 !== ''         ? "{$newLine}portm2={$this->port2}" : "";
        $backupIpEntry  = $this->backupIp !== ''      ? "{$newLine}backupip={$this->backupIp}" : "";
        $actionEntry    = ($this->command != "meeting" && $this->codeType != HIT_JNJ_TYPE_TEST_CONNECTION_TO_MCU)
                                                      ? "{$newLine}action=1" : "";
        $videoSizeEntry = $this->videoWidth !== ''    ? "{$newLine}size_w={$this->videoWidth}{$newLine}size_h={$this->videoHeight}" : "";

        $jnjFile = <<<END
# if you see this file, please download and reinstall JoinNet software from http://www.homemeeting.com
[general]
codetype={$this->codeType}
ip={$this->mcuIp} {$backupIpEntry}
domain=HomeMeeting
portm={$this->port1} {$port2Entry} {$actionEntry} {$videoSizeEntry}   
END;

        // If the codetype is 13 (User-Info), include the userinfo entry in the JNJ file.
        if ($this->codeType == HIT_JNJ_TYPE_SPECIFIED_IN_USERINFO){
            // Generate user-info XML and encrypt it
            $encryptedUserInfo = '';
            $errCode = $this->getEncryptedUserInfo(
                $encryptedUserInfo,
                $this->privateKeyPath,
                $this->publicKeyPath,
                $this->siteId,
                $this->passPhrase
           );
            if ($errCode){
                $jnjFile = '';
                return $errCode;
            }
            $jnjFile .= "{$newLine}userinfo={$encryptedUserInfo}{$newLine}";
        }
    
        return 0;
    }




    //--------------------------------------------------------------------------
    // NAME:    launchJoinnet 
    // DESC:    Output HTTP response to make browser to launch JoinNet
    //--------------------------------------------------------------------------    
    function launchJoinnet()
    {
        // Get the content of JNJ file
        $content = '';
        $errCode = $this->getJnjFile(
            $content,
            $this->privateKeyPath,
            $this->publicKeyPath,
            $this->siteId,
            $this->passPhrase
       );
        if ($errCode){
            return $errCode;
        }
        
        hitLaunchJoinnet($content);
        exit();        
    }


} // end of class
    
    


//--------------------------------------------------------------------------
// NAME:    launchJoinnet 
// DESC:    Output HTTP response to make browser to launch JoinNet
// PARAM:   $jnjContent [in]
//              Content of JNJ file to be passed to the browser which in
//              turn will pass to JoinNet
//--------------------------------------------------------------------------    
function hitLaunchJoinnet($jnjContent){
    $randomFileName = uniqid("launch");
    header("Content-Disposition: inline; filename=$randomFileName.jnj");
    header("Content-Type: application/jnj");
    header("Content-Length: ".strlen($jnjContent));
    header("Cache-Control: private");
    print $jnjContent;
    flush();
    exit();        
}
    
?>