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
	
	$course_id = intval($_POST['cid']);
	
	$ticket = md5(sysTicketSeed . $sysSession->username . $course_id . $_POST['referer']);		// 產生 ticket
	if ($ticket != $_POST['ticket']) {	// 檢查 ticket
	   die('Fake ticket.');
	}
	
	$school_id = (!empty($_SERVER["HTTP_USER_AGENT"]))?$sysSession->school_id:'10001'; 
	
	
	
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
	    
		case "modify":
			foreach (array('Big5','GB2312','en') as $val) {
			    $lang[$val] = Filter_Spec_char(stripslashes(trim($_POST['quiz_name'])));
			}
			$caption = addslashes(serialize($lang));
			
			if ($_POST['type']=='e') {
				$type = 'exam';
			} else {
				$type = 'questionnaire';
			}
			
			dbSet('`WM_qti_' . $type . '_test`',sprintf('title="%s"', $caption ),"exam_id={$_POST['eid']}");
			
			$isModified = $sysConn->Affected_Rows();
            if ($isModified) {
                $data['code'] = 1;
            } else {
            	$data['code'] = 0;
            }
            $msg = json_encode($data);
	    break;
	    
	    case "copy":
	    	$rs = $sysConn->GetArray('select * from WM_qti_' . $_POST['type'] . '_test where exam_id in (' . $_POST['eid'] . ')');
	    	
			if (is_array($rs) && count($rs))
			{
			    // 開始拷貝試卷
			    foreach ($rs as $record)
			    {

			        $record['exam_id'] = 'NULL';
			    
					$title = unserialize($record['title']);
					$lang['Big5']   	 = empty($title['Big5'])		? '' : ('COPY_' .trim($title['Big5']));
					$lang['GB2312'] 	 = empty($title['GB2312'])		? '' : ('COPY_' .trim($title['GB2312']));
					$lang['en']     	 = empty($title['en'])			? '' : ('COPY_' .trim($title['en']));
					$lang['EUC-JP'] 	 = empty($title['EUC-JP'])		? '' : ('COPY_' .trim($title['EUC-JP']));
					$lang['user_define'] = empty($title['user_define'])	? '' : ('COPY_' .trim($title['user_define']));
					$record['title'] = serialize($lang);

					$record['sort'] = 0;
		
		            
		            $record['publish'] = 'prepare';
		            $record['begin_time'] = '0000-00-00 00:00:00';
		            $record['close_time'] = '9999-12-31 00:00:00';
		            unset($record['create_time']);
	
					$sysConn->AutoExecute('WM_qti_' . $_POST['type'] . '_test', $record, 'INSERT');
					$instance = $sysConn->Insert_ID();
					
				}
			}
			
			if ($sysConn->ErrorNo() > 0) {
                $data['code'] = 0;
            } else {
            	if ($_POST['type'] == 'questionnaire') {
			        $old_lists = aclGetAclIdByInstance(1800300200, $course_id, $_POST['eid']);
			        $will_rm = implode(',', $old_lists);
			        if ($will_rm != ''){
				        $rs = $sysConn->GetArray('select * from WM_acl_list where acl_id in (' . $will_rm . ')');
				        if (is_array($rs) && count($rs))
						{
						    foreach ($rs as $record_acl)
						    {
                                $old_aclid = $record_acl['acl_id'];
						        $record_acl['acl_id'] = 'NULL';
						        $record_acl['instance'] = $instance;
								$sysConn->AutoExecute('WM_acl_list', $record_acl, 'INSERT');
								$acl_id = $sysConn->Insert_ID();
								$sql = 'insert into WM_acl_member (acl_id,member) select '.$acl_id.',member from WM_acl_member where acl_id='.$old_aclid;
								$sysConn->Execute($sql);
							}
						}
					}
			    }
            	$data['code'] = 1;
            }
			
            $msg = json_encode($data);
	    	
	    break;	
	    
	    case "delete":
            
			dbDel('WM_qti_' . $_POST['type'] . '_test', "exam_id={$_POST['eid']}");
			dbDel('WM_qti_' . $_POST['type'] . '_result', "exam_id={$_POST['eid']}");

			// 清除續考資料
			if (mysql_num_rows(mysql_query("SHOW TABLES LIKE 'WM_qti_" . $_POST['type'] . "_result_extra'")) === 1) {
			    dbDel('WM_qti_' . $_POST['type'] . '_result_extra', "exam_id={$_POST['eid']}");
			}
			
	        if ($sysConn->ErrorNo() > 0) {
                $data['code'] = 0;
            } else {
            	$data['code'] = 1;
            }
			
            $msg = json_encode($data);
	    break;
	    

	}
	
	if ($msg != '') {
        echo $msg;
    }
	

	

?>
