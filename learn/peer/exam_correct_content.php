<?php
	/**************************************************************************************************
	 *                                                                                                *
	 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                               *
	 *                                                                                                *
	 *		Programmer: Wiseguy Liang                                                                 *
	 *		Creation  : 2003/04/11                                                                    *
	 *		work for  : 取得某考生對某次測驗的答案卷                                                       *
	 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                        *
	 *                                                                                                *
	 **************************************************************************************************/

	require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_learn.php');
	require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
	define('QTI_DISPLAY_ANSWER',   true);   // 定義常數；顯示標準答案
	define('QTI_DISPLAY_OUTCOME',  true);   // 定義常數；顯示批改結果及得分
	define('QTI_DISPLAY_RESPONSE', true);   // 定義常數；顯示學生答案
	require_once(sysDocumentRoot . '/teach/exam/QTI_transformer.php');
	require_once(sysDocumentRoot . '/lib/attach_link.php');
	require_once(sysDocumentRoot . '/lib/acl_api.php');
	require_once(sysDocumentRoot . '/lib/quota.php');
	include_once(sysDocumentRoot . '/lib/group_assignment_lib.php');
	require_once(sysDocumentRoot . '/teach/grade/grade_recal.php');
	require_once(sysDocumentRoot . '/mooc/common/common.php');

	//ACL begin
	$sysSession->cur_func='1710300100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

	}
	//ACL end
    showXHTML_head_B('Answer detail', '8');
    
    showXHTML_CSS('include', "/theme/default/bootstrap/css/bootstrap.min.css");
    if ($profile['isPhoneDevice']) {
	    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
	    echo '<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">';
	    echo '<link href="/theme/default/bootstrap336/css/bootstrap.min.css" rel="stylesheet" />';
	    echo '<link rel="stylesheet" href="/sys/tpl/vendor/font-awesome/css/font-awesome.css" />';
	    require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
	        $smarty->display('phone/learn/exam_style.tpl');
	}
    showXHTML_CSS('include', "/theme/default/learn_mooc/application.css");
    echo '<style>';
echo '

	@media (max-width: 767px) {
        .esn-container>.panel {
	        width: 100%;
	    }
	    
	    .container {
            min-width: 100%;
        }
    }
';
echo '</style>';

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	$curr_mode = $ADODB_FETCH_MODE;
	$ADODB_FETCH_MODE = ADODB_FETCH_NUM;

	if ($topDir == 'academic')
		$isQuotaExceed = getRemainQuota($sysSession->school_id) > 0 ? 0 : 1;
	else
		$isQuotaExceed = getRemainQuota($sysSession->course_id) > 0 ? 0 : 1;
	$ADODB_FETCH_MODE = $curr_mode;

	if (!isset($_SERVER['argv'][0])) {	// 檢查 ticket 是否存在
	   wmSysLog($sysSession->cur_func, $course_id , $_SERVER['argv'][1] , 1, 'auto', $_SERVER['PHP_SELF'], 'Access Denied!');
	   die('Access denied.');
	}
	$ticket_head = sysTicketSeed . $course_id . $_SERVER['argv'][1] . $_SERVER['argv'][2] . $_SERVER['argv'][3];

	if (md5($ticket_head) != $_SERVER['argv'][0]) die('Fake ticket.');			// 檢查 ticket

    $mode = $_SERVER['argv'][2];
    
    $msgNoPay = '';
    // 作業類別：群組或個人
    $assignmentsForGroup = getAssignmentsForGroup($course_id, 'peer');
    
    // 互評、自評模式時
    if ($mode === 'peer' || $mode === 'self') {
        // 先判斷有沒有評分資格：個人或群組要先繳交作業
        // 群組
        if (isset($assignmentsForGroup[$_SERVER['argv'][1]])) {
            // 自己的分組有沒有繳交
            $times = isAlreadySubmittedAssignmentForGroup($_SERVER['argv'][1], $sysSession->username, $course_id, 'peer'); 
            if ($times === false) {
                $msgNoPay = $MSG['group_no_pay_no_score'][$sysSession->lang];
            } else {
                // 取本作業的設定的分組
                $team_id = $sysConn->GetOne(sprintf("SELECT DISTINCT SUBSTRING( member, 2, 1 ) FROM WM_acl_list LEFT JOIN WM_acl_member ON WM_acl_member.acl_id = WM_acl_list.acl_id WHERE instance =  '%s'", $_SERVER['argv'][1]));

                // 取自己分組的成員
                $group_mates = getMyGroupMates($team_id, null, $course_id);
                if (is_array($group_mates) && count($group_mates) >= 1) {
                    $selfGroupCondition = "and WM_qti_peer_result.examinee not in ('" . implode("','" , $group_mates) . "')";
                } else {
                    $selfGroupCondition = '';
                }
            }
        } else {
            $peer_result = dbGetStSr('WM_qti_peer_result',
                'examinee',
                "exam_id = {$_SERVER['argv'][1]} and examinee = '{$sysSession->username}' and time_id = {$_SERVER['argv'][3]} and status != 'break' limit 0, 1",
                ADODB_FETCH_ASSOC);

            if (count($peer_result['examinee']) === 0) {
                $msgNoPay = $MSG['no_pay_no_score'][$sysSession->lang];
            }
        }
    }
    if (empty($msgNoPay) === false) {
        echo '<div class="container esn-container">
                  <div class="panel block-center">
                      <form class="well form-horizontal message-pull-center">
                          <fieldset>
                              <div class="input block-center">
                                  <div class="row">&nbsp;</div>
                                  <div class="control-group">
                                      <div class="message">
                                          <div id="message">
                                              <div>' . $msgNoPay . '</div>
                                          </div>
                                      </div>
                                  </div>
                                  <div class="row">&nbsp;</div>
                                  <div class="control-group">
                                      <div class="controls">
                                          <div class="lcms-left">
                                              <a href="/learn/homework/homework_list.php" class="btn btn-primary aNormal margin-right-10 btn-blue" id="btnForget">' . $MSG['back_to_list'][$sysSession->lang] . '</a>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </fieldset>
                      </form>
                  </div>
              </div>';
        die();
    }

    // 如果是互評模式，取應試者
    if ($mode === 'peer') {
        // 刪除不存在的SESSION
	    dbDel('WM_qti_peer_result_action', 'idx not in (select idx from WM_session)');

		// 判斷是否有評分進行中，如果有就繼續評那個人，而不隨機取新人來評分
        $exist_examinee = dbGetStSr('WM_qti_peer_result_action',
		                    'examinee',
		                    "exam_id = {$_SERVER['argv'][1]} and time_id = {$_SERVER['argv'][3]} and creator = '{$sysSession->username}' limit 0, 1",
						    ADODB_FETCH_ASSOC);

        if (count($exist_examinee['examinee']) === 1) {
            $_SERVER['argv'][2] = $exist_examinee['examinee'];
        } else {
            // 取0-3互評資料，由小到大

            // 取目前被LOCK名單
            $rsExistsExaminee = dbGetStMr('WM_qti_peer_result_action',
                'examinee',
                "exam_id = {$_SERVER['argv'][1]} and time_id = {$_SERVER['argv'][3]}",
                ADODB_FETCH_ASSOC);
            $lockIng = array();
            if ($rsExistsExaminee) {
                while(!$rsExistsExaminee->EOF) {
                    $lockIng[] = "'" . $rsExistsExaminee->fields['examinee'] . "'";

                    $rsExistsExaminee->MoveNext();
                }
            }

            if (count($lockIng) >= 1) {
                $lockIngCondition = 'and WM_qti_peer_result.examinee not in (' . implode(',' , $lockIng) . ')';
            } else {
                $lockIngCondition = '';
            }

            // 取被登入者評分過的名單
            $rsRatedExaminee = dbGetStMr('WM_qti_peer_result_score',
                'examinee',
                "exam_id = {$_SERVER['argv'][1]} and time_id = {$_SERVER['argv'][3]} and creator = '{$sysSession->username}' and score_type=0",
                ADODB_FETCH_ASSOC);
            $rated = array();
            $ratedNoApos = array();
            if ($rsRatedExaminee) {
                while(!$rsRatedExaminee->EOF) {
                    $rated[] = "'" . $rsRatedExaminee->fields['examinee'] . "'";
                    $ratedNoApos[] = $rsRatedExaminee->fields['examinee'];

                    $rsRatedExaminee->MoveNext();
                }
            }

            if (count($rated) >= 1) {
                // 群組，取所有被評分過的分組裡的所有成員
                if (isset($assignmentsForGroup[$_SERVER['argv'][1]])) {
                    $ratedGroupmates = array();
                    foreach ($ratedNoApos as $v) {
                        // 取分組的成員
                        $group_mates = getMyGroupMates($team_id, $v, $course_id);
                        if (is_array($group_mates) && count($group_mates) >= 1) {
                            $ratedGroupmates = array_merge($ratedGroupmates, $group_mates);
                        }
                    }
                    $ratedGroupmates = array_unique($ratedGroupmates);
                    foreach ($ratedGroupmates as $v) {
                        $ratedGroupmatesCondition .= sprintf("'%s',", $v);
                    }
                    if (is_array($ratedGroupmates) && count($ratedGroupmates) >= 1) {
                        $ratedGroupmatesCondition = substr($ratedGroupmatesCondition, 0, -1);
                    }
                    $ratedCondition = 'and WM_qti_peer_result.examinee not in (' . $ratedGroupmatesCondition . ')';
                } else {
                    $ratedCondition = 'and WM_qti_peer_result.examinee not in (' . implode(',' , $rated) . ')';
                }
            } else {
                $ratedCondition = '';
            }
            
            $RS = dbGetStMr('WM_qti_peer_result left join WM_qti_peer_result_score on  WM_qti_peer_result.exam_id = WM_qti_peer_result_score.exam_id and WM_qti_peer_result.examinee = WM_qti_peer_result_score.examinee and WM_qti_peer_result_score.score_type = 0',
                'WM_qti_peer_result.examinee, count(WM_qti_peer_result_score.score) cnt',
                "WM_qti_peer_result.exam_id = {$_SERVER['argv'][1]} and WM_qti_peer_result.examinee != '{$sysSession->username}' AND WM_qti_peer_result.content >='0' " . // !='' 效率較差
                $lockIngCondition . // 不包含已被LOCK者
                $ratedCondition . // 不包含已登入者被評分過者
                $selfGroupCondition . // 不包含自己分組的成員
                " group by WM_qti_peer_result.examinee
                order by count(WM_qti_peer_result_score.score) limit 0, 1000",
                ADODB_FETCH_ASSOC);
            $peer_rating_times = array();
            if ($RS) {
                while(!$RS->EOF) {
                    $peer_rating_times[$RS->fields['cnt']][] = $RS->fields['examinee'];

                    $RS->MoveNext();
                }
            }
            // 隨機取人
            foreach($peer_rating_times as $val) {
                srand((double) microtime() * 10000000);
                $rand_keys = array_rand($val, 1);
                $_SERVER['argv'][2] = $val[$rand_keys];

                // 有取到人，則跳出
                if ($_SERVER['argv'][2] !== '') {
                    break;
                }
            }
        }
    } else if ($mode === 'self') {
        $_SERVER['argv'][2] = $sysSession->username;
    }

	$keep = $ADODB_FETCH_MODE;
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	if (QTI_which == 'peer' && isAssignmentForGroup($_SERVER['argv'][1], $sysSession->course_id, 'peer'))
	{
            // 取本作業的設定的分組
            $team_id = $sysConn->GetOne(sprintf("SELECT DISTINCT SUBSTRING(member, 2, 1) FROM WM_acl_list LEFT JOIN WM_acl_member ON WM_acl_member.acl_id = WM_acl_list.acl_id WHERE function_id = '%s' and unit_id = '%s' and instance = '%s'", '1710400200', $sysSession->course_id, $_SERVER['argv'][1]));

            // 取子分組流水號
            $group_id = $sysConn->GetOne(sprintf("select group_id from WM_student_div where course_id = '%s' and team_id = '%s' and username = '%s'", $sysSession->course_id, $team_id, $_SERVER['argv'][2]));
            $sqls = 'SELECT status, score, comment_txt as comment, R.content, ref_url, assess, peer_percent, self_percent, teacher_percent, assess_way, total_score, R.operator, R.upd_time, R.examinee ' .
                                    'FROM WM_student_div AS D ' .
                                    'INNER JOIN WM_qti_peer_result AS R ON D.username = R.examinee ' .
                                    'INNER JOIN WM_qti_peer_test AS T ON D.course_id = T.course_id AND R.exam_id = T.exam_id ' .
                                    'WHERE D.course_id =' . $sysSession->course_id .
                                    ' AND D.group_id =' . $group_id .
                                    ' AND D.team_id =' . $team_id .
                                    ' AND R.exam_id =' . $_SERVER['argv'][1] .
                                    ' AND R.content >="0"';
	    $fields = $sysConn->GetRow($sqls);
	    $isGroupingAssignment = $_SERVER['argv'][4];
	    $_SERVER['argv'][2] = $fields['examinee'];
	}
	else
	{
		$fields = dbGetStSr('WM_qti_peer_result, WM_qti_peer_test',
		                    'status, score, comment_txt as comment, WM_qti_peer_result.content, ref_url, assess, peer_percent, self_percent, teacher_percent, assess_way, total_score, WM_qti_peer_result.operator, WM_qti_peer_result.upd_time',
		                    "WM_qti_peer_result.exam_id = WM_qti_peer_test.exam_id and WM_qti_peer_result.exam_id={$_SERVER['argv'][1]} and examinee='{$_SERVER['argv'][2]}' and time_id={$_SERVER['argv'][3]}",
						    ADODB_FETCH_ASSOC);
        $isGroupingAssignment = false;
	}

        // 如果是同儕評分，分數改取自 WM_qti_peer_result_score
        if ($mode === 'peer' || $mode === 'self') {
            $peerScore = dbGetStSr('WM_qti_peer_result_score',
                                'score, comment_txt as comment',
                                "exam_id = {$_SERVER['argv'][1]} and examinee = '{$_SERVER['argv'][2]}' and time_id = {$_SERVER['argv'][3]}
                                and creator = '{$sysSession->username}'",
                                ADODB_FETCH_ASSOC);
            $fields['score']   = $peerScore['score'];
            $fields['comment'] = $peerScore['comment'];
        }
    $ADODB_FETCH_MODE = $keep;

	$ticket = md5(sysTicketSeed . $_COOKIE['idx'] . $_SERVER['argv'][1] . $_SERVER['argv'][2] . $_SERVER['argv'][3]);

	if ($topDir == 'academic')
		$saved_path = sprintf(sysDocumentRoot . '/base/%05d/%s/A/%09u/%s/',
		  					 $sysSession->school_id,
		  					 QTI_which,
		  					 $_SERVER['argv'][1],
		  					 $_SERVER['argv'][2]);
	else
		$saved_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09u/%s/',
		  					 $sysSession->school_id,
		  					 $sysSession->course_id,
		  					 QTI_which,
		  					 $_SERVER['argv'][1],
		  					 $_SERVER['argv'][2]);

	$saved_uri = substr($saved_path, strlen(sysDocumentRoot));

	if (empty($fields['content']))
	{
	   wmSysLog($sysSession->cur_func, $course_id , $_SERVER['argv'][1] , 2, 'auto', $_SERVER['PHP_SELF'], 'Reading ' . QTI_which . ' content faliure.');
        echo '<div class="container esn-container">
                  <div class="panel block-center">
                      <form class="well form-horizontal message-pull-center">
                          <fieldset>
                              <div class="input block-center">
                                  <div class="row">&nbsp;</div>
                                  <div class="control-group">
                                      <div class="message">
                                          <div id="message">
                                              <div>' . $MSG['rating_finished'][$sysSession->lang] . '</div>
                                          </div>
                                      </div>
                                  </div>
                                  <div class="row">&nbsp;</div>
                                  <div class="control-group">
                                      <div class="controls">
                                          <div class="lcms-left">
                                              <a href="/learn/homework/homework_list.php" class="btn btn-primary aNormal margin-right-10 btn-blue" id="btnForget">' . $MSG['back_to_list'][$sysSession->lang] . '</a>
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </fieldset>
                      </form>
                  </div>
              </div>';
        die();
	}

    // 進行LOCK
    if ($mode === 'peer') {
		// 進行LOCK
        $lock = dbGetStSr('WM_qti_peer_result_action',
		                    'count(idx) cnt',
		                    "exam_id = {$_SERVER['argv'][1]} and examinee = '{$_SERVER['argv'][2]}' and time_id = {$_SERVER['argv'][3]} and creator = '{$sysSession->username}'",
						    ADODB_FETCH_ASSOC);

        if ($lock['cnt'] === '0') {
            dbNew('WM_qti_peer_result_action',
                'exam_id, examinee, time_id, idx, creator, create_time',
                sprintf('%d, "%s", %u, "%s", "%s", now()',
                    $_SERVER['argv'][1],
                    $_SERVER['argv'][2],
                    $_SERVER['argv'][3],
                    $_COOKIE['idx'],
                    $sysSession->username
                )
            );
        }
    }

	$fields['content'] = str_replace('<item xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd" xmlns:wm="http://www.sun.net.tw/WisdomMaster" ', '<item ', $fields['content']);
	$fields['content'] = str_replace(' xmlns="http://www.imsglobal.org/question/qtiv1p2/qtiasiitemncdtd/ims_qtiasiv1p2.dtd"', '', $fields['content']);
	if(!$dom = domxml_open_mem($fields['content'])) {
		wmSysLog($sysSession->cur_func, $course_id , $_SERVER['argv'][1] , 3, 'auto', $_SERVER['PHP_SELF'], 'Error while parsing the document.');
		die('Error while parsing the document.');
	}
	$ctx = xpath_new_context($dom);
	$root = $dom->document_element();

    //互評分數
    $rsScorePeer = dbGetStMr('WM_qti_peer_result_score',
                        'examinee, SUBSTRING((CONCAT(LPAD(time_id,6,\'0\'),score)),7) score, comment_txt as comment, creator, create_time',
                        'exam_id = ' . $_SERVER['argv'][1]. ' and examinee = \'' . $_SERVER['argv'][2]. '\'and score_type = 0',
                        ADODB_FETCH_ASSOC);
    $scorePeer = array();
    $score_peer = 0;
    if ($rsScorePeer) {
        while(!$rsScorePeer->EOF){
            $scorePeer[$rsScorePeer->fields['create_time'] . '-' . $rsScorePeer->fields['creator']]['create_time'] = $rsScorePeer->fields['create_time'];
            $scorePeer[$rsScorePeer->fields['create_time'] . '-' . $rsScorePeer->fields['creator']]['type'] = '';

            $user = getUserDetailData($rsScorePeer->fields['creator']);
            $scorePeer[$rsScorePeer->fields['create_time'] . '-' . $rsScorePeer->fields['creator']]['creator'] = $rsScorePeer->fields['creator'] . ' (' . $user['realname'] . ')';
            $scorePeer[$rsScorePeer->fields['create_time'] . '-' . $rsScorePeer->fields['creator']]['score'] = $rsScorePeer->fields['score'];
            $score_peer = $score_peer + $rsScorePeer->fields['score'];
            $scorePeer[$rsScorePeer->fields['create_time'] . '-' . $rsScorePeer->fields['creator']]['comment'] = $rsScorePeer->fields['comment'];

            $rsScorePeer->MoveNext();
        }

        if ($rsScorePeer->RecordCount() >= 1) {
            $score_peer = $score_peer / $rsScorePeer->RecordCount();
        } else {
            $score_peer = 0;
        }
    }

    // 自評分數
    $score_self = dbGetStSr('WM_qti_peer_result_score',
                        'examinee, SUBSTRING(MIN(CONCAT(LPAD(time_id,6,\'0\'),score)),7) score, comment_txt as comment, create_time',
                        'exam_id = ' . $_SERVER['argv'][1]. ' and examinee = \'' . $_SERVER['argv'][2]. '\'and score_type = 1',
                        ADODB_FETCH_ASSOC);

    // 取調整後分數
    $grade = dbGetStSr('WM_grade_item, WM_grade_list',
                        'WM_grade_item.score',
                        "WM_grade_item.username = '{$_SERVER['argv'][2]}' AND WM_grade_item.grade_id = WM_grade_list.grade_id AND WM_grade_list.course_id = '" . $course_id . "' AND WM_grade_list.property = '" . $_SERVER['argv'][1] . "'",
                        ADODB_FETCH_ASSOC);

	// 判斷是觀摩佳作或者是觀看個人作業結果
	$type = $_SERVER['argv'][4];

	if ($type == 'exemplar')
		$row = dbGetStSr('WM_qti_homework_result', 'examinee, score, comment_txt as comment, content, ref_url', sprintf('exam_id=%u and time_id=%d and examinee="%s" and status="publish"', $_SERVER['argv'][0], $_SERVER['argv'][1], $_SERVER['argv'][3]), ADODB_FETCH_ASSOC);
	else if ($type == 'personal')
	{
	    if (isAssignmentForGroup($_SERVER['argv'][0])){
			$row = getRecordOfAssignmentForGroup($_SERVER['argv'][0], $_SERVER['argv'][3]);
		}
	    else
			$row = dbGetStSr('WM_qti_homework_result', 'examinee, score, comment_txt as comment, content, ref_url', sprintf('exam_id=%u and time_id=%d and examinee="%s"', $_SERVER['argv'][0], $_SERVER['argv'][1], $_SERVER['argv'][3]), ADODB_FETCH_ASSOC);
	}

	// 作業繳交的附檔
        // 群組
        if (isset($assignmentsForGroup[$_SERVER['argv'][1]])) {
//            // 取分組的成員
            $group_mates = getMyGroupMates($team_id, $_SERVER['argv'][2], $course_id);
            $foldList= $group_mates;
        } else {
            $foldList = array($_SERVER['argv'][2]);
        }
        
        $homework_files = '';
        foreach($foldList as $v) {
            $homework_file_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09u/%s/',
                                                             $sysSession->school_id,
                                                             $sysSession->course_id,
                                                             QTI_which,
                                                             $_SERVER['argv'][1],
                                                             $v);
            $homework_uri = substr($homework_file_path, strlen(sysDocumentRoot));
            if ($d = @dir($homework_file_path))
            {
                    while (false !== ($entry = $d->read()))
                    {
                            if (is_file($homework_file_path . $entry))
                            {
                    $peer_files .= '<div>' . genPureFileLink($homework_uri, $entry) . '</div>';
                                    $homework_files .= genPureFileLink($homework_uri, $entry);
                            }
                    }
                    $d->close();
            }
        }

    $save_ref_path = sprintf($saved_path . 'ref/%09u/', $_SERVER['argv'][3]);
    $save_ref_uri = substr($save_ref_path, strlen(sysDocumentRoot));
    if ($d = @dir($save_ref_path))
    {
        while (false !== ($entry = $d->read()))
        {
            if (is_file($save_ref_path . $entry))
            {
                $reference_files .= '<div><input type="checkbox" name="rmfiles[]" value="' . rawurlencode($entry) . '"> ' . genPureFileLink($save_ref_uri, $entry) . '</div>';
            }
        }
        $d->close();
    }

	// 開始 output HTML

        echo '<meta http-equiv="X-UA-Compatible" content="IE=8">';
        
        showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$topDir}/wm.css");
        showXHTML_CSS('include', "/theme/default/learn_mooc/peer.css");

        showXHTML_script('include', '/lib/jquery/jquery.min.js');
        //showXHTML_script('include', '/teach/exam/exam_maintain.js');

	  $msgQuota = str_replace('%TYPE%', $MSG[$topDir == 'academic' ? 'school' : 'course'][$sysSession->lang], $MSG['quota_exceed'][$sysSession->lang]);
	  $scr = <<< EOB
function scoreAdd(num1, num2)
{
	var r1, r2, m;
	try{ r1 = num1.toString().split(".")[1].length; } catch(e){ r1 = 0; }
	try{ r2 = num2.toString().split(".")[1].length; } catch(e){ r2 = 0; }
	m = Math.pow(10,Math.max(r1,r2));
	return (num1 * m + num2 * m) / m;
}
function reload_score(){
	var forms = document.getElementsByTagName('form');
	var scores = forms[0].getElementsByTagName('input');
	var total_score = 0.0;

	for(var i=0; i< scores.length; i++){
		if (scores[i].name.indexOf('item_scores[') === 0) total_score = scoreAdd(total_score, parseFloat(scores[i].value));
	}
	forms[0].total_score.value = total_score;
}

// 提示確認給分正確
function check_data() {
    var p = $("input[name^='point[']:checked");
    var score_input = document.getElementsByTagName('form')[0];
    if ($('.point-table tbody tr').length >= 1 && p.length !== $('.point-table tbody tr').length) {
        alert('{$MSG['no_check'][$sysSession->lang]}');
        return false;
    } else if (score_input.total_score !== null && typeof(score_input.total_score) !== 'undefined') {
        if (score_input.total_score.value == '') {
            alert('{$MSG['no_rating'][$sysSession->lang]}');
            return false;
        } else {
            if (confirm('{$MSG['send_rating_confirm1'][$sysSession->lang]} ' + $("input[name='total_score']").val() + ' {$MSG['send_rating_confirm2'][$sysSession->lang]}') === false) {
                return false;
            }
        }
    } else {
        return true;
    }
}

$( document ).ready( function() {

    // 模擬評量表多選改為單選
    $("input[name^='point[']").click(function() {
        var checkedDom = $(this).attr('name');
        $(this).parent().parent().find('input').each(function(){
            // 非目前勾選到的，全部不勾選
            if (checkedDom !== $(this).attr('name')) {
                $(this).prop('checked', false);
            }
        });
    });

    // 連動開放觀摩
    $("input[name='tmpExemplary']").click(function() {
        $("input[name='exemplary']").attr('checked', $(this).attr('checked') === 'checked');
    });
});

function ValidateFloat2(e, pnumber) {
    if (e.value !== '') {
        if (!/^\d+[.]?[0-9]?$/.test(pnumber)) {
            var newValue = /\d+[.]?[0-9][0-9]?/.exec(e.value);
            if (newValue != e.value) {
                if (newValue != null) {
                    e.value = newValue;
                } else {
                    e.value = '';
                }
            }
        }
        if ((pnumber*100) > 10000) {
            e.value = 100;
            alert('{$MSG['rating_over_100'][$sysSession->lang]}');
        }
    }
}

window.onload=function()
{
	var score_input = document.getElementsByTagName('form')[0];
        // 老師評分才作成績加總
        if ('$topDir' == 'teach') {
            if (score_input.total_score !== null && typeof(score_input.total_score) !== 'undefined') {
                if (score_input.total_score.value == '' || score_input.total_score.value == 0) reload_score();
            }
        }
	if ({$isQuotaExceed}) alert('{$msgQuota}');
};

EOB;

	  
	  showXHTML_script('inline', $scr);
	showXHTML_head_E();
	showXHTML_body_B();

if ($topDir === 'teach') {
    $pageTitle = $MSG['rating'][$sysSession->lang];
} else {
    if ($mode === 'peer') {
        $pageTitle = $MSG['peer_assessment'][$sysSession->lang];
    } else {
        $pageTitle = $MSG['self_assessment'][$sysSession->lang];
    }
}

echo
    '<div style="width:'.(($profile['isPhoneDevice'])?'98%':'1120px').'; margin: auto auto;">
        <ul class="bar" id="peer-page-title">
            <li class="left">
                <span>' . $pageTitle . '</span>
            </li>
        </ul>
        <div class="navbar-form"></div>
        <div class="box box-padding-t-1 box-padding-lr-3 box-padding-b-3">';

if ($fields['assess_way'] >= 1) {

  // 取級距
  $rsLevel = dbGetStMr('WM_evaluation_level',
      'level_id, caption, score',
      "eva_id = '" . $fields['assess_way'] . "' order by permute",
      ADODB_FETCH_ASSOC);

  $level = array();
  $levelCaption = '';
  $ratingLevelCaption = '';
  if ($rsLevel) {
      while(!$rsLevel->EOF){
          $level['level'][$rsLevel->fields['level_id']]['caption'] = $rsLevel->fields['caption'];
          $level['level'][$rsLevel->fields['level_id']]['score']   = $rsLevel->fields['score'];
          $levelCaption .= '<th><div class="breakword" style="width: 140px;">' . htmlspecialchars($rsLevel->fields['caption']) .'</div></th>';
          $ratingLevelCaption .= '<th class="span2 "><div class="breakword" style="width: 140px;">' . htmlspecialchars($rsLevel->fields['caption']) .'</div></th>';

          $rsLevel->MoveNext();
      }
  }

  // 取指標X級距
  $rsPointNote = dbGetStMr('WM_evaluation_point_note',
      'point_id, level_id, note',
      "point_id in (select point_id from WM_evaluation_point
      where eva_id = '" . $fields['assess_way'] . "') order by point_id, level_id",
      ADODB_FETCH_ASSOC);

  if ($rsPointNote) {
      while(!$rsPointNote->EOF){
          $level['point_note'][$rsPointNote->fields['point_id']][$rsPointNote->fields['level_id']] = $rsPointNote->fields['note'];

          $rsPointNote->MoveNext();
      }
  }

  // 取指標
  $rsPoint = dbGetStMr('WM_evaluation_point',
      'point_id, caption',
      "eva_id = '" . $fields['assess_way'] . "' order by permute",
      ADODB_FETCH_ASSOC);

  $pointCaption = '';
  if ($rsPoint) {

    // 取之前評分的數值
    if ($topDir == 'teach') {
        $rsPointed = dbGetStMr('WM_qti_peer_result_eva',
            'point_id, level_id',
            "exam_id = " . $_SERVER['argv'][1] . " and examinee = '" . $_SERVER['argv'][2] . "' and time_id = " . $_SERVER['argv'][3] . " and creator = '" . $sysSession->username . "' and eva_id = " . $fields['assess_way'] . " and score_type = 2 order by point_id, level_id",
            ADODB_FETCH_ASSOC);
        $pointed = array();
        if ($rsPointed) {
            while(!$rsPointed->EOF) {
                $pointed[$rsPointed->fields['point_id']][$rsPointed->fields['level_id']] = true;

                $rsPointed->MoveNext();
            }
        }
    }

      while(!$rsPoint->EOF){
          $level['point'][$rsPoint->fields['point_id']]['caption'] = $rsPoint->fields['caption'];

          // 評量表說明
          $pointCaption .= '<tr>
                              <th class="span2 box-padding-lr-3 left"><div class="breakword" style="width: 140px;"><strong>' . htmlspecialchars($rsPoint->fields['caption']) . '</strong></div></th>';
          foreach ($level['level'] as $key => $val) {
              $pointCaption .= '<td class="span3"><div class="breakword" style="width: 170px;">' . htmlspecialchars($level['point_note'][$rsPoint->fields['point_id']][$key]) . '</div></td>';
          }
          $pointCaption .= '</tr>';

          // 評分區塊
          $ratingPointCaption .= '<tr>
                              <th class="span2 box-padding-lr-3 left"><div class="breakword" style="width: 140px;"><strong>' . htmlspecialchars($rsPoint->fields['caption']) . '</strong></div></th>';
          foreach ($level['level'] as $key => $val) {
              if ($pointed[$rsPoint->fields['point_id']][$key] === true) {
                  $checkedFlag = 'checked';
              } else {
                  $checkedFlag = '';
              }
              $ratingPointCaption .= '<td><input ' . $checkedFlag. ' type="checkbox" name="point[' . $rsPoint->fields['point_id'] . '_' . $key . ']" value="' . $val['score'] . '" data-id="' . $rsPoint->fields['point_id'] . '_' . $key .'" style="width: 20px; height: 20px"></td>';
          }
          $ratingPointCaption .= '</tr>';

          $rsPoint->MoveNext();
      }
  }
}

  if ($topDir == 'teach') {
      
//      $fields['total_score'] = 0;
      // 補驗證加權分數是否無值，如果沒有數值補運算
      if (empty($fields['total_score']) === TRUE) {
          
         // 老師分數
        $fixScoreTeacher = round($sysConn->GetOne('SELECT SUBSTRING(MIN(CONCAT(LPAD(time_id, 6, "0"),score)), 7) FROM WM_qti_peer' . "_result WHERE exam_id = {$_SERVER['argv'][1]} AND examinee = '{$_SERVER['argv'][2]}' AND (status = 'revised' OR status = 'publish' OR status = 'submit')"), 2);

        // 互評分數
        $fixScorePeer = round($sysConn->GetOne('SELECT AVG(score) FROM WM_qti_peer_result_score WHERE exam_id = ' . $_SERVER['argv'][1] . ' AND examinee = \'' . $_SERVER['argv'][2]. '\' AND score_type = 0'), 2);

        // 自評分數
        $fixScoreSelf = round($sysConn->GetOne('SELECT SUBSTRING(MIN(CONCAT(LPAD(time_id, 6, \'0\'), score)),7) FROM WM_qti_peer_result_score WHERE exam_id = ' . $_SERVER['argv'][1]. ' AND examinee = \'' . $_SERVER['argv'][2]. '\'AND score_type = 1'), 2);

        // 權重
        list($teacher_percent, $peer_percent, $self_percent) = dbGetStSr('WM_qti_peer_test', 'teacher_percent, peer_percent, self_percent', 'exam_id = ' . $_SERVER['argv'][1], ADODB_FETCH_NUM);
        $fixScore = ($fixScoreTeacher * $teacher_percent / 100) + ($fixScorePeer * $peer_percent / 100) + ($fixScoreSelf * $self_percent / 100);
        
        $fields['total_score'] = $fixScore;
                        
        $fixUpdate = sprintf('total_score = %.2f', $fields['total_score']);
        $fixWhere = sprintf('exam_id = %d AND examinee = "%s" LIMIT 1', $_SERVER['argv'][1], $_SERVER['argv'][2]);

        dbSet('WM_qti_peer_result', unescape_percent_sign($fixUpdate), $fixWhere);
      }
      
      echo '<div class="margin-bottom-15">
                <div>
                    <div class="icon-rating-info"></div>
                    <div class="score-title">' . $MSG['watch_performance'][$sysSession->lang] . '</div>
                </div>
                <div class="score-require"><span><input name="tmpExemplary" ' . ($fields['status'] === 'publish' ? ' checked' : '') . ' type="checkbox"></span>' . $MSG['score_publish_peer'][$sysSession->lang] . '</div>
                <div class="final all-radius bkcolor-palegray" style="position: relative;">
                    <div class="final-text">
                        <span class="total">' . $MSG['total_score'][$sysSession->lang] . '：<span>' . $grade['score'] . '</span></span>
                        <span>' . $MSG['formula'][$sysSession->lang] . '：<span class="strong">' . round($score_self['score'], 2) . '</span> (<div class="icon-self"></div>' . $MSG['self_assessment'][$sysSession->lang] . ') * ' . $fields['self_percent'] . '% + <span class="strong">' . round($score_peer, 2) . '</span> (' . $MSG['peer_assessment'][$sysSession->lang] . ') * ' . $fields['peer_percent'] . '% + <span class="strong">' . round($fields['score'], 2) . '</span> (<div class="icon-teacher"></div>' . $MSG['teacher_rating'][$sysSession->lang] . ') * ' . $fields['teacher_percent'] . '% = <span class="strong">' . round($fields['total_score'], 2) . '</span></span>
                    </div>
                </div>
            </div>';
  } else {
      echo '<div class="margin-bottom-15">
                <div>
                    <div class="icon-rating-info"></div>
                    <div class="rating-title">' . $MSG['rating_standards'][$sysSession->lang] . '</div>
                </div>
                <div class="note all-radius bkcolor-palegray" style="position: relative;">
                    <div class="scrollbar thin note-text">
                        <div class="force-overflow breakword" style="width: 70em;">
                            ' . $fields['assess'] . '
                        </div>
                    </div>
                </div>
            </div>';

    if ($fields['assess_way'] >= 1) {
        echo '<div class="margin-bottom-15">
                  <div>
                    <div class="icon-rating-info"></div>
                    <div class="rating-title">' . $MSG['checklist_description'][$sysSession->lang] . '</div>
                  </div>
                  <div class="point-note bottom-radius bkcolor-palegray">
                      <div class="scrollbar thin point-note-text">
                          <div class="force-overflow">
                              <table class="point-note-table">
                                  <thead>
                                      <tr>
                                          <th><div>&nbsp;</div></th>' .
                                          $levelCaption .
                                     '</tr>
                                  </thead>
                                  <tbody>' .
                                      $pointCaption .
                                 '</tbody>
                              </table>
                          </div>
                      </div>
                  </div>
              </div>';
    }
  }

        // 老師環境的顯示 => $studentName: 判斷是否顯示學生真實姓名、$cancelBtn: 判斷取消按鈕的功能
        if ($topDir == 'teach') {
            // 群組顯示成員名單
            if (isset($assignmentsForGroup[$_SERVER['argv'][1]])) {
                // 取分組的成員
                $studentName = '';
                foreach ($group_mates as $v) {
                    $user = getUserDetailData($v);
                    $user['realname'] = trim($user['realname']);
                    $studentName .= $v . ' (' . (empty($user['realname']) === true ? $MSG['no_name'][$sysSession->lang] : $user['realname']) . '),';
                }
                $studentName = substr($studentName, 0, -1);
            } else {
                $user = getUserDetailData($_SERVER['argv'][2]);
                $studentName = $_SERVER['argv'][2] . ' (' . $user['realname'] . ')';
            }
            $cancelBtn = '<button type="reset" class="btn">' . $MSG['cancel'][$sysSession->lang] . '</button>';
        } else {
            $studentName = $MSG['student'][$sysSession->lang];
            $cancelBtn = '<button type="button" class="btn" onclick="location.replace(\'/learn/homework/homework_list.php\');">' . $MSG['cancel'][$sysSession->lang] . '</button>';
        }


        echo '<div class="margin-bottom-15">
                  <div>
                      <div class="icon-rating-2"></div>
                      <div class="rating-title">' . $MSG['rating_2'][$sysSession->lang] . '</div>
                      <div class="rating-require strong-note">' . $MSG['required'][$sysSession->lang] . '</div>
                  </div>
                  <div class="rating bottom-radius bkcolor-white" style="position: relative;">
                      <div class="rating-text">
                          <form accept-charset="UTF-8" lang="zh-tw" method="POST" action="exam_correct_content1.php" enctype="multipart/form-data" onsubmit="return check_data(this);">
                              <div>
                                  <div class="title top-radius bkcolor-orange">' . $studentName . '</div>
                                  <div>
                                      <table class="rating-table">
                                          <tbody>
                                              <tr class="attach">
                                                  <th>' . $MSG['attachments'][$sysSession->lang] . '</th>
                                                  <td>' . $peer_files . '</td>
                                              </tr>';

                                                  if ($fields['assess_way'] >= 1) {
                                                      echo '<tr style="vertical-align: top;">
                                                                <th><span class="strong-note">*</span> ' . $MSG['checklist'][$sysSession->lang] . '</th>
                                                                <td>
                                                                    <table class="point-table">
                                                                        <thead>
                                                                            <tr>
                                                                                <th><div>&nbsp;</div></th>' .
                                                                                $ratingLevelCaption .
                                                                           '</tr>
                                                                        </thead>
                                                                        <tbody>' .
                                                                            $ratingPointCaption .
                                                                       '</tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>';
                                                  } else {
                                                      echo '<tr>
                                                                <th><span class="strong-note">*</span> ' . $MSG['rating_2'][$sysSession->lang] . '</th>
                                                                <td><input name="total_score" maxlength="6" type="text" value="' . $fields['score'] . '" style="ime-mode:disabled" onkeyup="ValidateFloat2(this,value);"></td>
                                                            </tr>';
                                                  }

                                        echo '<tr style="vertical-align: top;">
                                                  <th>' . $MSG['comments'][$sysSession->lang] . '<input name="exemplary" ' . ($fields['status'] === 'publish' ? ' checked' : '') . ' type="checkbox" value="1" style="display: none;"></th>
                                                  <td><textarea name="comment" rows="5">' . $fields['comment'] . '</textarea></td>
                                              </tr>';

                                    if ($topDir == 'teach') {
                                        echo '<tr style="display: none;">
                                                  <th>' . $MSG['ref_url'][$sysSession->lang] . '</th>
                                                  <td><input name="refurl" maxlength="80" type="text" value="' . $fields['ref_url'] . '"></td>
                                              </tr>
                                              <tr class="reference-file" style="display: none;">
                                                  <th>' . $MSG['reference_file'][$sysSession->lang] . '</th>
                                                  <td>
                                                      <div>
                                                          ' . $reference_files . '
                                                      </div>
                                                      <div><input name="reffile" id="reffile" onkeydown="return false;" type="file" value=""></div>
                                                  </td>
                                              </tr>';
                                    }

                                echo '</tbody>
                                  </table>
                              </div>
                              <div class="actions">
                                  <button type="submit" class="btn btn-warning">' . $MSG['confirm'][$sysSession->lang] . '</button>'
                                  . $cancelBtn .
                                  '<input type="hidden" name="examinee" id="examinee" value="' . $_SERVER['argv'][2] . '">
                                  <input type="hidden" name="exam_id" id="exam_id" value="' . $_SERVER['argv'][1] . '">
                                  <input type="hidden" name="team_id" id="team_id" value="' . $team_id . '">
                                  <input type="hidden" name="time_id" id="time_id" value="' . $_SERVER['argv'][3] . '">
                                  <input type="hidden" name="ticket" id="ticket" value="' . $ticket . '">
                                  <input type="hidden" name="mode" id="mode" value="' . md5($mode) . '">
                              </div>
                              <div class="clear-both"></div>
                              <div class="margin-bottom-15">&nbsp;</div>
                          </div>
                      </form>
                  </div>
              </div>';

    if ($topDir == 'teach') {
        // 取評分明細
        // 自評
        $ratingList = '';
        if (isset($score_self['examinee'])) {
            $user = getUserDetailData($score_self['examinee']);
            $ratingList .= '<tr>
                                <td><div class="icon-self"></div></td>
                                <td>' . $score_self['examinee']  . ' (' . $user['realname'] . ')</td>
                                <td>' . $score_self['score'] . '</td>
                                <td><div class="breakword left" style="width: 550px;">' . nl2br(htmlspecialchars($score_self['comment'])) . '</div></td>
                                <td>' . $score_self['create_time'] . '</td>
                            </tr>';
        }

        // 老師評分
        if ((isset($fields['score']))){
            $scorePeer[$fields['upd_time'] . '-' . $fields['creator']]['create_time'] = $fields['upd_time'];
            $scorePeer[$fields['upd_time'] . '-' . $fields['creator']]['type'] = '<div class="icon-teacher"></div>';

            $user = getUserDetailData($fields['operator']);
            $scorePeer[$fields['upd_time'] . '-' . $fields['creator']]['creator'] = $fields['operator'] . ' (' . $user['realname'] . ')';
            $scorePeer[$fields['upd_time'] . '-' . $fields['creator']]['score'] = $fields['score'];
            $scorePeer[$fields['upd_time'] . '-' . $fields['creator']]['comment'] = $fields['comment'];
        }

        sort($scorePeer);
        foreach ($scorePeer as $key => $val) {
        $ratingList .=  '<tr>
            <td>' . $val['type'] . '</td>
            <td>' . $val['creator'] . '</td>
            <td>' . $val['score'] . '</td>
            <td><div class="breakword left" style="width: 550px;">' . nl2br(htmlspecialchars($val['comment'])) . '</div></td>
            <td>' . $val['create_time'] . '</td>
        </tr>';
        }

        if (count($scorePeer) >= 1 || isset($score_self['examinee'])) {
            echo '  <div class="margin-bottom-15">
                        <div>
                            <div class="icon-rating-detail"></div>
                            <div class="rating-title">' . $MSG['rating_details'][$sysSession->lang] . '</div>
                            <div class="rating-require">
                                <span><div class="icon-self"></div>' . $MSG['self_assessment'][$sysSession->lang] . '</span>
                                <span><div class="icon-teacher"></div>' . substr($MSG['teacher_assessment'][$sysSession->lang], 7) . '</span>
                            </div>
                        </div>
                        <div class="score all-radius bkcolor-palegray" style="position: relative;">
                            <div class="score-text">
                                <div>
                                    <table class="score-table">
                                        <thead>
                                            <tr>
                                                <th colspan="2"><div>' . $MSG['account_name'][$sysSession->lang] . '</div></th>
                                                <th><div>' . $MSG['score'][$sysSession->lang] . '</div></th>
                                                <th><div>' . $MSG['comments'][$sysSession->lang] . '</div></th>
                                                <th class="span2"><div>' . $MSG['rating_date'][$sysSession->lang] . '</div></th>
                                            </tr>
                                        </thead>
                                        <tbody>' .
                                            $ratingList .
                                       '</tbody>
                                    </table>
                                    <div style="height: 0.2em;">&nbsp;</div>
                                </div>
                            </div>
                            <div class="clear-both"></div>
                        </div>
                    </div>';
        }
    }

        echo   '<div class="clear-both"></div>
                <div class="margin-bottom--15"></div>
            </div>
        </div>
        <div>&nbsp;</div>
    </div>';

	showXHTML_body_E();