<?php
/**
 * 聊天室
 *
 * @since   2003/11/26
 * @author  ShenTing Lin
 * @version $Id: chat_room.php,v 1.1 2009-06-25 09:26:24 edi Exp $
 * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lang/chatroom.php');
require_once(sysDocumentRoot . '/learn/chat/chat_lib.php');
require_once(sysDocumentRoot . '/webmeeting/global.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');
require_once(sysDocumentRoot . '/lib/common.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');

if ( defined('PHONE_INTERFACE') && PHONE_INTERFACE === 1){
    $profile['isPhoneDevice'] = true;
}

$sysSession->cur_func = '2000200100';
$sysSession->restore();
if (!aclVerifyPermission(2000200100, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}

$rid = $sysSession->room_id; // 聊天室編號
if (empty($rid)) {
    wmSysLog($sysSession->cur_func, $sysSession->course_id, 0, 1, 'auto', $_SERVER['PHP_SELF'], '禁止進入(room_id is empty)');
    die($MSG['chat_deny'][$sysSession->lang]);
}

getChatPath(); // 確保會建立討論室的夾檔目錄
$username = addslashes($sysSession->username);
$realname = addslashes($sysSession->realname);

list($title, $sysH, $getH, $max, $state, $owner) = dbGetStSr('WM_chat_setting', '`title`, `host`, `get_host`, `maximum`, `state`, owner', "`rid`='{$rid}'", ADODB_FETCH_NUM);

// 取課程名稱，以利識別在哪門課的討論室（因為可以開分頁到不同課程）
$courseName = '';
$owner = explode('_', $owner);
$ownerId = (int)$owner[0];
list($captionCourseName) = dbGetStSr('WM_term_course', 'caption', "course_id = {$ownerId}", ADODB_FETCH_NUM);
$langCourseName = unserialize($captionCourseName);
$courseName = $langCourseName[$sysSession->lang];
    
// 檢查是否關閉
if ($state == 'disable') {
    dbSet('WM_session', "`room_id`=''", "`idx`='{$_COOKIE['idx']}'");
    showError($MSG['tabs_title_close'][$sysSession->lang], $MSG['chat_closed'][$sysSession->lang]);
    wmSysLog($sysSession->cur_func, $sysSession->course_id, $rid, 2, 'auto', $_SERVER['PHP_SELF'], '討論室已經關閉');
    die();
}
// 檢查人數上限
list($cnt) = dbGetStSr('WM_chat_session', 'count(*)', "`rid`='{$rid}'", ADODB_FETCH_NUM);
if (($max != 0) && ($cnt >= $max)) {
    dbSet('WM_session', "`room_id`=''", "`idx`='{$_COOKIE['idx']}'");
    showError($MSG['tabs_title_full'][$sysSession->lang], $MSG['msg_max_limit'][$sysSession->lang]);
    wmSysLog($sysSession->cur_func, $sysSession->course_id, $rid, 3, 'auto', $_SERVER['PHP_SELF'], '線上討論是已經客滿');
    die();
}
// 主持人
$nowH = getChatHost(); // 取得目前聊天室的主持人
$host = (empty($nowH)) ? 'Y' : 'N';
// 若登入的是設定的主持人，而且跟目前聊天室的主持人不視同一個人，就搶回主持權
if (($sysSession->username == $sysH) && ($getH == 'Y') && ($nowH != $sysH)) {
    dbSet('WM_chat_session', "`host`='N'", "`rid`='{$rid}'");
    $host = 'Y';
}
$lang  = getCaption($title);
$rname = $lang[$sysSession->lang];
if (!(strpos($rid, 'online') === FALSE)) {
    $host  = 'N';
    $rname = $rnm;
}
list($cnt) = dbGetStSr('WM_chat_session', 'count(*)', "`rid`='{$rid}' AND `username`='{$username}'", ADODB_FETCH_NUM);
if ($cnt <= 0) {
    // 寫入登入訊息
    setChatCont('', 1, $tone = 0);
}
// 建立 Session
dbNew('WM_chat_session', '`rid`, `idx`, `username`, `realname`, `host`, `voice`, `login`', "'{$rid}', '{$_COOKIE['idx']}', '{$username}', '{$realname}', '{$host}', 'allow', NOW()");
// ////////////////////////////////////////////////////////////////////////////
$theme = "/theme/{$sysSession->theme}/{$sysSession->env}/chat/";

$css = <<< BOF
.tabsOn {
    font-size: 12px;

    width: 98px;
    height: 25px;

    cursor: pointer;
    cursor: hand;

    color: #000;
    background-color: #5176d2;
}

.tabsOff {
    font-size: 12px;

    width: 98px;
    height: 25px;

    cursor: pointer;
    cursor: hand;

    color: #fff;
    background-color: #a7beed;
}

.lstUserRoom {
    overflow: -moz-scrollbars-vertical;
    overflow-x: hidden;
    overflow-y: auto;

    width: 195px;
    height: 213px;
}

.cssLstUserRoom {
    width: 197px;

    background-color: #e6eefd;
}

.cssChatUser {
    width: 197px;

    border: 1px solid #5176d2;
}

.cssChatRoom {
    width: 197px;

    border: 1px solid #5176d2;
}

.cssChatUserHead {
    background-color: #bcd0f3;
}

.cssChatRoomHead {
    background-color: #bcd0f3;
}

.cssDefContent {
    font-size: 12px;

    text-decoration: none;
    letter-spacing: 2px;

    color: #000;
    background-color: #fff;
}

.cssChatBG {
    position: absolute;
    top: 0;
    left: 0;

    width: 100%;
    height: 100%;

    color: #000;
    background-color: #8caae6;
}

.cssChatInOut {
    position: absolute;
    z-index: 5;
    top: 6px;
    left: 10px;

    background-color: #fff;
}

.cssChatCont {
    position: absolute;
    z-index: 10;
    top: 66px;
    left: 10px;

    background-color: #fff;
}

.cssChatLine {
    padding-top: 1px;
    padding-left: 24px;

    text-indent: -20px;
}

.cssChatFile {
    margin-right: 3px;
    padding-right: 3px;
    padding-left: 3px;

    text-indent: 0;
    /* background-color: #E3E9F2; */

    border: 0 solid #5176d2;
}

.cssChatTools {
    position: absolute;
    top: 456px;
    left: 0;
}

.cssChatToolsBg {
    background-color: #5176d2;
}

.cssConnerTL {
    overflow: hidden;

    width: 9px;
    height: 9px;

    border: 0;
    background: url('{$theme}conner_tl.gif') no-repeat;
}

.cssConnerTR {
    overflow: hidden;

    width: 9px;
    height: 9px;

    border: 0;
    background: url('{$theme}conner_tr.gif') no-repeat;
}
.cssConnerBL {
    overflow: hidden;

    width: 9px;
    height: 9px;

    border: 0;
    background: url('{$theme}conner_dl.gif') no-repeat;
}

.cssConnerBR {
    overflow: hidden;

    width: 9px;
    height: 9px;

    border: 0;
    background: url('{$theme}conner_dr.gif') no-repeat;
}

.cssFontB {
    display: inline-block;
    zoom: 1;

    width: 27px;
    height: 27px;

    border: 0;
    background: url('{$theme}icon_b0.gif') no-repeat;

    *display: inline;
    align: absmiddle;
}

.cssFontI {
    display: inline-block;
    zoom: 1;

    width: 27px;
    height: 27px;

    border: 0;
    background: url('{$theme}icon_i0.gif') no-repeat;

    *display: inline;
    align: absmiddle;
}

.cssFontU {
    display: inline-block;
    zoom: 1;

    width: 27px;
    height: 27px;

    border: 0;
    background: url('{$theme}icon_u0.gif') no-repeat;

    *display: inline;
    align: absmiddle;
}

.cssFile {
    display: inline-block;
    zoom: 1;

    width: 27px;
    height: 27px;

    border: 0;
    background: url('{$theme}icon_attach0.gif') no-repeat;

    *display: inline;
    align: absmiddle;
}

.cssSet {
    display: inline-block;
    zoom: 1;

    width: 27px;
    height: 27px;

    border: 0;
    background: url('{$theme}icon_set0.gif') no-repeat;

    *display: inline;
    align: absmiddle;
}

.cssHelp {
    display: inline-block;
    zoom: 1;

    width: 27px;
    height: 27px;

    border: 0;
    background: url('{$theme}icon_help0.gif') no-repeat;

    *display: inline;
    align: absmiddle;
}


BOF;
list($cnt) = dbGetStSr('WM_chat_user_setting', 'count(*)', "`username`='{$sysSession->username}'", ADODB_FETCH_NUM);
if ($cnt > 0) {
    list($user_exit, $user_inout) = dbGetStSr('WM_chat_user_setting', '`exit_action`, `inout_msg`', "`username`='{$sysSession->username}'", ADODB_FETCH_NUM);
    if (empty($user_exit))
        $user_exit = 'none';
    $user_inout = ($user_inout == 'visible') ? 'true' : 'false';
} else {
    $user_exit  = 'notebook';
    $user_inout = 'true';
}

$isPhoneDevice = $profile['isPhoneDevice']?'true':'false';

$js = <<< BOF
    var MSG_LOGIN      = "{$MSG['chat_login'][$sysSession->lang]}";
    var MSG_LOGOUT     = "{$MSG['chat_logout'][$sysSession->lang]}";
    var MSG_ENTER      = "{$MSG['chat_enter'][$sysSession->lang]}";
    var MSG_EXIT       = "{$MSG['chat_exit'][$sysSession->lang]}";
    var MSG_BTN_PAUSE  = "{$MSG['btn_pause'][$sysSession->lang]}";
    var MSG_BTN_CANCEL = "{$MSG['btn_cancel_pause'][$sysSession->lang]}";
    var MSG_CHAT_ALL   = "{$MSG['chat_all'][$sysSession->lang]}";
    var MSG_CHAT_HELP  = "{$MSG['chat_help'][$sysSession->lang]}{$MSG['chat_help_01'][$sysSession->lang]}{$MSG['chat_help_02'][$sysSession->lang]}{$MSG['chat_help_03'][$sysSession->lang]}{$MSG['chat_help_04'][$sysSession->lang]}{$MSG['chat_help_05'][$sysSession->lang]}{$MSG['chat_help_06'][$sysSession->lang]}{$MSG['chat_help_07'][$sysSession->lang]}{$MSG['chat_help_08'][$sysSession->lang]}{$MSG['chat_help_09'][$sysSession->lang]}";
    var MSG_FILE_NOTE  = "{$MSG['msg_file_note'][$sysSession->lang]}";
    var MSG_LOST_COECT = "{$MSG['msg_lost_connect'][$sysSession->lang]}";
    var MSG_FILE_SHARE = "{$MSG['msg_file_share'][$sysSession->lang]} ";
    var MSG_LOGOUT_OK  = "{$MSG['logout_ok'][$sysSession->lang]}";
    var isPhoneDevice  = "{$isPhoneDevice}";

    var mySelf         = "{$username}";
    var myName         = "{$realname}";
    var theme          = "{$theme}";
    var nowPath        = "/learn/chat/";
    var timer          = null;
    var dsc_times      = 0;    // 講話的次數

    // 個人喜好設定 (Preferences)
    var userPref = new Object();
    userPref["exit"]  = "{$user_exit}";
    userPref["inout"] = {$user_inout};

    window.onload = function () {
        chkBrowser();
        // winResize();
        session();
        timer = setInterval('session()', 10000);
        // userLst = new Object();
        // userLst[mySelf] = new Array("{$sysSession->realname}", "", "");
        var nodes = document.getElementsByTagName("html");
        if (nodes && nodes.length > 0) {
            if (isIE) {
                defSubW = parseInt(nodes[0].offsetWidth) - 640;
                defSubH = parseInt(nodes[0].offsetHeight) - 480;
            } else {
                defSubW = parseInt(window.innerWidth) - 632;
                defSubH = parseInt(window.innerHeight) - 480;
            }
        }
        chat_style();
BOF;
if (isMobileBrowser()) {
    $js .= <<< BOF
        if (typeof Touch !== 'undefined') {
            var btn = document.getElementById("btnAttachment");
            if (btn !== null) {
                btn.style.display = "none";
            }
        }
BOF;
}
$js .= <<< BOF
};

function OpenAnicamVideoWindow(mms, scuid, w, h) {
    pop = window.open("", 'newwindow', 'location=no,toolbar=no,menubar=no,status=no,scrollbars=no,resizable=yes,width=' + w + ',height=' + h + 71);
    pop.document.write("");
    pop.document.close();
    pop.document.open();
    pop.document.write('<HTML>\n <HEAD>\n <TITLE> OFFICE HOUR Window </TITLE>\n</HEAD>\n');
    pop.document.write('<BODY marginwidth="0" marginheight="0" topmargin="0" leftmargin="0">\n');
    pop.document.write('<p align="center">\n');
    pop.document.write('<OBJECT ID="NSPlay" classid="CLSID:22D6F312-B0F6-11D0-94AB-0080C74C7E95"\n');
    pop.document.write(' codebase="http://activex.microsoft.com/activex/controls/mplayer/en/nsmp2inf.cab#Version=5,1,52,701"\n');
    pop.document.write(' standby="Loading Microsoft Windows Media Player components..."\n');
    pop.document.write(' type="application/x-oleobject">\n');
    pop.document.write('  <PARAM NAME="FileName" VALUE="mms://' + mms + '/' + scuid + '"> \n');
    pop.document.write('  <PARAM NAME="ShowControls" VALUE="1">\n');
    pop.document.write('  <PARAM NAME="ShowStatusBar" VALUE="1">\n');
    pop.document.write('  <PARAM NAME="ShowDisplay" VALUE="1">\n');
    pop.document.write('  <PARAM NAME="autoStart" VALUE="1">\n');
    pop.document.write('  <PARAM NAME="AutoPlay" VALUE="1">\n');
    pop.document.write('</OBJECT>\n');
    pop.document.write('</p>\n');
    pop.document.write('</CENTER>\n</' + 'BODY>\n</HTML>');
    pop.document.close();

}
/*
function joinchat()
{
        obj = document.getElementById("ifrm_joinnet");
        obj.contentWindow.location.reload();
}
*/
window.onresize = function() {
    chat_style();
};

window.onunload = function() {
    if (timer) clearInterval(timer);
    logout(true);
};
BOF;

$connerT = <<< BOF
<td height="9" width="9" align="left" valign="top"><div class="cssConnerTL"></div></td>
<td height="9" colspan="3"></td>
<td height="9" align="right" valign="top"><div class="cssConnerTR"></div></td>
<td height="9"></td>

BOF;
$connerB = <<< BOF
<td height="9" width="9" align="left" valign="bottom"><div class="cssConnerBL"></div></td>
<td height="9" colspan="3"></td>
<td height="9" align="right" valign="bottom"><div class="cssConnerBR"></div></td>
<td height="9"></td>

BOF;

showXHTML_head_B($MSG['title'][$sysSession->lang]);
    echo '<meta name="viewport" content="width=790">';
    showXHTML_css('inline', $css);
    showXHTML_CSS('include', "/theme/default/learn_mooc/wm.css");
    showXHTML_script('include', '/lib/jquery/jquery.min.js');
    showXHTML_script('include', '/lib/xmlextras.js');
    showXHTML_script('include', '/learn/chat/chat.js');
    showXHTML_script('inline', $js);
showXHTML_head_E();

showXHTML_body_B('leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" class="cssChatBG"');
    // 呈現背景色而已 (Begin)
    echo '<div class="cssChatBG" style="background-color: #06A2A4">&nbsp;</div>';
    // 呈現背景色而已 (End)

    // 上站訊息 (Begin)
    showXHTML_table_B('width="'.(($profile['isPhoneDevice'])?'96%':'536').'" border="0" cellspacing="0" cellpadding="0" id="tabInOut" class="cssChatInOut"');
    showXHTML_tr_B();
    echo $connerT;
    showXHTML_tr_E();
    showXHTML_tr_B('class="cssTrEvn"');
    // 行動版增加顯示離開討論室按鈕
    if ($profile['isPhoneDevice']) {
        showXHTML_td_B();
            showXHTML_input('button', '', $MSG['exit'][$sysSession->lang], '', 'class="cssBtn" onclick="logout(true); window.parent.close();"');
        showXHTML_td_E();
    }else{
        showXHTML_td('', '&nbsp;');
    }
    showXHTML_td('colspan="4"', '<div id="chatInOut" style="width : '.(($profile['isPhoneDevice'])?'100%':'527px').'; height : 45px; overflow : auto;"></div>');
    /*
    if ($MMC_enable)
    {
    showXHTML_td('', '<iframe id="ifrm_joinnet" src="/webmeeting/joinmeeting.php?rid='.$WM3_Meeting_ID.'" style="display:none" width="100" height="36"></iframe>');
    }
    */
    if ($Anicam_enable) {
        if (isChatroomMMCExists($WM3_Meeting_ID, 'anicam', $obj)) {
            list($media, $frame_size) = explode('_', $obj->extra);
            if ($media == 0) {
                showXHTML_td('', '<iframe src="/webmeeting/Only_Audio.php?MMS_Server=' . $MMS_Server_addr . '&SCUID=' . $WM3_Meeting_ID . '" style="display:block" width="100" height="36"></iframe>');
            } else if ($media == 1) {
                list($width, $height) = explode('*', $frame_size);
                echo '<script language="javascript">';
                echo 'OpenAnicamVideoWindow("' . $MMS_Server_addr . '","' . $WM3_Meeting_ID . '", ' . intval($width) . ',' . intval($height) . ');';
                echo '</script>';
            }
        }
    }
    showXHTML_tr_E();
    showXHTML_tr_B();
    echo $connerB;
    showXHTML_tr_E();
    showXHTML_table_E();
    // 上站訊息 (End)

    // 聊天主要顯示畫面 (Begin)
    showXHTML_table_B('width="'.(($profile['isPhoneDevice'])?'96%':'780').'" border="0" cellspacing="0" cellpadding="0" id="tabCont" class="cssChatCont" style="height: 80%; top: 73px;"');
    showXHTML_tr_B();
    echo $connerT;
    showXHTML_tr_E();
    showXHTML_tr_B('class="cssTrEvn"');
    showXHTML_td('width="9"', '&nbsp;');
    // 內容顯示畫面
    showXHTML_td('width="'.(($profile['isPhoneDevice'])?'100%':'527').'"', '<div id="chatCont" style="width : '.(($profile['isPhoneDevice'])?'100%':'527px').'; height : 100%; overflow : auto;"></div>');
    showXHTML_td('width="4"', '&nbsp;');
    // 人員列表與聊天室列表
    if ($profile['isPhoneDevice']) {
        showXHTML_td_B('style="display:none"');
    }else{
        showXHTML_td_B('width="227" style="vertical-align: top;"');
    }
    showXHTML_table_B('valign="top" border="0" cellspacing="0" cellpadding="0" class="cssLstUserRoom"');
    showXHTML_tr_B();
        showXHTML_td_B('align="left" valign="" colspan="3" class="cssTrEvn"');
        showXHTML_td_E();
    showXHTML_tr_E();
    showXHTML_tr_B();
        showXHTML_td_B('align="right" valign="bottom" colspan="3" class="cssTrEvn"');
            showXHTML_input('button', '', $MSG['exit'][$sysSession->lang], '', 'class="cssBtn" onclick="logout(true); window.close();"');
        showXHTML_td_E();
    showXHTML_tr_E();
    showXHTML_tr_B();
    showXHTML_td_B('align="center" colspan="3" height="32" style="font-size : 13px;"');
    echo '<div id="course-name" style="overflow: hidden; height: 18px;" title="', htmlspecialchars($courseName), '">', $courseName, '</div>';
    echo '<div id="chatName" style="overflow: hidden; height: 18px;" title="', htmlspecialchars($rname), '">', $rname, '</div>';
    showXHTML_td_E();
    showXHTML_tr_E();
    showXHTML_tr_B();
    showXHTML_td('align="center" id="mLst" class="tabsOn"  onclick="lstClick(this)" onmouseover="lstOver(this)" onmouseout="lstOut(this)"', $MSG['member_list'][$sysSession->lang] . ' <span id="mLstCnt" style="font-size: 9px;">(1)</span>');
    showXHTML_td('width="3" height="25"', '');
    showXHTML_td('align="center" id="rLst" class="tabsOff" onclick="lstClick(this)" onmouseover="lstOver(this)" onmouseout="lstOut(this)"', $MSG['room_list'][$sysSession->lang] . ' <span id="rLstCnt" style="font-size: 9px;">(1)</span>');
    showXHTML_tr_E();
    showXHTML_tr_B();
    showXHTML_td_B('colspan="3"');
    // 人員列表 (Begin)
    showXHTML_table_B('border="0" cellspacing="0" celpadding="0" id="chatUser" class="cssDefContent cssChatUser"');
    showXHTML_tr_B('class="cssChatUserHead"');
    showXHTML_td('align="center" height="30" width="120" nowrap="NoWrap"', $MSG['th_name'][$sysSession->lang]);
    showXHTML_td('align="center" height="30" width="52" nowrap="NoWrap"', $MSG['th_whisper'][$sysSession->lang]);
    showXHTML_td('align="center" height="30" width="40" nowrap="NoWrap"', '<span id="thMute">' . $MSG['th_mute'][$sysSession->lang] . '</span>');
    showXHTML_td('align="center" height="30" width="15" nowrap="NoWrap"', '&nbsp;');
    showXHTML_tr_E();
    showXHTML_tr_B();
    showXHTML_td_B('colspan="4"');
    echo '<div id="divUser" class="lstUserRoom">';
    showXHTML_table_B('width="227" border="0" cellspacing="0" celpadding="0" id="tableUser"');
    showXHTML_tr_B('class="cssTrEvn"');
    if ($host == 'Y') {
        showXHTML_td('width="120" style="color: #FF0000;"', '* ' . $sysSession->realname . ' (' . $sysSession->username . ')');
    } else {
        showXHTML_td('width="120"', $sysSession->realname . ' (' . $sysSession->username . ')');
    }
    showXHTML_td('width="50"', '&nbsp;');
    showXHTML_td('width="40"', '&nbsp;');
    showXHTML_td('width="17"', '&nbsp;');
    showXHTML_tr_E();
    showXHTML_table_E();
    echo '</div>';
    showXHTML_td_E();
    showXHTML_tr_E();
    showXHTML_tr_B();
    showXHTML_td_B('colspan="4" height="30" align="right"');
    showXHTML_input('button', '', $MSG['btn_up_member'][$sysSession->lang], '', 'class="cssBtn" onclick="mute()"');
    showXHTML_td_E('&nbsp;&nbsp;');
    showXHTML_tr_E();
    showXHTML_table_E();
    // 人員列表 (End)
    // 聊天室列表 (Begin)
    showXHTML_table_B('width="227" border="0" cellspacing="0" celpadding="0" id="chatRoom" class="cssDefContent cssChatRoom" style="display : none;"');
    showXHTML_tr_B('class="cssChatRoomHead"');
    showXHTML_td('align="center" height="30"', $MSG['th_room_name'][$sysSession->lang]);
    showXHTML_td('align="center" height="30" width="10"', '&nbsp;');
    showXHTML_tr_E();
    showXHTML_tr_B();
    showXHTML_td_B('colspan="2"');
    echo '<div id="divRoom" class="lstUserRoom">';
    showXHTML_table_B('border="0" cellspacing="0" celpadding="0"');
    showXHTML_tr_B();
    showXHTML_td('', '&nbsp;');
    showXHTML_tr_E();
    showXHTML_table_E();
    echo '</div>';
    showXHTML_td_E();
    showXHTML_tr_E();
    showXHTML_tr_B();
    showXHTML_td_B('colspan="2" height="30" align="right"');
    showXHTML_input('button', '', $MSG['btn_up_room'][$sysSession->lang], '', 'class="cssBtn" onclick="session()"');
    echo '&nbsp;&nbsp;';
    showXHTML_td_E();
    showXHTML_tr_E();
    showXHTML_table_E();
    // 聊天室列表 (End)
    showXHTML_td_E();
    showXHTML_tr_E();
    showXHTML_table_E();
    showXHTML_td_E();
    showXHTML_td('width="9"', '&nbsp;');
    showXHTML_tr_E();
    showXHTML_tr_B();
    echo $connerB;
    showXHTML_tr_E();
    showXHTML_table_E();
    // 聊天主要顯示畫面 (End)

    // 工具列 (Begin)
    if ($profile['isPhoneDevice']) {
        showXHTML_table_B('width="96%" height="44" border="0" cellspacing="0" cellpadding="0" id="divTools" class="cssDefContent cssChatTools" style="top: initial; bottom: 12px;"');
        showXHTML_tr_B('class="cssChatToolsBg" style="background-color: #06A2A4"');
        showXHTML_td_B('style="display:none"');
    }else{
        showXHTML_table_B('width="800" height="44" border="0" cellspacing="0" cellpadding="0" id="divTools" class="cssDefContent cssChatTools"');
        showXHTML_tr_B('class="cssChatToolsBg" style="background-color: #06A2A4"');
        showXHTML_td_B('align="right" width="115" nowrap="nowrap"');
    }
    echo <<< BOF
    <div class="cssFontB"
     onclick="chgFontStyle('b')"
     onmouseover="if (typeof Touch !== 'undefined' && typeof Touch !== 'function') { return; }; chgCssImg(this, 'over');"
     onmouseout="if (typeof Touch !== 'undefined' && typeof Touch !== 'function') { return; }; chgCssImg(this, 'out');"
     ontouchstart="chgCssImg(this, 'over');"
     ontouchend="chgCssImg(this, 'out');"></div>
    <div class="cssFontI"
     onclick="chgFontStyle('i')"
     onmouseover="if (typeof Touch !== 'undefined' && typeof Touch !== 'function') { return; }; chgCssImg(this, 'over');"
     onmouseout="if (typeof Touch !== 'undefined' && typeof Touch !== 'function') { return; }; chgCssImg(this, 'out');"
     ontouchstart="chgCssImg(this, 'over');"
     ontouchend="chgCssImg(this, 'out');"></div>
    <div class="cssFontU"
     onclick="chgFontStyle('u')"
     onmouseover="if (typeof Touch !== 'undefined' && typeof Touch !== 'function') { return; }; chgCssImg(this, 'over');"
     onmouseout="if (typeof Touch !== 'undefined' && typeof Touch !== 'function') { return; }; chgCssImg(this, 'out');"
     ontouchstart="chgCssImg(this, 'over');"
     ontouchend="chgCssImg(this, 'out');"></div>

BOF;
    showXHTML_td_E();
    if ($profile['isPhoneDevice']) {
        showXHTML_td('width="10"', '&nbsp;');
        showXHTML_td_B('align="left" width="530"');
        echo '<div id="chatSend">';
        showXHTML_input('text', 'chatInput', '', '', 'id="chatInput" class="cssInput" size="30" style="padding:0;"');
        showXHTML_input('button', '', $MSG['btn_send'][$sysSession->lang], '', 'class="cssBtn" onclick="send()"');
        echo '</div>';
        showXHTML_td_E();
    }else{
        showXHTML_td('width="10"', '&nbsp;');
        showXHTML_td_B('align="left" width="530"');
        // 語氣 (Begin)
        // 顯示需要，所以自己產生
        echo $MSG['chat_tone'][$sysSession->lang];
        echo '<select class="cssInput" name="chatTone" id="chatTone" style="padding:0;">';
        foreach ($tones as $key => $val) {
            echo '<option value="' . $key . '" style="color : ' . $val[0] . '">' . $val[1] . '</option>';
        }
        echo '</select>&nbsp;';
        // 語氣 (End)

        echo $MSG['chat_target'][$sysSession->lang];
        echo '<span id="lstUser">';
        $ary = array(
            0 => $MSG['chat_all'][$sysSession->lang]
        );
        showXHTML_input('select', 'selUser', $ary, 0, 'id="selUser" class="cssInput" style="width: 150px; padding:0;"');
        echo '</span>&nbsp;';
        showXHTML_input('button', 'btnPause', $MSG['btn_pause'][$sysSession->lang], '', 'id="btnPause" class="cssBtn" onclick="pauseScroll()"');
        echo '<br />';
        // 傳送訊息
        echo '<div id="chatSend">';
        showXHTML_input('text', 'chatInput', '', '', 'id="chatInput" class="cssInput" size="55" style="padding:0;"');
        showXHTML_input('button', '', $MSG['btn_send'][$sysSession->lang], '', 'class="cssBtn" onclick="send()"');
        echo '</div>';
        // 請求發言
        echo '<div id="chatRequest" style="display: none">';
        echo $MSG['chat_request'][$sysSession->lang];
        showXHTML_input('button', '', $MSG['btn_request'][$sysSession->lang], '', 'class="cssBtn" onclick="say()"');
        echo '</div>';
        showXHTML_td_E();

        showXHTML_td_B('align="right" width="145"');
        // 上傳按鈕
        echo '<div class="cssFile" onclick="fileUpload()" id="btnAttachment" onmouseover="if (typeof Touch !== \'undefined\' && typeof Touch !== \'function\') { return; }; chgCssImg(this, \'over\');" onmouseout="if (typeof Touch !== \'undefined\' && typeof Touch !== \'function\') { return; }; chgCssImg(this, \'out\');" ontouchstart="chgCssImg(this, \'over\');" ontouchend="chgCssImg(this, \'out\');"></div>';
        echo '&nbsp;&nbsp;';
        // 討論室設定
        echo '<div class="cssSet" onclick="chat_set()" onmouseover="if (typeof Touch !== \'undefined\' && typeof Touch !== \'function\') { return; }; chgCssImg(this, \'over\');" onmouseout="if (typeof Touch !== \'undefined\' && typeof Touch !== \'function\') { return; }; chgCssImg(this, \'out\');" ontouchstart="chgCssImg(this, \'over\');" ontouchend="chgCssImg(this, \'out\');"></div>';
        echo '&nbsp;&nbsp;';
        // 線上說明
        echo '<div class="cssHelp" onclick="chat_help()" onmouseover="if (typeof Touch !== \'undefined\' && typeof Touch !== \'function\') { return; }; chgCssImg(this, \'over\');" onmouseout="if (typeof Touch !== \'undefined\' && typeof Touch !== \'function\') { return; }; chgCssImg(this, \'out\');" ontouchstart="chgCssImg(this, \'over\');" ontouchend="chgCssImg(this, \'out\');"></div>';
        echo '&nbsp;&nbsp;';

        // joinnet開啟
        /*if (isChatroomMMCExists($WM3_Meeting_ID, 'anicam', $obj))
        {
        echo '<a href="javascript:;" onclick="joinchat()">';
        echo '<img src="' . $theme . 'meeting.gif" width="27" height="27" border="0" align="absmiddle">';
        echo '</a>';
        }else
        */
        if (isChatroomMMCExists($WM3_Meeting_ID, 'joinnet', $obj)) {
            echo '<a href="javascript:;" onclick="joinchat()">';
            echo '<img src="' . $theme . 'meeting.gif" width="27" height="27" border="0" align="absmiddle">';
            echo '</a>';
        }
        showXHTML_td_E();

    }


    showXHTML_tr_E();
    showXHTML_table_E();
    // 工具列 (End)

    // 浮動視窗 (Begin)
    /*
    echo '<div width="100" height="44" border="0" cellspacing="0" cellpadding="0" id="divKeynote" class="cssDefContent" style="position: absolute; top: 456px; left: 0px; background-color: #5176d2; z-index: 20; display: none;">';
    echo '';
    echo '</div>';
    */
    // 浮動視窗 (End)

    showXHTML_form_B('action="/online/msg_talk.php" method="post" target="talkWin"', 'fmTalk');
        showXHTML_input('hidden', 'reciver', '', '', '');
        showXHTML_input('hidden', 'reciver_name', '', '', '');
    showXHTML_form_E();
showXHTML_body_E();