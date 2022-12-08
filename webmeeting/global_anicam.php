<?
	require_once($DOCUMENT_ROOT . '/config/constant.ini.php');

	$School_ID = MO_School_ID; //小寫 joint 只接受小寫
	//Set MMS (Microsoft Media Server)
  	$MMS = true;

	//Set MMC (Joint Net Server)
	$MMC            = true;
	$MO_Server      = MO_Server;
	$MO_Server_port = MO_Server_port;

	//$MO_Server="mmsl.nsysu.edu.tw";
  	$SCUID = $School_ID.'_'.$cid;

	//Set OH_Status to
  	$OH_Set_to = 'MO';

	$MO_Set_Prg            = 'mo/mo_set.php';
	$MO_Join_student_Prg   = 'mo/join_student.php';
	//  $MO_Check_Prg      = 'mo/mo_check.php';
  	$MO_Set_Commit_Prg     = 'mo/oh_set_commit.php';
  	$MO_Recfile_Query_Prg  = 'mo/CU_recfile_query.php';
  	$MO_Recfile_Delete_Prg = 'mo/CU_recfile_delete.php';
  	$MO_Online_Chat_Prg    = 'mo/get_mcu_info.php';


	//Set OH (Offiec Hour DataBase  Server)
  	$OH_Set_Server      = $MO_Server;
  	$OH_Set_Server_port = $MO_Server_port;

  	// Set MMS (Microsoft Media Server)

  	$MMS_Server      = MMS_Server;
  	$MMS_Server_port = MMS_Server_port;

	$OH_Set_Check_Prg       = '/oh/oh_set_check.php';
	$OH_Set_Commit_Prg      = '/oh/oh_set_commit.php';

	$MMS_Check_Path         ='/cgi-bin/OH-SET/check_oh_ip.exe';
 	$MMS_OH_Set_Path        ='/cgi-bin/OH-SET/oh_set_commit.exe';
 	$MMS_OH_Set_Commit_Path ='/cgi-bin/OH-SET/oh_set_commit.exe';

function Server_Request_MMS_POST($CGI_Path,$Post_Str)
{

   global $MMS_Server,$MMS_Server_port;
   return  Server_Request_POST( $MMS_Server,$MMS_Server_port,$CGI_Path,$Post_Str);
}

function Server_Request_MO_POST($CGI_Path,$Post_Str)
{
   global $MO_Server,$MO_Server_port;
   return  Server_Request_POST($MO_Server,$MO_Server_port,$CGI_Path,$Post_Str);
}

function Server_Request_OH_Set_POST($CGI_Path,$Post_Str)
{
   global $OH_Set_Server, $OH_Set_Server_port;
   return  Server_Request_POST( $OH_Set_Server, $OH_Set_Server_port,$CGI_Path,$Post_Str);
}

function Server_Request_MMC_POST($CGI_Path,$Post_Str,$Post_port)
{
   global $OH_Set_Server, $OH_Set_Server_port;

   $OH_Set_Server=$MMS_Server;

   if (! empty($Post_port)){
		$OH_Set_Server_port = $Post_port;
   }
   return  Server_Request_POST($OH_Set_Server, $OH_Set_Server_port,$CGI_Path,$Post_Str);
}

function Server_Request_POST($Server,$Port,$CGI_Path,$Post_Str)
{
    $header = 'POST /' . $CGI_Path . " HTTP/1.0\r\n" .
              "Content-Type: application/x-www-form-urlencoded\r\n" .
              'Content-Length: ' . strlen($Post_Str) . "\r\n\r\n";
    //echo $header;
    $fp = @fsockopen ($Server, $Port, $errno, $errstr, 5);

    if (!$fp) { // ERROR
        // echo "$errstr ($errno)";
        return false;
        } //if
    else {  //put the data..
        fputs ($fp, $header . $Post_Str);
           $res='';
         //read the data returned...
           while (!feof($fp)) {
                 $res .= fgets ($fp, 128);
           } //while
        $result = substr(strstr($res, "\r\n\r\n"),4);
       // echo $result;
        fclose ($fp);
    }
    return $result;
}

?>
