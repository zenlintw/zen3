<?php
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lang/irs.php');

    /*if ($sysSession->username=='guest') {
	    header('Location: /mooc/index.php');
	    die();
    }*/

    if (!isset($_POST['code'])) {
    	header('HTTP/1.1 403 Forbidden');
        exit;
    }

    $ticket = md5(sysTicketSeed . $sysSession->username . $_COOKIE['idx']);

    if ($_POST['ticket'] != $ticket) {
    	die('Fake ticket');
    }

    $course_id = base_convert(strtolower($_POST['code']),16,10);

    list($courseExists) = dbGetStSr('WM_term_course', 'count(*)', "course_id={$course_id}", ADODB_FETCH_NUM);

    if ($courseExists==0) {
        header('Location: /mooc/user/code.php?1');
	    die();
    }

    foreach(array('questionnaire', 'exam') as $type) {
        $sql = "select exam_id from `WM_qti_" . $type . "_test` where course_id={$course_id} and type=5 and publish='action' and begin_time!='0000-00-00 00:00:00' and close_time='9999-12-31 00:00:00'";
        $exam_id = $sysConn->GetOne($sql);
        if($exam_id!='') {
        	$qtype = $type;
        	$q_id = $exam_id;
        	break;
        }
    }

    if ($q_id == '') {
        header('Location: /mooc/user/code.php?2');
        die();
    }

    $goto = sysNewEncode(serialize(array('course_id'=>$course_id, 'type'=>$qtype, 'exam_id'=>$q_id)), 'wm5IRS');

    header('Location: /mooc/irs/check.php?action=start&goto='.$goto);
    die();






