<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2004/09/08                                                            *
	 *		work for  : grade property modify                                                         *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *		identifier: $Id: grade_modify3.php,v 1.1 2010/02/24 02:40:27 saly Exp $
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lang/live_list.php');
	
	$sysSession->cur_func = '1300500100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){
	}
	
	if (!isset($_POST['ticket'])) {	// 檢查 ticket 是否存在
	   wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'auto', $_SERVER['PHP_SELF'], 'Access Denied!');
	   die('Access denied.');
	}
	$ticket = md5(sysTicketSeed . $sysSession->course_id . $_POST['referer']);		// 產生 ticket
	if ($ticket != $_POST['ticket']) {	// 檢查 ticket
	   wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 2, 'auto', $_SERVER['PHP_SELF'], 'Fake Ticket!');
	   die('Fake ticket.');
	}

	switch($_POST['action']) {
		case "modify":
			dbSet('APP_live_activity',sprintf('name="%s"', $_POST['name'] ),"id={$_POST['id']}");
	    break;
	    
	    case "create":
             list($xml) = dbGetStSr('WM_term_path', 'content', "course_id={$sysSession->course_id} order by serial desc", ADODB_FETCH_NUM);
             
             $liveRow = dbGetRow('APP_live_activity','*',sprintf("id='%d'",$_POST['id']));
             
             if (strpos($xml,str_replace('&', '&amp;',$liveRow['url'])) === false) {
             
	             $xml = preg_replace(array('/<resource( [^>]+)?>\s*(<file [^>]*>)*\s*<\/resource>/sU','/\bxsi:schemaLocation\s*=\s*"[^"]*"/'),
							array('<resource\1></resource>',''),
							mb_convert_encoding($xml, 'UTF-8', 'UTF-8'));
	             
	             
	             
	             $identifier = 'SCO_'.$sysSession->course_id.'_'.time().rand(100,999);
	             
	             $item = '<item identifier="I_'. $identifier .'" identifierref="'.$identifier.'">'.
		                                '<title>'. str_replace('&', '&amp;',$liveRow['name']) .'</title>'.
		                             '</item>';	
		            
		         $res = '<resource identifier="'. $identifier .'" adlcp:scormtype="asset" type="webcontent" href="'. str_replace('&', '&amp;',$liveRow['url']) .'">'.
		                                '<file href="'. str_replace('&', '&amp;',$liveRow['url']) .'"/>'.
		                            '</resource>';
		         
		         
		         if (empty($xml)){
			         $xml = sprintf('<manifest xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_rootv1p2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" version="1.3" xsi:schemaLocation="http://www.imsproject.org/xsd/imscp_rootv1p1p2 imscp_rootv1p1p2.xsd                            http://www.imsglobal.org/xsd/imsmd_rootv1p2p1 imsmd_rootv1p2p1.xsd                            http://www.adlnet.org/xsd/adlcp_rootv1p2 adlcp_rootv1p2.xsd"><organizations default="Course%d"><organization identifier="Course%d"><title>%s</title>%s</organization></organizations><resources>%s</resources></manifest>',
			            $sysSession->course_id, $sysSession->course_id, $sysSession->course_name,$item,$res
			        );
			     }else{
			     	if(!strpos($xml,"item identifier") && !strpos($xml,"item target=\"_blank\" identifier")) {
			    		$xml = sprintf('<manifest xmlns:adlcp="http://www.adlnet.org/xsd/adlcp_rootv1p2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" version="1.3" xsi:schemaLocation="http://www.imsproject.org/xsd/imscp_rootv1p1p2 imscp_rootv1p1p2.xsd                            http://www.imsglobal.org/xsd/imsmd_rootv1p2p1 imsmd_rootv1p2p1.xsd                            http://www.adlnet.org/xsd/adlcp_rootv1p2 adlcp_rootv1p2.xsd"><organizations default="Course%d"><organization identifier="Course%d"><title>%s</title>%s</organization></organizations><resources>%s</resources></manifest>',
			            $sysSession->course_id, $sysSession->course_id, $sysSession->course_name,$item,$res
			            );
			    	} else {
				    	$xml = preg_replace('/<\/organization>/', $item.'</organization>', $xml, 1);
			            $xml = preg_replace('/<\/resources>/', $res.'</resources>', $xml, 1);
			    	}
			            
			     }
			     //$xml = str_replace('&', '&amp;', $xml);
			     $GLOBALS['HTTP_RAW_POST_DATA'] = $sysConn->qstr($xml);
	
			     $sysConn->Execute("insert into WM_term_path (course_id,serial,content, username, update_time) select {$sysSession->course_id},if(max(serial) IS NULL,1,max(serial)+1),{$GLOBALS['HTTP_RAW_POST_DATA']},'{$sysSession->username}', now() from WM_term_path where course_id={$sysSession->course_id} limit 1");
			     echo <<< EOB
	<script>
	    alert('{$MSG['create_success'][$sysSession->lang]}');
		location.replace('live_list.php');
	</script>
EOB;
             } else {
		     echo <<< EOB
	<script>
	    alert('{$MSG['create_already'][$sysSession->lang]}');
		location.replace('live_list.php');
	</script>
EOB;
             }
	    break;
	    
	    case "delete":
			dbDel('APP_live_activity',"id={$_POST['id']}");
			echo <<< EOB
	<script>
		location.replace('live_list.php');
	</script>
EOB;
	    break;
	}
	

	

?>
