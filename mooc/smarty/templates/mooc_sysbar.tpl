<body>
<link href="{$userTheme}mooc_sysbar.css" rel="stylesheet" type="text/css">
{if $theme !== 'black'}
    <link href="{$appRoot}/public/css/theme_white.css" rel="stylesheet" />
{/if}
<div class="mooc-sidebar" id="moocSidebar">
{$COURSE_DROPLIST}
    {*
    <!-- 選單格式 -->
    <div class="section">
        <h2>個人區</h2>
        <ul>
            <li><a href="#">個人設定</a></li>
        </ul>
    </div>
    *}
</div>
<script type="text/javascript" src="{$appRoot}/lib/common.js"></script>
<script src="/public/js/third_party/crypto-js/3.1.2/rollups/aes.js"></script>
<form name="node_list" method="POST" style="display: none;">
    <input type="hidden" name="cid">
    <input type="hidden" name="bid">
    <input type="hidden" name="nid">
</form>
<script language="JavaScript">
    var
        isLoaded = false, isParse = false, ticket = '', baseUri = '',
        sysGotoLabel = '{$sysGotoLabel}', lang = '{$userLang}', fmDefault = "{$fmDefault}",
        sysbarEnv  = '', sysbarSid  = 0, sysbarCsid = 0, sysbarCaid = 0,
        qtiWin = null, boardWin = null, chatWin = null,
        hwmsgcnt='{$hw_message_cnt}', exmsgcnt='{$hw_exam_cnt}', imcnt='{$IM_cnt}', postcnt='{$Post_cnt}', peercnt='{$Peer_cnt}',
        courseName='', sysbarLoad = 0, sidebarshow = true, sidebarAlwaysShow = '{$showSidebar}';
        MSG_SysError          = '{$MSG_SysError}',
        MSG_NotSupportBrowser = '{$MSG_NotSupportBrowser}',
        MSG_CantLoadLib       = '{$MSG_CantLoadLib}',
        MSG_NoTitle           = '{$MSG_NoTitle}',
        MSG_NEED_VARS         = '{$MSG_NEED_VARS}',
        MSG_DATA_ERROR        = '{$MSG_DATA_ERROR}',
        MSG_IP_DENY           = '{$MSG_IP_DENY}',
        MSG_ADMIN_ROLE        = '{$MSG_ADMIN_ROLE}',
        MSG_DIRECTOR_ROLE     = '{$MSG_DIRECTOR_ROLE}',
        MSG_TEACHER_ROLE      = '{$MSG_TEACHER_ROLE}',
        MSG_STUEDNT_ROLE      = '{$MSG_STUEDNT_ROLE}',
        MSG_SLID_ERROR        = '{$MSG_SLID_ERROR}',
        MSG_CAID_ERROR        = '{$MSG_CAID_ERROR}',
        MSG_CSID_ERROR        = '{$MSG_CSID_ERROR}',
        MSG_CS_DELTET         = '{$MSG_CS_DELTET}',
        MSG_CS_NOT_OPEN       = '{$MSG_CS_NOT_OPEN}',
        MSG_BAD_BOARD_ID      = '{$MSG_BAD_BOARD_ID}',
        MSG_BAD_BOARD_RANGE   = '{$MSG_BAD_BOARD_RANGE}',
        MSG_BOARD_NOTOPEN     = '{$MSG_BOARD_NOTOPEN}',
        MSG_BOARD_CLOSE       = '{$MSG_BOARD_CLOSE}',
        MSG_BOARD_DISABLE     = '{$MSG_BOARD_DISABLE}',
        MSG_BOARD_TAONLY      = '{$MSG_BOARD_TAONLY}',
        MSG_IN_CHAT_ROOM      = '{$MSG_IN_CHAT_ROOM}';
        
    // compute that show the numbers of calender
    var cal_count    = {$cal_count};

    // 是否 login alert calendar
    var login_alert  = "{$login_alert}";
    var alert_num    = "{$alert_num}";
    var alert_date   = "{$alert_date}";
    var sys_date     = "{$sys_date}";

    var CalWin       = null;
    var isPopCal     = "{$isPopCal}";    
    
        courseId              = '';
        courseBulletin        = '';

    {literal}
    function goEnv(csid, env, func) {
        // 先判斷目前功能是否有未存檔的操作
        var tmp = parent[fmDefault], $xml;
        // add try catch prevent cross-origin frame js error
        try {
        if (typeof tmp.notSave === 'boolean' && tmp.notSave)
            if (!confirm(tmp.MSG_EXIT)) {
                return false;
            } else {
                tmp.notSave = false;
            }
        }catch(e){}

        switch(env) {
            case 2 : env = 'teach'   ; break;
            case 3 : env = 'direct'  ; break;
            case 4 : env = 'academic'; break;
            default: env = 'learn'   ;
        }

        if (csid == '') {
            return false;
        }

        $xml = $.parseXML('<manifest><course_id>' + csid + '</course_id><env>' + env + '</env><func>' + func + '</func></manifest>');
        $.ajax(
                baseUri + 'goto_env.php',
                {
                    'type': 'POST',
                    'processData': false,
                    'data': $xml,
                    'success': function (data) {
                        var uri;
                        if (data === 'true') {
                            uri = '/' + env + '/index.php';
                            parent.window.location.replace(uri);
                        }
                    }
                }
        );
        return true;
    }

    function goQTI(tp, val, target) {
        var txt = '/learn/goto_qti.php?tp=' + tp + '&v=' + val;

        if (target === '_blank') {
            qtidWin = window.open(txt, '_blank', 'width=780,height=550,toolbar=0,location=0,status=0,menubar=0,directories=0,scrollbars=1,resizable=1');
        } else {
            parent.frames[target].location.replace(txt);
        }
    }

    function goBoard(val, target, isGroupBoard) {
        var $xml = $.parseXML('<manifest><board_id>' + val + '</board_id></manifest>');
        if ((target === undefined) || (target === '')) {
            target = fmDefault;
        }
        $.ajax(
            baseUri + 'goto_board.php',
            {
                'type': 'POST',
                'processData': false,
                'data': $xml,
                'success': function (data) {
                    switch (data) {
                        case 'Bad_ID'       : alert(MSG_BAD_BOARD_ID);    break;
                        case 'Bad_Range'    : alert(MSG_BAD_BOARD_RANGE); break;
                        /*case 'board_notopen': alert(MSG_BOARD_NOTOPEN);   break;
                        case 'board_close'  : alert(MSG_BOARD_CLOSE);     break;
                        case 'board_disable': alert(MSG_BOARD_DISABLE);   break;*/
                        case 'board_taonly' : alert(MSG_BOARD_TAONLY);    break;
                        default:
                            if (target == '_blank') {
                                if (isGroupBoard == 1) {
                                    boardWin = window.open('/forum/index.php', '_blank', 'width=780,height=550,toolbar=0,location=0,status=0,menubar=0,directories=0,scrollbars=1,resizable=1');
                                }else if ( val==0 || val ==1) {
                                    boardWin = window.open('/forum/index.php', '_blank', 'width=780,height=550,toolbar=0,location=0,status=0,menubar=0,directories=0,scrollbars=1,resizable=1');
                                } else {
                                    boardWin = window.open('/forum/m_node_list.php?xbid='+encodeURIComponent(encodeURIComponent(val)), '_blank', 'width=780,height=550,toolbar=0,location=0,status=0,menubar=0,directories=0,scrollbars=1,resizable=1');
                                }
                            } else {
                                if (isGroupBoard == 1) {
                                    parent.frames[target].location.replace('/forum/index.php');
                                }else if ( val==0 || val ==1) {
                                    
                                    // 課程公告版
                                    $("form[name='node_list']")
                                        .prop('action', appRoot + '/forum/m_node_list.php')
                                        .prop('target', 's_main')
                                        .find("input[name='cid']")
                                            .val(courseId).end()
                                        .find("input[name='bid']")
                                            .val(courseBulletin);

                                    $("form[name='node_list']").submit();
                                } else {
                                    parent.frames[target].location.replace('/forum/m_node_list.php?xbid='+encodeURIComponent(encodeURIComponent(val)));
                                }
                            }
                        break;
                    }
                }
            }
        );
    }
    
    function format_key(key) {
        while (key.length < 16) {
            key = key + '\u0000';
        }
        return key;
    }

    /**
     * 進討論室
     * @param {string} val 討論室編號
     * @return
     **/
    function goChatroom(val) {
        if (window.console) {console.log('mooc_sysbar.tpl goChatroom()', val);}
        
        var $xml;
        if ((typeof chatWin === 'object') && (chatWin != null) && !chatWin.closed) {
            alert(MSG_IN_CHAT_ROOM);
            chatWin.focus();
        } else {
            $xml = $.parseXML('<manifest><chat_id>' + val + '</chat_id></manifest>');
            var message, action, live;
            $.ajax(
                baseUri + 'goto_chat.php',
                {
                    'type': 'POST',
                    'processData': false,
                    'async': false,
                    'data': $xml,
                    'success': function (data) {
                        message = $(data).find('msg').text();
                        action = $(data).find('uri').text();
                        live = $(data).find('live').text();
                    }
                }
            );
            // AJAX 使用WINDOWOPEN會被擋下，將移到AJAX外面判斷
            if (message !== '') {
                alert(message);
                return;
            }
            if (action === '') {
                action = 'about:blank';
            }
            
            if (window.console) {console.log('live', live);}
            
            if (live) {
                // 組加密的資料
                var encrypt_data;
                var key = 'readlcmsvideolog';
                key = format_key(key);
                var iv = 'KXyFiQCfgiKcyuVNCGoILQ==';
                key = CryptoJS.enc.Utf8.parse(key);
                iv = CryptoJS.enc.Base64.parse(iv);
                
                var msg = {r: val, l: live};
                
                var ciphertext = CryptoJS.AES.encrypt(JSON.stringify(msg), key, {iv: iv});
                var encrypt_data = ciphertext.toString();
                
                if (window.console) {console.log('encrypt_data', encrypt_data);}
                action = '/learn/chat/live.php?data=' + encrypt_data.replace('+', '!@#$');
                if (window.console) {console.log('encrypt_data', encrypt_data.replace('+', '!@#$'));}
                action = '/learn/chat/live.php?data=' + encodeURIComponent(encodeURIComponent(encrypt_data.replace('+', '!@#$')));
                
                chatWin = window.open(action, '_blank', 'width=' + (window.screen.availWidth) + ',height=' + (window.screen.availHeight) + ',left=0,top=0,toolbar=0,location=0,status=0,menubar=0,directories=0,resizable=1');
                chatWin.moveTo(0, 0);
            } else {
                chatWin = window.open(action, '_blank', 'width=800,height=600,toolbar=0,location=0,status=0,menubar=0,directories=0,resizable=1');
            }
        }
    }

    /**
     * 切換課程/環境/班級時, 強制登出討論室
     */
    function logoutChatroom() {
        if ((typeof chatWin === 'object') && (chatWin != null) && !chatWin.closed) {
            chatWin.focus();    // 先focus, 以讓跳出的訊息可以在上層
            chatWin.close();
        }
    }

    function getTitle($node) {
        var $title = $node.children('title').find(lang);

        if ($title.length > 0) {
            return $title.text();
        }
        return '';
    }
    
    function getItem($items,$item) {
         for (i = 0, c = $items.length; i < c; i += 1) {
             $self = $($items.get(i));
             if($self.attr('id')==$item){
                 $href = $self.children('href');
                 mnHref = $href.text();
                 mnTarget = $($href.prop('outerHTML')).attr('target');
                 var obj = [mnHref, mnTarget];
                 return obj;
             }
         }
        return '';
    }

    function parseParam($node) {
        var res;

        sysbarEnv = $node.find('env').text();
        res = $node.find('school_id').text();
        sysbarSid = parseInt(res, 10);
        res = $node.find('course_id').text();
        sysbarCsid = parseInt(res, 10);
        res = $node.find('class_id').text();
        sysbarCaid = parseInt(res, 10);
    }

    function parseSubItem($node) {
        var
            i, c,
            $items = $node.children('item'),
            $elem = $('<ul></ul>'),
            js = 'javascript:',
            $self, $li, $link, title, $href, kind, mnTarget, mnHref;

        for (i = 0, c = $items.length; i < c; i += 1) {
            $self = $($items.get(i));

            title = getTitle($self);
            $li = $('<li></li>');
            $href = $self.children('href');
            kind = parseInt($href.attr('kind'), 10);
            $link = $('<a></a>')
                .attr('id', $self.attr('id'))
                .data('kind', kind)
                .text(title)
                .appendTo($li);

            mnTarget = $href.attr('target');
            if (mnTarget === 'default') {
                mnTarget = fmDefault;
            }
            mnHref = $href.text();
            if (mnHref === '') {
                mnHref = 'about:blank';
            }
            switch (kind) {
                case 1: // 功能 (function)
                case 8: // 外部連結 (out site link)
                    $link.attr('href', mnHref);
                    if ($href.text().indexOf(js) !== 0) {
                        $link.attr('target', mnTarget);
                    }
                    break;
                case 2: // 教材 (course content)
                    $link.attr('href', '/' + sysbarSid + '_' + sysbarCsid + mnHref);
                    if ($href.text().indexOf(js) !== 0) {
                        $link.attr('target', mnTarget);
                    }
                    break;
                case 3 : // 作業 (homework)
                    $link.attr('href', 'javascript:;');
                    $link.attr('data-enqtiid', mnHref);
                    $link.click(function () {
                        var enqtiid = $(this).data('enqtiid');
                        goQTI('hw', enqtiid, mnTarget);
                    });
                    break;
                case 4 : // 考試 (exam)
                    $link.attr('href', 'javascript:;');
                    $link.attr('data-enqtiid', mnHref);
                    $link.click(function () {
                        var enqtiid = $(this).data('enqtiid');
                        goQTI('ex', enqtiid, mnTarget);
                    });
                    break;
                case 5 : // 問卷 (questionnaire)
                    $link.attr('href', 'javascript:;');
                    $link.attr('data-enqtiid', mnHref);
                    $link.click(function () {
                        var enqtiid = $(this).data('enqtiid');
                        goQTI('qs', enqtiid, mnTarget);
                    });
                    break;
                case 6 : // 議題討論 (subject forum)
                case 9 : // [群組] 議題討論 ([group] subject forum)
                    $link.attr('href', 'javascript:;');
                    $link.click(function () {
                        obj = getItem($items,this.id);    
                        if (obj[1] === 'default') {
                            obj[1] = fmDefault;
                        }
                        goBoard(obj[0], obj[1]);
                    });
                    break;
                case 7 : // 線上討論 (online chat)
                case 10: // [群組] 線上討論 ([group] online chat)
                    $link.attr('href', 'javascript:;');
                    $link.click(function () {
                        goChatroom(mnHref);
                    });
                    break;
            }
            $link.click(function () {
                var tg, menuFrame, objFrame;

                $('#moocSidebar').find('li').removeClass('active');
                $(this).parent().addClass('active');

                if (fmDefault === 's_main') {
                    tg = fmDefault.substring(0, fmDefault.lastIndexOf('_'));
                    menuFrame = parent.frames[tg + '_catalog'];
                    if ((this.id !== 'SYS_04_01_002') && (menuFrame.location.pathname === '/learn/path/manifest.php')) {
                        parent.FrameExpand(0, false, 0);
                        objFrame = menuFrame.document.getElementById("pathtree");
                        if (
                            (objFrame != null) &&
                            (objFrame.contentWindow.fetchResourceForm != null) &&
                            (objFrame.contentWindow.fetchResourceForm.href !== undefined) &&
                            (objFrame.contentWindow.fetchResourceForm.href.value !== 'about:blank')
                        ) {
                            objFrame.contentWindow.doUnload();
                        }
                    } else if (menuFrame.location.pathname === '/learn/scorm/InitialSCORM.php') {
                        parent.FrameExpand(0, false, 0);
                        menuFrame.doUnload();
                    }
                    if (this.id === 'SYS_04_01_002') {
                        if (sidebarAlwaysShow === 'true') {
                            parent.toggleSidebar(true);
                        } else {
                            parent.toggleSidebar(false);
                        }
                    }
                }
            });
            $li.appendTo($elem);
        }
        return $elem;
    }

    function GotoSchoolCourses(val) {
        $elem = $('#moocSidebar').find('#SYS_06_01_002');
        if (($elem.length == 0)&&(val++ < 3)) {
            setTimeout(function(){GotoSchoolCourses(val);},1000);
            return;
        }
        if ($elem.length > 0) {
            $elem.attr("href", "/learn/mycourse/index.php?tabs=3");
            $elem.get(0).click();
            $elem.attr("href", "/learn/mycourse/index.php");
        }
    }
    
    function parseSysbar(data, goSection) {
        var
            $sidebar = $('#moocSidebar'),
            $root = $(data).find('items').first(),
            $items = $root.children('item'),
            $section, $elem;

        $sidebar.children(".section").remove();
        $section = $('<div class="section" style="height:35px;position:fixed;z-index:999;"></div>');
        $section.appendTo($sidebar);
        parseParam($(data));
        $items.each(function () {
            var $self = $(this), $child, title;

            $section = $('<div class="section"></div>');
            title = getTitle($self);
            $('<h2></h2>')
                .attr('id', $self.attr('id'))
                .text(title)
                .appendTo($section);

            $child = parseSubItem($self);
            $child.appendTo($section);

            $section.appendTo($sidebar);
        });

        // 解決 開始上課樹狀結構 解析慢，造成尚未解析完成時，若點選其他功能，會覆蓋新功能畫面
        $('#moocSidebar li').click(function () {
            tg = fmDefault.substring(0, fmDefault.lastIndexOf('_'));
            menuFrame = parent.frames[tg + '_catalog'];
            if (menuFrame.location.pathname!='/message/msg_manage_tools.php') {
                parent.parent.document.getElementById('envClassRoom').cols = '0,*';
                $(window.parent.frames["s_catalog"].document).empty();
            }
        });
        if ( sysGotoLabel === '' || (sysbarCsid === 0 && sysGotoLabel!=='SYS_06_01_003' && sysGotoLabel!=='SYS_06_01_007' ) ) {
            sysGotoLabel = goSection;
        }

        // 執行功能
        setTimeout(function () {
            // 增加undefined字串判斷
            if (sysGotoLabel === '' || typeof(sysGotoLabel) === 'undefined' || sysGotoLabel === 'undefined') {
                $elem = $sidebar.find('a:first');
            } else {
                $elem = '';
                switch(sysGotoLabel) {
                    case 'SYS_04_02_001':
                        parent.frames["s_main"].location.href= '/learn/homework/homework_list.php';
                        break;
                    case 'SYS_04_02_002':
                        parent.frames["s_main"].location.href= '/learn/exam/exam_list.php';
                        break;
                    case 'SYS_04_02_003':
                        parent.frames["s_main"].location.href= '/learn/questionnaire/questionnaire_list.php';
                        break;
                    case 'SYS_04_01_005':
                        parent.frames["s_main"].location.href= '/forum/m_board_list.php';
                        break;
                    default:
                        $elem = $sidebar.find('#' + sysGotoLabel);
                        break;
                }
            }
            sysGotoLabel = '';
            if ($elem.length > 0) {
                $elem.get(0).click();
            }
        }, 300);
        isParse = true;
    }

    function loadSysbar(uri, extra, func, goSection) {
        var $xml = $.parseXML('<manifest><ticket>' + ticket + '</ticket>' + extra + '</manifest>');
        $.ajax(
            uri,
            {
                'type': 'POST',
                'processData': false,
                'data': $xml,
                'async':false,
                'success': function (data) {
                    var txt = '', cid, isTeach = false, isStudent = false, error_msg = '';
                    error_msg = $(data).find('error_msg').text();
                    switch (error_msg) {
                        case 'needVar'       : txt = MSG_NEED_VARS;     break;
                        case 'DataError'     : txt = MSG_DATA_ERROR;    break;
                        case 'IPLimit'       : txt = MSG_IP_DENY;       break;
                        case 'AdminRole'     : txt = MSG_ADMIN_ROLE;    break;
                        case 'DirectorRole'  : txt = MSG_DIRECTOR_ROLE; break;
                        case 'TeacherRole'   : txt = MSG_TEACHER_ROLE;  break;
                        case 'StudentRole'   : txt = MSG_STUEDNT_ROLE;  break;
                        case 'SchoolIDError' : txt = MSG_SLID_ERROR;    break;
                        case 'DirectIDError' : txt = MSG_CAID_ERROR;    break;
                        case 'CourseIDError' : txt = MSG_CSID_ERROR;    break;
                        case 'CourseDelete'  : txt = MSG_CS_DELTET;     break;
                        case 'CourseClose'   : txt = MSG_CS_NOT_OPEN;   break;
                    }
                    if (txt !== '') {
                        alert(txt);
                        returnPersonal();
                        return;
                    }
                    // 設定課程名稱，給 header 取用
                    courseName = $(data).find('course_name').text();

                    cid = parseInt($(data).find('course_id').text(), 10);
                    // 移除隱藏的選單
                    $(data).find('item[hidden="true"]').remove();
                    if (cid > 10000000) {
                        // 移除個人區與校園廣場
                        $(data).find('#SYS_06_01_000').remove();
                        $(data).find('#SYS_07_01_000').remove();
                        $('#moocSidebar').removeClass('personal');
                    }else{
                        $('#moocSidebar').addClass('personal');
                    }
                    // 顯示選單
                    parseSysbar(data, goSection);
                    if (typeof func === 'function') {
                        isTeach = $(data).find('roles').find('teach[have="true"]').length > 0;
                        isStudent = $(data).find('roles').find('learn[have="true"]').length > 0;
                        func.call(null, cid, $(data).find('course_name').text(), isTeach, isStudent);
                    }
                    // 顯示未讀訊息數
                    if (hwmsgcnt !== '') {
                        $('#SYS_06_01_004').append('<span class="cnt">('+ hwmsgcnt +')</span>');
                    }
                    if (exmsgcnt !== '') {
                        $('#SYS_06_01_005').append('<span class="cnt">('+ exmsgcnt +')</span>');
                    }
                    if (imcnt !== '') {
                        $('#SYS_06_01_009').append('<span class="cnt">('+ imcnt +')</span>');
                    }
                    {/literal}{* 未讀沒即時更新，先移除以後有需要再作處理
                    if (postcnt !== '') {
                        $('#SYS_06_01_010').append('<span class="cnt">('+ postcnt +')</span>');
                    *}{literal}
                    /*                                                                            
                    if (peercnt !== '') {
                        $('#SYS_06_01_012').append('<span class="cnt">('+ peercnt +')</span>');
                    }
                    */

                    $('#moocSidebar').scrollTop(0);
                    // sidebar 預設開關
                    sidebarshow = (($(data).find('item').children('item').attr('id') === 'SYS_04_01_002') && (typeof(goSection) === 'undefined') && sysGotoLabel === '') ? false : true;
                    sidebarshow = (sidebarAlwaysShow === 'true') ? true : sidebarshow;
                    parent.toggleSidebar(sidebarshow);
                }
            }
        );
    }

    window.onunload = function () {
        if ((chatWin != null) && !chatWin.closed) chatWin.close();
        if ((boardWin != null) && !boardWin.closed) boardWin.close();
    };

    $(function () {
        t = window.location.pathname.split('/');
        t[t.length - 1] = '';
        baseUri = t.join('/');

        loadSysbar('goto_course.php', '', function (cid, cname, isTeach, isStudent) {
            sysbarConnect(cid, isTeach);
        });
        isLoaded = true;
        
        /*if (isPopCal == 'Y') {
            if (login_alert == 'Y') {
                if (cal_count > 0){
                    showCalList();
                }
            } else if ((alert_num == 0) || (sys_date != alert_date)) {
                if (cal_count > 0) {
                    showCalList();
                }
            }
        }*/

    });

    // 判斷 sysbar 連結是否顯示
    function sysbarConnect(cid, isTeach) {
        var sysbar = parent.document.getElementById('s_sysbar');
        if ((sysbar !== null) && (typeof sysbar === 'object')) {
            if (typeof sysbar.contentWindow.showTeach === 'function' &&
                typeof sysbar.contentWindow.showPersonal === 'function' &&
                typeof sysbar.contentWindow.showTeachEnv === 'function' &&
                typeof sysbar.contentWindow.showDirectorEnv === 'function' &&
                typeof sysbar.contentWindow.showManagerEnv === 'function' &&
                typeof sysbar.contentWindow.showCourseInfo === 'function') {
                sysbar.contentWindow.showTeach.call(null, isTeach);
                sysbar.contentWindow.showPersonal.call(null, cid > 10000000);
                sysbar.contentWindow.showTeachEnv.call(null, cid <= 10000000);
                sysbar.contentWindow.showDirectorEnv.call(null, cid <= 10000000);
                sysbar.contentWindow.showManagerEnv.call(null, cid <= 10000000);
                sysbar.contentWindow.showCourseInfo.call(null, cid > 10000000, cid);
            } else if (sysbarLoad < 11) {
                sysbarLoad++;
                setTimeout(function () {
                    sysbarConnect(cid, isTeach);
                }, 500);
            }
        }
    }
    
    function showCalList() {
        if ((CalWin != null) && !CalWin.closed) {
            CalWin.focus();
        } else {
            CalWin = showDialog("calender_alert.php", false , "", true, "200px", "300px", "600px", "400px", "status=0, resizable=1, scrollbars=1");
        }
    }
    
    function returnPersonal() {
        var sysbar = parent.document.getElementById('s_sysbar');
        if ((sysbar !== null) && (typeof sysbar === 'object')) {
            if (typeof sysbar.contentWindow.goPersonal === 'function') {
                sysbar.contentWindow.goPersonal();
            } else if (sysbarLoad < 11) {
                sysbarLoad++;
                setTimeout(function () {
                    returnPersonal();
                }, 500);
            }
        }
    }

    function updateCourseList()
    {
        if (this.name != 'mooc_sysbar') return;
        obj = document.getElementById("selcourse");
        if (obj != null)
        {
            $.ajax(
                baseUri + 'getCourseList.php',
                {
                    'type': 'POST',
                    'success': function (data) {
                        obj.outerHTML = data;
                    }
                }
            );
        }
    }
    
    function setSelCourseIdVal(csid) {
        if (csid == 0) csid = 10000000;
        document.getElementById('selcourse').value = csid;
    }
    {/literal}
</script>
</body>
