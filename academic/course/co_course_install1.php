<?php
	/**
	 * 安裝課程
	 *
	 * @since   2005/05/17
	 * @author  ShenTing Lin
	 * @version $Id: course_install1.php,v 1.1 2010/02/24 02:38:19 saly Exp $
	 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
	 **/

	set_time_limit(0);
	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lib/lib_encrypt.php');
	require_once(sysDocumentRoot . '/lang/co_course_pack_install.php');
   
    define('CO_INSTALL_TIME_OUT', 14400);    //4hr timeout
    //$sysConn->debug=true;
    
    function getNewCourseId($schoolId) {
        global $sysConn;
        $lastCourseId = $sysConn->GetOne(sprintf("select max(course_id) from %s%s.WM_term_course",sysDBprefix,$schoolId)) +1;
        $sysConn->Execute(sprintf("insert into %s%s.WM_term_course (course_id, status) value (%s,9)", sysDBprefix,$schoolId,$lastCourseId));
        return $lastCourseId;
    }  
    
    function queueImport($srcCourseId, $srcSchoolId, $tarSchoolId, $tarCourseId, $importParams){
        global $sysConn,$sysSession; 
        
        $sysConn->Execute(sprintf('insert into %s.CO_course_install (src_school_id,tar_school_id,src_course_id,tar_course_id,import_params,state,reg_time,owner) value (%s,%s,%s,%s,"%s","%s",NOW(),"%s")',sysDBname,$srcSchoolId,$tarSchoolId,$srcCourseId, $tarCourseId, $importParams,"wait",$sysSession->username));
        
        return 'ADD_QUEUE';
    }
    
    function runDaemon(){
        $daemonFile = sysDocumentRoot.'/config/co_course_install_daemon.php';
        
        $cmd = "ps aux|grep '{$daemonFile}'|awk '{print \$11}'|grep php";
        $res = shell_exec($cmd);
        if(empty($res)){
            //run daemon
            $cmd = "nohup /usr/local/bin/php {$daemonFile} > /dev/null &";
            shell_exec($cmd);
        }
    }
    runDaemon();
    //來源課程
    $srcCourseId = $_POST['course_id'];
    //來源學校
    $srcSchoolId = $sysSession->school_id;
    //目的學校
    $tarSchoolId = $_POST['package_how'];
    //判斷是否已存在queue
    $existData = $sysConn->GetRow(sprintf('select id,reg_time from %s.CO_course_install where src_school_id=%s and tar_school_id=%s and src_course_id=%s and state in ("wait","running")',sysDBname, $srcSchoolId,$tarSchoolId,$srcCourseId));
    if(count($existData) > 0){
        //資料已超過4小時就砍掉此筆記錄
        if( strtotime($existData['reg_time']) < time()-CO_INSTALL_TIME_OUT ){
            $sysConn->Execute(sprintf('delete from %s.CO_course_install where id=%s ',sysDBname, $existData['id']));
        }else{
            //已存在
            $result = 'IMPORT_EXISTS';
        }
    }
    if($result != 'IMPORT_EXISTS'){
        //建立一個目的課程編號
        $tarCourseId = getNewCourseId($tarSchoolId);
        $importParams = implode(',', $_POST['course_elements']);
        $result = queueImport($srcCourseId, $srcSchoolId, $tarSchoolId, $tarCourseId, $importParams);
    }
    $showMsg = $MSG[$result][$sysSession->lang];
    
	showXHTML_head_B('');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
	showXHTML_head_E();
	showXHTML_body_B();
		$ary = array();
		$ary[] = array($MSG['msg_install_title'][$sysSession->lang], 'tabs1');
		echo '<div align="center">';
		showXHTML_tabFrame_B($ary, 1, 'actFm', '', 'action="course_property.php" method="post" enctype="multipart/form-data" style="display: inline;"');
			showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td('align="center"', $showMsg);
				showXHTML_tr_E();
				$col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
				showXHTML_tr_B($col);
					showXHTML_td_B('align="center"');
						
						showXHTML_input('button', 'btnReturn', $MSG['btn_return'][$sysSession->lang], '', 'onclick="window.location.replace(\'co_course_install.php\')" class="cssBtn"');
					showXHTML_td_E();
				showXHTML_tr_E();
			showXHTML_table_E();
		showXHTML_tabFrame_E();
		echo '</div>';
	showXHTML_body_E();
?>
