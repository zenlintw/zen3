<?php


//////////////////////////////////////////////////////////////////////////////
//
//  hit_common.php
//
//  Contains common helper functions used by the HIT sample.
//
//  Note: This file is *NOT* required for generating JNJ file
//
//////////////////////////////////////////////////////////////////////////////

// Error code
define( 'XML_PARSE_ERROR'       , 1000 );
define( 'FILE_NOT_FOUND'        , 1001 );
define( 'CANNOT_OPEN_FILE'      , 1002 );
define( 'CANNOT_GET_SHARED_LOCK', 1003 );


    
// use CRLF as carriage return, so Windows users can use Notepad to edit/view XML file.
define( 'NEWLINE', "\x0D\x0A" ); 

    
// Automatic OS detection
if( function_exists( 'posix_uname' )){
    define( 'PLATFORM', 'linux' );      
} else {
    define( 'PLATFORM', 'win32' );
}


// Start PHP session; create a new session if this is the first time visit
session_start();
if( PHP_VERSION < '4.1' ){
    if( !session_is_registered('session')){
        session_register('session');
    }
    
} else {
    if( !isset( $_SESSION['session'] )){
        $_SESSION['session'] = array();
    }
    $session =& $_SESSION['session'];
}





//---------------------------------------------------------------------------
// NAME:    GetSessionValue
// DESC:    Return a saved session variable
// PARAM:   name [in]
//            Name of the saved variable
//          defaultValue [in]
//            Return value if requested variable does not exist
// RETURN:  Value of the requested session variable
//---------------------------------------------------------------------------
function GetSessionValue( $name, $defaultValue='' ){
  global $session;
  return GetHashValue( $session, $name, $defaultValue );
}  



//---------------------------------------------------------------------------
// NAME:    SaveSessionValue
// DESC:    Save a session variable, so it can be retrieved later
// PARAM:   name [in]
//            Variable name of the value to be saved
//          value [in]
//            value to be saved
//---------------------------------------------------------------------------
function SaveSessionValue( $name, $value ){
    global $session;
    $session[$name] = $value;
}
  


 
    
    
//---------------------------------------------------------------------------
// NAME:  GetHashValue
// DESC:  Return the value of a Hash.  This function is useful to avoid
//        PHP displays error message if the key does not exist in the hash.
// PARAM: hashVar [in]
//          variable that stores the hash
//        hashKey [in]
//          name of the key
//        defaultValue [in]
//          return value if the key is not found in the hash
// RETURN: corresponding Value of the hash key
//---------------------------------------------------------------------------
function GetHashValue( &$hashVar, $hashKey, $defaultValue='' ){
  if( isset( $hashVar[$hashKey] ))
    return $hashVar[$hashKey];
  else
    return $defaultValue;
}


//---------------------------------------------------------------------------
// NAME:    GetFormValue
// DESC:    Extract form input value from current HTML request. 
//          If it is also not found, 'defaultValue' will be returned.
// PARAMS:  controlName [in]
//              Name of the form control whose value to be retrieved
//          defaultValue [in]
//              Value to be returned if the requested form control does not exist
// Return:  value of the requested form control
//---------------------------------------------------------------------------
function GetFormValue($controlName, $defaultValue='' ){
    global $HTTP_GET_VARS;
    global $HTTP_POST_VARS;
  
    do {
        // Use GET value if available
        if( isset( $HTTP_GET_VARS[$controlName] )){
            $value = $HTTP_GET_VARS[$controlName];
            break;
        }
        
        // use POST value if available
        if( isset( $HTTP_POST_VARS[$controlName] )){
            $value = $HTTP_POST_VARS[$controlName];
            break;
        }            
            
        return $defaultValue;

    } while( false );
  
    // Convert \" to " and \' to '
    if( get_magic_quotes_gpc() )
        SmartStripSlashes( $value );

    return $value;
}


   


//---------------------------------------------------------------------------
// NAME:    SmartStripSlashes
// DESC:    Remove escapeing backslashes such as converting \" to "
// PARAMS:  value [in]
//              String to be operated.
//---------------------------------------------------------------------------
function SmartStripSlashes( &$value ){
    if( is_scalar( $value ) && !is_bool( $value )){
        $value = stripslashes( $value );
        return;
    }
} 
 
 

//---------------------------------------------------------------------------
// NAME:    FormatShortDuration
// DESC:    Give a duration in short format
//              For example, 12:34 for 12 minutes and 34 seconds
// PARAMS:  seconds [in]
//              duration in seconds
//---------------------------------------------------------------------------
function FormatShortDuration( $seconds ){
    $minute = floor( $seconds/60 );
    $second = $seconds % 60;
    return sprintf( "%d:%02d", $minute, $second );
}
 


//---------------------------------------------------------------------------
// NAME:    FormatShortDate
// DESC:    Format a date
// PARAMS:  date [in]
//              time in Unix timestamp format
//---------------------------------------------------------------------------
function FormatShortDate( $date ){
    if( !$date || $date < 0 ){
         return "N/A";
    }
    
    return date( "M-d G:i", $date );
}



//---------------------------------------------------------------------------
// NAME:    FormatCellValue
// DESC:    Format content that will be placed inside a table.
//          Specifically, content be be HTML encoded and explicit space
//          (&nbsp;) will be replaced if the content only contains spaces 
//          or is empty    
// PARAMS:  value [in]
//              content to be put in a table's cell
//          blank [in]
//              value to be insert if the content is blank
//              or only contain spaces.
// RETURN:  formatted output
//---------------------------------------------------------------------------
function FormatCellValue( $value, $blank = "&nbsp;" ){
    if( trim($value) == '' ){
        return $blank;
    } else {
        return htmlspecialchars( $value );
    }
}





//---------------------------------------------------------------------------
// NAME:    DeleteDirectory
// DESC:    Delete a directory and the files/sub-directories inside it.
// PARAM:   dirPath [in]
//              Directory to be deleted.
//---------------------------------------------------------------------------
function DeleteDirectory( $dirPath ){
    // make sure we are not deleting all files in the partition!
    if( $dirPath === '' || $dirPath === NULL || $dirPath === FALSE || 
        $dirPath == "." || $dirPath == ".." )
    {
        return;
    }
    
    if( $hDir = @opendir( $dirPath )){
        // Delete all the files inside the directory
        while( false !== ($fileName = readdir($hDir))) {
            if( $fileName != "." && $fileName != ".." ){
                if( is_dir( "$dirPath/$fileName" )){
                    DeleteDirectory( "$dirPath/$fileName" );
                } else {
                    @unlink( "$dirPath/$fileName" );
                }
            }
        }
        closedir( $hDir );
        
        // Delete the folder
        @rmdir( $dirPath );
    }
}




//---------------------------------------------------------------------------
// NAME:    HtmlRadioBttn
// DESC:    Generate HTML code for radio button
// PARAM:   name [in]
//              Name of the radio-button group
//          value [in]
//              Value of the button
//          defaultValue [in]
//              Default value of the group
//          onClick [in]
//              javascript to be executed when users click the button
//          id [in]
//              ID of the button
// RETURN:  HTML code
//---------------------------------------------------------------------------
function HtmlRadioBttn( $name, $value, $defaultValue, $onClick = "", $id = "" ){
    $htmlName = htmlspecialchars( $name );
    $htmlValue = htmlspecialchars( $value );
    
    if( $id != "" ){
        $id = "id=\"$id\"";
    }
    if( $onClick <> "" ){
        $onClick = "onclick=\"$onClick\"";
    }
    $checked = ( $defaultValue == $value ? "checked" : "" );
    return "<input type='radio' name='$htmlName' value='$htmlValue' $onClick $checked $id>";
}


//---------------------------------------------------------------------------
// NAME:    BuildHtmlNav
// DESC:    Generate HTML code for Navigation Menu (the top yellow menu)
// PARAM:   activeTab [in]
//              ID of the menu item that is currently active
// RETURN:  HTML code
//---------------------------------------------------------------------------
function BuildHtmlNav( $activeTab = '' ){
    
    $output = "<table width='100%' border='0' cellpadding='0' cellspacing='0' class='mainNav'><tr><td nowrap rowspan='2' style='padding:0'><img src='' width='1'></td>";
    
    $thisTab = ($activeTab == 'intro') ? "class='activeTab'" : '';
    $output .= "<td nowrap $thisTab rowspan='2'><a $thisTab href='intro.php' title='Introduction of Homemeeting system Intergration Toolkit'>HIT<br>Overview</a></td>";

    $thisTab = ($activeTab == 'view') ? "class='activeTab'" : '';
    $output .= "<td nowrap $thisTab rowspan='2'><a $thisTab href='viewhistory.php' title='View all the past meetings'>View<br>History</a></td>";

    $output .= "<td nowrap colspan='4' style='border-bottom: 1px solid #7DA7D9;'>Generate JNJ file</td>" .
	           "<td nowrap colspan='3' style='border-bottom: 1px solid #7DA7D9;'>Utilities</td>" .
	           "<td nowrap rowspan='2' width='99%' style='background: url(images/hit.gif) right no-repeat;'>&nbsp;</td></tr><tr>";


    $thisTab = ($activeTab == 'new') ? "class='activeTab'" : '';
    $output .= "<td nowrap $thisTab ><a $thisTab href='newmeeting.php' title='Generate a JNJ file for creating a new meeting'>New</a></td>";

    $thisTab = ($activeTab == 'join') ? "class='activeTab'" : '';
    $output .= "<td nowrap $thisTab ><a $thisTab href='joinmeeting.php' title='Generate a JNJ file for joining an existing meeting'>Join</a></td>";

    $thisTab = ($activeTab == 'play') ? "class='activeTab'" : '';
    $output .= "<td nowrap $thisTab ><a $thisTab href='playrecording.php' title='Generate a JNJ file for playing a recording'>Playback</a></td>";

    $thisTab = ($activeTab == 'test') ? "class='activeTab'" : '';
    $output .= "<td nowrap $thisTab ><a $thisTab href='testjoinnet.php' title='Generate a JNJ file for testing video/audio devices and network connection'>Test</a></td>";

    $thisTab = ($activeTab == 'base64') ? "class='activeTab'" : '';
    $output .= "<td nowrap $thisTab ><a $thisTab href='base64.php' title='Perform a base64 encoding/decoding'>Base64</a></td>";

    $thisTab = ($activeTab == '3des') ? "class='activeTab'" : '';
    $output .= "<td nowrap $thisTab ><a $thisTab href='3des.php' title='Perform a Triple DES encryption/cecryption'>3DES</a></td>";

    $thisTab = ($activeTab == 'pke') ? "class='activeTab'" : '';
    $output .= "<td nowrap $thisTab ><a $thisTab href='pke.php' title='Perform a public-key encryption/decryption'>PKE</a></td>";

    $output .= "</tr></table>";
    
    return $output;
}

?>