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
	require_once(sysDocumentRoot . '/lib/filter_spec_char.php');
	
	if (!isset($_POST['ticket'])) {	// 檢查 ticket 是否存在
	   die('Access denied.');
	}
	
	$ticket = md5(sysTicketSeed . $sysSession->username . $_POST['referer']);		// 產生 ticket
	if ($ticket != $_POST['ticket']) {	// 檢查 ticket
	   die('Fake ticket.');
	}
	
	$school_id = (!empty($_SERVER["HTTP_USER_AGENT"]))?$sysSession->school_id:'10001'; 
	
	$courseQuota = ''; // 課程的空間上限

	$sysConn->Execute('use ' . sysDBname);
    $rs = $sysConn->Execute("select courseQuota from WM_school where school_id = '10001'");
    
	if ($rs) while ($fields = $rs->FetchRow())
	{
        $courseQuota = $fields['courseQuota'];
	}
    /**
     * 建立課程相關資料
     */
    function _addCourseOther($WM_course_id) {
        global $sysConn,$school_id,$MSG;

        // 1.建立課程目錄
		$schCourse  = "/base/{$school_id}/course";
		@mkdir (sysDocumentRoot . $schCourse, 0755);
		$coursePath = "/base/{$school_id}/course/{$WM_course_id}";
		@mkdir (sysDocumentRoot . $coursePath, 0755);
		@mkdir (sysDocumentRoot . $coursePath . '/chat' , 0755);
		@mkdir (sysDocumentRoot . $coursePath . '/board', 0755);
		@mkdir (sysDocumentRoot . $coursePath . '/quint', 0755);
		@mkdir (sysDocumentRoot . $coursePath . '/content', 0755);

		// 2.建立第一個教材路徑
		_addTermPath($WM_course_id);

		// 3.建立課程討論板 及 課程公告板
		$discuss_id  = _addBoards($WM_course_id, $MSG['discuss']);
		$bulletin_id = _addBoards($WM_course_id, $MSG['bulletin']);

		// 4.儲存課程相關設定
		$sysConn->Execute("update WM_term_course set path='{$coursePath}', discuss={$discuss_id}, bulletin={$bulletin_id} where course_id={$WM_course_id}");

		// 5.新增預設討論室
		$rid   = uniqid('');
		$title = addslashes(serialize($MSG['sync_chat_room']));
		$sysConn->Execute("insert into WM_chat_setting (rid,owner,title,host,get_host,open_time,close_time,ip,port) 
		                   values ('{$rid}','{$WM_course_id}','{$title}','','N','0000-00-00 00:00:00','0000-00-00 00:00:00','',0)");

		// 6.新增的預設點名
		_addRollCall($WM_course_id);
		
		// 7.設定file owner
		// exec("chown {$GLOBALS['wm3_owner']}:{$GLOBALS['wm3_group']} -R " . sysDocumentRoot . $coursePath);
        // MIS#15316 by lubo 2010/2/22 下午 02:37:41
        @exec("chown elearn:elearn -R " . sysDocumentRoot . $coursePath);
    }

	/**
	 * 檢查課程編號是否在規定的範圍內
	 * @param integer $csid : 課程編號
	 * @return integer $csid : 檢查後的課程編號
	 *         假如不在範圍內則回傳 false
	 **/
	function _checkCourseID($csid) 
	{
		$csid = intval($csid);
		if (($csid <= 10000000) || ($csid >= 100000000)) 
		{
			return false;
		}
		return $csid;
	}

    /**
     * 建立第一個教材路徑
     */         
	function _addTermPath($course_id) {
	    global $sysConn,$school_id;   
	
		$course_id = _checkCourseID($course_id);
		
        $sysConn->Execute('use ' . sysDBprefix . $school_id);
		$serial = $sysConn->GetOne("select MAX(serial) AS serial from WM_term_path where course_id = {$course_id}");
        	
		if (empty($serial))
            $serial = 1;
        else        
            $serial++;

        $sysConn->Execute("insert into WM_term_path (course_id, serial, content) values ($course_id, $serial, '')");
	}

	/**
	 * 建立課程討論版/課程公告版
	 * @param int $cid 課程編號
	 * @param string $bname 討論版名稱
	 * @return int 討論版編號
	 */
	function _addBoards($cid, $bname)
	{
		global $sysConn, $school_id;
		
		$boardName = addslashes(serialize($bname));
		$bid       = 0;
		
		$sysConn->Execute("insert into WM_bbs_boards (bname, owner_id) values ('{$boardName}', $cid)");
		if ($sysConn->Affected_Rows() && ($bid = $sysConn->Insert_ID()))
		{
			// 建立討論板存放夾檔的目錄
			$coursePath ="/base/{$school_id}/course/{$cid}/board/{$bid}";
			@mkdir(sysDocumentRoot . $coursePath, 0755);
			
			// 加入 WM_term_subject
			$sysConn->Execute("insert into WM_term_subject (course_id,board_id) values ($cid, $bid)");
		}
		return $bid;
	}

	/**
	 * 加入預設的寄信點名
	 * @param int $cid 課程編號
	 * 備註 : 預塞big5內容即可
	 */
	function _addRollCall($cid)
	{
		global $MSG, $sysConn;
		
		$sysConn->Execute("insert into WM_roll_call (course_id, team_id, group_id, enable, role, mtType, mtFilter, mtOP, mtVal, frequence, freq_extra,begin_time, end_time, mail_subject, mail_content, mail_attach, mail_cc) values 
		                   ({$cid}, 0, 0, 'disable', 'student','lesson', 'off', 'greater', '7', 'week', 'Saturday', NULL, NULL, '{$MSG['roll_call_mail_subject_default1']['Big5']}', '{$MSG['roll_call_mail_content_default1']['Big5']}', '', 0)");
		
		$sysConn->Execute("insert into WM_roll_call (course_id, team_id, group_id, enable, role, mtType, mtFilter, mtOP, mtVal, frequence, freq_extra,begin_time, end_time, mail_subject, mail_content, mail_attach, mail_cc) values 
		                   ({$cid}, 0, 0, 'disable', 'student','exam', 'no', 'greater_equal', '1', 'week', 'Saturday', NULL, NULL, '{$MSG['roll_call_mail_subject_default2']['Big5']}', '{$MSG['roll_call_mail_content_default2']['Big5']}', '', 0)");
		
		$sysConn->Execute("insert into WM_roll_call (course_id, team_id, group_id, enable, role, mtType, mtFilter, mtOP, mtVal, frequence, freq_extra,begin_time, end_time, mail_subject, mail_content, mail_attach, mail_cc) values 
		                   ({$cid}, 0, 0, 'disable', 'student','homework', 'no', 'greater_equal', '1', 'week', 'Saturday', NULL, NULL, '{$MSG['roll_call_mail_subject_default3']['Big5']}', '{$MSG['roll_call_mail_content_default3']['Big5']}', '', 0)");
		
		$sysConn->Execute("insert into WM_roll_call (course_id, team_id, group_id, enable, role, mtType, mtFilter, mtOP, mtVal, frequence, freq_extra,begin_time, end_time, mail_subject, mail_content, mail_attach, mail_cc) values 
		                   ({$cid}, 0, 0, 'disable', 'student','questionnaire', 'no', 'greater_equal', '1', 'week', 'Saturday', NULL, NULL, '{$MSG['roll_call_mail_subject_default4']['Big5']}', '{$MSG['roll_call_mail_content_default4']['Big5']}', '', 0)");
	}
	
	if (!function_exists('json_encode')) {

        function json_encode($val)
        {
            $json = new Services_JSON();
            return $json->encode($val);
        }

        function json_decode($val)
        {
            $json = new Services_JSON();
            return $json->decode($val);
        }
    }

	switch($_POST['action']) {
		case "add":
			foreach (array('Big5','GB2312','en') as $val) {
			    $lang[$val] = Filter_Spec_char(stripslashes(trim($_POST['course_name'])));
			}
			$caption = addslashes(serialize($lang));
			
	        // 新增課程
		    $fields = 'caption, teacher, kind, status, quota_limit';
		    $sql = 'insert into WM_term_course (caption, teacher, kind, status, quota_limit) values ("'.$caption.'","'.$sysSession->realname.'","course",3,'.$courseQuota.')';
		    $sysConn->Execute('use ' . sysDBprefix . $school_id);
		    $sysConn->Execute($sql);
		    $child = $sysConn->Insert_ID();
		    
		    if ( !empty($child) ) {
		        // 建立課程相關資料
                _addCourseOther($child);
                
                
                 
                // 新增教師身分
                $sysConn->Execute('use ' . sysDBprefix . $school_id);
                $sysConn->Execute("insert into WM_review_sysidx (discren_id, flow_serial) values ('{$child}','1')");
                $sysConn->Execute("insert into WM_term_major (username,course_id,role,add_time) values ('{$sysSession->username}','{$child}'," . $sysRoles['teacher'] . ",NOW())");

                $data['code'] = 1;

		    } else {
		        $data['code'] = 0;
		    }

            $msg = json_encode($data);
	    break;
	    
		case "modify":
			foreach (array('Big5','GB2312','en') as $val) {
			    $lang[$val] = Filter_Spec_char(stripslashes(trim($_POST['course_name'])));
			}
			$caption = addslashes(serialize($lang));
			
			dbSet('WM_term_course',sprintf('caption="%s"', $caption ),"course_id={$_POST['cid']}");
			
			$isModified = $sysConn->Affected_Rows();
            if ($isModified) {
                $data['code'] = 1;
            } else {
            	$data['code'] = 0;
            }
            $msg = json_encode($data);
	    break;
	    
	    case "delete":

			dbSet('WM_term_course','status=9',"course_id={$_POST['cid']}");
			
			$isModified = $sysConn->Affected_Rows();
            if ($isModified) {
                $data['code'] = 1;
            } else {
            	$data['code'] = 0;
            }
            $msg = json_encode($data);
	    break;
	    
	    case "delete_student":
	    	
            $sysConn->Execute('use ' . sysDBprefix . $school_id);

			// 刪除測驗紀錄
			$sql = "SELECT exam_id FROM WM_qti_exam_test WHERE course_id={$_POST['course_id']}";
	        $exam_data = $sysConn->GetArray($sql);
	        if (count($exam_data)>0) {
	        	$temp = array();
			    foreach($exam_data as $v){
			        $temp[] .= $v['exam_id'];
			    }
			    $exam_id = implode(',', $temp);
			    
			    $sql = "insert IGNORE into WM_history_qti_exam_result select null,WM_qti_exam_result.* from WM_qti_exam_result where examinee='{$_POST['username']}' and exam_id in ({$exam_id})";
				$sysConn->Execute($sql);
				
				$sql = "delete from WM_qti_exam_result where examinee='{$_POST['username']}' and exam_id in ({$exam_id})";
				$sysConn->Execute($sql);
	        }
			
			// 刪除問卷紀錄
			$sql = "SELECT exam_id FROM WM_qti_questionnaire_test WHERE course_id={$_POST['course_id']}";
	        $exam_data = $sysConn->GetArray($sql);
	        if (count($exam_data)>0) {
		        $temp = array();
			    foreach($exam_data as $v){
			        $temp[] .= $v['exam_id'];
			    }
			    $exam_id = implode(',', $temp);
			    
			    $sql = "insert IGNORE into WM_history_qti_questionnaire_result select null,WM_qti_questionnaire_result.* from WM_qti_questionnaire_result where examinee='{$_POST['username']}' and exam_id in ({$exam_id})";
				$sysConn->Execute($sql);
				
				$sql = "delete from WM_qti_questionnaire_result where examinee='{$_POST['username']}' and exam_id in ({$exam_id})";
				$sysConn->Execute($sql);
	        }
			
			$sysConn->Execute("update WM_term_major set role=0 where course_id={$_POST['course_id']} and username='{$_POST['username']}'");
			
			$isModified = $sysConn->Affected_Rows();
            if ($isModified) {
                $data['code'] = 1;
            } else {
            	$data['code'] = 0;
            }
            $msg = json_encode($data);
	    break;
	    

	}
	
	if ($msg != '') {
        echo $msg;
    }
	

	

?>
