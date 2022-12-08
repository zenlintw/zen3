<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lib/attach_link.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/username.php');
require_once(sysDocumentRoot . '/lib/group_assignment_lib.php');

$sysSession->cur_func = '1710400300';
$sysSession->restore();
if (!aclVerifyPermission(1700400300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

define('QTI_DISPLAY_ANSWER', true);
define('QTI_DISPLAY_OUTCOME', true);
define('QTI_DISPLAY_RESPONSE', true);
require_once(sysDocumentRoot . '/teach/exam/exam_preview.php');
header('Content-type: text/html'); // 因為 exam_preview.php 會輸出 text/xml header 所以在此糾正回來 #1148

showXHTML_head_B($MSG['title'][$sysSession->lang]);
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    // MIS#21298 by Small 2011/6/8
    $ticket = md5(sysTicketSeed . $_SERVER['argv'][0] . $_SERVER['argv'][1] . $_COOKIE['idx']);
    $url    = 'exemplar_list.php?' . sprintf('%s+%s+%s', $_SERVER['argv'][0], $_SERVER['argv'][1], $ticket);
    // $url = 'exemplar_list.php?' . sprintf('%s+%s+%s', $_SERVER['argv'][0], $_SERVER['argv'][1], $_SERVER['argv'][2]);
    showXHTML_script('inline', "
    function exemplar_list() {
        location.href = '{$url}';
    }");
    echo '<style>';
    echo 'td {font-size: 1.2em;}';
    echo '.cssBtn {font-size: 1em; height: 1.9em;}';
    echo '.box01 td {padding: 0.4em;}';
    echo '.cssTabs {font-size: 1em;}';
    echo '</style>';
showXHTML_head_E();

showXHTML_body_B();
    if ($_SERVER['argv'][2] != md5(sysTicketSeed . $_SERVER['argv'][0] . $_SERVER['argv'][1] . $_SERVER['argv'][3] . $_COOKIE['idx'])) {
        wmSysLog($sysSession->cur_func, $sysSession->course_id, 0, 1, 'auto', $_SERVER['PHP_SELF'], 'Fake Ticket!');
        die('Fake ticket !');
    }

    $res = checkUsername($_SERVER['argv'][3]);
    if ($res != 2) {
        wmSysLog($sysSession->cur_func, $sysSession->course_id, 0, 1, 'auto', $_SERVER['PHP_SELF'], 'Access Deny!');
        die('Access Deny !');
    }
    // 判斷是觀摩佳作或者是觀看個人作業結果
    $type = $_SERVER['argv'][4];
    if ($type == 'exemplar')
        $row = dbGetStSr('WM_qti_peer_result', 'examinee, score, comment, content, ref_url, comment_txt', sprintf('exam_id=%u and time_id=%d and examinee="%s" and status="publish"', $_SERVER['argv'][0], $_SERVER['argv'][1], $_SERVER['argv'][3]), ADODB_FETCH_ASSOC);
    else if ($type == 'personal') {
        if (isAssignmentForGroup($_SERVER['argv'][0], $sysSession->course_id, 'peer')) {
            /*
            // 修改抓取群組的部分 by Small 2006/12/26
            list($group_id) = dbGetStSr('WM_student_div', 'group_id', "username='$sysSession->username' and course_id='$sysSession->course_id'", ADODB_FETCH_NUM);
            $arr_student = dbGetCol('WM_student_div', 'username', "course_id='$sysSession->course_id' and group_id='$group_id'");
            $group_student = "('".implode("','",$arr_student)."')";
            $row = dbGetStSr('WM_qti_peer_result', 'score, comment, content, ref_url, comment_txt', sprintf('exam_id=%u and time_id=%d and examinee in '.$group_student, $_SERVER['argv'][0], $_SERVER['argv'][1]), ADODB_FETCH_ASSOC);
            */
            $row = getRecordOfAssignmentForGroup($_SERVER['argv'][0], $_SERVER['argv'][3], $sysSession->course_id, 'peer');
        } else
            $row = dbGetStSr('WM_qti_peer_result', 'examinee, score, comment, content, ref_url, comment_txt', sprintf('exam_id=%u and time_id=%d and examinee="%s"', $_SERVER['argv'][0], $_SERVER['argv'][1], $_SERVER['argv'][3]), ADODB_FETCH_ASSOC);
    }

    if (!$row) {
        $errMsg = $sysConn->ErrorMsg();
        wmSysLog($sysSession->cur_func, $sysSession->course_id, $_SERVER['argv'][0], 2, 'auto', $_SERVER['PHP_SELF'], $errMsg);
        die($errMsg);
    }

    $ref_url = $row['ref_url'];


    if (!defined('QTI_env'))
        list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
    else
        $topDir = QTI_env;

    $course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;
    // list($content,$ref_url) = dbGetStSr('WM_qti_peer_result', 'content,ref_url', sprintf('exam_id=%u and examinee="%s" and time_id=%d', $_SERVER['argv'][0], $sysSession->username, $_SERVER['argv'][1]));

    if ($topDir == 'academic')
        $saved_path = sprintf(sysDocumentRoot . '/base/%05d/%s/A/%09u/%s/ref/%09u/', $sysSession->school_id, QTI_which, $_SERVER['argv'][0], $row['examinee'], // $_SERVER['argv'][3],
            $_SERVER['argv'][1]);
    else
        $saved_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09u/%s/ref/%09u/', $sysSession->school_id, $sysSession->course_id, QTI_which, $_SERVER['argv'][0], $row['examinee'], // $_SERVER['argv'][3],
            $_SERVER['argv'][1]);

    // 老師批改的附檔
    $saved_uri = substr($saved_path, strlen(sysDocumentRoot));
    $ref_files = '';
    if ($d = @dir($saved_path)) {
        while (false !== ($entry = $d->read())) {
            if (is_file($saved_path . $entry)) {
                $ref_files .= genFileLink($saved_uri, $entry);
            }
        }
        $d->close();
    }

    //作業繳交的附檔
    $homework_file_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09u/%s/', $sysSession->school_id, $sysSession->course_id, QTI_which, $_SERVER['argv'][0], $row['examinee']); // $_SERVER['argv'][3]);
    $homework_uri       = substr($homework_file_path, strlen(sysDocumentRoot));
    $homework_files     = '';
    if ($d = @dir($homework_file_path)) {
        while (false !== ($entry = $d->read())) {
            if (is_file($homework_file_path . $entry)) {
                $homework_files .= genFileLink($homework_uri, $entry);
            }
        }
        $d->close();
    }

    $comment = nl2br(htmlspecialchars($row['comment_txt']));

    showXHTML_tabFrame_B(array(
            array(
                $MSG['ref_data'][$sysSession->lang]
            )
        ));
        showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="950" style="border-collapse: collapse" class="box01"');
            showXHTML_tr_B('class="cssTrEvn"');
                showXHTML_td('width="100"', $MSG['total_score'][$sysSession->lang]);
                showXHTML_td('', '&nbsp;');
            showXHTML_tr_E();
            if ($ref_files || $ref_url || $homework_files || $comment) {
                showXHTML_tr_B('class="cssTrOdd"');
                    showXHTML_td('', $MSG['tech_comments'][$sysSession->lang]);
                    showXHTML_td('', $comment);
                showXHTML_tr_E();

                showXHTML_tr_B('class="cssTrEvn"');
                    showXHTML_td('', $MSG['attach_homework'][$sysSession->lang]);
                    showXHTML_td('', $homework_files);
                showXHTML_tr_E();
            }
        showXHTML_table_E();
    showXHTML_tabFrame_E();

    // 準備好夾檔
    if ($xmlDoc = domxml_open_mem($row['content'])) {
        $ids   = array();
        $nodes = $xmlDoc->get_elements_by_tagname('item');
        foreach ($nodes as $item)
            $ids[] = $item->get_attribute('ident');
        if ($ids) {
            $idents = 'ident in ("' . implode('","', $ids) . '")';
            $a      = dbGetAssoc('WM_qti_' . QTI_which . '_item', 'ident, attach', $idents);
            foreach ($a as $k => $v)
                if (preg_match('/^a:[0-9]+:{/', $v))
                    $GLOBALS['attachments'][$k] = unserialize($v);
        }
    }

    ob_start();
    parseQuestestinterop($row['content']);
    ob_end_clean();

    if ($row['score'] == '')
        showXHTML_script('inline', "
            var ss = document.getElementsByTagName('input');
            var total_score = 0.0;
            for (var i = 0; i < ss.length; i++) {
                if (ss[i].type == 'text' && ss[i].name.indexOf('item_scores[') === 0) total_score += parseFloat(ss[i].value);
            }
            var tb = document.getElementsByTagName('table')[2];
            tb.rows[0].cells[1].innerHTML = total_score;
    ");
    else
        showXHTML_script('inline', "document.getElementsByTagName('table')[2].rows[0].cells[1].innerHTML = '{$row['score']}';");

    showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="950" style="border-collapse: collapse; margin-top: 0.4em;" class="box01"');
        showXHTML_tr_B('class="cssTrEvn" align="center"');
            showXHTML_td_B('');
            if ($type == 'exemplar')
                showXHTML_input('button', '', $MSG['go_back'][$sysSession->lang], '', 'align="center" class="cssBtn" onclick="exemplar_list();"');
            showXHTML_input('button', '', $MSG['close'][$sysSession->lang], '', 'class="cssBtn" onclick="window.close();"');
            showXHTML_td_E();
        showXHTML_tr_E();
    showXHTML_table_E();

showXHTML_body_E();