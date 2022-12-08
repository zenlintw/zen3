<?php

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/mooc/common/common.php');
	require_once(sysDocumentRoot . '/lib/common.php');
	require_once(sysDocumentRoot . '/xmlapi/config.php');
	require_once(sysDocumentRoot . '/xmlapi/initialize.php');
	require_once(sysDocumentRoot . '/xmlapi/lib/common.php');
	require_once(sysDocumentRoot . '/xmlapi/lib/common-qti.php');
	require_once(sysDocumentRoot . '/xmlapi/lib/JsonUtility.php');
	require_once(sysDocumentRoot . '/xmlapi/lib/encryption.php');


    /*
    * $json->encode, $json->decode 宣告，以利後續使用
    */
    
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
    
    
    $course_id = intval($_POST['course_id']);
    $qti_type  = $_POST['qti_type'];
    $exam_id   = intval($_POST['exam_id']);
    $forGuest   = intval($_POST['forGuest']);
    $color = array('#815cb4', '#3aabdd', '#278f7f', '#ff7d13', '#e8483f', '#e3a729', '#42b6b5', '#81a41c', '#5e92fc', '#cf4aab');
    
    switch($_POST['action']) {
    	case "get_status":
		    
		    $data = array();
		    $role     = $sysRoles['student'] | $sysRoles['auditor'];
		    $data['major'] = $sysConn->GetOne('select count(*) from WM_term_major where course_id=' . $course_id .' and role&'.$role);
		    
		    $data['start'] = $sysConn->GetOne('select count(distinct(examinee)) from WM_qti_' . $qti_type . '_result where exam_id='.$exam_id);
		
		    $data['submit'] = $sysConn->GetOne('select count(*) from WM_qti_' . $qti_type . '_result where exam_id='.$exam_id.' and status in ("submit","revised")');
		    
		    if($data['start']!=0) {
		        $data['submit_rate'] = round($data['submit']/$data['start']*100);
		    } else {
                $data['submit_rate'] = '0';
		    }	
		    
		    if ($data['major']!=0) {
		        $data['start_rate'] = round($data['submit']/$data['major']*100);
		    } else {
		    	$data['start_rate'] = '0';
		    }
		
		    $msg = json_encode($data);
            break;

        case "start_active":
            $rtn = dbSet('WM_qti_' . $qti_type . '_test',"begin_time=NOW(),publish='action'",'exam_id=' . $exam_id);
            $isModified = $sysConn->Affected_Rows();
            if ($isModified) {
                $data['code'] = 1;
            } else {
            	$data['code'] = 0;
            }
            $msg = json_encode($data);
    		break;    
            
    	case "over_active":
            $rtn = dbSet('WM_qti_' . $qti_type . '_test',"close_time=NOW(),publish='close'",'exam_id=' . $exam_id);
            $isModified = $sysConn->Affected_Rows();
            if ($isModified) {
                $data['code'] = 1;
            } else {
            	$data['code'] = 0;
            }
            $msg = json_encode($data);
    		break;
    		
    	case "get_result":
			require_once(sysDocumentRoot . '/xmlapi/actions/get-questionnaire-result.class.php');
			define('API_QTI_which',$qti_type);
            define('QTI_env', 'teach');
            require_once(sysDocumentRoot . '/xmlapi/lib/qti.php');

			$oAction = new GetQuestionnaireResultAction;
			$oAction->_courseId = $course_id;
            $oAction->_qtiType = $qti_type;
            $oAction->_qtiId =  intval($exam_id);

            $result = $oAction->getQtiResult();
            $arr_major = array();
            $role     = $sysRoles['student'] | $sysRoles['auditor'];
            if ($forGuest) {
                $rs = $sysConn->Execute('select examinee,time_id,comment from WM_qti_' . $qti_type . '_result where exam_id='.$exam_id.' and status in ("submit","revised")');
                if ($rs) while ($fields = $rs->FetchRow())
                {
                    $tmp_username = $fields['examinee'].$fields['time_id'];
                    $arr_major[$tmp_username] = $fields['comment'];           
                }
            } else {
                $rs = $sysConn->Execute('select A.username,CONCAT(B.last_name,B.first_name) as name from WM_term_major A JOIN WM_user_account B ON A.username=B.username where A.course_id=' . $course_id .' and A.role&'.$role);
                if ($rs) while ($fields = $rs->FetchRow())
    	        {
                    $arr_major[$fields['username']] = $fields['name'];
                }
            }
            
            
            $data = array();
            foreach ($result['items'] as $key => $val) {
            	
            	$total = 0;
            	foreach ($val['optionals'] as $key1 => $val1) {
            		$data['stastic'][$val['item_id']][$key1] = $val1['select_amount'];
            		$total+=$val1['select_amount'];
            	}
            	$data['total'][$val['item_id']] = $total;
            	$data['correct'][$val['item_id']] = implode(',',$val['quizAnswer']);
            	
            	$i = 0;
            	foreach ($val['short_answer'] as $key2 => $val2) {
                    if ($i==10) $i = 0;
            		$data['ans'][$val['item_id']][$val2['username']][0] = stripslashes($val2['content']);
            		$data['ans'][$val['item_id']][$val2['username']][1] = $arr_major[$val2['username']];
            		$data['ans'][$val['item_id']][$val2['username']][2] = mb_substr($arr_major[$val2['username']],0,1,"utf-8");
            		$data['ans'][$val['item_id']][$val2['username']][3] = $color[$i];
            		$i++;
            	}
            	
            }

            $msg = json_encode($data);
    		break;  	

    	case "get_people":
    		$arr_major = array();
    		$arr_select = array();
    		$role     = $sysRoles['student'] | $sysRoles['auditor'];

            if ($forGuest) {
                $arr_submit = array();
                $rs = $sysConn->Execute('select examinee,comment from WM_qti_' . $qti_type . '_result where exam_id='.$exam_id.' and status in ("submit","revised")');
                if ($rs) while ($fields = $rs->FetchRow())
                {
                    $arr_submit[] = $fields['comment'];           
                }
                
                $no_submit = array();
                $html = '';
                $i = 0;
                foreach ($arr_submit as $val) {
                    if ($i==10) $i = 0;
                    $html .= '<div class="col-md-2 img-circle people" style="background-color:'.$color[$i].'">'.$val.'</div>';
                    $i++;
                }
            } else {
                $rs = $sysConn->Execute('select A.username,CONCAT(B.last_name,B.first_name) as name from WM_term_major A JOIN WM_user_account B ON A.username=B.username where A.course_id=' . $course_id .' and A.role&'.$role);
                if ($rs) while ($fields = $rs->FetchRow())
                {
                    $arr_major[$fields['username']] = $fields['name'];   
                    $arr_select[] = $fields['username'];           
                }
                $arr_submit = array();
                $rs = $sysConn->Execute('select examinee from WM_qti_' . $qti_type . '_result where exam_id='.$exam_id.' and status in ("submit","revised")');
                if ($rs) while ($fields = $rs->FetchRow())
                {
                    $arr_submit[] = $fields['examinee'];           
                }
                
                $no_submit = array_diff($arr_select,$arr_submit);
                $html = '';
                $i = 0;
                foreach ($arr_submit as $val) {
                    if ($i==10) $i = 0;
                    $html .= '<div class="col-md-2 img-circle people" style="background-color:'.$color[$i].'">'.$arr_major[$val].'</div>';
                    $i++;
                }
                
                foreach ($no_submit as $val) {
                    $html .= '<div class="col-md-2 img-circle people">'.$arr_major[$val].'</div>';
                }
            }

            $data['html'] = $html;
            $data['submit'] = count($arr_submit);
            $data['nosubmit'] = count($no_submit);
            
    		$msg = json_encode($data);
    		break;
        case "get_exam":

            $data['code'] = 0;
            foreach(array('questionnaire', 'exam') as $type) {
                $where = '';
                if ($qti_type==$type) $where = ' and exam_id!='.$exam_id;
                $sql = "select exam_id from `WM_qti_" . $type . "_test` where course_id={$course_id} and type=5 and publish='action' and begin_time!='0000-00-00 00:00:00' and close_time='9999-12-31 00:00:00'".$where;
                $now_exam_id = $sysConn->GetOne($sql);
                if($now_exam_id!='') {
                    $goto = sysNewEncode(serialize(array('course_id'=>$course_id, 'type'=>$type, 'exam_id'=>$now_exam_id)), 'wm5IRS');
                    $data['goto'] = $goto;
                    $data['code'] = 1;
                    break;
                }
            }

            $msg = json_encode($data);
            break;
    			
    }

    if ($msg != '') {
        echo $msg;
    }