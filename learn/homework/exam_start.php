<?php
    /**************************************************************************************************
     *                                                                                                *
     *        Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
     *                                                                                                *
     *        Programmer: Wiseguy Liang                                                         *
     *        Creation  : 2003/03/21                                                            *
     *        work for  : list all available exam(s)                                            *
     *        work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
     *                                                                                                *
     **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/exam_lib.php');
    require_once(sysDocumentRoot . '/lib/attach_link.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/quota.php');
    require_once(sysDocumentRoot . '/lib/common.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    require_once(sysDocumentRoot . '/lang/' . QTI_which . '_teach.php');
    require_once(sysDocumentRoot . '/lang/' . QTI_which . '_learn.php');
    require_once(sysDocumentRoot . '/lang/files_manager.php');
        
    if (QTI_which == 'homework') include_once(sysDocumentRoot . '/lib/group_assignment_lib.php');

    $sysSession->cur_func = (QTI_which == 'homework' ? 1700400200 : 1800300200);
    $sysSession->restore();
    $course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id;
    $isForTA   = isset($_SERVER['argv'][3]) && $_SERVER['argv'][3] == '1' && aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['assistant'] | $sysRoles['instructor'], $sysSession->course_id);
        $now       = date('Y-m-d H:i:s');

    if (!$isForTA && !defined('forGuestQuestionnaire') && !aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable'), $course_id, intval($_SERVER['argv'][0]))){
        echo <<< EOB
<h2 align="center">Permission Denied.</h2>
<p align="center"><form><input type="button" value="return previous page" onclick="history.back();" /></form></p>

EOB;
        exit;
    }

    if ($_SERVER['argc'] < 3) die('Arguments Error!.');        // 沒有三個參數則執行失敗
    if (!ereg('^[0-9]+$', $_SERVER['argv'][0]) ||            // 檢查 exam_id 格式
        !ereg('^[0-9]+$', $_SERVER['argv'][1]) ||            // 檢查 times_id 格式
        !eregi('^[a-z0-9]{32}$', $_SERVER['argv'][2])        // 檢查 ticket 格式
       ) {
        wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 1, 'classroom', $_SERVER['PHP_SELF'], 'Argument format incorrect!');
        die('Argument format incorrect.');
    }
    $ticket = md5(sysTicketSeed . $_SERVER['argv'][0] . $_SERVER['argv'][1] . $_COOKIE['idx']);
    if ($ticket != $_SERVER['argv'][2]) { // 檢查 ticket 正確與否
       wmSysLog($sysSession->cur_func, $sysSession->course_id , 0 , 2, 'classroom', $_SERVER['PHP_SELF'], 'Fake Ticket!');
       die('Fake Ticket !');
    }
    
    if (isset($_SERVER['argv'][4]) && $_SERVER['argv'][4] == 'phone') {
        $profile['isPhoneDevice']=true;
    }

    $check_code = md5(uniqid(rand(), true));                                            // 新 ticket 檢查碼
    $ticket = md5((defined('forGuestQuestionnaire') ? $_SERVER['HTTP_HOST'] : sysTicketSeed) . $_SERVER['argv'][0] . $check_code);    // 新 ticket

    // MIS#22742 刪除暫存資料 by Small 2011/10/13
    // $sysConn->debug=true;
    dbDel('WM_save_temporary',"locate({$sysSession->cur_func},function_id) and username='{$sysSession->username}'");

    //客製 - 加上補交處理
        $f = array();
        if (QTI_which == 'homework') {
            list($title, $f['modifiable'], $f['begin_time'], $f['close_time'], $f['delay_time'], $setting, $notice, $content) = dbGetStSr('WM_qti_' . QTI_which . '_test', 'title,modifiable,begin_time,close_time,delay_time,setting,notice,content', 'exam_id=' . $_SERVER['argv'][0], ADODB_FETCH_NUM);
        } else {
            list($title, $f['modifiable'], $f['begin_time'], $f['close_time'], $setting, $notice, $content) = dbGetStSr('WM_qti_' . QTI_which . '_test', 'title,modifiable,begin_time,close_time,setting,notice,content', 'exam_id=' . $_SERVER['argv'][0], ADODB_FETCH_NUM);
        }
        
    $times = intval($_SERVER['argv'][1]);
    // QTI類別
    $f['test_type'] = QTI_which;
    if (checkExamWhetherTimeout($f, time(), max(0, $times-1)) && !$isForTA)
    {
        die('<script>alert("' . $MSG['msg_exam_close'][$sysSession->lang] . '"); self.close();</script>');
    }

    if (!$title || $sysConn->ErrorNo()) {
       $errNo = $sysConn->ErrorNo();
       $errMsg = $sysConn->ErrorMsg();
       wmSysLog($sysSession->cur_func, $sysSession->course_id , $_SERVER['argv'][0] , 3, 'classroom', $_SERVER['PHP_SELF'], $errMsg);
       die('ERROR: ' . $errNo . ': ' . $errMsg);
    }
    if (strpos($title, 'a:') === 0)
        $titles = getCaption($title);
    else
        $titles[$sysSession->lang] = title;
    if (empty($content)){
        $examDetail = '<questestinterop />';
    }
    else{
        $examDetail = str_replace(array("'", "\n", "\r"),
                                  array("\\'", '', ''),
                                  $content
                                 );
    }

        $is_group = false;
    if ($isForTA)
        ; // 如果是教師試做則不做任何處理
    else if (QTI_which == 'homework' && isAssignmentForGroup($_SERVER['argv'][0]))
    {
        if (isAlreadySubmittedAssignmentForGroup($_SERVER['argv'][0], $sysSession->username) &&
            ($group_record = getRecordOfAssignmentForGroup($_SERVER['argv'][0], $sysSession->username))
           )
        {
            $examDetail = str_replace(array("'", "\n", "\r"),
                                      array("\\'", '', ''),
                                      $group_record['content']
                                     );
            dbSet('WM_qti_' . QTI_which . '_result',
                  "examinee='{$sysSession->username}'",
                  "exam_id={$_SERVER['argv'][0]} and examinee='{$group_record['examinee']}' and time_id=1"
                 );
            /*@chdir(sprintf('%s/base/%05u/course/%08u/homework/A/%09u/',
                          sysDocumentRoot,
                          $sysSession->school_id,
                          $sysSession->course_id,
                          $_SERVER['argv'][0]
                         )) and
            @rename($group_record['examinee'], $sysSession->username);*/

            wmSysLog($sysSession->cur_func, $sysSession->course_id , $_SERVER['argv'][0] , 0, 'classroom', $_SERVER['PHP_SELF'], QTI_which . ' start(modify group)!');
        }
        else
        {
            dbNew('WM_qti_' . QTI_which . '_result', 'exam_id,examinee,time_id,begin_time',"{$_SERVER['argv'][0]},'{$sysSession->username}',1,now()");
            wmSysLog($sysSession->cur_func, $sysSession->course_id , $_SERVER['argv'][0] , 0, 'classroom', $_SERVER['PHP_SELF'], QTI_which . ' start(new group)!');
        }
                list($group_id) = dbGetStSr('WM_student_div', 'group_id', "username='{$sysSession->username}' and course_id='$sysSession->course_id'", ADODB_FETCH_NUM);
                // 取本作業的設定的分組
                $examinee_perm = array(
                    'homework' => 1700400200,
                    'peer' => 1710400200,
                    'exam' => 1600400200,
                    'questionnaire' => 1800300200
                );
                $team_id       = $sysConn->GetOne(sprintf("SELECT DISTINCT SUBSTRING(member, 2, 1) FROM WM_acl_list LEFT JOIN WM_acl_member ON WM_acl_member.acl_id = WM_acl_list.acl_id WHERE function_id = '%s' and unit_id = '%s' and instance = '%s'", $examinee_perm[QTI_which], $sysSession->course_id, $_SERVER['argv'][0]));
                $arr_student   = dbGetCol('WM_student_div', 'username', "course_id='$sysSession->course_id' and group_id='$group_id' AND team_id='$team_id'");                
                $is_group=true;
    }
    elseif (QTI_which != 'questionnaire' || !aclCheckWhetherForGuestQuest($course_id, $_SERVER['argv'][0]))
    {
        list($max_times) = dbGetStSr('WM_qti_' . QTI_which . '_result', 'max(time_id)', "exam_id={$_SERVER['argv'][0]} and examinee='{$sysSession->username}'", ADODB_FETCH_NUM);
        if (empty($max_times)){
            dbNew('WM_qti_' . QTI_which . '_result', 'exam_id,examinee,time_id,begin_time',"{$_SERVER['argv'][0]},'{$sysSession->username}',1,now()");
            wmSysLog($sysSession->cur_func, $sysSession->course_id , $_SERVER['argv'][0] , 0, 'classroom', $_SERVER['PHP_SELF'], QTI_which . ' start(new)!');
        }
        else
        {
            list($response) = dbGetStSr('WM_qti_' . QTI_which . '_result', 'content', "exam_id={$_SERVER['argv'][0]} and examinee='{$sysSession->username}' and time_id=1 and status!='break'", ADODB_FETCH_NUM);
            if (!empty($response)){
                $response=str_replace('\\','&#92;',$response);
                $examDetail = str_replace(array("'", "\n", "\r"),
                                          array("\\'", '', ''),
                                          $response
                                         );
            }
            wmSysLog($sysSession->cur_func, $sysSession->course_id , $_SERVER['argv'][0] , 0, 'classroom', $_SERVER['PHP_SELF'], QTI_which . ' start(modify)!');
        }
    }

    $QTI_which = QTI_which;

    $school_q = $sysConn->GetOne("select course_id={$sysSession->school_id} from WM_qti_{$QTI_which}_test where exam_id={$_SERVER['argv'][0]}") ? '?school' : '';
    /*BUG(B)#032012 by mars 20140407*/
    $Exam_course_id = $sysConn->GetOne("select course_id from WM_qti_{$QTI_which}_test where exam_id={$_SERVER['argv'][0]}");
    if($Exam_course_id!=$sysSession->course_id){
        $sysSession->course_id = $Exam_course_id;
        $sysSession->restore();
    }
    /*BUG(E)#032012 by mars 20140407*/
    $st_kind = $school_q ? $sysSession->school_id : $sysSession->course_id;

    $isQuotaExceed = getRemainQuota($st_kind) <= 0 ? 1 : 0;
    $msgQuota = str_replace('%TYPE%', $MSG[$school_q ? 'school' : 'course'][$sysSession->lang], $MSG['quota_full'][$sysSession->lang]);

        $attach_ticket = md5(sysTicketSeed . QTI_which . $_COOKIE['idx'] . $_SERVER['argv'][0]);

        if (strpos($setting, 'required') !== false) {
            $need = 'true';
        } else {
            $need = 'false';
        }
        
        /**
         * 檢查路徑 (系統路徑)
         * @param string $base : root path
         * @param string $curr : 要檢查的路徑
         * @return boolean : true : 合法，false : 不合法
         **/
        function checkRealPath($base, $curr)
        {
            return (strpos(realpath($curr), realpath($base)) === 0);
        }

        if ($profile['isPhoneDevice']) {
            $dia_width = '100%';
        } else {
            $dia_width = '400';
        }
        
    // 開始產生 HTML
    showXHTML_head_B($MSG[QTI_which . '_title'][$sysSession->lang]);
    if ($profile['isPhoneDevice']) {
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        echo '<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">';
        echo '<link href="/theme/default/bootstrap336/css/bootstrap.min.css" rel="stylesheet" />';
        echo '<link rel="stylesheet" href="/sys/tpl/vendor/font-awesome/css/font-awesome.css" />';
    }
        showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
        showXHTML_CSS('include', "/lib/jquery/css/jquery-ui-1.8.22.custom.css");
        showXHTML_script('include', '/lib/xmlextras.js');
        showXHTML_script('include', '/lib/anicamWB/WMRecorder.js');
        showXHTML_script('include', '/lib/ckeditor/ckeditor.js');
        showXHTML_script('include', '/lib/jquery/jquery.min.js', true, null, 'UTF-8');
        showXHTML_script('include', '/lib/jquery/jquery-ui-1.8.22.custom.min.js');
      if (!defined('forGuestQuestionnaire'))
      {
          $xajax_save_temp->printJavascript('/lib/xajax/');
          $stemp = <<< EOB
    xajax_check_temp(st_id, 'homeworkForm');
    window.setInterval(function(){xajax_save_temp(st_id, document.getElementById('homeworkForm').innerHTML);}, 100000);
EOB;
      }
      else
        $forQ = empty($school_q ) ? '?+1' : '+1';


echo '<style>';
echo '
    table {
        text-align: left;
    }
    .cke_textarea_inline {
        padding: 10px;
        height: 200px;
        width: 525px;
        overflow: auto;
        border: 1px solid gray;
        -webkit-appearance: textfield;
    }
    .cssTrEvn,
    .cssTrOdd,
    .cssTrHead,
    .cssBtn {
        font-size: 16px;
    }
    
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
    
    showXHTML_script('inline',"

var objTable;
var examDetail = XmlDocument.create();
examDetail.loadXML('$examDetail ');
var st_id = '{$sysSession->cur_func}{$st_kind}{$_SERVER['argv'][0]}';
var course_id = '{$sysSession->course_id}';
var msg_upload_file_disabled = '{$MSG['msg_upload_file_disabled'][$sysSession->lang]}';
var isMobileBrowser = ".(isMobileBrowser()?'true':'false').";
var exec_env = '{$sysSession->env}';
var noBtn = true;
var button_ok_text = '{$MSG['button_ok'][$sysSession->lang]}';
var button_cancel_text = '{$MSG['button_cancel'][$sysSession->lang]}';
var need = '{$need}';
var dia_width='{$dia_width}';
window.onload=function(){
    rm_whitespace(document.body);
    objTable = document.getElementsByTagName('table')[2];
    prevExamPaper();
    
    /*
        if (isMobileBrowser) {
        var node = null;
        node = document.getElementById('btnMoreAtt');
        if (node !== null && navigator.userAgent.search('Chrome') < 0) {
            node.style.display = 'none';
        }
        node = document.getElementById('uploadFile');
        if (node !== null && navigator.userAgent.search('Chrome') < 0) {
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

    {$stemp}
    if (!(navigator.userAgent.match(/iPhone/i)) && window.File && window.FileReader && window.FileList && window.Blob) {
        if ($('#fileUploadTraditional').length === 1) {
            document.getElementById('fileUploadTraditional').style.display = 'none';
        }
        $('#btnMoreAtt').hide();
        $('#fileUploadHtml5').show('slow');
    } else {
        if ($('#fileUploadTraditional').length === 1) {
            document.getElementById('fileUploadTraditional').style.display = 'table-row';
        }
        $('#btnMoreAtt').show();
        document.getElementById('fileUploadHtml5').style.display = 'none';
    }
};

window.onbeforeunload = function() {
    if (noBtn) {
        return '';
    }
};

function rm_whitespace(node){
    var nodes = node.childNodes;
    for(var i=nodes.length-1; i>=0; i--){
        if (nodes[i].nodeType == 3 && nodes[i].nodeValue.search(/^\s+$/) == 0)
            node.removeChild(nodes[i]);
        else if(nodes[i].nodeType == 1)
            rm_whitespace(nodes[i]);
    }
}

/**
 * generate homework
 */
function prevExamPaper()
{
    var xmlHttp = XmlHttp.create();
    xmlHttp.open('POST', '{$forGuestRedir}{$QTI_which}_display.php{$school_q}{$forQ}', false);
    var ret = xmlHttp.send(examDetail);
    if (ret == false)
        alert('false');
    else{
        var tmp1 = document.getElementById('tempDisplayPanel');
        var tmp2 = document.getElementById('homeworkDisplayPanel');
        if (xmlHttp.responseText.match(/<form[^>]*>([^\\x00]*)<\/form>/i) == null)
            tmp2.innerHTML = '<input type=\"hidden\" name=\"exam_id\"><input type=\"hidden\" name=\"time_id\"><input type=\"hidden\" name=\"ticket\">';
        else
            tmp2.innerHTML = RegExp.$1.replace(/\/00000000\//g, '/'+course_id+'/');
        var objs = tmp2.getElementsByTagName('object');
        for(var i=0; i<objs.length; i++)
            if (objs[i].codeBase.indexOf('AnicamWebSoundRec.cab') > -1) setupRecorder(objs[i]);

        var tmp3 = document.getElementById('homeworkForm');
        tmp3.exam_id.value='{$_SERVER['argv'][0]}';
        tmp3.time_id.value='{$check_code}';
        tmp3.ticket.value='{$ticket}';
    }
                
        var ua = window.navigator.userAgent;
        var edge = ua.indexOf('Edge/');
        if (edge <= 0) {
            $('textarea').each(function(index) {
                if ($(this).attr('id') !== undefined) {
                    CKEDITOR.inline($(this).attr('id'), {
                        extraAllowedContent: 'a(documentation);abbr[title];code',
                        toolbar: 'EXAM'
                    });
                }
            });
        }
}

function alert_warning_window() {
    $('#dialog-message').dialog({
        resizable: false,
        draggable: false,
        width: dia_width,
        height: 260,
        modal: true,
        dialogClass: 'dlg-no-close',
        buttons: [{
                text: button_ok_text,
                click: function() {
                    $(this).dialog('close');
                    var num = $(\"#iframeFileUpload\").contents().find(\".itemCancel\").length;
                    if (num == 0 && need == 'true') {
                        alert('{$MSG['file_required'][$sysSession->lang]}');
                        return false;
                    }
                    var test = $(\"#iframeFileUpload\").contents().find(\".pro\");
                    for(var i=0; i<test.length; i++) {
                        if(test[i].innerHTML!='100 %') {
                            alert('{$MSG['file_not_complete'][$sysSession->lang]}');
                            return false; 
                        }
                    }
                    xajax_clean_temp(st_id);
                    noBtn = false;
                    var obj = document.getElementById('homeworkForm');
                    obj.submit();
                }
            },
            {
                text: button_cancel_text,
                click: function() {
                    $(this).dialog('close');
                }

            }
        ]
    });
    
    var ua = navigator.userAgent;
    ua = ua.toLowerCase();
    var rtablet = /(ipad)/;
    var ipad = rtablet.exec(ua);
    if (ipad) {
        var screen_height = window.screen.height/2;
        var s_top = $('#test').offset().top-500;
        $('.ui-dialog').css({ top: s_top });
    }
}

function delete_attach(key, file) {
    $.ajax({
        'url': 'delete_attach.php',
        'type': 'POST',
        'data': {
            name: file,
            exam_id: {$_SERVER['argv'][0]},
            ticket: '{$attach_ticket}'
        },
        'dataType': 'json',
        'success': function(res) {
            if (res) {
                $('#' + key).remove();
            } else {
                alert('Fail');
            }
        },
        'error': function(res) {
            console.log(res);
        }
    });
}

function remove_file(span)
{
    if (span.previousSibling == null && span.nextSibling == null)
    {
        var newNode = span.cloneNode(true);
        /* #55972 FireFox 複製後值仍保留 */
        newNode.getElementsByTagName('input')[0].value = '';
        span.parentNode.replaceChild(newNode, span);
        return;
    }

    span.parentNode.removeChild(span);
}

function more_file(td)
{
    td.appendChild(td.lastChild.cloneNode(true));
    try {
        td.lastChild.getElementsByTagName(\"input\")[0].value = \"\";
    } catch (ex) {
    }
}

function examOver()
{
    var mainForm = document.getElementById('homeworkForm');
    mainForm.action += '?over' + ('{$school_q}' == '?school' ? '+school' : '');
    mainForm.submit();
}

if (typeof(xajax_clean_temp) == 'undefined') xajax_clean_temp = function(id){};

var recorderIndex=0;
function setupRecorder(rec)
{
    if (!{$isQuotaExceed})
    {
        var listObj = 'MyRecList' + recorderIndex;
        var recObj  = 'MyRecorder' + recorderIndex;

        eval(listObj + ' = new RecorderList();' +
             listObj + '.OutputTo(rec.previousSibling.previousSibling.previousSibling.previousSibling);' +
             recObj  + ' = new WMRecorder();' +
             recObj  + '.objname = \"' + recObj + '\";' +
             recObj  + '.SetPostURI(\"http://\" + window.location.host + \"/lib/anicamWB/anicam_upload.php\");' +
             recObj  + '.SetPostData(document.cookie, \"999999999\", \"10000001\");' +
             recObj  + '.SetRecFile(\"temp$$$.mp3\");' +
             recObj  + '.SetTimeLimit(2);' +
             recObj  + '.AttachButton(rec.previousSibling.previousSibling.previousSibling,rec.previousSibling.previousSibling,rec.previousSibling);' +
             recObj  + '.AttachComponent(rec);' +
             listObj + '.AddRecorder(' + recObj + ');');
        recorderIndex++;
    }
}

", false);
    $ary = array();
    showXHTML_head_E();
    showXHTML_body_B();
      $ary[] = array($MSG[QTI_which . '_title'][$sysSession->lang], 'tabsSet',  '');
      echo '<div align="center">';
      
        $cid = 0;
        preg_match('/^\/Q\/[\d]{5}\/[\d]+\/[\d]+\/[\d]+\/[0-9a-z]+\/([\d]{8}+)$/', $_SERVER['PHP_SELF'], $matches);

        if (empty($_COOKIE['showmeinfo']) === FALSE) {
            echo '<pre>';
            var_dump('exam_start', 'PHP_SELF', $_SERVER['PHP_SELF'], $matches[1]);
            echo '</pre>';
        }      

        if (preg_match('/^\/Q\/[\d]{5}\/[\d]+\/[\d]+\/[\d]+\/[0-9a-z]+\/([\d]{8}+)$/', $_SERVER['PHP_SELF'], $matches)) {
            $cid = $matches[1];
        }

        if (empty($_COOKIE['showmeinfo']) === FALSE) {
            echo '<pre>';
            var_dump('cid', $cid);
            echo '</pre>';
        }      
      
      showXHTML_tabFrame_B($ary, 1, 'homeworkForm', null, 'style="display: inline" method="POST" enctype="multipart/form-data" action="save_answer.php" ');
            showXHTML_input('hidden', 'isForTA', $isForTA ? '1' : '0');    // 判斷是否教師試做
            showXHTML_input('hidden', 'start_time', $now );
            showXHTML_input('hidden', 'course_id', $cid);
            showXHTML_table_B('id="homeworkInnerTable" border="0" cellpadding="3" cellspacing="1" style="border-collapse: collapse; ' . (($profile['isPhoneDevice'])?'':'min-width:1000px') . '" class="cssTable"');
              showXHTML_tr_B('class="cssTrHead"');
                showXHTML_td((($profile['isPhoneDevice'])?'style="display:none"':'nowrap style="width:77px;"') , $MSG['exam_name'][$sysSession->lang]);
                showXHTML_td('colspan="3"', $titles[$sysSession->lang]);
              showXHTML_tr_E();
              showXHTML_tr_B('class="cssTrEvn"');
                showXHTML_td((($profile['isPhoneDevice'])?'style="display:none"':'nowrap') , $MSG['homework_content'][$sysSession->lang]);
                showXHTML_td('colspan="3" id="homeworkDisplayPanel"');
              showXHTML_tr_E();

                if ($school_q) {
                    $save_path = sprintf(sysDocumentRoot . '/base/%05d/%s/A/%09u/', $sysSession->school_id, QTI_which, $_SERVER['argv'][0]);
                } else {
                    $save_path = sprintf(sysDocumentRoot . '/base/%05d/course/%08d/%s/A/%09u/', $sysSession->school_id, $sysSession->course_id, QTI_which, $_SERVER['argv'][0]);
                }

        $save_uri = substr($save_path, strlen(sysDocumentRoot));
        if (strpos($setting, 'upload') !== false) {
            if ($d = @dir($save_path))
            {

                    
                    showXHTML_tr_B('class="cssTrEvn"');
                      showXHTML_td('nowrap' , $MSG['uploaded_files'][$sysSession->lang]);
                      showXHTML_td_B('colspan="3"');
                      // 附檔列表
                            showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="'.(($profile['isPhoneDevice'])?'100%':'996').'" class="cssTable"');

                                showXHTML_tr_B('class="cssTrHead"');
                                    showXHTML_td('class="cssTd" align="center"', $MSG['serial_no'][$sysSession->lang]);
                                    if ($is_group) {
                                        showXHTML_td('class="cssTd" align="center"', $MSG['uploaded_man'][$sysSession->lang]);
                                    }
                                    showXHTML_td('class="cssTd" align="center"', $MSG['uploaded_time'][$sysSession->lang]);
                                    showXHTML_td('class="cssTd"', $MSG['uploaded_filename'][$sysSession->lang]);
                                    showXHTML_td('class="cssTd" align="right"', $MSG['uploaded_filesize'][$sysSession->lang]);
                                    showXHTML_td('class="cssTd" align="right"', '');
                                showXHTML_tr_E();
                                $serno = 1;
                                while (false !== ($entry = $d->read())) {
                                    if ($entry == '.' || $entry == '..')
                                        continue;

                                    if ($d1 = @dir($save_path . $entry . '/')) {
                                        while (false !== ($entry1 = $d1->read())) {
                                            if (is_file($save_path . $entry . '/' . $entry1)) {
                                                if (($entry!=$sysSession->username && !$is_group) || ($is_group && !in_array($entry,$arr_student))) continue;
                                                $id = 'attach' . $serno;
                                                showXHTML_tr_B('class="cssTrEvn" id="' . $id . '" style="height:2.5em"');
                                                    showXHTML_td('class="cssTd" align="center"', $serno);
                                                    if ($is_group) {
                                                        $RS       = dbGetStSr('WM_user_account', 'first_name, last_name', "username='" . $entry . "'", ADODB_FETCH_ASSOC);
                                                        $realname = checkRealname($RS['first_name'], $RS['last_name']);
                                                        showXHTML_td('class="cssTd" align="left"', $realname . ' (' . $entry . ')');
                                                    }
                                                    $saved_uri = substr($save_path . $entry . '/', strlen(sysDocumentRoot));
                                                    showXHTML_td('class="cssTd" align="center"', date("Y/m/d H:i:s", filemtime(sysDocumentRoot . $saved_uri . $entry1)));
                                                    $filesize = @filesize(sysDocumentRoot . $saved_uri . $entry1);
                                                    showXHTML_td('class="cssTd"', sprintf('<a target="_blank" href="%s" class="cssAnchor" style="word-break: break-all;">%s</a>', $saved_uri . $entry1, $entry1) . (($filesize === 0) ? '&nbsp;<span style="color: red;">(' . $MSG['file_content_blank'][$sysSession->lang] . ')</span>' : ''));
                                                    if ($filesize === 0) {
                                                        $filesizeColor = 'red';
                                                    } else {
                                                        $filesizeColor = 'black';
                                                    }
                                                    showXHTML_td('class="cssTd" align="right"', '<span style="color: ' . $filesizeColor . ';">' . FileSizeConvert($filesize) . '</span>');
                                                    showXHTML_td_B('align="center"');
                                                        if ($entry == $sysSession->username) {
                                                            $entry1 =  str_ireplace("'",  "&apos;", $entry1);
                                                            showXHTML_input('button', '', $MSG['remove'][$sysSession->lang], '', ' style="background-color:#05ABAB;border-radius:3px;height:2.5em;" onclick=\'if (confirm("' . $MSG['are_you_sure'][$sysSession->lang] . '")) delete_attach("' . $id . '","' . $entry1 . '"); else return false;\'');
                                                        } else {
                                                            echo '';
                                                        }
                                                    showXHTML_td_E();
                                                showXHTML_tr_E();
                                                $serno++;
                                            }

                                        }
                                        $d1->close();
                                    }
                                }
                                $d->close();
                            showXHTML_table_E();
                        showXHTML_td_E();
                    showXHTML_tr_E();
            }
        }

                if (strpos($setting, 'upload') !== false) {
                    function glob_recursive($pattern, $flags = 0)
                    {
                        $files = glob($pattern, $flags);
                        foreach (glob(dirname($pattern) . '/*', GLOB_ONLYDIR | GLOB_NOSORT) as $dir) {
                            $files = array_merge($files, glob_recursive($dir . '/' . basename($pattern), $flags));
                        }
                        return $files;
                    }
                    $ori_path = sprintf('%s/base/%05d/temp/course/%08d/%s/A/%09d/%s/', sysDocumentRoot, $sysSession->school_id, $sysSession->course_id, QTI_which, $_SERVER['argv'][0], $sysSession->username);

                    $files = glob_recursive($ori_path . '*');

                    foreach ($files as $v) {
                        unlink($v);
                    }

                    // 拖拉上傳
                    showXHTML_tr_B('class="cssTrOdd" id="fileUploadHtml5" style="display:none;"');
                        showXHTML_td('nowrap', $MSG['attach_file'][$sysSession->lang]);

                        global $currPath;

                        $currPath = rawurldecode($_POST['currPath']);
                        if (preg_match('!(\.\./)+!', $currPath))
                            die('access denied.');

                        if ($currPath == '')
                            $currPath = 'temp_learn_'.QTI_which;
                        $errno = 0;
                        if (!checkRealPath($basePath, $basePath . $currPath)) {
                            $errno = 1; // 目前只給 rm 使用而已
                        }
                        $currPathx = rawurlencode($currPath);

                        showXHTML_td_B('id="uploadFile" colspan="2"');
                echo $MSG['upload_file'][$sysSession->lang] . '(' . $MSG['file_limit'][$sysSession->lang] . '<span style="color: red; font-weight: bold">' . ini_get('post_max_size') . '</span>)';
                            echo '<div>';
                            echo '<iframe id="iframeFileUpload" src="/learn/files/basic_auto.php?currPath=' . $currPathx . '&serial_number=' . $_SERVER['argv'][0] . '" width="'.(($profile['isPhoneDevice'])?'98%':'950').'" height="320" frameborder="0" border="0" cellspacing="0" style="border: none;"></iframe>';
                            echo '</div>';
                        showXHTML_td_E();
                    showXHTML_tr_E();
                    
                // 附檔繳交區
                    showXHTML_tr_B('class="cssTrOdd" id="fileUploadTraditional"');
                        showXHTML_td('nowrap', $MSG['attach_file'][$sysSession->lang]);
                      showXHTML_td_B('colspan="3"');
                      
                            showXHTML_table_B('border="0" cellpadding="3" cellspacing="1" width="'.(($profile['isPhoneDevice'])?'100%':'996').'" class=""');

                                showXHTML_tr_B('class=""');
                        
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
                            showXHTML_td_E();
                            showXHTML_tr_E();
                        showXHTML_table_E();
                        
                        showXHTML_td_E();
                            
                    showXHTML_tr_E();
                }
                /*
                showXHTML_tr_B('class="cssTrEvn"');
                    showXHTML_td('nowrap', '&nbsp;');
                    showXHTML_td_B('colspan="2"');

                        // 更多檔案
                        if (strpos($setting, 'upload') !== false)
                            showXHTML_input('button', '', $MSG['more_file'][$sysSession->lang], '', 'id="btnMoreAtt" class="cssBtn"' . ($isQuotaExceed ? ' disabled' : 'onclick="more_file(this.parentNode.parentNode.previousSibling.childNodes[1]);"'));
                    showXHTML_td_E();
                showXHTML_tr_E();
                */
                
                if (strpos($setting, 'upload') !== false && QTI_which == 'homework') { 
                    // 注意事項
                    showXHTML_tr_B('class="cssTrOdd"');
                        showXHTML_td('nowrap', $MSG['tip'][$sysSession->lang]);

                        showXHTML_td_B('colspan="2" style="color: red"');
                            echo $MSG['attach_tip'][$sysSession->lang];
                            if (strpos($setting, 'required') !== false) {
                                echo '<br>' . $MSG['attach_required'][$sysSession->lang];
                            }
                        showXHTML_td_E();
                    showXHTML_tr_E();
                }

                showXHTML_tr_B('class="cssTrOdd"');
                
//                    showXHTML_td('nowrap', '&nbsp;');

                    showXHTML_td_B('colspan="3" align="center"');

                        showXHTML_table_B('width="100%"');
                            showXHTML_tr_B('class="cssTrOdd"');
                                showXHTML_td_B('align="center"');
                                    showXHTML_input('button', '', $MSG['sure_to_submit'][$sysSession->lang], '', ' id="test" style="background-color:#05ABAB;border-radius:3px;height:2.5em;" onclick="alert_warning_window();"');
                                showXHTML_td_E();
                            showXHTML_tr_E();
                        showXHTML_table_E();

                    showXHTML_td_E();
                showXHTML_tr_E();

    showXHTML_table_E();

    showXHTML_tabFrame_E();
    echo '</div>';
    
    echo "<div id=\"tempDisplayPanel\" style=\"display: none\"></div>\n";
    echo '<div id="dialog-message" title="" style="display:none;"><p>' . $MSG['are_you_sure_to_submit'][$sysSession->lang] . '</p></div>';
    //list($max_time) = dbGetStSr('WM_qti_' . QTI_which . '_result', 'max(time_id)', "exam_id={$_SERVER['argv'][0]} and examinee='{$sysSession->username}'");