<?php

//////////////////////////////////////////////////////////////////////////////
//
//  hit_recording.php
//
//  Contains tools for reading and manipulating meeting recording files
//
//  Note: thie file is *NOT* required for generating JNJ file
//
//////////////////////////////////////////////////////////////////////////////
    
//require_once("include/hit_common.php");

    
// Note: path should NOT have "/" appended at the end
define('DEFAULT_RECORDING_DIR', realpath('./recording'));
 

function IsMeetingIdUsed($ownerId, $meetingId){
    $dir = DEFAULT_RECORDING_DIR . "/_user/$ownerId/$meetingId";
    return file_exists($dir);
}
 

function IsJnrFileFound($ownerId, $meetingId, $recordingId){
    $path = DEFAULT_RECORDING_DIR . "/_user/$ownerId/$meetingId/$recordingId.jnr";
    return file_exists($path);
}

function IsMeetingBeingClosed($ownerId, $meetingId, $recordingId){
    $path = DEFAULT_RECORDING_DIR . "/_user/$ownerId/$meetingId/$recordingId.xml.internal";
    return file_exists($path);
}


function DeleteMeeting($ownerId, $meetingId, $recordingId){
    $path = DEFAULT_RECORDING_DIR . "/_user/$ownerId/$meetingId/$recordingId.jnr";
    if (file_exists($path))
        unlink($path);

    $path = DEFAULT_RECORDING_DIR . "/_user/$ownerId/$meetingId/$recordingId.xml";
    if (file_exists($path))
        unlink($path);
    
    DeleteMeetingFolderIfEmpty($ownerId, $meetingId);
}


function DeleteMeetingFolderIfEmpty($ownerId, $meetingId){

    $meetingPath = DEFAULT_RECORDING_DIR . "/_user/$ownerId/$meetingId/";
    if ($hMeetingDir = @opendir($meetingPath)){
        while (false !== ($fileName = readdir($hMeetingDir))){
            if ($fileName != '.' && $fileName != '..' && $fileName != 'participant'){
                closedir($hMeetingDir);
                return;
            }
        }
        closedir($hMeetingDir);
        DeleteDirectory($meetingPath);
    }


}


function DeleteOwnerFolderIfEmpty($ownerId){

    $userPath = DEFAULT_RECORDING_DIR . "/_user/$ownerId/";
    if ($hUserDir = @opendir($userPath)){
        while (false !== ($meetingId = readdir($hUserDir))){
            if ($meetingId != '.' && $meetingId != '..'){
                closedir($hUserDir);
                return;
            }
        }
        closedir($hUserDir);
        DeleteDirectory($userPath);
    }
}

function GetMeetingList($userId, &$meetings){
    $meetings = array();

    // Go through each sub-directories inside the user's folder
    $userPath = DEFAULT_RECORDING_DIR . "/_user/$userId/";
    if ($hUserDir = @opendir($userPath)){
        while (false !== ($meetingId = readdir($hUserDir))){
            if ($meetingId != '.' && $meetingId != '..'){

                // Go through each files inside the meeting folder
                $meetingPath = $userPath.$meetingId.'/';
                if ($hMeetingDir = @opendir($meetingPath)){
                    while (false !== ($recordingId = readdir($hMeetingDir))){
                        if ($recordingId != '.' && $recordingId != '..'){
                            
                            // Write down the meeting if we found its XML file
                            if (preg_match("/(.*)\.xml(\.internal)?$/i", $recordingId, $matches)){
                                $recording = array(
                                    'meeting-id'   => $meetingId,
                                    'recording-id' => $matches[1]
                               );
                                $meetings[] = $recording;
                            }                            
                        }
                    }
                    closedir($hMeetingDir);
                }
            }
        }
        closedir($hUserDir);
    }
    return;
} 




 
function GetUserList(&$users){
    $users = array();
    

    // Get directory handle
    $dir = DEFAULT_RECORDING_DIR . '/_user/';
    if (! $hDir = @ opendir($dir)){
        return;
    }
    // Go through all the files in the directory
    while (false !== ($fileName = readdir($hDir))) { 
        if ($fileName != '.' && $fileName != '..'){
            if (is_dir($dir.$fileName)){
                $users[] = $fileName;
            }
        }
    }
    closedir($hDir);

    return;
} 













////////////////////////////////////////////////////////////////////////////////
//
//  RecordingHelper
//
//  This helper provides functions to retrieve information about a recording
//
////////////////////////////////////////////////////////////////////////////////
class RecordingHelper{

    
    //--------------------------------------------------------------------------
    // NAME:    RecordingHelper
    // DESC:    Constructor
    // PARAM:   recordingPath [in]
    //              Path of the directory that stores users' meeting/recording
    //              Note: path should NOT have "/" appended at the end
    //--------------------------------------------------------------------------
    function RecordingHelper($recordingPath = DEFAULT_RECORDING_DIR){
        $this->info = array();
        $this->errorMessage = "";
        $this->recordingPath = $recordingPath;
    }
    
    
    //--------------------------------------------------------------------------
    // NAME:    GetInfo
    // DESC:    Retrieve properties (info) of a recording
    // PARAM:   ownerId [in]
    //              ID of the owner who owns the recording
    //          meetingId [in]
    //              ID of the meeting which contains the recording
    //          recordingId [in]
    //              ID of the recording session
    //          info [out]
    //              Information in hash format.
    // RETURN:  0 if operation is 0ful
    //--------------------------------------------------------------------------
    function GetInfo($ownerId, $meetingId, $recordingId, &$info){

        $xmlFilePath = "$this->recordingPath/_user/$ownerId/$meetingId/$recordingId.xml";
        // If the XML info file is not found, look for the intermediate file (.internal)
        if (! file_exists($xmlFilePath)){
            $xmlFilePath .= '.internal';
        }
        if ($result = $this->Read($xmlFilePath)){
            $info = array();
            return $result;
        }
        
        $info = $this->info;
        return 0;
    }        



    //--------------------------------------------------------------------------
    // NAME:  Read
    // DESC:  Retrieve profile info; This function is intended to be called by Load()
    // PARAM: filePath [in]
    //          Path of the XML file
    // RETURN:  0 if operation is 0ful
    //--------------------------------------------------------------------------
    function Read($filePath){

        // Reset data
        $this->info = array();

        // Read the recording XML file
        $fp = '';
        if (! $fp = @ fopen($filePath, 'r')){
            $result = CANNOT_OPEN_FILE;
            $this->errorMessage = "Could not open recording XML [$filePath] (Code:$result)";
            return $result;
        }



        // Initialize XML parser
        $this->parser = xml_parser_create('UTF-8');
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'startElement', 'endElement');
        xml_set_character_data_handler($this->parser, 'characterData');

        // Skip Windows UTF-8 file header
        $data = fread($fp, 3);
        if ($data == "\xEF\xBB\xBF")
            $data = '';

        // Begin parsing
        $this->xpath = '/';
        while ($data .= fread($fp, 4096)) {
            if (!xml_parse($this->parser, $data, feof($fp))){
                $errCode = XML_PARSE_ERROR;
                $this->errorMessage =
                    sprintf("XML Parse Error: [%d] %s (Code:$errCode)<br>",
                             xml_get_error_code($this->parser),
                             xml_error_string(xml_get_error_code($this->parser))) .
                             '<pre>' . htmlspecialchars($data) . '</pre>';
                $this->info = array();
                return $errCode;
            }
            $data = '';
        }

        // Cleanup memory usage
        xml_parser_free($this->parser);
        @flock($fp, LOCK_UN);
        @fclose($fp);

        return 0;
    }



    //--------------------------------------------------------------------------
    // NAME:  startElement
    // DESC:  callback functions for parsing XML file
    //--------------------------------------------------------------------------
    function startElement($parser, $name, $attrs) {
        // update xpath
        $this->xpath .= strtolower("$name/");

        switch($this->xpath){
        case '/status/':
            $this->info['time']         = '';
            $this->info['duration']     = '';
            $this->info['title']        = '';
            $this->info['recording']    = '';
            $this->info['participants'] = array();
            return;
        
        case '/status/starttime/':
            $this->info['time'] = GetHashValue($attrs, 'UTC', 0);
            return;
            
        case '/status/participant/':
            $userId      = GetHashValue($attrs, 'USERID', ''     );
            $displayName = GetHashValue($attrs, 'NAME'  , $userId);
            $absent      = GetHashValue($attrs, 'ABSENT', '0'    );
            
            if ($userId != '' && $absent != '1'){
                $this->info['participants'][$userId]['user-id']      = $userId;
                $this->info['participants'][$userId]['display-name'] = $displayName;
            }
            return;
        }
    }


    //--------------------------------------------------------------------------
    // NAME:  characterData
    // DESC:  callback functions for parsing XML file
    //--------------------------------------------------------------------------
    function characterData($parser, $data){
        switch($this->xpath){
        case '/status/duration/':
            $this->info['duration'] .= $data;
            break;
            
        case '/status/title/':
            $this->info['title'] .= $data;
            break;
        
        case '/status/recording/':
            $this->info['recording'] .= $data;
            break;
        }
    }


    //--------------------------------------------------------------------------
    // NAME:  endElement
    // DESC:  callback functions for parsing XML file
    //--------------------------------------------------------------------------
    function endElement($parser, $name) {
        $this->xpath = preg_replace("/[^\/]+\/$/", "", $this->xpath);
    }
} 
?>