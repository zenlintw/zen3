<?php
    /**************************************************************************************************
     *                                                                                                *
     *        Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C                       *
     *                                                                                                *
     *        Programmer: Wiseguy Liang                                                         *
     *        Creation  : 2003/03/24                                                            *
     *        work for  : express exam                                                          *
     *        work on   : Apache 1.3.27, MySQL 4.0 up, PHP 4.3.1                                *
     *                                                                                                *
     **************************************************************************************************/

    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lib/exam_lib.php');
    require_once(sysDocumentRoot . '/mooc/common/common.php');
    
    require_once(sysDocumentRoot . '/lang/' . QTI_which . '_learn.php');
    
    ignore_user_abort(true);
    set_time_limit(0);    
    $isForTA    = isset($_SERVER['argv'][3]) && $_SERVER['argv'][3] == '1' ? 1 : 0;
    
    $isCon = isset($_SERVER['argv'][4]) && $_SERVER['argv'][4] == '1' ? 1 : 0;

    if (isset($_SERVER['argv'][4]) && $_SERVER['argv'][4] == 'phone') {
        $profile['isPhoneDevice']=true;
    }

    if (!defined('QTI_env'))
        list($foo, $topDir, $foo) = explode(DIRECTORY_SEPARATOR, $_SERVER['PHP_SELF'], 3);
    else
        $topDir = QTI_env;
    $course_id = ($topDir == 'academic') ? $sysSession->school_id : $sysSession->course_id; //10000000;
    if(!isForTA){
    if (!aclVerifyPermission(1600400200, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable'), $course_id, intval($_SERVER['argv'][0]))){
        echo <<< EOB
<h2 align="center">Permission Denied.</h2>
<p align="center"><form><input type="button" value="return previous page" onclick="history.back();" /></form></p>

EOB;
        exit;
    }
    }
    $sysSession->cur_func = 1600400200;
    $sysSession->restore();

    function new_ticket($time_id)
    {
        return md5(sysTicketSeed . $_SERVER['argv'][0] . $_SERVER['argv'][1] . $time_id . $_COOKIE['idx']);
    }

    if ($_SERVER['argc'] < 3) die('Arguments Error!.');        // 沒有三個參數則執行失敗
    if (!ereg('^[0-9]+$', $_SERVER['argv'][0]) ||                // 檢查 exam_id 格式
        !ereg('^[0-9]+$', $_SERVER['argv'][1]) ||                // 檢查 times_id 格式
        !eregi('^[a-z0-9]{32}$', $_SERVER['argv'][2])            // 檢查 ticket 格式
       )
        die('Argument format incorrect.');
    if (new_ticket('') != $_SERVER['argv'][2]) die('Fake Ticket !');    // 檢查 ticket 正確與否
    $ticket     = new_ticket(0);
    $nextTicket = new_ticket(2);
    $autoSubmitTicket = new_ticket(-1);

    if ($isForTA && !aclCheckRole($sysSession->username, $sysRoles['teacher'] | $sysRoles['assistant'] | $sysRoles['instructor'], $sysSession->course_id))
    {
        die('<script>alert("Access Denied!"); self.close();</script>');    // 檢查是否有教師身分
    }

    // MIS#22742 刪除暫存資料 by Small 2011/10/13
    // $sysConn->debug=true;
    dbDel('WM_save_temporary',"locate({$sysSession->cur_func},function_id) and username='{$sysSession->username}'");

    // 由資料庫取出資料
    $RS = dbGetStSr('WM_qti_' . QTI_which . '_test', '*', 'exam_id=' . $_SERVER['argv'][0], ADODB_FETCH_ASSOC);

    if (strpos($RS['title'], 'a:') === 0)
        $locale_title = getCaption($RS['title']);
    else
        $locale_title[$sysSession->lang] = $RS['title'];

    if(strpos($RS['content'], '<wm_immediate_random_generate_qti') !== FALSE)
    {
        $regs = array();
        if (preg_match('!<amount [^>]*>(.*)</amount>!sU', $RS['content'], $regs))
            $total_items = intval($regs[1]);
        else
            $total_items = 0;
    }
    else
    {
        $total_items = substr_count($RS['content'], '<item ');            // 共幾題
        $ansed_items = substr_count($RS['content'], '<item_result ');    // 已答過幾題 (用於續考/分頁)
    }

    if (QTI_which == 'exam') {
        // 取已作答總次數 (有測驗但是不一定完成測驗)
        list($times) = dbGetStSr('WM_qti_' . QTI_which . '_result', 'count(*)', "exam_id    ={$RS['exam_id']} and examinee='{$sysSession->username}'", ADODB_FETCH_NUM);
    } else {
        $times = intval($_SERVER['argv'][1]);
    }

    $cur_time = time();
    if (checkExamWhetherTimeout($RS, $cur_time, max(0, $times-1)) && !$isForTA)
    {
        die('<script>alert("' . $MSG['msg_exam_close'][$sysSession->lang] . '"); self.close();</script>');
    }
    if (QTI_which == 'exam') {
        $times = intval($_SERVER['argv'][1]);
    }

    if (strpos($RS['item_cramble'], 'enable') !== FALSE &&
        strpos($RS['item_cramble'], 'random_pick') !== FALSE)
        $total_items = min($total_items, $RS['random_pick']);

    $how_long_time_you_can_do = min($cur_time + intval($RS['do_interval'])*60 , ($RS['close_time'] == '9999-12-31 00:00:00' ? ($cur_time +86400) : strtotime($RS['close_time']))) - $cur_time;
    if ($RS['do_interval'] == 0 || $RS['do_interval'] == '')
    {
        $RS['do_interval'] = '&infin;';
        $how_long_time_you_can_do = -1;
    }

    $alertTimeout = ($RS['close_time']=='9999-12-31 00:00:00')? '' : $RS['close_time'];

    // 計算該份試卷的頁數(加入Section的判斷)
    function getPageCount($node, &$currPage) {
        $child_nodes = $node->child_nodes();
        $item_count = 0;
        global $item_per_page;
        foreach ($child_nodes as $value) {
            if ($value->tagname == 'item') {
                if (++$item_count == $item_per_page) {
                    $currPage++;
                    $item_count = 0;
                }
            }

            if ($value->tagname == 'section') {
                if ($item_count != 0) $currPage++;
                $item_count = 0;
                getPageCount($value, $currPage);
            }
          }
          if ($item_count != 0) $currPage++;
    }

    $RS['item_per_page']    = intval($RS['item_per_page']);
    if (empty($RS['item_per_page']))
    {
        $item_per_page = $RS['item_per_page'] = $total_items;
        $total_page = 1;
    }
    else
    {
        if (strpos($RS['content'], '<wm_immediate_random_generate_qti') !== FALSE)
        {
            $total_page = ceil($total_items / $RS['item_per_page']);
        }
        else
        {
            if ($RS['item_per_page'] != 0)
            {
                $dom = domxml_open_mem($RS['content']);
                if ($dom !== false) {
                    $root          = $dom->document_element();
                    $item_per_page = $RS['item_per_page'];
                    $currPage      = 0; getPageCount($root, $currPage);
                    $total_page    = $currPage;
                }
                else
                     $total_page = ceil($total_items / $RS['item_per_page']);
            }
            else
                $total_page = 0;
        }
    }

    if(strpos($RS['content'], '<wm_immediate_random_generate_qti') !== FALSE)
    {
        if (preg_match('!<score [^>]*>(.*)</score>!sU', $RS['content'], $regs))
            $total_score = intval($regs[1]);
        else
            $total_score = 0;
    }
    else
    {
        $out = array();
        preg_match_all('/ score="([0-9.]+)">/', $RS['content'], $out, PREG_PATTERN_ORDER);
        if (count($out[1]) > $total_items)
            $total_score = array_sum(array_splice($out[1], 0, $total_items));
        else
            $total_score = isset($out[1]) ? array_sum($out[1]) : 0;
    }

    if ($RS['ctrl_timeout'] == 'auto_submit')
        $ctrl_timeout = 'mainForm.action += \'?timeout+' . $isForTA . '\'; mainForm.submit();';
    elseif ($RS['ctrl_timeout'] == 'mark')
        $ctrl_timeout = 'mainForm.action += \'?mark+' . $isForTA . '\';';
    else
        $ctrl_timeout = '';

    // 開始秀網頁
    showXHTML_head_B($MSG['ready2test'][$sysSession->lang]);
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
    showXHTML_script('include', '/lib/jquery/jquery.min.js');
    showXHTML_script('include', '/lib/jquery/jquery-ui-1.8.22.custom.min.js');
    
    if (QTI_which == 'exam' && ($RS['ctrl_window'] == 'lock' || $RS['ctrl_window'] == 'lock2')) {
        showXHTML_script('include', 'winlock.js');
        $winlock = 'true';
        $type = $RS['ctrl_window'];
    } else
        $winlock = 'false';

    $next_word = $MSG['next_page'][$sysSession->lang];
    $over_word = $MSG['terminate_exam'][$sysSession->lang];

    $qti_which = QTI_which;
    
    $now = time();
    if (QTI_which == 'exam'){
            // Bug#1417 修正刪除考試紀錄後，無法進入考試 by Small 2006/09/14
            // 取已作答總次數 (有測驗但是不一定完成測驗)
            list($times1)   = dbGetStSr('WM_qti_' . QTI_which . '_result', 'count(*)', "exam_id={$RS['exam_id']} and examinee='{$sysSession->username}'", ADODB_FETCH_NUM);            
        }
        else
        {
            list($times1)   = dbGetStSr('WM_qti_' . QTI_which . '_result', 'count(*)', "exam_id    ={$RS['exam_id']} and examinee='{$sysSession->username}' and status!='break'", ADODB_FETCH_NUM);
        }
    $isTimeout = checkExamWhetherTimeout($RS, $now, $times1);
    
    if ($profile['isPhoneDevice']) {
        $dia_width = '100%';
    } else {
        $dia_width = '400';
    }

    $scr = <<< EOB
var winlock_warning = '{$MSG['winlock_warning'][$sysSession->lang]}';
var button_ok_text = '{$MSG['button_ok'][$sysSession->lang]}';
var button_cancel_text = '{$MSG['button_cancel'][$sysSession->lang]}';
var window_warning_title = '{$MSG['window_warning_title'][$sysSession->lang]}';
var next_word       = '{$next_word}', over_word='{$over_word}';
var msg_exam_over   = '{$MSG['exam_over'][$sysSession->lang]}';
var total_seconds   = {$how_long_time_you_can_do};
var ctrl_paging     = '{$RS['ctrl_paging']}';
var winlock         = {$winlock};
var locktype        = '{$type}';
var item_per_page   = {$RS['item_per_page']};
var cur_item        = 0;
var cur_page        = 0;
var total_page      = {$total_page};
var prevTicket      = '';
var nextTicket      = '{$nextTicket}';
var tc;
var timer;
var myTop            = window.screenTop;
var myLeft            = window.screenLeft;
var st_id            = '{$sysSession->cur_func}{$course_id}{$_SERVER['argv'][0]}{$times}';
var msg_timeout_forTA            = '{$MSG['msg_timeout_forTA'][$sysSession->lang]}';

var alertTimout_msg = '{$MSG['msg_alert_timeout'][$sysSession->lang]}';
var alertTimeout_closetime    = '{$alertTimeout}';
var submitflag = false;
var dia_width='{$dia_width}';

function alertTimeout(){
    if(alertTimeout_closetime!='')
    {
        var alertTimeoutMsg = alertTimout_msg + alertTimeout_closetime;
        alert(alertTimeoutMsg);
    }
}

// 測驗開始，啟用倒數計時
function examBegin(){
    var isForTA = {$isForTA};
    if(isForTA==0)
    {
        tc = setInterval('countDowm()', 1000);
        // window.setInterval(function(){xajax_save_temp(st_id + cur_page, document.getElementById('presentPanel').innerHTML);}, 100000);
    }
        
    
    var error = '0';
    if ('{$qti_which}' === 'exam' && {$RS['do_times']} === 1) {
        // 偵測測驗是否設定只能考一次，且已經考了
        $.ajax({
            'url': '/mooc/controllers/course_ajax.php',
            'type': 'POST',
            'dataType': 'json',
            'async': false,
            'data': {action: 'getQTIResultNum', type: 'exam', exam_ids: {$_SERVER['argv'][0]}, examinee: '{$sysSession->username}'},
            'success': function (res) {
                if (window.console) {
                    console.log(res);
                    console.log(res.data === 1);
                }

                if ({$RS['do_times']} <= res.data) {
                    alert('{$MSG['only_tested'][$sysSession->lang]}' + {$RS['do_times']} + '{$MSG['times'][$sysSession->lang]}');
                    error = '1';
                }
            },
            'error': function () {
                if (window.console) {
                    console.log('getQTIResultNum Ajax Error!');
                }
            }
        });
    }

    if (error === '1') {
        return fasle;
    }
                    
    examtouchSession();
    timer = setInterval('examtouchSession()', 60000);
    document.getElementById('infoTable').rows[0].cells[5].innerHTML = msg_timeout_forTA;
    document.getElementById('infoPanel').style.display = '';
    stepItem(0,'{$ticket}');
}

// co session
function examtouchSession(){
    var txt;
      txt  = "<manifest>";
        txt += "<ticket></ticket>";
        txt += "<erase></erase>";
        txt += "</manifest>";
        
        var xHttp = XmlHttp.create();
        var xDocs = XmlDocument.create();
        var xVars = XmlDocument.create();

        try {
            xVars.loadXML(txt);
            xHttp.open("POST", "/online/session.php", true);
            xHttp.send(xVars);
        } catch (e) {
            // alert(e);
        }
}

// 將控制按鈕加到試卷中
function genButton()
{
    return document.getElementById('buttonLine').innerHTML;
}

function alert_submit_window()
{
   $( "#dialog-message2" ).dialog( "open" );
}


function save_result(e) {
    var presentPanel = document.getElementById('presentPanel');
    var tb = presentPanel.getElementsByTagName('table')[2];
    var btns = tb.rows[tb.rows.length-1].cells[0].getElementsByTagName('input');
    /**
     * MIS#23713 by Small 2012/01/20
     * 若是分頁測驗，則按鈕為上一頁、送出答案翻下頁、放棄作答(此按鈕，靜宜客製隱藏)
     * 若都在同一頁，則按鈕為送出答案翻下頁、放棄作答(此按鈕，靜宜客製隱藏)
     **/
    if(btns.length == 3)
        btns[1].style.visibility="visible";
    else
        btns[0].style.visibility="visible";
    if (e.preventDefault) e.preventDefault();
    return false;
}

function submit_confirm(e)
{
    // MIS#23713 送出答案的提示訊息，改寫在最後一頁才跳出 by Small 2012/01/20
    if(cur_page == total_page)
    {
         if (!submitflag) {
             alert_submit_window();
             if (e.preventDefault) e.preventDefault();
             return false;
         }
    }
}

    // 翻頁控制 (page=頁數；ticket=此頁的 ticket 值)
    function stepItem(page, ticket, isover) {
        scroll(0,0);
        // xajax_clean_temp(st_id + cur_page);
        var xmlHttp = XmlHttp.create();
        var p;
        var over = '';
        if ((typeof isover != "undefined") && isover) {
            over = "+over";

            if ({$isCon}) over = over + "+1";
        }
        xmlHttp.open('POST', 'item_fetch.php?{$_SERVER['argv'][0]}+{$times}+' + page + '+' + ticket + '+{$isForTA}' + over, false);
        xmlHttp.send(null);
        var ret = xmlHttp.responseText;

    if (ret.substr(0, 4) == '<h2 ')
    {
        document.getElementById('presentPanel').innerHTML = strip_xxx(ret);
        closeMyself();
        return;
    }
    else if(ret == '<over />')
    {
        closeMyself();
        return;
    }
    else if(ret == '<chgWin />')
    {    // 切換視窗在計算成績後也要關閉視窗 by Small 2012/02/03
        closeMyself();
        return;
    }
    else if (ret == '<close />')
    {
        alert('{$MSG['msg_exam_close'][$sysSession->lang]}');
        closeMyself();
        return;
    }
    else
    {
        var presentPanel = document.getElementById('presentPanel');
        presentPanel.innerHTML = ret.replace('<!--BUTTON_LINE-->', genButton());
        // 語音答題設定 begin
        delete(recLists);
        var objs = presentPanel.getElementsByTagName('object');
        for(var i=0; i<objs.length; i++)
            if (objs[i].codeBase.indexOf('AnicamWebSoundRec.cab') > -1) setupRecorder(objs[i]);

        var mainForm = presentPanel.getElementsByTagName('form')[0];
        if (navigator.userAgent.search("MSIE") !== -1)
        {
            mainForm.attachEvent('onsubmit', submit_confirm);
            if (winlock) mainForm.attachEvent('onsubmit', free_winlock);
            mainForm.attachEvent('onsubmit', uploadAudios);
        }
        else
        {
            mainForm.addEventListener('submit', submit_confirm, false);
            if (winlock) mainForm.addEventListener('submit', free_winlock, false);
            mainForm.addEventListener('submit', uploadAudios, false);
        }
        // 語音答題設定 end

        if((p = document.getElementById('prevTicket')) != null) prevTicket = p.innerHTML;
        if((p = document.getElementById('nextTicket')) != null) nextTicket = p.innerHTML;

        if((p = document.getElementById('total_page')) != null) {
        //     alert('total page = ' + p.innerHTML);
            total_page = parseInt(p.innerHTML);
        }

        if((p = document.getElementById('currentPage')) != null)
        {
            cur_page = parseInt(p.innerHTML);
            if ((p = document.getElementById('curr_item'))!= null)
            {
                cur_item = parseInt(p.innerHTML);
            }
        }
        else
        {
            cur_item = (cur_item == 0) ? 1 : (cur_item + item_per_page);
            cur_page = Math.ceil(cur_item / item_per_page);
        }

        if (p != null) p.parentNode.innerHTML='';
        document.getElementById('totalPagePanel').innerHTML = total_page;
        document.getElementById('itemPanel').innerHTML = cur_item;
        document.getElementById('pagePanel').innerHTML = cur_page;

        var tb = presentPanel.getElementsByTagName('table')[2];
        var btns = tb.rows[tb.rows.length-1].cells[0].getElementsByTagName('input');
        // MIS#23713 因為拿掉『下一頁』的按鈕，所以按鈕數由4變3 by Small 2012/01/20
        if(btns.length == 3)
        {
            if (cur_page == 1){
                btns[0].disabled = true;
            }
            if (cur_page == total_page){
                btns[2].disabled = true;
                btns[1].value = btns[1].value.replace(next_word, over_word);
                // btns[2].value = btns[2].value.replace(next_word, over_word);
            }
        }
        else
            if (cur_page == total_page){
                btns[0].value = btns[0].value.replace(next_word, over_word);
                // btns[1].value = btns[1].value.replace(next_word, over_word);
            }
                
            var ua = window.navigator.userAgent;
        var edge = ua.indexOf('Edge/');
        if (edge <= 0) {
            $( "textarea" ).each(function( index ) {
                CKEDITOR.inline( $( this ).attr('id'), {
                    extraAllowedContent: 'a(documentation);abbr[title];code',
                    toolbar: 'EXAM'
                });
            });
        }


        // xajax_check_temp(st_id + cur_page, 'presentPanel');
        if (winlock) { init_winlock(); self.focus(); }
        if (page==0 && opener.location.pathname!='/learn/path/SCORM_fetchResource.php') { opener.location.reload(); } 
    }
}

// 轉換秒數為時間格式
function sec2timestamp(sec)
{
    var s = parseInt(sec), r;
    var ret = '';
    if (s == 0) return 0;
    for(var i =0; i<2; i++)
    {
        r = new String(s % 60);
        if (r.length < 2) r = '0' + r;
        ret = ':' + r + ret; // sprintf(':%02d', s % 60)
        if ((s = Math.floor(s / 60)) == 0) return ret.substr(1);
    }
    return s + ret;
}

// 倒數計時函式
function countDowm(){
    if (total_seconds == -1) {clearInterval(tc); window.onmouseout=null; return;}
    var mainForm = document.getElementById('presentPanel').getElementsByTagName('form')[0];
    total_seconds--;
    document.getElementById('infoTable').rows[0].cells[5].innerHTML = sec2timestamp(total_seconds);
    if (total_seconds == 0)
    {
        clearInterval(tc);
        clearInterval(timer);
                if ($( "#dialog-message2" ).dialog( "isOpen" )) {
            $('#dialog-message2').dialog( "close" );
        }
        {$ctrl_timeout}
    }
    // clearClipboard();
}

// 考完後對答案，把 form disable
function strip_xxx(ret)
{
    var x = ret.toString();
    x = x.replace(/ action=".*"/ig, '');
    x = x.replace(/<input[^>]* type="button" [^>]*>/ig, '');
    x = x.replace(/<input /ig, '<input disabled="true" readonly="true" ');
    x = x.replace(/<textarea /ig, '<textarea disabled="true" readonly="true" ');
    return x;
}

// 按鈕 handler
function doFunc(n)
{
    switch(n)
    {
        case 1:
            window.setTimeout(function(){stepItem(cur_page - 1, prevTicket);},500);
            break;
        case 2:
            stepItem(cur_page + 1, nextTicket);
            break;
            case 3:
                stepItem(0,'{$ticket}', true);
                break;
            case -1:
                stepItem(-1, '{$autoSubmitTicket}', true);
    }
}

function examOver()
{
    var mainForm = document.getElementById('presentPanel').getElementsByTagName('form')[0];
    mainForm.action += '?over+{$isForTA}';
    mainForm.submit();
}

/*針對『切換視窗，強制交卷』新增一個function  by Small 2012/02/03*/
function examChgWinOver()
{
    var mainForm = document.getElementById('presentPanel').getElementsByTagName('form')[0];
    mainForm.action += '?chgWin+{$isForTA}';
    mainForm.submit();
}

function closeMyself()
{
    // xajax_clean_temp(st_id + cur_page);
    document.getElementById('presentPanel').innerHTML = '<h1 align="center">' + msg_exam_over + '</h1>';
    clearInterval(tc);
    if (winlock) free_winlock();

    if ('{$qti_which}' == 'exam' && '{$RS['announce_type']}' == 'now' && !$isForTA)
        setTimeout('location.replace("/learn/exam/view_result.php?{$_SERVER['argv'][0]}+{$_SERVER['argv'][1]}+{$_SERVER['argv'][2]}")', 1500);
    else
        setTimeout('self.close()', 1500);
}

function checkAltKey(e)
{

    if (navigator.userAgent.search('MSIE') === -1)
    {
        event = e;
    }

    if (event.keyCode == 80 || event.keyCode == 17 || event.keyCode == 18)
    {

        event.keyCode = 0;
        return false;
    }
    return true;
}

function right(e)
{
    var e = e || window.event;
    var btnCode;

    if ('object' == typeof e)
    {
        btnCode = e.button;
        // IE's left button = 1 but Firefox/Mozilla's is 0
        if (btnCode > ((navigator.userAgent.indexOf('MSIE') > -1) ? 1 : 0))
            return false;
    }
    return true;
}

function clearClipboard()
{
    return; // custom by yea for mis#20196
    if (navigator.userAgent.indexOf('MSIE') > -1) window.clipboardData.clearData();
}

document.onmousedown=right;
document.onmouseup=right;
if (navigator.userAgent.indexOf('MSIE') == -1) window.captureEvents(Event.MOUSEDOWN);
if (navigator.userAgent.indexOf('MSIE') == -1) window.captureEvents(Event.MOUSEUP);
window.onmousedown=right;
window.onmouseup=right;

window.onbeforeprint=function()
{
    bodyHTML=document.body.innerHTML;
    document.body.innerHTML='';
};

window.onafterprint=function()
{
    document.body.innerHTML=bodyHTML;
    bodyHTML=null;
};

window.onbeforeunload = function () {
    // clearClipboard();
    if (opener && !opener.closed && opener.location.toString().indexOf('SCORM_fetchResource') == -1) {
        opener.location.reload();
    }
};

// 語音答題設定 begin
function uploadAudios()
{
    if (typeof(recLists) == 'undefined' || recLists.length < 1) return;
    for(var i=0; i< recLists.length; i++)
        eval(recLists[i] + '.SendData();');
}

var recorderIndex=0;
function setupRecorder(rec)
{
    var listObj = 'MyRecList' + recorderIndex;
    var recObj  = 'MyRecorder' + recorderIndex;

    eval(listObj + ' = new RecorderList();' +
         listObj + '.OutputTo(rec.previousSibling.previousSibling.previousSibling.previousSibling);' +
         recObj  + ' = new WMRecorder();' +
         recObj  + '.objname = recObj;' +
         recObj  + '.SetPostURI("http://{$_SERVER['HTTP_HOST']}/lib/anicamWB/anicam_upload.php");' +
         recObj  + '.SetPostData(document.cookie, "exam_audio", "{$sysSession->course_id}");' +
         recObj  + '.SetRecFile("tmp_' + recorderIndex + '.mp3");' +
         recObj  + '.SetTimeLimit(2);' +
         recObj  + '.AttachButton(rec.previousSibling.previousSibling.previousSibling,rec.previousSibling.previousSibling,rec.previousSibling);' +
         recObj  + '.AttachComponent(rec);' +
         listObj + '.AddRecorder(' + recObj + ');');
    if (typeof(recLists) == 'undefined') recLists = new Array();
    recLists[recorderIndex] = listObj;
    recorderIndex++;
}
// 語音答題設定 end

$(document).ready(function () {
    $( "#dialog-message2" ).dialog({
      autoOpen: false,
      modal: true,
      resizable: false,
      draggable: false,
      width: dia_width,
      height: 200,
      buttons: [
          {
              text:button_ok_text,
              click: function() {
                  $( this ).dialog( "close" );
                  submitflag  = true;
                  var mainForm = presentPanel.getElementsByTagName('form')[0];
                  mainForm.submit();
              }
          },
          {
              text:button_cancel_text,
              click: function() {
                  $(this).dialog("close");
                  save_result();
              }

          }
      
      ]
    });
    
});
EOB;
$begin_time    = $MSG['from'][$sysSession->lang] . (strpos($RS['begin_time'], '0000') === 0 ? $MSG['now'][$sysSession->lang] : date('Y-m-d H:i', strtotime($RS['begin_time'])) );
$close_time    = $MSG['to2'][$sysSession->lang]  . (strpos($RS['close_time'], '9999') === 0 ? $MSG['forever'][$sysSession->lang] : date('Y-m-d H:i', strtotime($RS['close_time'])) );
$ary_count     = array('none'         => $MSG['exam_type0'][$sysSession->lang],
                       'first'         => $MSG['count_type1'][$sysSession->lang],
                       'last'         => $MSG['count_type2'][$sysSession->lang],
                       'max'         => $MSG['count_type3'][$sysSession->lang],
                       'min'         => $MSG['count_type4'][$sysSession->lang],
                       'average'     => $MSG['count_type5'][$sysSession->lang]
                      );
$ary_paging    = array('none'        => $MSG['unlimited'][$sysSession->lang],
                       'can_return'  => $MSG['flip_control1'][$sysSession->lang],
                       'lock'        => $MSG['flip_control2'][$sysSession->lang]
                      );
$ary_window    = array('none'        => $MSG['unlimited'][$sysSession->lang],
                       'lock'        => $MSG['window_control1'][$sysSession->lang] . $MSG['window_control1_extra'][$sysSession->lang],
                       'lock2'       => $MSG['window_control12'][$sysSession->lang] . $MSG['window_control1_extra'][$sysSession->lang]
                      );
$ary_timeout   = array('none'        => $MSG['nop'][$sysSession->lang],
                       'mark'        => $MSG['timeout_control1'][$sysSession->lang],
                       'auto_submit' => $MSG['timeout_control2'][$sysSession->lang]
                      );
$ary_announce  = array('never'       => $MSG['score_publish0'][$sysSession->lang],
                        'now'         => $MSG['score_publish1'][$sysSession->lang],
                        'close_time'  => $MSG['score_publish2'][$sysSession->lang],
                        'user_define' => $MSG['score_publish3'][$sysSession->lang]
                       );
$display_words = array(
                        array($MSG['exam_name'][$sysSession->lang],                     $locale_title[$sysSession->lang]),
                        array($MSG['total_score'][$sysSession->lang],                 $total_score.$MSG['minute'][$sysSession->lang]),
                        array($MSG['exam_percent'][$sysSession->lang],                 $RS['percent'].'%'),
                        array($MSG['item_total_amount'][$sysSession->lang],             $total_items.$MSG['item'][$sysSession->lang]),
                        array($MSG['enable_duration'][$sysSession->lang],             $begin_time . ' ' . $close_time),
                        array($MSG['exam_duration'][$sysSession->lang],                 $RS['do_interval'] . $MSG['minute'][$sysSession->lang]),
                        array($MSG['count_type'][$sysSession->lang],                 $ary_count[$RS['count_type']]),
                        array($MSG['exam_times'][$sysSession->lang],                 ($RS['do_times']==0)?$MSG['unlimited'][$sysSession->lang]:$RS['do_times']),
                        array($MSG['item_per_page'][$sysSession->lang],                 ($RS['item_per_page'] > 0 ? ($MSG['each_page'][$sysSession->lang] . $RS['item_per_page'] . ' ' . $MSG['item'][$sysSession->lang]) : $MSG['display_all'][$sysSession->lang])),
                        array($MSG['flip_control'][$sysSession->lang],                 $ary_paging[$RS['ctrl_paging']]),
                        array($MSG['window_control'][$sysSession->lang],             $ary_window[$RS['ctrl_window']]),
                        array($MSG['timeout_control'][$sysSession->lang],             $ary_timeout[$RS['ctrl_timeout']]),
                        array($MSG['score_publish_' . QTI_which][$sysSession->lang], $ary_announce[$RS['announce_type']]),
                        array($MSG['score_publish_hint'][$sysSession->lang],         $RS['announce_time']),
                        array($MSG['pre-notice'][$sysSession->lang],                 $RS['notice'])
                      );

    showXHTML_script('inline', $scr);
echo '<style>';
echo '
        table{
            text-align:left;
        }
        .cke_textarea_inline
        {
            padding: 10px;
            height: 200px;
            width : 525px;
            overflow: auto;

            border: 1px solid gray;
            -webkit-appearance: textfield;
        }
        
        @media (max-width: 767px) {
            .cke_textarea_inline {
                width: 80%;
            }
        }
';
echo '</style>';
    if ($profile['isPhoneDevice']) {
        require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
        $smarty->display('phone/learn/exam_style.tpl');
    }
    $xajax_save_temp->printJavascript('/lib/xajax/');
showXHTML_head_E();
     showXHTML_body_B('onhelp="return false;" oncontextmenu="return false;" ondragstart="return false;" onselectstart="return true;"'); // onblur="clearClipboard();"
      echo "<center><div id=\"infoPanel\" style=\"display: none\">\n";
      $ary = array(array($MSG['exam_info'][$sysSession->lang]));
      showXHTML_tabFrame_B($ary);
            // 判斷是否內嵌在frame中，目前批改試卷頁面是設定0
            if (SINGLE === '0') {
                $width = 800;
            } else {
                // 因應 ipad mini 最大先設定到950
                $width = 950;
            }
        showXHTML_table_B('id="infoTable" width="' . $width . '" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
          showXHTML_tr_B('class="cssTrEvn"');
            showXHTML_td('align="right"', $MSG['item_amount'][$sysSession->lang]);
            showXHTML_td('', '<span id="itemPanel"></span>/' . $total_items);
            showXHTML_td('align="right"', $MSG['page_amount'][$sysSession->lang]);
            showXHTML_td('', '<span id="pagePanel"></span>/<span id="totalPagePanel"></span>');
            showXHTML_td('align="right"', $MSG['surplus_time'][$sysSession->lang]);
            showXHTML_td('', '');
          showXHTML_tr_E();
        showXHTML_table_E();
      showXHTML_tabFrame_E();
      echo "</div>\n<div id=\"presentPanel\">\n";
      $ary = array(array($MSG['prepare_to_exam'][$sysSession->lang]));
      showXHTML_tabFrame_B($ary);
        showXHTML_table_B('width="'.(($profile['isPhoneDevice'])?'98%':'760').'" border="0" cellspacing="1" cellpadding="3" class="cssTable"');
          showXHTML_tr_B();
            showXHTML_td('width="100%" class="cssTrHelp" colspan="2"', $MSG['respond_caption'][$sysSession->lang]);
          showXHTML_tr_E();
            foreach($display_words as $row){
                  $css_class = $css_class == 'class="cssTrEvn"' ? 'class="cssTrOdd"' : 'class="cssTrEvn"' ;
              showXHTML_tr_B($css_class);
                showXHTML_td('width="20%" align="right"', $row[0] . '&nbsp;&nbsp;');
                showXHTML_td('width="80%" align="left"', $row[1]);
              showXHTML_tr_E();
            }
          showXHTML_tr_B();
            showXHTML_td_B('width="100%" class="cssTrOdd" colspan="2" align="center"');
              showXHTML_input('button', '', $MSG['start_to_respond'][$sysSession->lang], '', 'class="cssBtn" '.(($isTimeout&&!$isForTA)?'disabled':'onclick="this.disabled=true; alertTimeout(); examBegin();"'));
              showXHTML_input('button', '', $MSG['maybe_nexttime'][$sysSession->lang]  , '', 'class="cssBtn" onclick="alert(\'Bye! Bye!\'); self.close();"');
            showXHTML_td_E();
          showXHTML_tr_E();
        showXHTML_table_E();
      showXHTML_tabFrame_E();

    echo <<< EOB
    </div>
</center>
<iframe id="submitTarget" name="submitTarget" src="about:blank" style="display: none"></iframe>
EOB;
        showXHTML_form_B('style="display: none"', 'buttonLine');
          if ($RS['ctrl_paging'] != 'lock' && $RS['item_per_page'] < $total_items)
          {
              showXHTML_input('button', '', $MSG['prev_page'][$sysSession->lang],        '', 'class="cssBtn" onclick="this.disabled=true; this.form.action+=\'?prepage\'; this.form.submit();  doFunc(1);"');
          }
          showXHTML_input('submit', 'submit_go_next', $MSG['submit_go_next'][$sysSession->lang],    '', 'class="cssBtn" onclick=""'); // setTimeout(\'doFunc(total_page==cur_page?3:2)\', 500);"');
          /*
          // MIS#23713 用『送出答案翻下頁』取代『下一頁』 by Small 2012/01/20 --begin
          if(($RS['ctrl_paging'] == 'lock') || ($RS['item_per_page'] == $total_items))
            showXHTML_input('submit', 'submit_go_next', $MSG['submit_go_next'][$sysSession->lang],    '', 'class="cssBtn" onclick="this.style.visibility=\'hidden\';"'); // setTimeout(\'doFunc(total_page==cur_page?3:2)\', 500);"');
          if ($RS['ctrl_paging'] != 'lock' && $RS['item_per_page'] < $total_items)
          {
              // showXHTML_input('button', '', $MSG['next_page'][$sysSession->lang],    '', 'class="cssBtn" onclick="this.disabled=true; doFunc(total_page==cur_page?3:2);"');
            showXHTML_input('submit', 'submit_go_next', $MSG['submit_go_next'][$sysSession->lang],    '', 'class="cssBtn" onclick="this.style.visibility=\'hidden\';"'); // setTimeout(\'doFunc(total_page==cur_page?3:2)\', 500);"');
          }
          */
          // MIS#23713 用『送出答案翻下頁』取代『下一頁』 by Small 2012/01/20 --end
          echo '<span style="width: 100px"></span>';
          // MIS#23354 隱藏"放棄作答"的按鈕 by Small 2011/12/06
          showXHTML_input('button', '', $MSG['quit_exam'][$sysSession->lang],        '', 'class="cssBtn" style="color: red;display:none;" onclick="if (winlock) free_winlock(); if (confirm(\''.$MSG['confirm_end_exam'][$sysSession->lang].'\')) examOver(); else if (winlock) init_winlock();"');
          // MIS#23354 增加說明文字 by Small 2011/12/08
          echo '<br><span>'.$MSG['msg_exam_remind1'][$sysSession->lang].'</span>';
          echo '<br><span>'.$MSG['msg_exam_remind2'][$sysSession->lang].'</span>';
    echo '<div id="dialog-message" title="" style="display:none;"><p>'.nl2br($MSG['winlock_warning'][$sysSession->lang]).'</p></div>';
        echo '<div id="dialog-message2" title="" style="display:none;"><p>'.nl2br($MSG['are_you_sure_to_submit'][$sysSession->lang]).'</p></div>';
        showXHTML_form_E();
    showXHTML_body_E();

?>
