<?php
	/**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2003/09/23                                                                      *
	*		work for  : 附屬人員                                                                        *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       $Id: attach_people.php,v 1.1 2010/02/24 02:38:14 saly Exp $                                                                                          *
	**************************************************************************************************/
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/people_manager.php');
	require_once(sysDocumentRoot . '/lib/character_class.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/common.php');

	$sysSession->cur_func = '2400300100';
	$sysSession->restore();
	if (!aclVerifyPermission(2400300100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
	{
	}
/**
   查詢的 XML

< ?xml version="1.0" encoding="UTF-8" ? >
<manifest>
	<classes></classes>     <- 班級代碼
	<student></student>   <- 學生們的帳號
</manifest>
**/

    // echo  $GLOBALS['HTTP_RAW_POST_DATA'];
    // exit();

	header("Content-type: text/xml");
	echo '<?xml version="1.0" encoding="UTF-8" ?>' . "\n";

	$query = '';
	$counter = 0;
	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		if($dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			$root = $dom->document_element();

			$class_id = getNodeValue($dom, 'classes');	// 班級代碼
			$student  = getNodeValue($dom, 'student');	// 學生們的帳號

			// 原使用 explide 切割，改用 preg_split 來切割，同時也濾掉不合法的字元
			$class_id1 = preg_split('/[^\d]+/', $class_id, -1, PREG_SPLIT_NO_EMPTY);
			if (is_array($class_id1) && count($class_id1))
				$class_captions = WMdirector::getLocaleCaption($class_id1);
			else
				$class_captions = array();

			$c_num = count($class_id1);

			// 原使用 explide 切割，改用 preg_split 來切割，同時也濾掉不合法的字元
			$student1 = preg_split('/[^\w.-]+/', $student, -1, PREG_SPLIT_NO_EMPTY);

			$s_num = count($student1);

			$field = 'class_id,username,role';

			// for i loop (begin)
			for ($i = 0;$i < $c_num;$i++){

				$exists_ta = WMdirector::listMember($class_id1[$i]);
				if (!is_array($exists_ta)) $exists_ta = array();

				// for j loop (begin)
				for ($j = 0;$j < $s_num;$j++){
					// 判斷 此人是否在 某個班級 俱有 導師助教
					if (!in_array($student1[$j], $exists_ta)){
						// 判斷 此人是否有存在 於 某個班級
						$num = aclCheckRole($student1[$j], $sysRoles['student'], $class_id1[$i]);

						if ($num == 0){
							$value = $class_id1[$i] . ',' . "'" . $student1[$j] . "'," . $sysRoles['student'];
							$RS = dbNew('WM_class_member',$field,$value);

							// 記錄到 WM_log_manager
							$msg = $sysSession->username . ' add ' . $student1[$j]  . '(class_id=' . $class_id1[$i] . ') ' . ' role = ' . $sysRoles['student'];
							wmSysLog('2400300100',$sysSession->school_id,0,'0','manager',$_SERVER['SCRIPT_FILENAME'],$msg);

							$show_msg .= $student1[$j] . $MSG['title43'][$sysSession->lang] . '[' . $class_captions[$class_id1[$i]] . ']' . $MSG['title133'][$sysSession->lang] . "\r\n";

						}else{
							$show_msg .= $student1[$j] . $MSG['title43'][$sysSession->lang] . '[' . $class_captions[$class_id1[$i]] . ']' . $MSG['add_fail'][$sysSession->lang] . "\r\n";
						}
					}else{
						$show_msg .= $student1[$j] . $MSG['title43'][$sysSession->lang] . '[' . $class_captions[$class_id1[$i]] . ']' . $MSG['add_fail'][$sysSession->lang] . "\r\n";
					}

				}
				// for j loop (end)
			}
			// for i loop (end)
			echo "<manifest><result>", htmlspecialchars($show_msg, ENT_NOQUOTES, 'UTF-8'), "</result></manifest>";

		}
	}else {
		echo "<manifest />\n";
		exit();
	}

?>
