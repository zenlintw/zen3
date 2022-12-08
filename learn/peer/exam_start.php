<?php
/**************************************************************************************************
 *                                                                                                *
 *		Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
 *                                                                                                *
 *		Programmer: Wiseguy Liang                                                         *
 *		Creation  : 2003/03/21                                                            *
 *		work for  : list all available exam(s)                                            *
 *		work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
 *                                                                                                *
 **************************************************************************************************/

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lib/exam_lib.php');
require_once(sysDocumentRoot . '/lang/' . QTI_which . '_learn.php');
require_once(sysDocumentRoot . '/lib/attach_link.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/quota.php');
if ((QTI_which === 'homework' || QTI_which === 'peer'))
    include_once(sysDocumentRoot . '/lib/group_assignment_lib.php');

$sysSession->cur_func = (QTI_which == 'homework' ? 1700400200 : 1800300200);
$sysSession->restore();
$course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id;
$isForTA   = isset($_SERVER['argv'][3]) && $_SERVER['argv'][3] == '1' && aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['assistant'] | $sysRoles['instructor'], $sysSession->course_id);

if (!$isForTA && !defined('forGuestQuestionnaire') && !aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable'), $course_id, intval($_SERVER['argv'][0]))) {
    echo <<< EOB
<h2 align="center">Permission Denied.</h2>
<p align="center"><form><input type="button" value="return previous page" onclick="history.back();" /></form></p>

EOB;
    exit;
}

if ($_SERVER['argc'] < 3)
    die('Arguments Error!.'); // 沒有三個參數則執行失敗
if (!ereg('^[0-9]+$', $_SERVER['argv'][0]) || // 檢查 exam_id 格式
    !ereg('^[0-9]+$', $_SERVER['argv'][1]) || // 檢查 times_id 格式
    !eregi('^[a-z0-9]{32}$', $_SERVER['argv'][2]) // 檢查 ticket 格式
    ) {
    wmSysLog($sysSession->cur_func, $sysSession->course_id, 0, 1, 'classroom', $_SERVER['PHP_SELF'], 'Argument format incorrect!');
    die('Argument format incorrect.');
}
$ticket = md5(sysTicketSeed . $_SERVER['argv'][0] . $_SERVER['argv'][1] . $_COOKIE['idx']);
if ($ticket != $_SERVER['argv'][2]) { // 檢查 ticket 正確與否
    wmSysLog($sysSession->cur_func, $sysSession->course_id, 0, 2, 'classroom', $_SERVER['PHP_SELF'], 'Fake Ticket!');
    die('Fake Ticket !');
}

if (isset($_SERVER['argv'][4]) && $_SERVER['argv'][4] == 'phone') {
    $profile['isPhoneDevice']=true;
}

$check_code = md5(uniqid(rand(), true)); // 新 ticket 檢查碼
$ticket     = md5((defined('forGuestQuestionnaire') ? $_SERVER['HTTP_HOST'] : sysTicketSeed) . $_SERVER['argv'][0] . $check_code); // 新 ticket

$f = array();
list($title, $f['modifiable'], $f['begin_time'], $f['close_time'], $setting, $notice, $content) = dbGetStSr('WM_qti_' . QTI_which . '_test', 'title,modifiable,begin_time,close_time,setting,notice,content', 'exam_id=' . $_SERVER['argv'][0], ADODB_FETCH_NUM);
$times = intval($_SERVER['argv'][1]);
if (checkExamWhetherTimeout($f, time(), max(0, $times - 1)) && !$isForTA) {
    die('<script>alert("' . $MSG['msg_exam_close'][$sysSession->lang] . '"); self.close();</script>');
}

if (!$title || $sysConn->ErrorNo()) {
    $errNo  = $sysConn->ErrorNo();
    $errMsg = $sysConn->ErrorMsg();
    wmSysLog($sysSession->cur_func, $sysSession->course_id, $_SERVER['argv'][0], 3, 'classroom', $_SERVER['PHP_SELF'], $errMsg);
    die('ERROR: ' . $errNo . ': ' . $errMsg);
}
if (strpos($title, 'a:') === 0)
    $titles = unserialize($title);
else
    $titles[$sysSession->lang] = title;
if (empty($content)) {
    $examDetail = '<questestinterop />';
} else {
    $examDetail = str_replace(array(
        "'",
        "\n",
        "\r"
    ), array(
        "\\'",
        '',
        ''
    ), $content);
}

if ($isForTA); // 如果是教師試做則不做任何處理
else if ((QTI_which === 'homework' || QTI_which === 'peer') && isAssignmentForGroup($_SERVER['argv'][0], null, QTI_which)) {
    if (isAlreadySubmittedAssignmentForGroup($_SERVER['argv'][0], $sysSession->username, null, QTI_which) && ($group_record = getRecordOfAssignmentForGroup($_SERVER['argv'][0], $sysSession->username, null, QTI_which))) {
        $examDetail = str_replace(array(
            "'",
            "\n",
            "\r"
        ), array(
            "\\'",
            '',
            ''
        ), $group_record['content']);
        dbSet('WM_qti_' . QTI_which . '_result', "examinee='{$sysSession->username}'", "exam_id={$_SERVER['argv'][0]} and examinee='{$group_record['examinee']}' and time_id=1");
        @chdir(sprintf('%s/base/%05u/course/%08u/%s/A/%09u/', sysDocumentRoot, $sysSession->school_id, $sysSession->course_id, QTI_which, $_SERVER['argv'][0])) and @rename($group_record['examinee'], $sysSession->username);
        
        wmSysLog($sysSession->cur_func, $sysSession->course_id, $_SERVER['argv'][0], 0, 'classroom', $_SERVER['PHP_SELF'], QTI_which . ' start(modify group)!');
    } else {
        dbNew('WM_qti_' . QTI_which . '_result', 'exam_id,examinee,time_id,begin_time', "{$_SERVER['argv'][0]},'{$sysSession->username}',1,now()");
        wmSysLog($sysSession->cur_func, $sysSession->course_id, $_SERVER['argv'][0], 0, 'classroom', $_SERVER['PHP_SELF'], QTI_which . ' start(new group)!');
    }
} elseif (QTI_which != 'questionnaire' || !aclCheckWhetherForGuestQuest($course_id, $_SERVER['argv'][0])) {
    list($max_times) = dbGetStSr('WM_qti_' . QTI_which . '_result', 'max(time_id)', "exam_id={$_SERVER['argv'][0]} and examinee='{$sysSession->username}'", ADODB_FETCH_NUM);
    if (empty($max_times)) {
        dbNew('WM_qti_' . QTI_which . '_result', 'exam_id,examinee,time_id,begin_time', "{$_SERVER['argv'][0]},'{$sysSession->username}',1,now()");
        wmSysLog($sysSession->cur_func, $sysSession->course_id, $_SERVER['argv'][0], 0, 'classroom', $_SERVER['PHP_SELF'], QTI_which . ' start(new)!');
    } else {
        list($response) = dbGetStSr('WM_qti_' . QTI_which . '_result', 'content', "exam_id={$_SERVER['argv'][0]} and examinee='{$sysSession->username}' and time_id=1 and status!='break'", ADODB_FETCH_NUM);
        if (!empty($response))
            $examDetail = str_replace(array(
                "'",
                "\n",
                "\r"
            ), array(
                "\\'",
                '',
                ''
            ), $response);
        wmSysLog($sysSession->cur_func, $sysSession->course_id, $_SERVER['argv'][0], 0, 'classroom', $_SERVER['PHP_SELF'], QTI_which . ' start(modify)!');
    }
}

$QTI_which = QTI_which;

$school_q = $sysConn->GetOne("select course_id={$sysSession->school_id} from WM_qti_{$QTI_which}_test where exam_id={$_SERVER['argv'][0]}") ? '?school' : '';
$st_kind  = $school_q ? $sysSession->school_id : $sysSession->course_id;

$isQuotaExceed = getRemainQuota($st_kind) <= 0 ? 1 : 0;
$msgQuota      = str_replace('%TYPE%', $MSG[$school_q ? 'school' : 'course'][$sysSession->lang], $MSG['quota_full'][$sysSession->lang]);

// 開始產生 HTML
showXHTML_head_B($MSG[QTI_which . '_title'][$sysSession->lang]);
if ($profile['isPhoneDevice']) {
    echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
    echo '<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">';
    echo '<link href="/theme/default/bootstrap336/css/bootstrap.min.css" rel="stylesheet" />';
    echo '<link rel="stylesheet" href="/sys/tpl/vendor/font-awesome/css/font-awesome.css" />';
}
showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
showXHTML_script('include', '/lib/xmlextras.js');
showXHTML_script('include', '/lib/anicamWB/WMRecorder.js');
showXHTML_script('include', '/lib/jquery/jquery.min.js', true, null, 'UTF-8');
if (!defined('forGuestQuestionnaire')) {
    $xajax_save_temp->printJavascript('/lib/xajax/');
    $stemp = <<< EOB
    xajax_check_temp(st_id, 'homeworkForm');
	window.setInterval(function(){xajax_save_temp(st_id, document.getElementById('homeworkForm').innerHTML);}, 100000);
EOB;
} else
    $forQ = empty($school_q) ? '?+1' : '+1';

echo '<style>';
echo '
	@media (max-width: 767px) {
        .cssBtn {
            height: 24px;
            line-height: 12px;
            margin: 5px;
        }

        body > table {
            margin: 0 auto;
            width: 96%;
        }

        #homeworkInnerTable {
            width: 100%;
        }

        input[type=file] {
            max-width: 220px;
        }

        textarea {
            width: 80%;
        }
        
        .cke_textarea_inline {
            width: 80%;
        }
    }
';
echo '</style>';    
    
showXHTML_script('inline', "

var objTable;
var examDetail = XmlDocument.create();
examDetail.loadXML('$examDetail ');
var st_id = '{$sysSession->cur_func}{$st_kind}{$_SERVER['argv'][0]}';
var course_id = '{$sysSession->course_id}';
var msg_upload_file_disabled = '{$MSG['msg_upload_file_disabled'][$sysSession->lang]}';

window.onload = function() {
    rm_whitespace(document.body);
    objTable = document.getElementsByTagName('table')[2];
    prevExamPaper();

    /*
    if (typeof Touch !== 'undefined') {
        var node = null;
        node = document.getElementById('btnMoreAtt');
        if (node !== null && navigator.userAgent.search('iPad') < 0 && navigator.userAgent.search('Chrome') < 0) {
            node.style.display = 'none';
        }
        node = document.getElementById('uploadFile');
        if (node !== null && navigator.userAgent.search('iPad') < 0 && navigator.userAgent.search('Chrome') < 0) {
            node.innerHTML = msg_upload_file_disabled;
        }
        node = document.getElementById('uploadFileMsg');
        if (node !== null) {
            node.innerHTML = '';
        }
    }
   */
   
    if ({$isQuotaExceed})
        alert('{$msgQuota}');

    {
        $stemp
    }
};

function rm_whitespace(node) {
    var nodes = node.childNodes;
    for (var i = nodes.length - 1; i >= 0; i--) {
        if (nodes[i].nodeType == 3 && nodes[i].nodeValue.search(/^\s+$/) == 0)
            node.removeChild(nodes[i]);
        else if (nodes[i].nodeType == 1)
            rm_whitespace(nodes[i]);
    }
}

/**
 * generate homework
 */
function prevExamPaper() {
    var xmlHttp = XmlHttp.create();
    xmlHttp.open('POST', '{$forGuestRedir}{$QTI_which}_display.php{$school_q}{$forQ}', false);
    var ret = xmlHttp.send(examDetail);
    if (ret == false)
        alert('false');
    else {
        var tmp1 = document.getElementById('tempDisplayPanel');
        var tmp2 = document.getElementById('homeworkDisplayPanel');

        if (xmlHttp.responseText.match(/<form[^>]*>([^\\x00]*)<\/form>/i) == null)
            tmp2.innerHTML = tmp2.innerHTML + '<input type=\"hidden\" name=\"exam_id\"><input type=\"hidden\" name=\"time_id\"><input type=\"hidden\" name=\"ticket\">';
        else
            tmp2.innerHTML = RegExp.$1.replace(/\/00000000\//g, '/' + course_id + '/');
        var objs = tmp2.getElementsByTagName('object');
        for (var i = 0; i < objs.length; i++)
            if (objs[i].codeBase.indexOf('AnicamWebSoundRec.cab') > -1) setupRecorder(objs[i]);

        var tmp3 = document.getElementById('homeworkForm');
        tmp3.exam_id.value = '{$_SERVER['argv'][0]}';
        tmp3.time_id.value = '{$check_code}';
        tmp3.ticket.value = '{$ticket}';
    }
}

function remove_file(span) {
    if (span.previousSibling == null && span.nextSibling == null) {
        var newNode = span.cloneNode(true);
        /* #55972 FireFox 複製後值仍保留 */
        newNode.getElementsByTagName('input')[0].value = '';
        span.parentNode.replaceChild(newNode, span);
        return;
    }

    span.parentNode.removeChild(span);
}

function more_file(td) {
    td.appendChild(td.lastChild.cloneNode(true));
    try {
        td.lastChild.getElementsByTagName(\"input\")[0].value = \"\";
        }
        catch (ex) {}
    }

    function examOver() {
        var mainForm = document.getElementById('homeworkForm');
        mainForm.action += '?over' + ('{$school_q}' == '?school' ? '+school' : '');
        mainForm.submit();
    }

    if (typeof(xajax_clean_temp) == 'undefined') xajax_clean_temp = function(id) {};

    var recorderIndex = 0;

    function setupRecorder(rec) {
        if (!{$isQuotaExceed}) {
            var listObj = 'MyRecList' + recorderIndex;
            var recObj = 'MyRecorder' + recorderIndex;

            eval(listObj + ' = new RecorderList();' +
                listObj + '.OutputTo(rec.previousSibling.previousSibling.previousSibling.previousSibling);' +
                recObj + ' = new WMRecorder();' +
                recObj + '.objname = \"' + recObj + '\";' +
                recObj + '.SetPostURI(\"http://\" + window.location.host + \"/lib/anicamWB/anicam_upload.php\");' +
                recObj + '.SetPostData(document.cookie, \"999999999\", \"10000001\");' +
                recObj + '.SetRecFile(\"temp$$$.mp3\");' +
                recObj + '.SetTimeLimit(2);' +
                recObj + '.AttachButton(rec.previousSibling.previousSibling.previousSibling,rec.previousSibling.previousSibling,rec.previousSibling);' +
                recObj + '.AttachComponent(rec);' +
                listObj + '.AddRecorder(' + recObj + ');');
            recorderIndex++;
        }
    }

    function checkData() {
        var existsFiles = $('#homeworkForm table input[name=\"rm_files[]\"]').length;
        var rmFiles = $('#homeworkForm table input[name=\"rm_files[]\"]:checked').length;

        var noPath = true;
        for (i = 0, j = $('#homeworkForm table input[type=file]').length; i < j; i = i + 1) {
            if ($('#homeworkForm table input[type=file]').eq(i).val() >= '0') {
                noPath = false;
            }
        }

        if (existsFiles - rmFiles === 0 && noPath === true) {
            alert('{$MSG['a_file_at_least'][$sysSession->lang]}');
            return false;
        }

        if (confirm('{$MSG['are_you_sure_to_submit'][$sysSession->lang]}')) {
            xajax_clean_temp(st_id);
        } else {
            return false;
        }
    }

", false);
        $ary = array();
showXHTML_head_E();
showXHTML_body_B();
$ary[] = array(
    $MSG[QTI_which . '_title'][$sysSession->lang],
    'tabsSet',
    ''
);
showXHTML_tabFrame_B($ary, 1, 'homeworkForm', null, 'style="display: inline" method="POST" enctype="multipart/form-data" action="save_answer.php" onsubmit="return checkData();"');
showXHTML_input('hidden', 'isForTA', $isForTA ? '1' : '0'); // 判斷是否教師試做
showXHTML_table_B('id="homeworkInnerTable" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse;" class="cssTable"');
showXHTML_tr_B('class="cssTrHead"');
showXHTML_td('nowrap', $MSG['exam_name'][$sysSession->lang]);
showXHTML_td('colspan="2" id="homeworkDisplayPanel"', $titles[$sysSession->lang]);
showXHTML_tr_E();

if ($school_q) {
    $save_path = sprintf(sysDocumentRoot . '/base/%05d/%s/A/%09u/%s/', $sysSession->school_id, QTI_which, $_SERVER['argv'][0], $sysSession->username);
} else {
    $save_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09u/%s/', $sysSession->school_id, $sysSession->course_id, QTI_which, $_SERVER['argv'][0], $sysSession->username);
}
$save_uri = substr($save_path, strlen(sysDocumentRoot));
if ($d = @dir($save_path)) {
    showXHTML_tr_B('class="cssTrEvn"');
    showXHTML_td('nowrap', $MSG['file_delete'][$sysSession->lang]);
    showXHTML_td_B('colspan="2"');
    while (false !== ($entry = $d->read())) {
        if (is_file($save_path . $entry)) {
            showXHTML_input('checkbox', 'rm_files[]', rawurlencode($entry));
            echo genFileLink($save_uri, $entry);
        }
    }
    $d->close();
    showXHTML_td_E();
    showXHTML_tr_E();
}

if (strpos($setting, 'upload') !== false) {
    showXHTML_tr_B('class="cssTrOdd"');
    showXHTML_td('nowrap', $MSG['attach_file'][$sysSession->lang]);
    showXHTML_td_B('id="uploadFile"');
    echo '<span>';
    showXHTML_input('file', 'homework_files[]', '', '', 'size="50" class="cssInput"' . ($isQuotaExceed ? ' disabled' : ''));
    echo '&nbsp;';
    showXHTML_input('button', '', $MSG['homework_cede'][$sysSession->lang], '', 'class="cssBtn" onclick="remove_file(this.parentNode);"');
    echo '<br></span>';
    showXHTML_td_E();
    $msgAry = array(
        '%MIN_SIZE%' => '<span style="color: red; font-weight:bold">' . ini_get('upload_max_filesize') . '</span>',
        '%MAX_SIZE%' => '<span style="color: red; font-weight:bold">' . ini_get('post_max_size') . '</span>'
    );
    showXHTML_td('id="uploadFileMsg"', strtr($MSG['attachement_msg'][$sysSession->lang], $msgAry));
    showXHTML_tr_E();
}
showXHTML_tr_B('class="cssTrEvn"');
showXHTML_td('nowrap', '&nbsp;');
showXHTML_td_B('colspan="2"');
if (strpos($setting, 'upload') !== false)
    showXHTML_input('button', '', $MSG['more_file'][$sysSession->lang], '', 'id="btnMoreAtt" class="cssBtn" ' . ($isQuotaExceed ? ' disabled' : 'onclick="more_file(this.parentNode.parentNode.previousSibling.childNodes[1]);"'));
showXHTML_input('submit', '', $MSG['sure_to_submit'][$sysSession->lang], '', 'class="cssBtn"');
echo '<span style="width: 100px"></span>';
showXHTML_input('button', '', $MSG['cancel_exam'][$sysSession->lang], '', 'class="cssBtn" style="color: red" onclick="if (confirm(\'' . $MSG['confirm_end_exam'][$sysSession->lang] . '\')) { xajax_clean_temp(st_id); examOver();}"');
showXHTML_td_E();
showXHTML_tr_E();

showXHTML_table_E();

showXHTML_tabFrame_E();
echo "<div id=\"tempDisplayPanel\" style=\"display: none\"></div>\n";

//list($max_time) = dbGetStSr('WM_qti_' . QTI_which . '_result', 'max(time_id)', "exam_id={$_SERVER['argv'][0]} and examinee='{$sysSession->username}'");