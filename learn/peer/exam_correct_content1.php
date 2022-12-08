<?php
    /**************************************************************************************************
     *                                                                                                *
     *        Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
     *                                                                                                *
     *        Programmer: Wiseguy Liang                                                         *
     *        Creation  : 2004/02/26                                                            *
     *        work for  : 儲存某考生對某次測驗的評分                                          *
     *        work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
     *        identifier: $Id: exam_correct_content1.php,v 1.1 2010/02/24 02:40:25 saly Exp $
     *                                                                                                *
     **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/quota.php');
    require_once(sysDocumentRoot . '/teach/grade/grade_recal.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
        
    /**
     * 在 XML 中插入分數
     *
     * @param   string  $score_id   題目的identifier
     * @param    float   $item_score 分數
     */
    function setScore(&$score_id, $item_score)
    {
        global $dom, $xpath;

        $ret = $xpath->xpath_eval('//item_result[@ident_ref="' . $score_id . '"]');
        if (is_array($ret->nodeset) && count($ret->nodeset))
        {
            $item_result = $ret->nodeset[0];
            $ret = $xpath->xpath_eval('./outcomes', $item_result);
            if (is_array($ret->nodeset) && count($ret->nodeset))
                $outcomes = $ret->nodeset[0];
            else
                $outcomes = $item_result->append_child($dom->create_element('outcomes'));

            $ret = $xpath->xpath_eval('./score', $outcomes);
            if (is_array($ret->nodeset) && count($ret->nodeset))
                $score = $ret->nodeset[0];
            else
                $score = $outcomes->append_child($dom->create_element('score'));
            $score->set_attribute('varname', 'SCORE');
            $score->set_attribute('vartype', 'Integer');

            $ret = $xpath->xpath_eval('./score_value', $score);
            if (is_array($ret->nodeset) && count($ret->nodeset))
            {
                $score_value = $ret->nodeset[0];
                foreach ($score_value->child_nodes() as $child)
                    $score_value->remove_child($child);
            }
            else
            {
                $score_value = $score->append_child($dom->create_element('score_value'));
            }
            $score_value->append_child($dom->create_text_node($item_score));

            // 記錄批改
            $date = $item_result->append_child($dom->create_element('date'));
            $type_label = $date->append_child($dom->create_element('type_label'));
            $type_label->append_child($dom->create_text_node('assign the score'));
            $datetime = $date->append_child($dom->create_element('datetime'));
            $datetime->append_child($dom->create_text_node(date('Y-m-d\TH:i:s')));

            return true;
        }
        else
            return false;
    }

    //ACL begin
    include_once(sysDocumentRoot . '/lib/group_assignment_lib.php');
    $sysSession->cur_func='1710300100';
    showXHTML_head_B('Answer detail', '8');
    showXHTML_CSS('include', "/theme/default/bootstrap/css/bootstrap.min.css");
    if ($profile['isPhoneDevice']) {
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        echo '<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">';
        echo '<link href="/theme/default/bootstrap336/css/bootstrap.min.css" rel="stylesheet" />';
        echo '<link rel="stylesheet" href="/sys/tpl/vendor/font-awesome/css/font-awesome.css" />';
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
    
    showXHTML_head_E();
    $sysSession->restore();
    if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))){

    }
    //ACL end

    $course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id;

    if ($_POST['ticket'] != md5(sysTicketSeed . $_COOKIE['idx'] . $_POST['exam_id']. $_POST['examinee']. $_POST['time_id']))
    {
        wmSysLog($sysSession->cur_func, $course_id , $_POST['exam_id'] , 1, 'auto', $_SERVER['PHP_SELF'], 'Fake Ticket!');
        die('Fake ticket !');
    }

    $isResultExists = intval(dbGetOne(
        'WM_qti_peer_result',
        'count(*)',
        sprintf('exam_id=%d and examinee="%s" and time_id=%d ',
            $_POST['exam_id'], $_POST['examinee'], $_POST['time_id']
        )
    ));

    if (!$isResultExists) {
        header('HTTP/1.1 403 Forbidden');
        exit;
    }

    if ($topDir == 'academic')
        $ans_save_path = sprintf(sysDocumentRoot . '/base/%05d/%s/A/%09u/%s/ref/%09u/',
                                   $sysSession->school_id,
                                   QTI_which,
                                   $_POST['exam_id'],
                                   $_POST['examinee'],
                                   $_POST['time_id']);
    else
        $ans_save_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09u/%s/ref/%09u/',
                                   $sysSession->school_id,
                                   $sysSession->course_id,
                                   QTI_which,
                                   $_POST['exam_id'],
                                   $_POST['examinee'],
                                   $_POST['time_id']);
        // 判斷分數是否在範圍內(目前一百為最高)
        if ($_POST['total_score'] != null) {
            if (!($_POST['total_score'] >= 0) || !($_POST['total_score'] <= 100) || !is_numeric($_POST['total_score'])) {
                echo '<div class="container esn-container">
                    <div class="panel block-center">
                        <form class="well form-horizontal message-pull-center">
                            <fieldset>
                                <div class="input block-center">
                                    <div class="row">&nbsp;</div>
                                    <div class="control-group">
                                        <div class="message">
                                            <div id="message">
                                                <div>'.$MSG['rating_over_100'][$sysSession->lang].'</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">&nbsp;</div>
                                    <div class="control-group">
                                        <div class="controls">
                                            <div class="lcms-left">
                                                <a href="javascript:;" onclick="history.go(-1);" class="btn btn-primary aNormal margin-right-10 btn-blue">'.$MSG['back'][$sysSession->lang].'</a>
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

    // 批改模式
    $mode = trim($_POST['mode']);
    // 判斷互評(0)、自評(1)或是老師評(2)
    $scoreType = 0;
    if ($mode === md5('peer')) {
        $scoreType = 0;
    } else if($mode === md5('self')) {
        $scoreType = 1;
    } else if ($mode === md5('teacher') or $topDir === 'teach') {
        $scoreType = 2;
    }

    // 刪除舊檔
    if (is_array($_POST['rmfiles']))
        foreach($_POST['rmfiles'] as $item)
        {
            $item = rawurldecode($item);
            if (file_exists($ans_save_path . $item)) @unlink($ans_save_path . $item);
        }

    // 儲存新檔
    if (is_uploaded_file($_FILES['reffile']['tmp_name']))
    {
        // 若目錄不存在則建立目錄
        if (!is_dir($ans_save_path)) exec("mkdir -p '$ans_save_path'");
        // 將參考檔案搬到該目錄
        move_uploaded_file($_FILES['reffile']['tmp_name'], $ans_save_path . mb_convert_encoding($_FILES['reffile']['name'], 'utf-8', 'utf-8,cp950,gb2312,gbk,JIS,eucjp-win,sjis-win'));
    }

    // 更新quota資訊
    $tmp_id = $topDir == 'academic' ? $sysSession->school_id : $sysSession->course_id;
    getCalQuota($tmp_id, $quota_used, $quota_limit);
    setQuota($tmp_id, $quota_used);

    // 是否要作觀模範本
    $status = (isset($_POST['exemplary']) && $_POST['exemplary']==1) ? 'publish' : 'revised';

    $where = sprintf('exam_id=%d and examinee="%s" and time_id=%d limit 1',
                    $_POST['exam_id'], $_POST['examinee'], $_POST['time_id']);

    // 儲存每一題給分 begin
    list($content) = dbGetStSr('WM_qti_peer_result', 'content', $where, ADODB_FETCH_NUM);
    if ($dom = @domxml_open_mem($content))
    {
        if (is_array($_POST['item_scores']) && count($_POST['item_scores']))
        {
            $xpath = xpath_new_context($dom);
            foreach ($_POST['item_scores'] as $score_id => $item_score)
            {
                setScore($score_id, sprintf('%.2f', $item_score));
            }
        }
    }
    else
        die('Result XML incorrect !');

    // 判斷有無評分過了
    $existScore = dbGetStSr('WM_qti_peer_result_score',
        'score',
        "exam_id = {$_POST['exam_id']} and examinee = '{$_POST['examinee']}' and time_id = {$_POST['time_id']} and creator = '{$sysSession->username}' limit 0, 1",
        ADODB_FETCH_ASSOC);

    // 刪除LOCK資料
    dbDel('WM_qti_peer_result_action', "exam_id = {$_POST['exam_id']} and examinee = '{$_POST['examinee']}' and time_id = {$_POST['time_id']} and creator = '{$sysSession->username}'");

    // 取互評自評比例、最小份數、優先權
    $test = dbGetStSr('WM_qti_peer_test',
        'peer_percent, self_percent, peer_times, assess_relation, assess_way',
        "exam_id = {$_POST['exam_id']} and course_id = {$sysSession->course_id}",
        ADODB_FETCH_ASSOC);

    // 如果是評量表給分，取分方式要調整
    if ($test['assess_way'] >= 1) {

        // 沒勾選
        if (count($_POST['point']) === 0) {
            die('評量表沒有勾選');

        // 有勾選且尚未計分
        } else {
            $totalScore = 0;
            if (count($existScore['score']) === 0 || (($mode === md5('teacher') or $topDir === 'teach') && count($existScore['score']) >= 1)) {

                // 刪除舊資料
                dbDel('WM_qti_peer_result_eva', "exam_id = {$_POST['exam_id']} and examinee = '{$_POST['examinee']}' and time_id = {$_POST['time_id']} and creator = '{$sysSession->username}' and score_type = {$scoreType}");

                // 儲存到評量表
                foreach ($_POST['point'] as $key => $val) {
                    $cols = explode('_', $key);
                    // 重新到資料表取最新值
                    list($score) = dbGetStSr('WM_evaluation_level', 'avg(score)', sprintf('level_id  = %d', $cols[1]), ADODB_FETCH_NUM);
                    (int)$totalScore = (int)$totalScore + (int)$score;

                    dbNew('WM_qti_peer_result_eva',
                        'exam_id, examinee, time_id, creator, eva_id, point_id, level_id, score_type',
                        sprintf('%d, "%s", %d, "%s", %d, %d, %d, %d',
                            $_POST['exam_id'],
                            $_POST['examinee'],
                            $_POST['time_id'],
                            $sysSession->username,
                            $test['assess_way'],
                            $cols[0],
                            $cols[1],
                            $scoreType
                        )
                    );
                }
            }

            // 總分
            $_POST['total_score'] = sprintf('%.2f', $totalScore);
        }
    }

    // 儲存每一題給分 end
    if ($mode === md5('peer') or $mode === md5('self') && count($existScore['score']) === 0) {

        dbNew('WM_qti_peer_result_score',
            'exam_id, examinee, time_id, score_type, score, comment_txt, creator, operator, create_time, upd_time',
            sprintf('%d, "%s", %u, "%s", %.2f, "%s", "%s", "%s", now(), now()',
                $_POST['exam_id'],
                $_POST['examinee'],
                $_POST['time_id'],
                $scoreType,
                $_POST['total_score'],
                escape_percent_sign($_POST['comment']),
                $sysSession->username,
                $sysSession->username
            )
        );
        
        if ($sysConn->Affected_Rows() === false) {
            wmSysLog($sysSession->cur_func, $sysSession->school_id , 0 , 0, 'classroom', $_SERVER['PHP_SELF'], 'insert_peer_score: ' . mysql_real_escape_string($sysConn->ErrorMsg()), $sysSession->username);
        }
        
        $update = sprintf('status="%s",content=%s, upd_time=now()',
                          $status, $sysConn->qstr($dom->dump_mem()));
        // 儲存本卷
        dbSet('WM_qti_peer_result', unescape_percent_sign($update), $where);
    } else if ($mode === md5('teacher') or $topDir === 'teach') {
        $update = sprintf('status="%s",score=%.2f,comment_txt="%s",ref_url="%s",content=%s, operator="%s", upd_time=now()',
            $status,
            $_POST['total_score'],
            escape_percent_sign($_POST['comment']),
            escape_percent_sign($_POST['refurl']),
            $sysConn->qstr($dom->dump_mem()),
            $sysSession->username
        );
        // 儲存本卷
        dbSet('WM_qti_peer_result', unescape_percent_sign($update), $where);

        $display = 'style="display: none;"';
    }

    // 更新總成績：底下的 reCalculateQTIGrade 會幫忙處理

    if (($mode === md5('peer') or $mode === md5('self')) && count($existScore['score']) >= 1) {
        $results = '本學員您評分過了';
    } else {
        if ($sysConn->ErrorNo()) {
            $results = $MSG['save_grade_failure'][$sysSession->lang] . $sysConn->ErrorMsg();
            wmSysLog($sysSession->cur_func, $course_id , $_POST['exam_id'] , 2, 'auto', $_SERVER['PHP_SELF'],
                     $results . sprintf('exam_id=%d and examinee="%s" and time_id=%d',
                     $_POST['exam_id'], $_POST['examinee'], $_POST['time_id']));
        }
        elseif($sysConn->Affected_Rows()) {
            // 同步儲存到成績系統
            // 批改作業，強制更新該學員成績
            if (reCalculateQTIGrade($_POST['examinee'], $_POST['exam_id'], QTI_which, $_POST['comment'], $_POST['team_id']))
                reCalculateGrades($sysSession->course_id);

            $results = $MSG['save_grade_success'][$sysSession->lang];
            wmSysLog($sysSession->cur_func, $course_id , $_POST['exam_id'] , 0, 'auto', $_SERVER['PHP_SELF'], $results . sprintf('exam_id=%d and examinee="%s" and time_id=%d',                                                                                                    $_POST['exam_id'], $_POST['examinee'], $_POST['time_id']));
        }
        else {
            $results = $MSG['grade_no_mdify'][$sysSession->lang];
            wmSysLog($sysSession->cur_func, $course_id , $_POST['exam_id'] , 0, 'auto', $_SERVER['PHP_SELF'], $results . sprintf('exam_id=%d and examinee="%s" and time_id=%d',
                                                                                                                                $_POST['exam_id'], $_POST['examinee'], $_POST['time_id']));
        }
    }

    $urlPeer = md5(sysTicketSeed . $sysSession->course_id . $_POST['exam_id'] . 'peer' . $_POST['time_id']) . '+' . $_POST['exam_id'] . '+peer+' . $_POST['time_id'];
    $urlSelf = md5(sysTicketSeed . $sysSession->course_id . $_POST['exam_id'] . 'self' . $_POST['time_id']) . '+' . $_POST['exam_id'] . '+self+' . $_POST['time_id'];

    // 取互評自評比例、最小份數、優先權
    $rsNumbers = dbGetStMr('WM_qti_peer_result_score',
        'score_type, COUNT(score) cnt',
        "exam_id = {$_POST['exam_id']} and creator = '{$sysSession->username}' GROUP BY score_type ORDER BY score_type",
        ADODB_FETCH_ASSOC);
    $numbers = array();
    if($rsNumbers) {
        while(!$rsNumbers->EOF) {
            $numbers[$rsNumbers->fields['score_type']] = $rsNumbers->fields['cnt'];
            $rsNumbers->MoveNext();
        }
    }

    // 自評按鈕出現時機：還沒有自評 且 （先互評且互評完成 或 先自評 或 無優先權）
    if ($numbers['1'] === 0 && (($numbers['0'] >= $test['peer_times'] && $test['assess_relation'] === 1) or $test['assess_relation'] === 2 or $test['assess_relation'] === 0)) {
        $btnSelf = '<a href="exam_correct_content.php?' . $urlSelf . '" class="btn btn-primary aNormal margin-right-10 btn-blue">自評</a>';
    } else {
        $btnSelf = '';
    }
    echo <<< EOB
<div class="container esn-container">
    <div class="panel block-center">
        <form class="well form-horizontal message-pull-center">
            <fieldset>
                <div class="input block-center">
                    <div class="row">&nbsp;</div>
                    <div class="control-group">
                        <div class="message">
                            <div id="message">
                                <div>{$results}</div>
                            </div>
                        </div>
                    </div>
                    <div class="row">&nbsp;</div>
                    <div class="control-group" {$display}>
                        <div class="controls">
                            <div class="lcms-left">
                                <a href="exam_correct_content.php?{$urlPeer}" class="btn btn-primary aNormal margin-right-10 btn-blue">繼續評分</a>
                                {$btnSelf}
                                <a href="/learn/homework/homework_list.php" class="btn btn-primary aNormal margin-right-10 btn-blue">回到列表</a>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div>
EOB;
                                
// 老師在 teach 環境批改才跳轉頁面
if ($mode === md5('teacher') or $topDir === 'teach') {
    echo '<script>
        setTimeout("parent.correctOne()", 1500);
    </script>';
}