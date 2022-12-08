<?php

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lib/attach_link.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/username.php');
require_once(sysDocumentRoot . '/lib/group_assignment_lib.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');

$sysSession->cur_func = '1700400300';
$sysSession->restore();
if (!aclVerifyPermission(1700400300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

define('QTI_DISPLAY_ANSWER', true);
define('QTI_DISPLAY_OUTCOME', true);
define('QTI_DISPLAY_RESPONSE', true);
require_once(sysDocumentRoot . '/teach/exam/exam_preview.php');
header('Content-type: text/html'); // 因為 exam_preview.php 會輸出 text/xml header 所以在此糾正回來 #1148

showXHTML_head_B($MSG['check_result'][$sysSession->lang]);
showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
if ($profile['isPhoneDevice']) {
            echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
            echo '<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">';
            echo '<link href="/theme/default/bootstrap336/css/bootstrap.min.css" rel="stylesheet" />';
            echo '<link rel="stylesheet" href="/sys/tpl/vendor/font-awesome/css/font-awesome.css" />';
            echo '<script type="text/javascript" src="/lib/jquery/jquery.new.min.js?20200113"></script>';
            echo '<script type="text/javascript" src="/theme/default/bootstrap336/js/bootstrap.min.js"></script>';
            require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
            $smarty->display('phone/learn/exam_style.tpl');
        }
// MIS#21298 by Small 2011/6/8
$ticket = md5(sysTicketSeed . $_SERVER['argv'][0] . $_SERVER['argv'][1] . $_COOKIE['idx']);
$url    = 'exemplar_list.php?' . sprintf('%s+%s+%s', $_SERVER['argv'][0], $_SERVER['argv'][1], $ticket);
// $url = 'exemplar_list.php?' . sprintf('%s+%s+%s', $_SERVER['argv'][0], $_SERVER['argv'][1], $_SERVER['argv'][2]);
showXHTML_script('inline', "
function exemplar_list() {
    location.href = '{$url}';
}
    ");
showXHTML_head_E();
showXHTML_body_B();

if ($_SERVER['argv'][2] != md5(sysTicketSeed . $_SERVER['argv'][0] . $_SERVER['argv'][1] . $_SERVER['argv'][3] . $_COOKIE['idx'])) {
    wmSysLog($sysSession->cur_func, $sysSession->course_id, 0, 1, 'auto', $_SERVER['PHP_SELF'], 'Fake Ticket!');
    die('Fake ticket !');
}

$res = checkUsername($_SERVER['argv'][3]);
// 帳號使用中才可以觀看（不含保留帳號）
if ($res != 2) {
    wmSysLog($sysSession->cur_func, $sysSession->course_id, 0, 1, 'auto', $_SERVER['PHP_SELF'], 'Access Deny!');
    die('Access Deny !');
}

// 判斷是觀摩佳作或者是觀看個人作業結果
$type = $_SERVER['argv'][4];
if ($type == 'exemplar')
    $row = dbGetStSr('WM_qti_homework_result', 'examinee, score, comment, content, ref_url, status', sprintf('exam_id=%u and time_id=%d and examinee="%s" and status="publish"', $_SERVER['argv'][0], $_SERVER['argv'][1], $_SERVER['argv'][3]), ADODB_FETCH_ASSOC);
else if ($type == 'personal') {
    if (isAssignmentForGroup($_SERVER['argv'][0])) {
        /*
        // 修改抓取群組的部分 by Small 2006/12/26
        list($group_id) = dbGetStSr('WM_student_div', 'group_id', "username='$sysSession->username' and course_id='$sysSession->course_id'", ADODB_FETCH_NUM);
        $arr_student = dbGetCol('WM_student_div', 'username', "course_id='$sysSession->course_id' and group_id='$group_id'");
        $group_student = "('".implode("','",$arr_student)."')";
        $row = dbGetStSr('WM_qti_homework_result', 'score, comment, content, ref_url', sprintf('exam_id=%u and time_id=%d and examinee in '.$group_student, $_SERVER['argv'][0], $_SERVER['argv'][1]), ADODB_FETCH_ASSOC);
        */
        $row = getRecordOfAssignmentForGroup($_SERVER['argv'][0], $_SERVER['argv'][3]);
    } else
        $row = dbGetStSr('WM_qti_homework_result', 'examinee, score, comment, content, ref_url, status', sprintf('exam_id=%u and time_id=%d and examinee="%s"', $_SERVER['argv'][0], $_SERVER['argv'][1], $_SERVER['argv'][3]), ADODB_FETCH_ASSOC);
}

if (isAssignmentForGroup($_SERVER['argv'][0])) {
    // 取本作業的設定的分組
    $examinee_perm = array(
        'homework' => 1700400200,
        'peer' => 1710400200,
        'exam' => 1600400200,
        'questionnaire' => 1800300200
    );
    $team_id       = $sysConn->GetOne(sprintf("SELECT DISTINCT SUBSTRING(member, 2, 1) FROM WM_acl_list LEFT JOIN WM_acl_member ON WM_acl_member.acl_id = WM_acl_list.acl_id WHERE function_id = '%s' and unit_id = '%s' and instance = '%s'", $examinee_perm[QTI_which], $sysSession->course_id, $_SERVER['argv'][0]));
    list($group_id) = dbGetStSr('WM_student_div', 'group_id', "username='{$_SERVER['argv'][3]}' and course_id='$sysSession->course_id' and team_id='$team_id'", ADODB_FETCH_NUM);
    $arr_student   = dbGetCol('WM_student_div', 'username', "course_id='$sysSession->course_id' and group_id='$group_id' AND team_id='$team_id'");
    $group_student = "('" . implode("','", $arr_student) . "')";
    $is_group      = true;
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
// list($content,$ref_url) = dbGetStSr('WM_qti_homework_result', 'content,ref_url', sprintf('exam_id=%u and examinee="%s" and time_id=%d', $_SERVER['argv'][0], $sysSession->username, $_SERVER['argv'][1]));

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
// 群組
if ($is_group) {
    $homework_files = '';
    // 群組學員列表
    foreach ($arr_student as $username) {
        $homework_file_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09u/%s/', $sysSession->school_id, $sysSession->course_id, QTI_which, $_SERVER['argv'][0], $username); // $_SERVER['argv'][3]);
        $homework_uri       = substr($homework_file_path, strlen(sysDocumentRoot));
        // 該學員姓名
        preg_match('/A\/\d*\/(.*)\/$/', $homework_uri, $matches);
        $RS       = dbGetStSr('WM_user_account', 'first_name, last_name', "username='" . $matches[1] . "'", ADODB_FETCH_ASSOC);
        $realname = checkRealname($RS['first_name'], $RS['last_name']);
        $homework_files .= '<div style="font-weight: bold; margin-top: 1em; font-size: 1.1em;">' . $realname . ' (' . $matches[1] . ')</div>';
        if ($d = @dir($homework_file_path)) {
            while (false !== ($entry = $d->read())) {
                if (is_file($homework_file_path . $entry)) {
                    // 該學員上傳的檔案列表
                    $homework_files .= '<span style="margin-left: 1em;">' . genFileLink($homework_uri, $entry) . '</span>';
                }
            }
            $d->close();
        }
    }
    // 個人
} else {
    $homework_file_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09u/%s/', $sysSession->school_id, $sysSession->course_id, QTI_which, $_SERVER['argv'][0], $_SERVER['argv'][3]); // $_SERVER['argv'][3]);
    $homework_uri       = substr($homework_file_path, strlen(sysDocumentRoot));
    $homework_files     = '';
    if ($d = @dir($homework_file_path)) {
        $homework_files .= $username . '<BR>';
        while (false !== ($entry = $d->read())) {
            if (is_file($homework_file_path . $entry)) {
                $homework_files .= genFileLink($homework_uri, $entry);
            }
        }
        $d->close();
    }
}
$comment = $row['comment'];

// 觀看結果
if (isset($_SERVER['argv'][5]) === false) {
    echo '<div align="center">';
    
        showXHTML_tabFrame_B(array(
            array(
                $MSG['ref_data'][$sysSession->lang]
            )
        ));
            showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="'.(($profile['isPhoneDevice'])?'100%':'996').'" class="cssTable"');
        showXHTML_tr_B('class="cssTrEvn" id="total_score"');
                    showXHTML_td(($profile['isPhoneDevice'])?'width="50%"':'width="100"', $MSG['total_score'][$sysSession->lang]);
                    showXHTML_td('', '&nbsp;');
                showXHTML_tr_E();
                
                if ($ref_files || $ref_url || $homework_files || $comment) {
                    showXHTML_tr_B('class="cssTrOdd"');
                        showXHTML_td('', $MSG['reference_file'][$sysSession->lang]);
                        showXHTML_td('', $ref_files);
                    showXHTML_tr_E();

                    showXHTML_tr_B('class="cssTrEvn"');
                        showXHTML_td('', $MSG['reference_url'][$sysSession->lang]);
                        showXHTML_td('', sprintf('<a href="%s" target="_blank">%s</a>', $ref_url, $ref_url));
                    showXHTML_tr_E();

                    showXHTML_tr_B('class="cssTrOdd"');
                        showXHTML_td('', $MSG['tech_comments'][$sysSession->lang]);
                        showXHTML_td('', $comment);
                    showXHTML_tr_E();

                }
            showXHTML_table_E();
        showXHTML_tabFrame_E();
    echo '</div>';
}

echo '<div style="height: 1em;"></div>';
// 繳交作業後的結果畫面
// 檔案列表
$setting = dbGetOne('WM_qti_' . QTI_which . '_test', 'setting', 'exam_id=' . $_SERVER['argv'][0], ADODB_FETCH_ASSOC);
if (strpos($setting, 'upload') !== FALSE) {
echo '<div align="center">';
showXHTML_tabFrame_B(array(
    array(
        $MSG['uploaded_files'][$sysSession->lang]
    )
));

    showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="'.(($profile['isPhoneDevice'])?'100%':'996').'" class="cssTable"');

    showXHTML_tr_B('class="cssTrHead"');
        showXHTML_td('class="cssTd" align="center"', $MSG['serial_no'][$sysSession->lang]);
        if ($is_group) {
            showXHTML_td('class="cssTd" align="center"', $MSG['uploaded_man'][$sysSession->lang]);
        }
        showXHTML_td('class="cssTd" width="200" align="center"', $MSG['uploaded_time'][$sysSession->lang]);
        showXHTML_td('class="cssTd"', $MSG['uploaded_filename'][$sysSession->lang]);
        showXHTML_td('class="cssTd" width="100" align="right"', $MSG['uploaded_filesize'][$sysSession->lang]);
    showXHTML_tr_E();

    // 顯示自己上傳的檔案列表
    $homework_file_path   = array();
    $homework_file_path[] = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09u/', $sysSession->school_id, $sysSession->course_id, QTI_which, $_SERVER['argv'][0]);
    $i                    = 1;
    // 因應未來要顯示全部組員，先用陣列
    foreach ($homework_file_path as $v) {
        if ($d = @dir($v)) {
            while (false !== ($entry = $d->read())) {
                if ($entry == '.' || $entry == '..')
                    continue;
                if ($d1 = @dir($v . $entry . '/')) {
                    while (false !== ($entry1 = $d1->read())) {
                        if (is_file($v . $entry . '/' . $entry1)) {
                            if (($entry!=$_SERVER['argv'][3] && !$is_group) || ($is_group && !in_array($entry,$arr_student))) continue;
                            showXHTML_tr_B('class="cssTrEvn"');
                                // 序號
                                showXHTML_td('class="cssTd" align="center"', $i);

                                // 上傳者
                                if ($is_group) {

                                    $RS       = dbGetStSr('WM_user_account', 'first_name, last_name', "username='" . $entry . "'", ADODB_FETCH_ASSOC);
                                    $realname = checkRealname($RS['first_name'], $RS['last_name']);
                                    showXHTML_td('class="cssTd" align="left"', $realname . ' (' . $entry . ')');
                                }
                                $saved_uri = substr($v . $entry . '/', strlen(sysDocumentRoot));
                                // 上傳時間
                                showXHTML_td('class="cssTd" align="center"', date("Y/m/d H:i:s", filemtime(sysDocumentRoot . $saved_uri . $entry1)));

                                // 上傳檔名
                                $filesize = @filesize(sysDocumentRoot . $saved_uri . $entry1);
                                showXHTML_td('class="cssTd"', sprintf('<a target="_blank" href="%s" class="cssAnchor">%s</a>', $saved_uri . $entry1, $entry1) . (($filesize === 0) ? '&nbsp;<span style="color: red;">(' . $MSG['file_content_blank'][$sysSession->lang] . ')</span>' : ''));
                                if ($filesize === 0) {
                                    $filesizeColor = 'red';
                                } else {
                                    $filesizeColor = 'black';
                                }

                                // 檔案大小
                                showXHTML_td('class="cssTd" align="right"', '<span style="color: ' . $filesizeColor . ';">' . FileSizeConvert($filesize) . '</span>');

                                // （排版好看用）

                            showXHTML_tr_E();
                            $i++;
                        }
                    }
                }
            }
            $d->close();
        }
    }

    showXHTML_table_E();
showXHTML_tabFrame_E();
echo '</div>';
}
echo '<div style="height: 1em;"></div>';

if (empty($row['content']))
    {
       $xml_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/C/%09u/%s/',
                               $sysSession->school_id,
                               $sysSession->course_id,
                               QTI_which,
                               $_SERVER['argv'][0],
                               $_SERVER['argv'][3]);
       $file =     $_SERVER['argv'][1].'.xml';          

       $full_path = $xml_path.$file;
       if (is_file($full_path)) {
           $row['content'] = file_get_contents($full_path);
       }
    }
$row['content'] = str_replace(array(
    "\n",
    "\r"
), array(
    '',
    ''
), $row['content']);
echo '<div align="center">';
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

$exam_id  = $_SERVER['argv'][0];
$time_id  = $_SERVER['argv'][1];
$examinee = $row['examinee']; // $_SERVER['argv'][3];
ob_start();

if (!$pathExist = @domxml_open_mem($row['content'])) {
    return;
}
$ctxExist = xpath_new_context($pathExist);
xpath_register_ns($ctxExist, "xml", "");
$retExist   = $ctxExist->xpath_eval("/questestinterop/item");
$identExist = array();
if ($retExist) {
    foreach ($retExist->nodeset as $resExist) {
        $identExist[] = $resExist->get_attribute('ident');
    }
}

// 作業有出題才顯示
if (is_array($identExist) && count($identExist) >= 1) {
    parseQuestestinterop($row['content']);
    echo '<div style="height: 1em;"></div>';
}

$exam_content = ob_get_contents();
ob_end_clean();
echo preg_replace(array('/<form [^>]* action="save_answer.php".*<!/isU', '<<br/>>'), array('<form style="display: inline"><!', ''), $exam_content);
    
if ($row['status'] === 'break' || $row['status'] === 'submit') {
    $m6 = 0;
} else {
    $m6 = 1;
}

if ($row['score'] == '')
    showXHTML_script('inline', "
        var ss = document.getElementsByTagName('input');
        var status  = '{$m6}';
        var total_score = 0.0;
        for(var i=0; i<ss.length; i++)
        {
        if (ss[i].type=='text' && ss[i].name.indexOf('item_scores[') === 0) total_score += parseFloat(ss[i].value);
        }
        var tb = document.getElementById('total_score');

        if (status == '0') {
            total_score = '{$MSG['not_yet_revised'][$sysSession->lang]}';
        }
        tb.cells[1].innerHTML = total_score;
");
else
    showXHTML_script('inline', "document.getElementById('total_score').cells[1].innerHTML = '{$row['score']}';");

    showXHTML_table_B('align="center" border="0" cellpadding="3" cellspacing="1" width="'.(($profile['isPhoneDevice'])?'100%':'996').'" style="border-collapse: collapse"');
    showXHTML_tr_B('align="center"');
        showXHTML_td_B('');
            if ($type == 'exemplar')
                showXHTML_input('button', '', $MSG['go_back'][$sysSession->lang], '', 'align="center" style="background-color:#05ABAB;border-radius:3px;height:2.5em;width:6.5em;font-size:16px;" onclick="exemplar_list();"');
            if (isset($_SERVER['argv'][5]) === true) {
                if ($profile['isPhoneDevice']) {
                    showXHTML_input('button', '', $MSG['close'][$sysSession->lang], '', 'style="background-color:#05ABAB;border-radius:3px;height:2.5em;width:6.5em;font-size:16px;" onclick="window.close();"');
                } else {
                    showXHTML_input('button', '', $MSG['back_list'][$sysSession->lang], '', 'style="background-color:#05ABAB;border-radius:3px;height:2.5em;width:6.5em;font-size:16px;" onclick="location.href=\'/learn/homework/homework_list.php\';"');
                }
            } else {
                showXHTML_input('button', '', $MSG['close'][$sysSession->lang], '', 'style="background-color:#05ABAB;border-radius:3px;height:2.5em;width:6.5em;font-size:16px;" onclick="window.close();"');
            }
        showXHTML_td_E();
    showXHTML_tr_E();
showXHTML_table_E();
echo '</div>';
showXHTML_body_E();
