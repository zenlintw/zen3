<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2003/09/23                                                                      *
	*		work for  : 移除 人員                                                                        *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       $Id: remove_people.php,v 1.1 2010/02/24 02:38:15 saly Exp $                                                                                          *
	**************************************************************************************************/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/people_manager.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	
	$sysSession->cur_func='2400300200';
	$sysSession->restore();
	
	if (!aclVerifyPermission(2400300200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable'))){

	}

/**
   查詢的 XML

< ?xml version="1.0" encoding="UTF-8" ? >
<manifest>
	<classes></classes>     <- 班級代碼
	<student></student>   <- 學生們的帳號
</manifest>
**/

	header("Content-type: text/xml");
	echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";

	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if($dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			
			$class_id = intval(getNodeValue($dom, 'classes')); //// 班級代碼
			$student  = getNodeValue($dom, 'student');

            // 原使用 explide 切割，改用 preg_split 來切割，同時也濾掉不合法的字元
			$student1 = preg_split('/[^\w.-]+/', $student, -1, PREG_SPLIT_NO_EMPTY);

            $s_num = count($student1);

            for ($i = 0;$i < $s_num;$i++){
                //  WM_class_director
                dbDel('WM_class_director','class_id=' . $class_id . " and username='" . $student1[$i] . "' ");
                if ($sysConn->Affected_Rows()){
                    // 記錄到 WM_log_manager
                    $msg = $sysSession->username . ' delete ' . $student1[$i]  . '(class_id=' . $class_id . ') at WM_class_director ';
                    wmSysLog('2400300200',$sysSession->school_id,0,'0','manager',$_SERVER['SCRIPT_FILENAME'],$msg);
                }

                //  WM_class_member
                dbDel('WM_class_member','class_id=' . $class_id . " and username='" . $student1[$i] . "' ");
                if ($sysConn->Affected_Rows()){
                    // 記錄到 WM_log_manager
                    $msg = $sysSession->username . ' delete ' . $student1[$i]  . '(class_id=' . $class_id . ') at WM_class_member ';
                    wmSysLog('2400300200',$sysSession->school_id,0,'0','manager',$_SERVER['SCRIPT_FILENAME'],$msg);

                }
            }

		    echo "<manifest><result>", $MSG['title123'][$sysSession->lang], "</result></manifest>";
        }
   }else {
		die("</manifest>\n");
   }

?>
