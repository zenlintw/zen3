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

	//ACL begin
	$sysSession->cur_func = '1710300100';
	$sysSession->restore();
	if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {

	}
	//ACL end

    showXHTML_CSS('include', "/theme/default/bootstrap/css/bootstrap.min.css");
    showXHTML_CSS('include', "/theme/default/learn_mooc/application.css");

	if (!defined('QTI_env'))
		list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
	else
		$topDir = QTI_env;

	$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;

	$curr_mode = $ADODB_FETCH_MODE;
	$ADODB_FETCH_MODE = ADODB_FETCH_NUM;

	if ($topDir === 'academic')
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

    // 先判斷有沒有評分資格：自己要先繳交作業
    if ($mode === 'peer' || $mode === 'self') {
        // 判斷是否為群組作業
        if ($_SERVER['argv'][4] === '') {
            $peer_result = dbGetStSr('WM_qti_peer_result',
                'examinee',
                "exam_id = {$_SERVER['argv'][1]} and examinee = '{$sysSession->username}' and time_id = {$_SERVER['argv'][3]} and status != 'break' limit 0, 1",
                ADODB_FETCH_ASSOC);
        } else {
            $peer_result = getRecordOfAssignmentForGroup($_SERVER['argv'][1], $sysSession->username, $sysSession->course_id, 'peer');
        }
        if ($peer_result === false || count($peer_result['examinee']) === 0) {
            echo '<div class="container esn-container">
                      <div class="panel block-center">
                          <form class="well form-horizontal message-pull-center">
                              <fieldset>
                                  <div class="input block-center">
                                      <div class="row">&nbsp;</div>
                                      <div class="control-group">
                                          <div class="message">
                                              <div id="message">
                                                  <div>' . $MSG['no_pay'][$sysSession->lang] . ' </div>
                                              </div>
                                          </div>
                                      </div>
                                      <div class="row">&nbsp;</div>
                                      <div class="control-group">
                                          <div class="controls">
                                              <div class="lcms-left">
                                                  <a href="#" class="btn btn-primary aNormal margin-right-10 btn-blue" id="btnForget" onClick="window.close();">' . $MSG['close_window'][$sysSession->lang] . '</a>
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
    }

    if ($mode === 'self') {
        $_SERVER['argv'][2] = $sysSession->username;
    }
	$keep = $ADODB_FETCH_MODE;
	$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;
	if (QTI_which == 'peer' && isAssignmentForGroup($_SERVER['argv'][1], $sysSession->course_id, 'peer')) {
        $sqls = 'SELECT R.*, T.peer_percent, T.self_percent, T.teacher_percent ' .
            'FROM WM_qti_peer_result AS R ' .
            'LEFT JOIN WM_qti_peer_test AS T ON T.exam_id = R.exam_id ' .
            'WHERE R.exam_id =' . $_SERVER['argv'][1] . ' ' .
            'AND R.examinee IN (SELECT username from WM_student_div where course_id =' . $sysSession->course_id . ' AND group_id = ' . $_SERVER['argv'][4] . ' and team_id = ' . $_SERVER['argv'][5] . ') ';
	    $fields = $sysConn->GetRow($sqls);
	    $isGroupingAssignment = $_SERVER['argv'][4];
	    $_SERVER['argv'][2] = $fields['examinee'];
	} else {
		$fields = dbGetStSr('WM_qti_peer_result, WM_qti_peer_test',
            'status, score, comment, comment_txt, WM_qti_peer_result.content, ref_url, assess, peer_percent, self_percent, teacher_percent, assess_way, total_score, WM_qti_peer_result.operator, WM_qti_peer_result.upd_time',
            "WM_qti_peer_result.exam_id = WM_qti_peer_test.exam_id and WM_qti_peer_result.exam_id={$_SERVER['argv'][1]} and examinee='{$_SERVER['argv'][2]}' and time_id={$_SERVER['argv'][3]}",
            ADODB_FETCH_ASSOC);
        $isGroupingAssignment = false;
	}

    $ADODB_FETCH_MODE = $keep;

	$ticket = md5(sysTicketSeed . $_COOKIE['idx'] . $_SERVER['argv'][1] . $_SERVER['argv'][2] . $_SERVER['argv'][3]);

	if (empty($fields['content'])) {
	   wmSysLog($sysSession->cur_func, $course_id , $_SERVER['argv'][1] , 2, 'auto', $_SERVER['PHP_SELF'], 'Reading ' . QTI_which . ' content faliure.');
//        echo '<div class="container esn-container">
//                  <div class="panel block-center">
//                      <form class="well form-horizontal message-pull-center">
//                          <fieldset>
//                              <div class="input block-center">
//                                  <div class="row">&nbsp;</div>
//                                  <div class="control-group">
//                                      <div class="message">
//                                          <div id="message">
//                                              <div>' . $MSG['rating_finished'][$sysSession->lang] . '</div>
//                                          </div>
//                                      </div>
//                                  </div>
//                                  <div class="row">&nbsp;</div>
//                                  <div class="control-group">
//                                      <div class="controls">
//                                          <div class="lcms-left">
//                                              <a href="peer_list.php" class="btn btn-primary aNormal margin-right-10 btn-blue" id="btnForget">' . $MSG['back_to_list'][$sysSession->lang] . '</a>
//                                          </div>
//                                      </div>
//                                  </div>
//                              </div>
//                          </fieldset>
//                      </form>
//                  </div>
//              </div>';
//        die();
	}

    //互評分數
    $rsScorePeer = dbGetStMr('WM_qti_peer_result_score',
                        'examinee, SUBSTRING((CONCAT(LPAD(time_id,6,\'0\'),score)),7) score, comment_txt as comment, creator, create_time',
                        'exam_id = ' . $_SERVER['argv'][1]. ' and examinee = \'' . $_SERVER['argv'][2]. '\' and score_type = 0',
                        ADODB_FETCH_ASSOC);
    $scorePeer = array();
    $score_peer = 0;
    if ($rsScorePeer) {
        while(!$rsScorePeer->EOF){
            $scorePeer[$rsScorePeer->fields['create_time'] . '-' . $rsScorePeer->fields['creator']]['create_time'] = substr($rsScorePeer->fields['create_time'], 0, 16);
            $scorePeer[$rsScorePeer->fields['create_time'] . '-' . $rsScorePeer->fields['creator']]['type'] = '';

            if ($topDir === 'teach') {
                $studentName = $rsScorePeer->fields['creator'];
            } else {
                $studentName = '學生';
            }
            $scorePeer[$rsScorePeer->fields['create_time'] . '-' . $rsScorePeer->fields['creator']]['creator'] = $studentName;
            $scorePeer[$rsScorePeer->fields['create_time'] . '-' . $rsScorePeer->fields['creator']]['score'] = $rsScorePeer->fields['score'];
            $score_peer = $score_peer + $rsScorePeer->fields['score'];
            $scorePeer[$rsScorePeer->fields['create_time'] . '-' . $rsScorePeer->fields['creator']]['comment'] = strip_tags($rsScorePeer->fields['comment']);

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
                        "WM_grade_item.username = '{$_SERVER['argv'][2]}' AND WM_grade_item.grade_id = WM_grade_list.grade_id AND WM_grade_list.course_id = '" . $course_id . "' AND source = '4' AND WM_grade_list.property = '" . $_SERVER['argv'][1] . "'",
                        ADODB_FETCH_ASSOC);

	// 開始 output HTML
	showXHTML_head_B('Answer detail');
        showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$topDir}/wm.css");
        showXHTML_CSS('include', "/theme/default/learn/peer.css");

        showXHTML_script('include', '/lib/jquery/jquery.min.js');

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

window.onload=function()
{
	var score_input = document.getElementsByTagName('form')[0];
	// if (score_input.total_score.value == '' || score_input.total_score.value == 0) reload_score();
	if ({$isQuotaExceed}) alert('{$msgQuota}');
};

EOB;
        showXHTML_script('inline', $scr);
    showXHTML_head_E();
    showXHTML_body_B();

    if ($fields['status'] === 'publish') {
        $award = '<span class="award"><img src="/theme/default/learn_mooc/icon_award.png" width="80" height="80" title=""/></span>';
    } else {
        $award = '';
    }

    if ($grade['score'] !== $fields['total_score']) {
        $pencil = '<img src="/theme/default/learn_mooc/icon_note2.png" width="19" height="19" title=""/>';
        $pencilNote = '';
    } else {
        $pencil = '';
        $pencilNote = 'display: none;';
    }

    echo '<div style="min-width: 969px; margin: auto auto; padding-left: 2em; padding-right: 2em;">
            <ul class="bar" id="peer-page-title">
                <li class="left">
                    <span style="line-height:1em;">' . $MSG['table_title9'][$sysSession->lang] . '</span>
                </li>
            </ul>
            <div class="row"></div>
            <div class="navbar-form"></div>
            <div class="box box-padding-t-1 box-padding-lr-3 box-padding-b-3">
                <div class="margin-bottom-15">
                    <div>
                        <div class="score-title">' . $MSG['watch_performance'][$sysSession->lang] . '</div>
                        <div class="rating-require">
                            <span style="' . $pencilNote . '"><img src="/theme/default/learn_mooc/icon_note2.png" width="19" height="19" title=""/>' . $MSG['adjusted_score'][$sysSession->lang] . '</span>
                        </div>
                    </div>
                    <div class="row"></div>
                    <div class="row" style="margin-bottom: -0.5em;"></div>
                    <div class="final all-radius bkcolor-palegray" style="position: relative; padding-left: 1em;">
                        <div class="final-text">' .
                            $award .
                           '<span class="total">' . $MSG['total_score'][$sysSession->lang] . '：<span style="font-size: 1.8em; position: relative; top: 5px;">' . $grade['score'] . '</span></span>' .
                            $pencil .
                           '<span class="formula">' . $MSG['formula'][$sysSession->lang] . '：<span class="strong">' . round($score_self['score'], 2) . '</span> (<img src="/theme/default/learn_mooc/icon_self.png" width="19" height="19" title="" class="look-result-icon"/>' . $MSG['self_assessment'][$sysSession->lang] . ') * ' . $fields['self_percent'] . '% + <span class="strong">' . round($score_peer, 2) . '</span> (' . $MSG['peer_assessment'][$sysSession->lang] . ') * ' . $fields['peer_percent'] . '% + <span class="strong">' . $fields['score'] . '</span> (<img src="/theme/default/learn_mooc/icon_teacher.png" width="19" height="19" class="look-result-icon"/>' . $MSG['teacher_rating'][$sysSession->lang] . ') * ' . $fields['teacher_percent'] . '% = <span class="strong">' . $fields['total_score'] . '</span></span>
                        </div>
                    </div>
                </div>';

            // 取評分明細
            // 自評
            $ratingList = '';
            if (isset($score_self['examinee'])) {
                $user = getUserDetailData($score_self['examinee']);
                $ratingList .= '<tr>
                                    <td><img src="/theme/default/learn_mooc/icon_self.png" width="19" height="19"/></td>
                                    <td>' . $score_self['examinee']  . ' (' . $user['realname'] . ')</td>
                                    <td>' . $score_self['score'] . '</td>
                                    <td><div class="breakword left">' . nl2br(htmlspecialchars($score_self['comment'])) . '</div></td>
                                    <td>' . substr($score_self['create_time'], 0, 16) . '</td>
                                </tr>';
            }

            // 老師評分
            if (isset($fields['score'])) {
                $scorePeer[$fields['upd_time'] . '-' . $fields['creator']]['create_time'] = substr($fields['upd_time'], 0, 16);
                $scorePeer[$fields['upd_time'] . '-' . $fields['creator']]['type'] = '<img src="/theme/default/learn_mooc/icon_teacher.png" width="19" height="19"/>';

                $user = getUserDetailData($fields['operator']);
                $scorePeer[$fields['upd_time'] . '-' . $fields['creator']]['creator'] = $fields['operator'] . ' (' . $user['realname'] . ')';
                $scorePeer[$fields['upd_time'] . '-' . $fields['creator']]['score'] = $fields['score'];
                $scorePeer[$fields['upd_time'] . '-' . $fields['creator']]['comment'] = strip_tags($fields['comment_txt']);
            }

            sort($scorePeer);
            foreach ($scorePeer as $key => $val) {
            $ratingList .=  '<tr>
                                <td>' . $val['type'] . '</td>
                                <td>' . $val['creator'] . '</td>
                                <td>' . $val['score'] . '</td>
                                <td><div class="breakword left">' . nl2br(htmlspecialchars($val['comment'])). '</div></td>
                                <td>' . $val['create_time'] . '</td>
                            </tr>';
            }

            if (count($scorePeer) >= 1) {
                echo '  <div class="margin-bottom-15">
                            <div>
                                <div class="rating-title">' . $MSG['rating_details'][$sysSession->lang] . '</div>
                                <div class="rating-require">
                                    <span><img src="/theme/default/learn_mooc/icon_self.png" width="19" height="19" class="look-result-icon"/>' . $MSG['self_assessment'][$sysSession->lang] . '</span>
                                    <span><img src="/theme/default/learn_mooc/icon_teacher.png" width="19" height="19" class="look-result-icon"/>' . $MSG['teacher_rating'][$sysSession->lang] . '</span>
                                </div>
                            </div>
                            <div class="row"></div>
                            <div class="row" style="margin-bottom: -0.5em;"></div>
                            <div class="score all-radius bkcolor-palegray" style="position: relative;">
                                <div class="score-text">
                                    <div style="text-align: center; padding-top: 1em; padding-bottom: 1em;">
                                        <table class="score-table">
                                            <thead>
                                                <tr>
                                                    <th colspan="2" style="min-width: 11em;"><div>' . $MSG['account_name'][$sysSession->lang] . '</div></th>
                                                    <th style="min-width: 2em;"><div>' . $MSG['score'][$sysSession->lang] . '</div></th>
                                                    <th><div>' . $MSG['comments'][$sysSession->lang] . '</div></th>
                                                    <th class="span2" style="min-width: 8em;"><div>' . $MSG['rating_date'][$sysSession->lang] . '</div></th>
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

            echo '  <div class="clear-both"></div>
                    <div class="margin-bottom--15"></div>
                </div>
            </div>
            <div>&nbsp;</div>
          </div>';

	showXHTML_body_E();