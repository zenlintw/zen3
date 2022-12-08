<?php
    /**************************************************************************************************
	*                                                                                                 *
	*		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                                 *
	*                                                                                                 *
	*		Programmer: Amm Lee                                                                         *
	*		Creation  : 2005/02/21                                                                       *
	*		work for  : 個人區→我的課程→全校課程→課程：可旁聽                                                                      *
	*		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                          *
	*       $Id: add_audit.php,v 1.2 2010/02/25 06:45:02 small Exp $                                                                                          *
	**************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/lang/audit_course.php');

/**
 * ========================================================================================
 *                                     主程式開始
 * ========================================================================================

查詢的 XML

< ?xml version="1.0" encoding="UTF-8" ? >
<manifest>
	<ticket></ticket>
	<classes_id></classes_id>     <- 查詢的 位在那個節點
</manifest>

**/

	// 這邊的判斷可能會因為 PHP 版本的更改而有所變動
	if (!isset($_REQUEST['HTTP_RAW_POST_DATA']) && isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
		header('Content-type: text/xml');
		echo '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";

		if (!$dom = domxml_open_mem($GLOBALS['HTTP_RAW_POST_DATA'])) {
			die('<manifest></manifest>');
		}

		// ticket
		$ticket = md5($sysSession->school_id . $sysSession->school_name . $sysSession->ticket . 'Add_Audit' . $sysSession->username);

		/* 修正 (begin) MIS#015279 by chiahua
		*  原本程式寫法取get_ticket有用intval()
		*  但是這樣會有問題，因為md5()出來的ticket會有英數字
		*  而intval()出來只有數字
		*  所以會有比對不符的情形發生
		*  因此get_ticket修正成直接取傳過來的值，不再做intval()轉換了
		*/
		//$get_ticket = intval(getNodeValue($dom, 'ticket'));
        $get_ticket = getNodeValue($dom, 'ticket');
		/* 修正 (end) */

        if ($ticket != $get_ticket){
        	echo '<manifest><state_msg>' . $MSG['illege_access'][$sysSession->lang] . '.</state_msg></manifest>';
		}
		// course_id
        $course_id = intval(getNodeValue($dom, 'course_id'));

		if (strlen($course_id) != 8){
        	echo '<manifest><state_msg>' . $MSG['illege_access'][$sysSession->lang] . '..</state_msg></manifest>';
		}

		// get course name
		list($cour_name, $a_limit) = dbGetStSr('WM_term_course','caption, a_limit','course_id=' . $course_id, ADODB_FETCH_NUM);
		$cour_lang = unserialize($cour_name);

		// 判斷是否超過旁聽生人數限制
		list($a_cnt) = dbGetStSr('WM_term_major', 'count(*)', 'course_id='. $course_id . ' and role & ' . $sysRoles['auditor'], ADODB_FETCH_NUM);
		if ($a_limit && $a_cnt >= $a_limit) {
			$state_msg = $MSG['add_fail4'][$sysSession->lang];
			$state_msg = str_replace('%COURSE_NAME%',$cour_lang[$sysSession->lang],$state_msg);
			$state_code = 1;
		}
		else {
			// 判斷是否為此門課的學生
			list($role) = dbGetStSr('WM_term_major','role','course_id=' . $course_id . ' and username="' . $sysSession->username . '"', ADODB_FETCH_NUM);
			if (empty($role)){
				$RS = dbNew('WM_term_major', 'username,course_id,role,add_time', "'" . $sysSession->username . "'," . $course_id . ',' . $sysRoles['auditor'] . ',NOW()');

				if ($sysConn->ErrorNo() == 0){
					$state_msg = $MSG['add_success'][$sysSession->lang];
					$state_msg = str_replace('%COURSE_NAME%',$cour_lang[$sysSession->lang],$state_msg);
					$state_code = 0; // success
				}else{
					$state_msg = $MSG['add_fail2'][$sysSession->lang];
					$state_code = 1;  // fail
				}
			}else{
				if ($role & $sysRoles['student']) {	// 已經是正式生
					$state_msg = $MSG['add_fail'][$sysSession->lang];
					$state_msg = str_replace('%COURSE_NAME%',$cour_lang[$sysSession->lang],$state_msg);
					$state_code = 1;  // fail
				}
				else if ($role & $sysRoles['auditor']) {	// 已經是旁聽生
					$state_msg = $MSG['add_fail3'][$sysSession->lang];
					$state_msg = str_replace('%COURSE_NAME%',$cour_lang[$sysSession->lang],$state_msg);
					$state_code = 1;
				}
				else if ($role & $sysRoles['teacher']) {	// 教師則可以加入旁聽生
					$mask = $sysRoles['all'] ^ $sysRoles['student'];
					dbSet('WM_term_major', "role=role & $mask | {$sysRoles['auditor']},add_time=NOW()", "username='{$sysSession->username}' and course_id={$course_id}");
					if ($sysConn->ErrorNo() == 0) {
						$state_msg = $MSG['add_success'][$sysSession->lang];
						$state_msg = str_replace('%COURSE_NAME%',$cour_lang[$sysSession->lang],$state_msg);
						$state_code = 0; // success
					}
					else {
						$state_msg = $MSG['add_fail2'][$sysSession->lang];
						$state_code = 1;  // fail
					}
				}
			}
		}
		$result = '<manifest><state_code>' . $state_code . '</state_code><state_msg>' .
				  htmlspecialchars($state_msg) . '</state_msg></manifest>';

		if (!empty($result)) {
			echo str_replace('<manifest>', "<manifest><ticket>{$ticket}</ticket>", $result);
		} else {
			echo "<manifest><ticket>{$ticket}</ticket></manifest>";
		}
		
        // if ($group_id > 1000000) end
	}
?>
