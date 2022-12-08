<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                         *
	 *		Creation  : 2003/03/21                                                            *
	 *		work for  : list all available exam(s)                                            *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
	 *		Identifier: $Id: exam_list.php,v 1.8 2010-11-05 09:36:10 lst Exp $
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lib/interface.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_learn.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/exam_lib.php');

	$assignmentsForGroup = array();
	$examinee_perm = array('homework' => 1700400200, 'exam' => 1600400200, 'questionnaire' => 1800300200, 'peer' => 1710400200);
	//ACL begin
    include_once(sysDocumentRoot . '/lib/group_assignment_lib.php');
    $assignmentsForGroup = getAssignmentsForGroup();

    $sysSession->cur_func = '1710400100';
    $sysSession->restore();
    if (!aclVerifyPermission(1710400100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }

	//ACL end

	// MIS#23642 若有不正常的作答紀錄則刪除 by Small 2012/01/04
	list($nullCount) = dbGetStSr('WM_qti_'.QTI_which.'_result','count(*)',"ISNULL(status) AND ISNULL(content) and `examinee`='{$sysSession->username}'",ADODB_FETCH_NUM);
	if($nullCount>0)
		dbDel('WM_qti_'.QTI_which.'_result',"ISNULL(status) AND ISNULL(content) and `examinee`='{$sysSession->username}'");

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;
	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	$announces = array('never'		=> $MSG['score_publish0'][$sysSession->lang],
	                   'now'		=> $MSG['score_publish4'][$sysSession->lang],
	                   'close_time'	=> $MSG['score_publish5'][$sysSession->lang]
					  );

	function genTicket($var, $times,$username=''){
		global $sysSession;
		return sprintf('%s+%s+%s', $var, $times, md5(sysTicketSeed . $var . $times . $username. $_COOKIE['idx']));
	}


	$exam_types = array($MSG['exam_type0'][$sysSession->lang],
					    $MSG['exam_type1'][$sysSession->lang],
					    $MSG['exam_type2'][$sysSession->lang],
					    $MSG['exam_type3'][$sysSession->lang],
					    $MSG['exam_type4'][$sysSession->lang],
					    $MSG['exam_type5'][$sysSession->lang],
					    $MSG['exam_type6'][$sysSession->lang],
					    $MSG['exam_type7'][$sysSession->lang],
					    $MSG['exam_type8'][$sysSession->lang],
					    $MSG['exam_type9'][$sysSession->lang]);

	showXHTML_head_B($MSG[QTI_which . '_title'][$sysSession->lang], '8');
	showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    showXHTML_CSS('include', "/theme/default/bootstrap/css/bootstrap.min.css");
    showXHTML_CSS('include', "/theme/default/learn_mooc/application.css");
    showXHTML_CSS('include', "/theme/default/learn_mooc/peer.css");

    showXHTML_script('include', '/lib/jquery/jquery.min.js');
    showXHTML_script('include', '/theme/default/sparkline/js/jquery.sparkline.min.js');
    showXHTML_script('include', "/theme/default/bootstrap/js/bootstrap-tooltip.js");
    showXHTML_script('include', '/learn/peer/exam_list.js');

	$is_exam = (QTI_which == 'exam') ? 'true' : 'false';

	$scr = <<< EOB
var examWin;
// setTimeout('reloadPage()', '60000');
function reloadPage()
{
	if (!examWin || examWin.closed)	// 測驗中不reload page
	{
		location.reload();
	}
}

function togo(id, blnTeacher){
	var isFirst = true;
	if (blnTeacher && !confirm("{$MSG['try2do_help'][$sysSession->lang]}")) return;
	id += blnTeacher ? '+1' : '+0'; // 是否是教師試做
	if ({$is_exam})
	{
		examWin = window.open('exam_start.php?' + id, '', 'top=0,left=0,width=' + (screen.availWidth-6) + ',height=' + (screen.availHeight-46) + ',toolbar=0,menubar=0,scrollbars=1,resizable=0,status=0');
		// examwin.resizeTo(screen.width, screen.height);
	}
	else
		location.replace('exam_pre_start.php?' + id);

	if (typeof examWin !== "undefined") {
		examWin.onunload = function () {
			if (isFirst) {
				isFirst = false;
				return;
			}
			window.focus();
			window.location.reload();
		};
	}
}

function viewResult(eid)
{
	window.open('view_result.php?' + eid, '', 'width=640, height=480, status=0, toolbar=0, menubar=0, resizable=1, scrollbars=1');
}

function viewExemplar(eid)
{
	window.open('exemplar_list.php?' + eid, '', 'width=640, height=480, status=0, toolbar=0, menubar=0, resizable=1, scrollbars=1');
}

function viewStat(eid)
{
	window.open('exam_statistics_result.php?' + eid, '', 'width=640, height=480, status=0, toolbar=0, menubar=0, resizable=1, scrollbars=1');
}

function view_homework(eid) {
	window.open('view_exemplar.php?' + eid + '+{$sysSession->username}+personal' , '', 'width=640, height=480, status=0, toolbar=0, menubar=0, resizable=1, scrollbars=1');
}

function homework_preview(eid)
{
	window.open('homework_preview.php?' + eid, '', 'width=800, height=480, status=0, toolbar=0, menubar=0, resizable=1, scrollbars=1');
}

$(function() {
    $('.sparkpie').sparkline('html', {type: 'pie', sliceColors: ['#f3800f', '#dadada'], offset: -90, width: '23px', height: '23px', disableHighlight: true, borderColor: '#000000',disableTooltips: true} );
    $('.exam-percent-tips,.exam-type-tips').tooltip('hide');
});

EOB;
	$now = time();
	// add by wing 判斷其身份為哪幾種
	$permit = false;
	list($roles) = dbGetStsr('WM_term_major','role',"course_id=$course_id and username='{$sysSession->username}'", ADODB_FETCH_NUM);

	if($roles & $sysRoles['student']) $permit=true;

	// 測驗、作業讓老師可以試做
	$isTeacher = $roles & ($sysRoles['teacher'] | $sysRoles['assistant'] | $sysRoles['instructor']);

	if (defined('QTI_env') && QTI_env == 'academic' && QTI_which == 'questionnaire')
		$permit = true;

	showXHTML_script('inline',$scr);
	showXHTML_head_E();
	showXHTML_body_B('class="body"');

        echo '<div style="min-width: 725px; margin: auto auto; padding-left: 2em; padding-right: 2em;">
                <ul class="bar" id="peer-page-title">
                    <li class="left">
                        <span>' . $MSG['homeworkandreport'][$sysSession->lang] . '</span>
                    </li>
                </ul>
                <div class="navbar-form"></div>
                <div class="box box-padding-t-1 box-padding-lr-3">';

        // 取同儕互評資料
        $RS = dbGetStMr('WM_qti_' . QTI_which . '_test',
                      'exam_id, title, type, publish, begin_time, close_time, count_type, percent, announce_type,
                       announce_time, assess, start_date, end_date, assess_type, peer_percent, self_percent,
                       teacher_percent, peer_times, assess_way, assess_relation',
                      "course_id = {$course_id} and publish = 'action' order by sort, exam_id desc", ADODB_FETCH_ASSOC);
        if ($sysConn->ErrorNo() > 0) {
            echo $sysConn->ErrorMsg();
        }

        // 計數
        $number = 0;
        if ($RS && $RS->RecordCount() >= 1) {

            while(!$RS->EOF){
                $p = aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable');
                $aclVerified = aclVerifyPermission($examinee_perm[QTI_which], $p, $course_id, $RS->fields['exam_id']);//WM2預設值 true代表有另外設定對象
                if ($aclVerified === 'WM2') $aclVerified = $permit;
                if ($aclVerified === true || $isTeacher >= 1) {
                    $number = $number + 1;

                    // 取是否為群組作業
                    $assignmentsForGroup = getAssignmentsForGroup(null, 'peer');
                    if (isset($assignmentsForGroup[$RS->fields['exam_id']])) {
                        $img = 'icGroup.png';
                        $imgTitle = $MSG['for group'][$sysSession->lang];
                    } else {
                        $img = 'icUser.png';
                        $imgTitle = $MSG['for personal'][$sysSession->lang];
                    }

                    // 取作業名稱
                    $tmpTitle = (strpos($RS->fields['title'], 'a:') === 0) ?
                             unserialize($RS->fields['title']):
                             array('Big5'		    => $RS->fields['title'],
                                    'GB2312'	    => $RS->fields['title'],
                                    'en'		    => $RS->fields['title'],
                                    'EUC-JP'	    => $RS->fields['title'],
                                    'user_define'	=> $RS->fields['title']
                             );

                    // 取繳交作業期間
                    $now = date('Y-m-d H:i:s');
                    if (($now >= $RS->fields['begin_time'] and $now <= $RS->fields['close_time']) or ($RS->fields['begin_time'] === null and $RS->fields['close_time'] === null) or
                    ($RS->fields['begin_time'] === null and $now <= $RS->fields['close_time']) or ($now >= $RS->fields['begin_time'] and $RS->fields['close_time'] === null)) {
                        $isPay = ' active';
                    } else {
                        $isPay = '';
                    }

                    $begin_time = substr($RS->fields['begin_time'], 0, 10);
                    $close_time = substr($RS->fields['close_time'], 0, 10);
                    if ($begin_time === '0000-00-00') {
                        $begin_time = $MSG['now'][$sysSession->lang];
                    }
                    if ($close_time === '9999-12-31') {
                        $close_time = $MSG['forever'][$sysSession->lang];
                    }
                    $payDate = $begin_time . ' ~ ' . $close_time;

                    // 取進入評分期間
                    if ((($now >= $RS->fields['start_date'] and $now <= $RS->fields['end_date']) or ($RS->fields['start_date'] === null and $RS->fields['end_date'] === null) or
                    ($RS->fields['start_date'] === null and $now <= $RS->fields['end_date']) or ($now >= $RS->fields['start_date'] and $RS->fields['end_date'] === null)) && ($RS->fields['peer_percent'] >= 1 || $RS->fields['self_percent'] >= 1)) {
                        $isRating = ' active';
                    } else {
                        $isRating = '';
                    }

                    $start_date = substr($RS->fields['start_date'], 0, 10);
                    $end_date   = substr($RS->fields['end_date'], 0, 10);
                    if ($start_date === '0000-00-00') {
                        $start_date = $MSG['now'][$sysSession->lang];
                    }
                    if ($end_date === '9999-12-31') {
                        $end_date = $MSG['forever'][$sysSession->lang];
                    }
                    $ratingDate = $start_date . ' ~ ' . $end_date;

                    // 取成績公告期間
                    $rsGradeList = dbGetStMr('WM_grade_list','publish_begin, publish_end',
                        'course_id = ' . sprintf('%08u', $sysSession->course_id) .
                        ' and property = ' . sprintf('%09u', $RS->fields['exam_id']));
                    if($rsGradeList) {
                        while(!$rsGradeList->EOF) {
                            $score_begin_time = $rsGradeList->fields['publish_begin'];
                            $score_close_time = $rsGradeList->fields['publish_end'];
                            $rsGradeList->MoveNext();
                        }
                        if (($now >= $score_begin_time and $now <= $score_close_time) or ($score_begin_time === null and $score_close_time === null) or
                        ($score_begin_time === null and $now <= $score_close_time) or ($now >= $score_begin_time and $score_close_time === null)) {
                            $isScore = ' active';
                        } else {
                            $isScore = '';
                        }
                        if ($score_begin_time === $score_close_time && $score_close_time === '0000-00-00 00:00:00') {
                            $scoreDate = $MSG['score_publish0'][$sysSession->lang];
                        } else {
                            if ($score_begin_time === '1970-01-01 00:00:00' || $score_begin_time === '0000-00-00 00:00:00') {
                                $score_begin_time = $MSG['now'][$sysSession->lang];
                            }
                            if ($score_close_time === '9999-12-31 00:00:00') {
                                $score_close_time = $MSG['forever'][$sysSession->lang];
                            }
                            $scoreDate = substr($score_begin_time, 0, 10) . ' ~ ' . substr($score_close_time, 0, 10);
                        }
                    }
                    $time_id = intval($RS->fields['max_time_id']);

                    // 取教師試作
                    if ($isTeacher) {
                        $isTry2Do = '<td class="pull-center active">
                                         <div onclick="togo(\'' . genTicket($RS->fields['exam_id'], $time_id + 1) . '\', true)">' . $MSG['try2do'][$sysSession->lang]. '</div>
                                     </td>';
                    } else {
                        $isTry2Do = '';
                    }

                    // 取繳交次數
                    if (isset($assignmentsForGroup[$RS->fields['exam_id']])) {	// 判斷群組作業是否已有人繳交
                        $times = isAlreadySubmittedAssignmentForGroup($RS->fields['exam_id'],
                            $sysSession->username,
                            $sysSession->course_id) ? 1 : 0;
                    } else {
                        list($times) = dbGetStSr('WM_qti_' . QTI_which . '_result',
                            'count(*)',
                            "exam_id = {$RS->fields['exam_id']} and examinee = '{$sysSession->username}' and status != 'break'", ADODB_FETCH_NUM);
                    }
                    $times = intval($times);

                    // 觀看上次作業
                    if ($times > 0 || isAlreadySubmittedAssignmentForGroup($RS->fields['exam_id'], $sysSession->username)) {
                        $isLookLast = '<td class="pull-center active">
                                           <div class="look-last" onclick="view_homework(\''.genTicket($RS->fields['exam_id'], 1, $sysSession->username).'\');">' . $MSG['look_last'][$sysSession->lang] . '</div>
                                       </td>';
                    } else {
                        $isLookLast = '<td class="pull-center">
                                           <div class="look-last">' . $MSG['look_last'][$sysSession->lang] . '</div>
                                       </td>';
                    }

                    // 取開放觀摩（有佳作 成績公告時間內 成績要公告 觀摩時間內）
                    // 判斷目前有無佳作
                    $publish_count = round($sysConn->GetOne('select count(exam_id) from WM_qti_peer_result where exam_id = ' . $RS->fields['exam_id']. ' and time_id = ' . ($time_id + 1) . ' and status = \'publish\''), 2);

                    if ($publish_count >= 1 &&
                        trim($isScore) === 'active' &&
                        $scoreDate !== $MSG['score_publish0'][$sysSession->lang] && (
                            ($RS->fields['announce_type'] === 'now' && $times >= 1) ||
                            ($RS->fields['announce_type'] === 'close_time' && $RS->fields['close_time'] <= $now) ||
                            ($RS->fields['announce_type'] === 'user_define' && (!($RS->fields['announce_time'] == '9999-12-31 00:00:00' || strtotime($RS->fields['announce_time']) > time()))))) {
                        $isLookBest = '<td class="pull-center active">
                                        <div class="look-best" onclick="viewExemplar(\'' . genTicket($RS->fields['exam_id'], 1) . '\');">' . $MSG['look_best'][$sysSession->lang] . '</div>
                                       </td>';
                    } else {
                        $isLookBest = '<td class="pull-center">
                                        <div>' . $MSG['look_best'][$sysSession->lang] . '</div>
                                       </td>';
                    }

                    // 取作業名稱
                    // 測驗關閉的條件
                    $isTimeout = checkExamWhetherTimeout($RS->fields, time(), $times);
                    if (!$isTimeout) {
                        $title = '<a href="javascript:;" onclick="homework_preview('.$RS->fields['exam_id'].');return false;">' . htmlspecialchars($tmpTitle[$sysSession->lang]) . '</a>';
                    } else {
                        $title = htmlspecialchars($tmpTitle[$sysSession->lang]);
                    }

                    // 繳交作業
                    if ($aclVerified && $isPay === ' active') {
                        $isPayClick = 'onclick="togo(\'' . genTicket($RS->fields['exam_id'], $time_id + 1) . '\');"';
                    } else {
                        $isPayClick = '';
                    }

                    $examinee1 = 'peer';
                    // 自評
                    $examinee2 = 'self';

                    // 進入評分
                    if ((int)$RS->fields['peer_percent'] >= 1 && (int)$RS->fields['self_percent'] >= 1) {

                        $enterRating = '<div class="level1 active">
                                            <div class="process-title">' . $MSG['rating'][$sysSession->lang] . '</div>
                                            <div class="process-period">' . $ratingDate . '</div>
                                        </div>';
                        // 互評份數
                        $peer_count = round($sysConn->GetOne('select count(score) from WM_qti_peer_result_score where exam_id = ' . $RS->fields['exam_id']. ' and time_id = ' . ($time_id + 1) . ' and creator = \'' . $sysSession->username . '\' and score_type = 0'), 2);
                        // 自評份數
                        $self_count = round($sysConn->GetOne('select count(score) from WM_qti_peer_result_score where exam_id = ' . $RS->fields['exam_id']. ' and time_id = ' . ($time_id + 1) . ' and examinee = \'' . $sysSession->username . '\' and score_type = 1'), 2);

                        // 互評按鈕顯示方式
                        if ((int)$peer_count === 0) {
                            $peerButton = $MSG['peer_assessment'][$sysSession->lang];
                        } else if ((int)$peer_count >= (int)$RS->fields['peer_times']) {
                            $peerButton = $MSG['rated'][$sysSession->lang] . ' <span class="strong">' . $peer_count . '</span> ' . $MSG['piece'][$sysSession->lang];
                        } else {
                            $peerButton = $MSG['rated'][$sysSession->lang] . ' <span class="strong">' . $peer_count . '</span> /' . $RS->fields['peer_times'] . $MSG['piece'][$sysSession->lang];
                        }

                        // 互評自評優先權 0無1先互評2先自評
                        switch($RS->fields['assess_relation']) {
                        case '0':
                            if ((int)$self_count === 1) {
                                $enterRating .= '<div class="level2">
                                                     <a href="exam_correct_content.php?' . md5(sysTicketSeed . $course_id . $RS->fields['exam_id']. $examinee1 . ($time_id + 1)) . '+' . $RS->fields['exam_id'] . '+' . $examinee1 . '+' . ($time_id + 1) . '" target="_self" class="btn btn-primary peer span2 btn-orange">' . $peerButton . '</a>
                                                     <button type="button" class="btn btn-primary self span2 btn-orange disabled">' . $MSG['self_assessment'][$sysSession->lang] . '</button>
                                                 </div>';
                            } else {
                                $enterRating .= '<div class="level2">
                                                     <a href="exam_correct_content.php?' . md5(sysTicketSeed . $course_id . $RS->fields['exam_id']. $examinee1 . ($time_id + 1)) . '+' . $RS->fields['exam_id'] . '+' . $examinee1 . '+' . ($time_id + 1) . '" target="_self" class="btn btn-primary peer span2 btn-orange">' . $peerButton . '</a>
                                                     <a href="exam_correct_content.php?' . md5(sysTicketSeed . $course_id . $RS->fields['exam_id']. $examinee2 . ($time_id + 1)) . '+' . $RS->fields['exam_id'] . '+' . $examinee2 . '+' . ($time_id + 1) . '" target="_self" class="btn btn-primary self span2 btn-orange">' . $MSG['self_assessment'][$sysSession->lang] . '</a>
                                                 </div>';
                            }
                            break;

                        case '1':
                            if ((int)$peer_count >= (int)$RS->fields['peer_times'] && (int)$self_count === 0) {
                                $enterRating .= '<div class="level2">
                                                     <a href="exam_correct_content.php?' . md5(sysTicketSeed . $course_id . $RS->fields['exam_id']. $examinee1 . ($time_id + 1)) . '+' . $RS->fields['exam_id'] . '+' . $examinee1 . '+' . ($time_id + 1) . '" target="_self" class="btn btn-primary peer span2 btn-orange">' . $peerButton . '</button></a>
                                                     <a href="exam_correct_content.php?' . md5(sysTicketSeed . $course_id . $RS->fields['exam_id']. $examinee2 . ($time_id + 1)) . '+' . $RS->fields['exam_id'] . '+' . $examinee2 . '+' . ($time_id + 1) . '" target="_self" class="btn btn-primary self span2 btn-orange">' . $MSG['self_assessment'][$sysSession->lang] . '</button></a>
                                                 </div>';
                            } else if ((int)$peer_count >= (int)$RS->fields['peer_times'] && (int)$self_count === 1) {
                                $enterRating .= '<div class="level2">
                                                     <a href="exam_correct_content.php?' . md5(sysTicketSeed . $course_id . $RS->fields['exam_id']. $examinee1 . ($time_id + 1)) . '+' . $RS->fields['exam_id'] . '+' . $examinee1 . '+' . ($time_id + 1) . '" target="_self" class="btn btn-primary peer span2 btn-orange">' . $peerButton . '</button></a>
                                                     <button type="button" class="btn btn-primary self span2 btn-orange disabled">' . $MSG['self_assessment'][$sysSession->lang] . '</button>
                                                 </div>';
                            } else {
                                $enterRating .= '<div class="level2">
                                                     <a href="exam_correct_content.php?' . md5(sysTicketSeed . $course_id . $RS->fields['exam_id']. $examinee1 . ($time_id + 1)) . '+' . $RS->fields['exam_id'] . '+' . $examinee1 . '+' . ($time_id + 1) . '" target="_self" class="btn btn-primary peer span2 btn-orange">' . $peerButton . '</button></a>
                                                     <button type="button" class="btn btn-primary self span2 btn-orange disabled">' . $MSG['self_assessment'][$sysSession->lang] . '</button>
                                                 </div>';
                            }
                            break;

                        case '2':
                            if ((int)$self_count === 1) {
                                $enterRating .= '<div class="level2">
                                                     <a href="exam_correct_content.php?' . md5(sysTicketSeed . $course_id . $RS->fields['exam_id']. $examinee1 . ($time_id + 1)) . '+' . $RS->fields['exam_id'] . '+' . $examinee1 . '+' . ($time_id + 1) . '" target="_self" class="btn btn-primary peer span2 btn-orange">' . $peerButton . '</button></a>
                                                     <button type="button" class="btn btn-primary self span2 btn-orange disabled">' . $MSG['self_assessment'][$sysSession->lang] . '</button>
                                                 </div>';
                            } else {
                                $enterRating .= '<div class="level2">
                                                     <button type="button" class="btn btn-primary peer span2 btn-orange disabled">' . $peerButton . '</button>
                                                     <a href="exam_correct_content.php?' . md5(sysTicketSeed . $course_id . $RS->fields['exam_id']. $examinee2 . ($time_id + 1)) . '+' . $RS->fields['exam_id'] . '+' . $examinee2 . '+' . ($time_id + 1) . '" target="_self" class="btn btn-primary self span2 btn-orange">' . $MSG['self_assessment'][$sysSession->lang] . '</button></a>
                                                 </div>';
                            }
                            break;
                        }
                    // 老師評
                    } elseif ((int)$RS->fields['peer_percent'] === 0 && (int)$RS->fields['self_percent'] === 0) {
                        $enterRating = '<div class="active">
                                            <div class="process-title">' . $MSG['without_rating'][$sysSession->lang] . '</div>
                                        </div>';
                    // 互評
                    } elseif ((int)$RS->fields['peer_percent'] >= 1 && (int)$RS->fields['self_percent'] === 0) {

                        if ($isRating === '') {
                            $enterRating = '<div class="active">
                                                <div class="process-title">' . $MSG['btn_enter'][$sysSession->lang] . $MSG['peer_assessment'][$sysSession->lang] . '</div>
                                                <div class="process-period">' . $ratingDate . '</div>
                                            </div>';
                        } else {

                            // 互評份數
                            $peer_count = round($sysConn->GetOne('select count(score) from WM_qti_peer_result_score where exam_id = ' . $RS->fields['exam_id']. ' and time_id = ' . ($time_id + 1) . ' and creator = \'' . $sysSession->username . '\' and score_type = 0'), 2);

                            // 互評按鈕顯示方式
                            if ((int)$peer_count === 0) {
                                $peerButton = $MSG['peer_assessment'][$sysSession->lang];
                            } else if ((int)$peer_count >= (int)$RS->fields['peer_times']) {
                                $peerButton = $MSG['rated'][$sysSession->lang] . ' <span class="strong">' . $peer_count . '</span> ' . $MSG['piece'][$sysSession->lang];
                            } else {
                                $peerButton = $MSG['rated'][$sysSession->lang] . ' <span class="strong">' . $peer_count . '</span> /' . $RS->fields['peer_times'] . $MSG['piece'][$sysSession->lang];
                            }

                            $enterRating =  '<div class="level1 active">
                                                 <div class="process-title">' . $MSG['rating'][$sysSession->lang] . '</div>
                                                 <div class="process-period">' . $ratingDate . '</div>
                                             </div>
                                             <div class="level2">
                                                 <a href="exam_correct_content.php?' . md5(sysTicketSeed . $course_id . $RS->fields['exam_id']. $examinee1 . ($time_id + 1)) . '+' . $RS->fields['exam_id'] . '+' . $examinee1 . '+' . ($time_id + 1) . '" target="_self" class="btn btn-primary peer btn-orange">' . $peerButton . '</a>
                                             </div>';
                        }
                    // 自評
                    } elseif ((int)$RS->fields['peer_percent'] === 0 && (int)$RS->fields['self_percent'] >= 1) {

                        // 自評份數
                        $self_count = round($sysConn->GetOne('select count(score) from WM_qti_peer_result_score where exam_id = ' . $RS->fields['exam_id'] . ' and examinee = \'' . $sysSession->username . '\' and score_type = 1'), 2);

                        if ((int)$self_count === 1 || $isRating === '') {
                            $enterRating = '<div class="active">
                                                <div class="process-title">' . $MSG['self_assessment_finished'][$sysSession->lang] . '</div>
                                                <div class="process-period">' . $ratingDate . '</div>
                                            </div>';
                        } else {
                            $enterRating = '<div class="active" onClick="javascript:location.href=\'exam_correct_content.php?' . md5(sysTicketSeed . $course_id . $RS->fields['exam_id']. $examinee2 . ($time_id + 1)) . '+' . $RS->fields['exam_id'] . '+' . $examinee2 . '+' . ($time_id + 1) . '\'">
                                                <div class="process-title">' . $MSG['btn_enter'][$sysSession->lang] . $MSG['self_assessment'][$sysSession->lang] . '</div>
                                                <div class="process-period">' . $ratingDate . '</div>
                                            </div>';
                        }
                    }

                    // 查看結果
                    if ($isScore === ' active') {
                        $isScoreClick = 'onClick="javascript:location.href=\'look_result.php?' . md5(sysTicketSeed . $course_id . $RS->fields['exam_id']. $examinee2 . ($time_id + 1)) . '+' . $RS->fields['exam_id'] . '+' . $examinee2 . '+' . ($time_id + 1) . '\'"';
                    } else {
                        $isScoreClick = '';
                    }

                    echo '<ul class="bar">
                              <li class="left">
                                  <img src="/theme/default/learn_mooc/' . $img . '" width="28" height="28" class="exam-type-tips" data-toggle="tooltip" title="' . $MSG['assignment_type'][$sysSession->lang] . ': ' . $imgTitle . '"/>
                                  <span class="sparkpie exam-percent-tips" data-toggle="tooltip" title="' . $MSG['homeworkpercent'][$sysSession->lang] . ': ' . $RS->fields['percent']. '%">' . $RS->fields['percent']. ',' . (100 - $RS->fields['percent']) . '</span>
                                  <div class="title">' . $title . '</div>
                              </li>
                              <li class="right">
                                  <table class="table table-bordered table-striped peer-func-list">
                                      <tbody>
                                          <tr data-id="' . $RS->fields['exam_id'] . '">' .
                                              $isTry2Do .
                                              $isLookLast .
                                              $isLookBest .
                                         '</tr>
                                      </tbody>
                                  </table>
                              </li>
                          </ul>
                          <table class="table table-bordered table-striped mooc-process">
                              <tbody>
                                  <tr>
                                      <td class="lcms-table-td-text_gray pull-center part-trisection pay' . $isPay. '" ' . $isPayClick . '>
                                          <div class="process-title">' . $MSG['rd_student_homework'][$sysSession->lang] . '</div>
                                          <div class="process-period">' . $payDate . '</div>
                                      </td>
                                      <td class="lcms-table-td-text_gray pull-center part-trisection rating' . $isRating. '">' .
                                            $enterRating .
                                         '</div>
                                      </td>
                                      <td class="lcms-table-td-text_gray pull-center score' . $isScore. '" ' . $isScoreClick . '>
                                          <div class="process-title">' . $MSG['table_title9'][$sysSession->lang] . '</div>
                                          <div class="process-period">' . $scoreDate . '</div>
                                      </td>
                                  </tr>
                              </tbody>
                          </table>';
                }
                $RS->MoveNext();
            }
        }

        if ($number === 0) {
            echo '<div>' . $MSG['no_jobs'][$sysSession->lang] . '</div>
                  <div style="height: 1em;">&nbsp;</div>';
        }

        echo '  </div>
              </div>';

        echo '<div class="form-footer-space"></div>';

        // 事件用表單
        echo '<form id="form1" name="form1" accept-charset="UTF-8" lang="zh-tw" style="display:inline" action="" method="POST">
                <input type="hidden" name="ticket" value="' . $ticket . '">
                <input type="hidden" name="referer" value="' . $_SERVER['QUERY_STRING'] . '">
                <input type="hidden" name="lists" class="lists" value="">
              </form>';

	showXHTML_body_E();