<?php
    set_time_limit(0);
    require_once(dirname(__FILE__) . '/console_initialize.php');
    $keep = $_COOKIE['school_hash']; unset($_COOKIE['school_hash']);
    $sysConn->Execute("SET SESSION wait_timeout=28800");
    $ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
    $sysConn->Execute('use '.sysDBprefix.'10001');

    $QTI_which = 'exam';
    $now    = date('Y-m-d').' 23:59:59';
    $b_month = date('Y-m-d', strtotime('-1 month')).' 00:00:00';
    $arr_course = array();
    print('begin'.date("Y-m-d H:i:s"));
    //$RS_user = dbGetStMr('WM_qti_' . $QTI_which . '_result', '*','`content`!="" and (begin_time between "'.$b_year.'" and "'.$now.'")',ADODB_FETCH_ASSOC);
    //$RS_user = dbGetStMr('WM_qti_' . $QTI_which . '_result', '*','begin_time <="2014-12-31 23:59:59" and `content`!=""',ADODB_FETCH_ASSOC);
    $RS_user = dbGetStMr('WM_qti_' . $QTI_which . '_result', '*', 'begin_time <= "' . $b_month . '" and `content`!="" and status!="break" limit 20000', ADODB_FETCH_ASSOC);
    while(!$RS_user->EOF) {
        
        $username = $RS_user->fields['examinee'];
        $exam_id = $RS_user->fields['exam_id'];
        $time_id  = $RS_user->fields['time_id'];
        $content  = $RS_user->fields['content'];
        
        $sysConn->Execute('use '.sysDBprefix.'10001');
        $course_id = $sysConn->GetOne("select course_id from WM_qti_" . $QTI_which . "_test where exam_id={$exam_id}");
        $arr_course[$course_id] = $course_id;
        if ($dom = @domxml_open_mem($content)) {
            $xml_path = sprintf(sysDocumentRoot . '/base/10001/course/%08d/%s/C/%09u/%s/',
                                   $course_id,
                                   $QTI_which,
                                   $exam_id,
                                   $username);
            $file =     $time_id.'.xml';

            if (!is_dir($xml_path)) {
                exec("mkdir -p '$xml_path'");
            }

            $full_path = $xml_path.$file;
            $file = fopen($full_path,"w");
            fwrite($file,$dom->dump_mem());
            fclose($file);
            //exec("chown elearn.elearn -R '$xml_path'"); 
            if (file_exists($full_path) === TRUE) {
                $sysConn->Execute('use '.sysDBprefix.'10001');
                $where = sprintf('exam_id=%d and examinee="%s" and time_id=%d limit 1',
                        $exam_id, $username, $time_id);
                dbSet('WM_qti_' . $QTI_which . '_result', 'content=""', $where);
            }
        }
        
        $RS_user->MoveNext();
    }
    
    foreach ( $arr_course as $key => $value) {
        $qti_path = sprintf(sysDocumentRoot . '/base/10001/course/%08d/%s',
                                   $key,
                                   $QTI_which);
        exec("chown elearn.elearn -R '$qti_path'"); 
    }

?>            
