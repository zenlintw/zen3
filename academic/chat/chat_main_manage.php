<?php
    /**
     * 教師端的聊天室管理
     *
     * @since   2003/12/25
     * @author  ShenTing Lin
     * @version $Id: chat_main_manage.php,v 1.1 2009-06-25 09:25:33 edi Exp $
     * @copyright Wisdom Master 3(C)  Copyright(R)   SunNet Co. Taiwan, R.O.C
     **/
    require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
    require_once(sysDocumentRoot . '/lib/interface.php');
    require_once(sysDocumentRoot . '/lang/chatroom.php');
    require_once(sysDocumentRoot . '/lib/common.php');
    require_once(sysDocumentRoot . '/lib/acl_api.php');
    require_once(sysDocumentRoot . '/webmeeting/include/hit_integration.php');
    require_once(sysDocumentRoot . '/webmeeting/global.php');
    require_once(sysDocumentRoot . '/breeze/global.php');

    // 排序時需要的顯示圖案 by Small
    $img_up_src = "/theme/{$sysSession->theme}/academic/dude07232001up.gif";
    $img_dn_src = "/theme/{$sysSession->theme}/academic/dude07232001down.gif";

    $icon_up = '<img src=' . $img_up_src . ' border="0" align="absmiddl">';
    $icon_dn = '<img src=' . $img_dn_src . ' border="0" align="absmiddl">';

    // 聊天室狀態
    $chatStatus = array(
        'disable' => $MSG['status_disable'][$sysSession->lang],
        'open'    => $MSG['status_open'][$sysSession->lang],
        'taonly'  => $MSG['status_taonly'][$sysSession->lang]
    );

    $chatVisible = array(
        'visible' => $MSG['chat_visible'][$sysSession->lang],
        'hidden'  => $MSG['chat_hidden'][$sysSession->lang]
    );


    function divMsg($width=100, $caption='&nbsp;', $title='', $without_title=false) {
        if (empty($title)) $title = $caption;
        return $without_title ? ('<div style="width: ' . $width . 'px; overflow:hidden;">' . $caption . '</div>') : ('<div style="width: ' . $width . 'px; overflow:hidden;" title="' . $title . '">' . $caption . '</div>');
    }

    function showSubject($val, $act) {
        global $sysSession, $mmc_rids;
        $lang = getCaption($val);
        return divMsg(150, '<a href="javascript:;" onclick="goChat(\'' . $act . '\'); return false;" class="cssAnchor">' . htmlspecialchars_decode($lang[$sysSession->lang]) . '</a>', strip_tags(htmlspecialchars_decode($lang[$sysSession->lang])));
    }

    //$sysSession->cur_func = '2000100300';
    //$sysSession->restore();
    if (!aclVerifyPermission(2000100300, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable')))
    {
    }

    // 必須設定 $env
    if (!isset($env)) die($MSG['access_deny'][$sysSession->lang]);
    $env = preg_replace('/\W/', '', $env);

    // 不得利用 cookie、post 或 get 方法設定 $env
    $ary = array($_COOKIE['env'], $_POST['env'], $_GET['env']);
    if (in_array($env, $ary)) die($MSG['access_deny'][$sysSession->lang]);

    // 必須設定 $owner_id
    if (!isset($owner_id)) die($MSG['access_deny'][$sysSession->lang]);
    $owner_id = preg_replace('/\W/', '', $owner_id);

    // 不得利用 cookie、post 或 get 方法設定 $owner_id
    $ary = array($_COOKIE['owner_id'], $_POST['owner_id'], $_GET['owner_id']);
    if (in_array($owner_id, $ary)) die($MSG['access_deny'][$sysSession->lang]);

    // 各項排序依據
    $OB = array(
        'admin'   => '`host`',       // 管理員
        'open'    => '`open_time`',  // 開啟時間
        'close'   => '`close_time`', // 關閉時間
        'status'  => '`state`',       // 狀態
        'media'   => '`media`',      // 語音
        'visible' => '`visibility`', // 顯示或隱藏
        'order'   => '`permute`',    // 排序
           );

    // 計算總共有幾筆資料
    list($total_msg) = dbGetStSr('WM_chat_setting', 'count(*)', "`owner`='{$owner_id}'", ADODB_FETCH_NUM);
    $total_msg = intval($total_msg);

    // 計算總共分幾頁
    $lines = 10;
    $total_page = ceil($total_msg / $lines);

    // 產生下拉換頁選單
    $all_page = range(0, $total_page);
    $all_page[0] = $MSG['all_page'][$sysSession->lang];

    // 設定下拉換頁選單顯示第幾頁
    $page_no = isset($_POST['page']) ? intval($_POST['page']) : 1;
    if (($page_no < 0) || ($page_no > $total_page)) $page_no = $total_page;

    // 取得排序的欄位
    $sb = '';
    $sortby = isset($_POST['sortby']) ? trim($_POST['sortby']) : '';
    $sb = $OB[$sortby];
    if (empty($sb))     $sb     = '`permute`';
    if (empty($sortby)) $sortby = 'asc';

    // 取得排序的順序是遞增或遞減
    $order = isset($_POST['order']) ? trim($_POST['order']) : 'asc';
    $od = ($order == 'asc') ? 'DESC' : 'ASC';
    if (empty($od))    $od    = 'ASC';
    if (empty($order)) $order = 'order';

    // 產生執行的 SQL 指令
    $sqls = '';
    if (!empty($sb)) $sqls .= " order by {$sb} {$od} ";

    if (!empty($page_no)) {
        $limit = intval($page_no - 1) * $lines;
        
        // 行動版顯示所有討論室資料
        if (!(defined('SHOW_PHONE_UI') && SHOW_PHONE_UI === 1)) {
            $sqls .= " limit {$limit}, {$lines} ";
        }
    }

    $lang = strtolower($sysSession->lang);

    list($cnt) = dbGetStSr('WM_chat_user_setting', 'count(*)', "`username`='{$sysSession->username}'", ADODB_FETCH_NUM);
    if ($cnt > 0) {
        list($user_exit, $user_inout) = dbGetStSr('WM_chat_user_setting', '`exit_action`, `inout_msg`', "`username`='{$sysSession->username}'", ADODB_FETCH_NUM);
        if (empty($user_exit)) $user_exit = 'none';
        $user_inout = ($user_inout == 'visible') ? 'true' : 'false';
    } else {
        $user_exit = 'notebook';
        $user_inout = 'true';
    }

    $js = <<< BOF
    var MSG_SURE_DEL      = "{$MSG['msg_sure_delete'][$sysSession->lang]}";
    var MSG_SEL_DEL       = "{$MSG['msg_del_sel'][$sysSession->lang]}";
    var MSG_SELECT_ALL    = "{$MSG['btn_select_all'][$sysSession->lang]}";
    var MSG_SELECT_CANCEL = "{$MSG['btn_select_cancel'][$sysSession->lang]}";

    var theme = "{$sysSession->theme}";
    var lang = "{$lang}";
    var total_page = "{$total_page}";

    var user_exit = "{$user_exit}";
    var user_inout = "{$user_inout}";
    var MSG_CANCEL = "{$MSG['msg_cancel_meeting'][$sysSession->lang]}";

    /**
     * 取得另外的視窗
     * @return Object 另外的視窗 (other frame)
     **/
    function getTarget() {
        var obj = null;
        switch (this.name) {
            case "s_main"   : obj = parent.s_sysbar; break;
            case "c_main"   : obj = parent.c_sysbar; break;
            case "main"     : obj = parent.sysbar;   break;
        }
        return obj;
    }

    /**
     * 進入聊天室
     * @param string val : 聊天室編號
     **/
    function goChat(val) {
        var obj = getTarget();
        if ((obj == null) || (typeof obj != "object")) return false;
        if (typeof(obj.goChatroom) == "function") obj.goChatroom(val);
    }

    /**
     * 設定聊天室
     * @param string val : 聊天室編號
     **/
    function setChat(val) {
        var obj = document.getElementById("editFm");
        if ((typeof(obj) != "object") || (obj == null)) return false;
        obj.chat_id.value = val;
        obj.submit();
    }

    /**
     * 刪除聊天室
     **/
    function delChat() {
        var obj = document.getElementById("delFm");
        var nodes = document.getElementsByTagName("input");
        var ary = new Array();

        if ((typeof(obj) != "object") || (obj == null)) return false;
        if ((nodes == null) || (nodes.length <= 0)) return false;
        for (var i = 0; i < nodes.length; i++) {
            if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
            if (nodes[i].checked) ary[ary.length] = nodes[i].value;
        }
        if (ary.length == 0) {
            alert(MSG_SEL_DEL);
            return false;
        } else {
            if (!confirm(MSG_SURE_DEL)) return false;
        }

        obj.chat_ids.value = ary.toString();
        obj.submit();
    }

    /**
     * change page
     * @param integer n : action type or page number
     * @return
     **/
    function go_page(n){
        var obj = document.getElementById("actFm");
        if ((typeof(obj) != "object") || (obj == null)) return '';
        switch(n){
            case -1:    // 第一頁
                obj.page.value = 1;
                break;
            case -2:    // 前一頁
                obj.page.value = parseInt(obj.page.value) - 1;
                if (parseInt(obj.page.value) == 0) obj.page.value = 1;
                break;
            case -3:    // 後一頁
                obj.page.value = parseInt(obj.page.value) + 1;
                break;
            case -4:    // 最末頁
                obj.page.value = parseInt(total_page);
                break;
            default:    // 指定某頁
                obj.page.value = parseInt(n);
                break;
        }
        obj.submit();
    }

    function sortBy(val) {
        var ta = new Array('',
            'admin',  'open',  'close',
            'status', 'media', 'visible'
        );
        var re = /asc/ig;

        var obj = document.getElementById("actFm");
        if ((typeof(obj) != "object") || (obj == null)) return '';

        if (trim(obj.sortby.value) == ta[val]) {
            obj.order.value = (re.test(obj.order.value)) ? 'desc' : 'asc';
        }
        obj.sortby.value = ta[val];
        obj.submit();
    }

    function chgCheckbox() {
        var cnt = 0;
        var bol = true;
        var nodes = document.getElementsByTagName("input");
        var obj  = document.getElementById("ck");
        var btn1 = document.getElementById("btnSel1");
        var btn2 = document.getElementById("btnSel2");
        if ((nodes == null) || (nodes.length <= 0)) return false;
        for (var i = 0; i < nodes.length; i++) {
            if ((nodes[i].type != "checkbox") || (nodes[i].id == "ck")) continue;
            if (nodes[i].checked == false) bol = false;
            else cnt++;
        }
        nowSel = bol;
        if (obj  != null) obj.checked = bol;
        if (btn1 != null) btn1.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
        if (btn2 != null) btn2.value = bol ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
        btn1 = document.getElementById("btnDel1");
        btn2 = document.getElementById("btnDel2");
        if (btn1 != null) btn1.disabled = (cnt <= 0);
        if (btn2 != null) btn2.disabled = (cnt <= 0);
    }

    var nowSel = false;
    function selfunc() {
        var obj  = document.getElementById("ck");
        var btn1 = document.getElementById("btnSel1");
        var btn2 = document.getElementById("btnSel2");

        nowSel = !nowSel;
        if (obj != null) obj.checked = nowSel;
        if (btn1 != null) btn1.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
        if (btn2 != null) btn2.value = nowSel ? MSG_SELECT_CANCEL : MSG_SELECT_ALL;
        btn1 = document.getElementById("btnDel1");
        btn2 = document.getElementById("btnDel2");
        if (btn1 != null) btn1.disabled = !nowSel;
        if (btn2 != null) btn2.disabled = !nowSel;

        select_func('', obj.checked);
    }

    function goto_chatgroup() {
        window.location.replace("chat_group_manage.php");
    }

    function goBreeze(scoid, urlpath)
    {
        var options = "toolbar=0,status=0,location=0,resizable=1";
        var url = "/breeze/JoinMeeting.php?scoid="+scoid+"&urlpath="+urlpath;
        var win = open(url, "", options);
    }

    // 清除現行會議的人員，並將會議紀錄產出
    function cancelSession(rid)
    {
        if(!confirm(MSG_CANCEL))
            return false;
        var txt;

        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        if ((typeof(xmlVar) != "object") || (xmlDoc == null)) xmlVar = XmlDocument.create();

        txt = "<manifest>";
        txt +=  "<exit>"+user_exit+"</exit>";
        txt += "<cancel>true</cancel>";
        txt += "<rid>" + rid + "</rid>";
        txt += "</manifest>";
        // alert(txt);

        xmlHttp = XmlHttp.create();
        xmlVar.loadXML(txt);
        xmlHttp.open("POST", "/learn/chat/chat_logout.php", false);
        xmlHttp.send(xmlVar);

        alert(xmlHttp.responseText);
    }

    window.onload = function () {
        var txt = "";
        var obj1 = obj2 = null;
        obj1 = document.getElementById("toolbar1");
        obj2 = document.getElementById("toolbar2");
        txt = obj1.innerHTML;
        txt = txt.replace("btnSel1", "btnSel2");
        txt = txt.replace("btnDel1", "btnDel2");
        obj2.innerHTML = txt;
    };
BOF;
    // SHOW_PHONE_UI 常數定義於 /mooc/teach/chat/chat_manage.php
    if (defined('SHOW_PHONE_UI') && SHOW_PHONE_UI === 1) {
        $datas = dbGetAll('WM_chat_setting', '*', "owner='{$owner_id}' {$sqls}", ADODB_FETCH_ASSOC);
        for($i=0, $size=count($datas); $i<$size; $i++) {
            $datas[$i]['state'] = $chatStatus[$datas[$i]['state']];
            list($count_rid) = dbGetStSr('WM_chat_session',"count(*)","`rid`='{$datas[$i]['rid']}'",ADODB_FETCH_NUM);
            list($count_msg) = dbGetStSr('WM_chat_msg',"count(*)","`rid`='{$datas[$i]['rid']}'",ADODB_FETCH_NUM);
            $datas[$i]['sessionCount'] = $count_rid+$count_msg;
        }

        // assign
        $smarty->assign('pageTitle', $aryTitle[0][0]);
        $smarty->assign('datalist', $datas);
        $smarty->assign('ticket', md5(sysTicketSeed . $_COOKIE['idx']));
        $smarty->display('common/tiny_header.tpl');
        $smarty->display('common/course_header.tpl');
        $smarty->display('phone/teach/chat/chat_manage.tpl');
        $smarty->display('common/tiny_footer.tpl');
        exit;
    }
    showXHTML_head_B('');
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
    showXHTML_script('include', '/lib/common.js');
    showXHTML_script('include', '/lib/xmlextras.js');
    showXHTML_script('inline', $js);
    showXHTML_head_E();
    showXHTML_body_B();
        echo '<div align="center">';
        showXHTML_tabFrame_B($aryTitle, 1);
            $cols = 9;   // td 的 colspan
            showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="tabs1" class="cssTable"');
                showXHTML_tr_B('class="cssTrHelp"');
                    showXHTML_td('colspan="' . $cols . '"', $help);
                showXHTML_tr_E();
                // 工具列 (Begin)
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td_B('colspan="' . $cols . '" id="toolbar1"');
                        showXHTML_input('button', 'btnSel1', $MSG['btn_select_all'][$sysSession->lang], '', 'id="btnSel1" class="cssBtn" onclick="selfunc();"');
                        echo $MSG['page_no_3'][$sysSession->lang];
                        showXHTML_input('select', 'ap', $all_page, $page_no, 'class="cssInput" onchange="go_page(this.value);"');
                        echo '&nbsp;';
                        showXHTML_input('button', 'fp', $MSG['page_first'][$sysSession->lang],    '', 'onclick="go_page(-1)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''));
                        showXHTML_input('button', 'pp', $MSG['page_previous'][$sysSession->lang], '', 'onclick="go_page(-2)" class="cssBtn"'. (($page_no == 1          ) || ($page_no == 0) ? ' disabled="disabled"' : ''));
                        showXHTML_input('button', 'np', $MSG['page_next'][$sysSession->lang],     '', 'onclick="go_page(-3)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''));
                        showXHTML_input('button', 'lp', $MSG['page_last'][$sysSession->lang],     '', 'onclick="go_page(-4)" class="cssBtn"'. (($page_no == $total_page) || ($page_no == 0) ? ' disabled="disabled"' : ''));

                        echo '&nbsp;&nbsp;';
                        showXHTML_input('button', '', $MSG['btn_new'][$sysSession->lang], '', 'class="cssBtn" onclick="setChat(\'\')"');
                        showXHTML_input('button', 'btnDel1', $MSG['btn_delete'][$sysSession->lang], '', 'id="btnDel1" class="cssBtn" onclick="delChat()" disabled="disabled"');
                    showXHTML_td_E();
                showXHTML_tr_E();
                // 工具列 (End)
                if ($total_msg <= 0) {
                    $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                    showXHTML_tr_B('colspan="' . $cols . '" align="center" colspan="2" ' . $col);
                        showXHTML_td('', $MSG['msg_no_chatroom'][$sysSession->lang]);
                    showXHTML_tr_E();
                } else {
                    $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                    showXHTML_tr_B($col);
                        showXHTML_td_B('nowrap="nowrap" align="center" title="' . $MSG['select_all_msg'][$sysSession->lang] . '"');
                            showXHTML_input('checkbox', 'ck', '', '', 'id="ck" exclude="true" onclick="selfunc();"');
                        showXHTML_td_E('');
                        showXHTML_td('align="center"', $MSG['th_room_name'][$sysSession->lang]);
                        showXHTML_td_B('nowrap="nowrap" align="center" onclick="sortBy(1);" title="' . $MSG['th_admin_msg'][$sysSession->lang] . '"');
                            echo '<a href="javascript:;" class="cssAnchor" onclick="return false;">';
                            echo $MSG['th_admin'][$sysSession->lang];

                            echo ($sortby == 'admin') ? ($order == 'asc' ? $icon_up : $icon_dn) : '';
                            echo '</a>';
                        showXHTML_td_E('');
                        showXHTML_td_B('nowrap="nowrap" align="center" onclick="sortBy(2);" title="' . $MSG['th_open_time_msg'][$sysSession->lang] . '"');
                            echo '<a href="javascript:;" class="cssAnchor" onclick="return false;">';
                            echo $MSG['th_open_time'][$sysSession->lang];

                            echo ($sortby == 'open') ? ($order == 'asc' ? $icon_up : $icon_dn) : '';
                            echo '</a>';
                        showXHTML_td_E('');
                        showXHTML_td_B('nowrap="nowrap" align="center" onclick="sortBy(3);" title="' . $MSG['th_close_time_msg'][$sysSession->lang] . '"');
                            echo '<a href="javascript:;" class="cssAnchor" onclick="return false;">';
                            echo $MSG['th_close_time'][$sysSession->lang];

                            echo ($sortby == 'close') ? ($order == 'asc' ? $icon_up : $icon_dn) : '';
                            echo '</a>';
                        showXHTML_td_E('');
                        showXHTML_td_B('nowrap="nowrap" align="center" onclick="sortBy(4);" title="' . $MSG['th_status_msg'][$sysSession->lang] . '"');
                            echo '<a href="javascript:;" class="cssAnchor" onclick="return false;">';
                            echo $MSG['th_status'][$sysSession->lang];

                            echo ($sortby == 'status') ? ($order == 'asc' ? $icon_up : $icon_dn) : '';
                            echo '</a>';
                        showXHTML_td_E('');

                        showXHTML_td_B('nowrap="nowrap" align="center" onclick="sortBy(6);" title="' . $MSG['th_visible_msg'][$sysSession->lang] . '"');
                            echo '<a href="javascript:;" class="cssAnchor" onclick="return false;">';
                            echo $MSG['th_visible'][$sysSession->lang];

                            echo ($sortby == 'visible') ? ($order == 'asc' ? $icon_up : $icon_dn) : '';
                            echo '</a>';
                        showXHTML_td_E('');
                        showXHTML_td('align="center"', $MSG['th_action'][$sysSession->lang]);
                    showXHTML_tr_E();
                    $mmc_rids = array();
                    if (($MMC_enable) || ($Anicam_enable))
                    {
                        if ($MMC_enable)
                        {
                            $online_meeting_info = get_online_meeting($MMC_Server_addr, $MMC_Server_API_Port, $MMC_Server_API_RootURL, $WM3_Meeting_Owner);
                        }else{
                            $online_meeting_info = 0;
                        }

                        if (strcmp($online_meeting_info,'0') != 0)
                        {
                            list($meetingId, $ownerName) = explode(':',$online_meeting_info);
                            $meetingData = getMeetingRid($meetingId);
                            $mmc_rids[] = $meetingData->rid;
                        }
                        DeleteExpireMeetingRid($meetingData->rid, $sysSession->course_id, 'joinnet');
                    }

                    if ($Breeze_enable)
                    {
                        $breeze_meetings = getBreezeMeetingList($sysSession->course_id);
                        $sess = getEnableSessionId();
                        for($i=0, $size=count($breeze_meetings); $i<$size; $i++)
                        {
                            $urlpath = getMeetingUrlPath($sess, $breeze_meetings[$i]->scoId);
                            $meetingData = getMeetingRid($breeze_meetings[$i]->scoId.":".$urlpath);
                            $mmc_rids[] = $meetingData->rid;
                        }
                        DeleteExpireMeetingRid($meetingData->rid, $sysSession->course_id, 'breeze');
                    }

                    //取得Breeze永久性會議列表
                    $Breeze_Enternal_Meetings = array();
                    $Breeze_Enternal_Urlpath = array();
                    if ($Breeze_enable)
                    {
                        $RS = dbGetStMr('WM_chat_mmc','rid,meetingID',"meetingType='breeze' and extra='eternal'", ADODB_FETCH_ASSOC);
                        while(!$RS->EOF)
                        {
                            $Breeze_Enternal_Meetings[] = $RS->fields['rid'];
                            $Breeze_Enternal_Urlpath[$RS->fields['rid']] = explode(':',$RS->fields['meetingID']);
                            $RS->MoveNext();
                        }
                    }
                    $RS = dbGetStMr('WM_chat_setting', '*', "owner='{$owner_id}' {$sqls}", ADODB_FETCH_ASSOC);
                    while (!$RS->EOF) {
                        // $lang  = getCaption($RS->fields['title']);
                        $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                        $ot = intval($RS->fields['open_time']);
                        $ct = intval($RS->fields['close_time']);
                        $ot = $MSG['from'][$sysSession->lang] . ( (empty($ot)) ? $MSG['now'][$sysSession->lang]: date('Y-m-d H:i', strtotime($RS->fields['open_time'])) );
                        $ct = $MSG['to'][$sysSession->lang] . ( (empty($ct)) ? $MSG['forever'][$sysSession->lang]: date('Y-m-d H:i', strtotime($RS->fields['close_time'])) );
                        showXHTML_tr_B($col);
                        if ((!in_array($RS->fields['rid'],$mmc_rids)) || (in_array($RS->fields['rid'],$Breeze_Enternal_Meetings)))
                        {
                            if (isMMC_Chatroom($RS->fields['rid'])){
                                if (!in_array($RS->fields['rid'],$Breeze_Enternal_Meetings))
                                {
                                    $RS->MoveNext();
                                    continue;
                                }
                            }
                            showXHTML_td_B('nowrap="nowrap" align="center"');
                            showXHTML_input('checkbox', 'rid[]', $RS->fields['rid'], '', 'onclick="chgCheckbox(); event.cancelBubble=true;"');
                            showXHTML_td_E('');
                            showXHTML_td('nowrap="noWrap"',showSubject($RS->fields['title'],trim($RS->fields['rid'])));
                            showXHTML_td('', $RS->fields['host']);
                            showXHTML_td('', $ot);
                            showXHTML_td('', $ct);
                            showXHTML_td('', $chatStatus[$RS->fields['state']]);
                            showXHTML_td('align="center"', $chatVisible[$RS->fields['visibility']]);
                            if (!in_array($RS->fields['rid'],$Breeze_Enternal_Meetings))
                            {
                                showXHTML_td_B('nowrap="noWrap"');
                                showXHTML_input('button', 'btnEdit' , $MSG['btn_edit'][$sysSession->lang] , '', 'class="cssBtn" onclick="setChat(\'' . trim($RS->fields['rid']) . '\')"');
                                $rid = trim($RS->fields['rid']);
                                list($count_rid) = dbGetStSr('WM_chat_session',"count(*)","`rid`='{$rid}'",ADODB_FETCH_NUM);
                                list($count_msg) = dbGetStSr('WM_chat_msg',"count(*)","`rid`='{$rid}'",ADODB_FETCH_NUM);
                                if($count_rid+$count_msg>0)
                                    showXHTML_input('button', 'btnCancel' , $MSG['btn_cancel_session'][$sysSession->lang] , '', 'class="cssBtn" onclick="cancelSession(\'' . trim($RS->fields['rid']) . '\')"');
                                showXHTML_td_E();
                            }else{
                                showXHTML_td('','&nbsp;');
                            }

                            if (in_array($RS->fields['rid'],$Breeze_Enternal_Meetings))
                            {

                                showXHTML_tr_E();
                                showXHTML_tr_B($col);
                                showXHTML_td('','&nbsp;');
                                $breeze_urlpath = sprintf('http://%s/%s/',BREEZE_SERVER_ADDR,$Breeze_Enternal_Urlpath[$RS->fields['rid']][1]);
                                $tmps = $MSG['label_breeze_eternal_meeting'][$sysSession->lang].'<a href="javascript:;" onClick="goBreeze(\''.$Breeze_Enternal_Urlpath[$RS->fields['rid']][0].'\',\''.$Breeze_Enternal_Urlpath[$RS->fields['rid']][1].'\');">'.$breeze_urlpath.'</a><br>';
                                $tmps .= $MSG['label_breeze_eternal_meeting1'][$sysSession->lang].'<a href="javascript:;" onClick="goBreeze(\''.$Breeze_Enternal_Urlpath[$RS->fields['rid']][0].'\',\''.$Breeze_Enternal_Urlpath[$RS->fields['rid']][1].'\');">/breeze/JoinMeeting.php?scoid='.$Breeze_Enternal_Urlpath[$RS->fields['rid']][0].'&urlpath='.$Breeze_Enternal_Urlpath[$RS->fields['rid']][1].'</a><br>';
                                showXHTML_td('colspan="7" nowrap="noWrap"',$tmps);
                            }
                        }else{        // Joinnet & Breeze Live
                            showXHTML_td_B('nowrap="nowrap"','&nbsp;');
                            $cplang = getCaption($RS->fields['title']);
                            showXHTML_td('nowrap="noWrap"',$cplang[$sysSession->lang]);
                            showXHTML_td('', $RS->fields['host']);
                            showXHTML_td('', $ot);
                            showXHTML_td('', $ct);
                            showXHTML_td('', $chatStatus[$RS->fields['state']]);
                            showXHTML_td('align="center"', $chatVisible[$RS->fields['visibility']]);
                            showXHTML_td('nowrap="noWrap"','&nbsp;');
                        }
                        showXHTML_tr_E();
                        $RS->MoveNext();
                    }
                }
                // 工具列 (Begin)
                $col = ($col == 'class="cssTrEvn"') ? 'class="cssTrOdd"' : 'class="cssTrEvn"';
                showXHTML_tr_B($col);
                    showXHTML_td('colspan="' . $cols . '" id="toolbar2"', '&nbsp;');
                showXHTML_tr_E();
                // 工具列 (End)
            showXHTML_table_E();

            showXHTML_table_B('width="760" border="0" cellspacing="1" cellpadding="3" id="tabs2" class="cssTable" style="display: none;"');
                showXHTML_tr_B('class="cssTrEvn"');
                    showXHTML_td('', '');
                showXHTML_tr_E();
            showXHTML_table_E();
        showXHTML_tabFrame_E();
        echo '</div>';

        showXHTML_form_B('action="chat_manage.php" method="post" enctype="multipart/form-data" style="display:none"', 'actFm');
            showXHTML_input('hidden', 'sortby', $sortby, '', '');
            showXHTML_input('hidden', 'order', $order, '', '');
            showXHTML_input('hidden', 'page', $page_no, '', '');
        showXHTML_form_E('');

        showXHTML_form_B('action="chat_property.php" method="post" enctype="multipart/form-data" style="display:none"', 'editFm');
            showXHTML_input('hidden', 'chat_id', '', '', '');
            showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . $_COOKIE['idx']), '', '');
        showXHTML_form_E();

        showXHTML_form_B('action="chat_delete.php" method="post" enctype="multipart/form-data" style="display:none"', 'delFm');
            showXHTML_input('hidden', 'chat_ids', '', '', '');
            showXHTML_input('hidden', 'ticket', md5(sysTicketSeed . $_COOKIE['idx'] . 'delete'), '', '');
        showXHTML_form_E();

    showXHTML_body_E();
?>
