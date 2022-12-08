<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/theme/default/bootstrap336/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="/theme/default/fancybox/jquery.fancybox.css">
<title>愛上互動</title>
<style type="text/css">
{literal}
    .head {
        margin:0 auto;
        height:60px;
        background:#333333;
        color:#FFFFFF;
        width:1280px;
    }
    
    #over_button {
       margin-top: 50px;
    }
    
    .over-box {
        width:500px;
        height:276px;
        display:none;
        font-size:20px;
    } 
    
    .icon_warn {
        background-image:url('/public/images/irs/ic-warning.png');
        background-repeat:no-repeat;
        width:95px;height:95px;
        background-position: center center;
        margin:28 auto;
    }
    
    .fancybox-inner {
        background: #FFFFFF;
        border-bottom-left-radius: 15px;
        border-bottom-right-radius: 15px;
    }
    
    .fancybox-skin {
        border-bottom-left-radius: 15px;
        border-bottom-right-radius: 15px;
    }
    
    .true_button {
        color:#FFFFFF;
        background:#E8483F;
        margin-top:20;
        margin-bottom:28;
        margin-left:117;
        border-radius: 4px;
        width:128px;
        height:38px;
        line-height:38px;
        float:left;
        text-align:center;
    }
    
    .false_button {
        color:#FFFFFF;
        background:#B5B5B5;
        margin-top:20;
        margin-bottom:28;
        margin-left:10;
        border-radius: 4px;
        width:128px;
        height:38px;
        line-height:38px;
        float:left;
        text-align:center;
    }

    .title {
        text-align:right;
        line-height:60px;
        font-size:30px;
        font-weight:Bold;
        overflow : hidden;
        text-overflow : ellipsis;
        white-space : nowrap;
    }
    
    .question {
        line-height:45px;
        font-size:30px;
        font-weight:Bold;
    }
    
    .clearboth {
        height:2px;
        background:#000000;
    }
    
    .content {
        color:#FFFFFF;
        margin:0 auto;
        margin-top:25px;
        width:1280px;
    }
    
    #people,#stastic {
        margin: auto 70;
    }
        
    .qrcode {
        text-align:center;
    }
    
    .prepare_img {
        background-image:url('/public/images/irs/prepare.png');
        background-repeat:no-repeat;
        width:427px;height:530px;
        background-position: center center;
        background-size: cover;
        text-align:center;
    }
    
    .qrcode_tip {
        font-size: 28px;
        text-align:center;
    }
    
    .exam_name {
        color:#21A55C;
        font-size: 24px;
        font-weight:Bold;
    }
    
    .status {
        margin:5px 0 5px 0;
        font-size: 18px;
    }
    
    .button {
        width:100%;
        background:#FF7D13;
        border-color:#FF7D13;
        border-radius:10px;
        height:60px;
        text-align:center;
        font-size: 28px;
    }

    .tip {
        margin-top:20px;
        font-size: 18px;
    }
    
    .tip_img {
        background-image:url('/public/images/irs/table-wh.svg');
        background-repeat:no-repeat;
        background-size: contain;
        width:100%;
        height:270px;
    }
    
    .time {
        font-size:30px;    
        line-height:60px;
    }
    
    html {
        background:#333333;
        margin: 0 auto;
    }
    
    body {
        background:#333333;
        margin: 0 auto;
        font-family: 微軟正黑體,Arial, Helvetica, sans-serif;
        overflow-x : hidden;  
        overflow-y : auto;        
    }

    .people {
        line-height:150px;
        font-size:26px;
        text-align:center;
        width:150px;
        height:150px;
        margin:20 20;
        background-color:#767171;
        overflow : hidden;
        text-overflow : ellipsis;
        white-space : nowrap;
        font-weight: bold;
        color:#FFFFFF;
    }
    
    .people_small {
        line-height:80px;
        font-size:50px;
        text-align:center;
        width:80px;
        height:80px;
        background-color:#767171;
    }
    
    .progress {
        height:40px;
        background-color:#afabaa;
    }
    
    .icon_bgchange:hover {
        background-image:url('/public/images/irs/btn-bgSwitcher-hover.png');
    }
    
    .icon_bgchange {
        float:right;
        margin:15px 10px;
        height:30px;
        width:38px;
        background-image:url('/public/images/irs/btn-bgSwitcher.png');
        background-repeat:no-repeat;
        background-size: contain;
    }
    
    .icon_full:hover {
        background-image:url('/public/images/irs/btn-fullScreen-hover.png');
    }
    
    .icon_full {
        float:right;
        margin:15px 10px;
        height:30px;
        width:38px;
        background-image:url('/public/images/irs/btn-fullScreen.png');
        background-repeat:no-repeat;
        background-size: contain;
    }
    
    .icon_qrcode:hover {
        background-image:url('/public/images/irs/btn-qrcode-hover.png');
    }
    
    .icon_qrcode {
        float:right;
        margin:15px 10px;
        height:30px;
        width:38px;
        background-image:url('/public/images/irs/btn-qrcode.png');
        background-repeat:no-repeat;
        background-size: contain;
    }

    .icon_stastic:hover {
        background-image:url('/public/images/irs/btn-stastic-hover.png');
    }
    
    .icon_stastic {
        float:right;
        margin:15px 10px;
        height:30px;
        width:38px;
        background-image:url('/public/images/irs/btn-stastic.png');
        background-repeat:no-repeat;
        background-size: contain;
    }
    
    .icon_people:hover {
        background-image:url('/public/images/irs/btn-student-hover.png');
    }
    
    .icon_people {
        float:right;
        margin:15px 10px;
        margin-right:20px;
        height:30px;
        width:38px;
        background-image:url('/public/images/irs/btn-student.png');
        background-repeat:no-repeat;
        background-size: contain;
    }
    
    /* unvisited link */
    a:link {
        color: #FFFFFF;
    }
    
    /* visited link */
    a:visited {
        color: #FFFFFF;
    }
    
    /* mouse over link */
    a:hover {
        color: #FFFFFF;
        text-decoration: none;
    }
    
    /* selected link */
    a:active {
        color: #FFFFFF;
    }

    
    .code {
        background-image:url('/public/images/irs/bg-mobile.png');
        background-repeat:no-repeat;
        background-size: contain;
        width:404px;
        height:505px;
        background-position: center center;
        margin: auto;
    }
    
    .code .input_tip {
        background: rgba(0,0,0,0.80);
        border-radius: 100px;
        font-size: 30px;
        color: #FFA700;
        text-align: center;
        height: 60px;
        line-height: 60px;
        position:relative;
        top:45%;
    
    }
    
    .code_number {
        font-size: 70px;
        color: #FFD677;
        font-family: Arial Black;
        position:relative;
        top:30%;
    }
    
    .code_number > .title {
        font-size: 25px;
        color: #FFFFFF;
        font-family: 微軟正黑體;
    }

    .switcher {
        display: block;
        height: 50px;
        margin-top:10px;
        padding: 4px;
        background: #383838;
        border-radius: 2px;
        width: 200px;
        border-radius: 40px;
        border: solid 1px #515050;
        position: relative;
        margin:auto;
    }

    .switcher__input {
        display: none;
    }

    .switcher__label {
        float: left;
        width: 50%;
        font-size: 18px;
        line-height: 40px;
        color: #FFF;
        text-align: center;
        cursor: pointer;
        position: inherit;
        z-index: 10;
        transition: color 0.2s cubic-bezier(0.4, 0.0, 0.2, 1);
        will-change: transform;
    }

    .switcher__toggle {
        position: absolute;
        float: left;
        height: 40px;
        width: 47%;
        font-size: 18px;
        line-height: 40px;
        cursor: pointer;
        background-color: #FFA700;
        border-radius: 40px;
        left: 5px;
        top: 4px;
        transition: left 0.25s cubic-bezier(0.4, 0.0, 0.2, 1);
        will-change: transform;
    }

    .switcher__input:checked + .switcher__label {
        color: #fff;
        font-weight: 600;
    }

    .switcher__input--yang:checked ~ .switcher__toggle {
        left: 100px;
    }

        
    @media screen and (-ms-high-contrast: active), (-ms-high-contrast: none) {
        .number {
            height:100%;
        }
        
        .tool {
            height:100%;
        }
    }


{/literal}
</style>
<script type="text/javascript" src="/lib/jquery/jquery.min.js"></script>
<script type="text/javascript" src="/theme/default/fancybox/jquery.fancybox.pack.js"></script>
<script type="text/javascript" src="/public/js/websocket.min.js"></script>
<script type="text/javascript">

    var color = 'black';
    var total_seconds  = 0;
    var tc;
    var csid = {$course_id};
    var exam = {$exam_id};
    var type = '{$qti_type}';
    var items= {$items};
    var publish= '{$publish}';
    var t_status,flag_status = 0;
    var t_result,flag_result = 0;
    var t_people,flag_people = 0;
    var save = false;
    var forGuest = '{$forGuest}';
    var username = '{$teach_username}';
    var sysWebsocketHost = '{$sysWebsocketHost}';
    
    
    
    {literal}
     
    function changecss() {
        if (color=='black') {
            $('.content').css({"color":"#000000","background":"#ffffff",});
            $('.people').css({"color":"#ffffff"});
            $('body').css({"background":"#ffffff",});
            $('html').css({"background":"#ffffff",});
            $('.tip_img').css({"background-image":"url('/public/images/irs/table-bl.svg')"});
            $('.refresh').attr('src', '/public/images/irs/ic_refresh_hover.png'); 
            $('.prev').attr('src', '/public/images/irs/ic_pre_hover.png');
            $('.next').attr('src', '/public/images/irs/ic_next_hover.png');
            $('.fa-arrow-circle-left').css({"color":"#000000"}); 
            $('.fa-arrow-circle-right').css({"color":"#000000"}); 
            color = 'white'; 
        } else {
            $('.content').css({"color":"#ffffff","background":"#333333",});
            $('body').css({"background":"#333333",});
            $('html').css({"background":"#333333",});
            $('.tip_img').css({"background-image":"url('/public/images/irs/table-wh.svg')"});
            $('.refresh').attr('src', '/public/images/irs/ic_refresh.png'); 
            $('.prev').attr('src', '/public/images/irs/ic_pre.png');
            $('.next').attr('src', '/public/images/irs/ic_next.png');
            $('.fa-arrow-circle-left').css({"color":"#ffffff"}); 
            $('.fa-arrow-circle-right').css({"color":"#ffffff"});
            color = 'black'; 
        }
    }
    
    function touchfullscreen() {
        if (!top.document.fullscreenElement &&
            !top.document.mozFullScreenElement && !top.document.webkitFullscreenElement && !top.document.msFullscreenElement ) {
            launchFullscreen();
        } else {
            exitFullscreen();
        }
    }
        
    function launchFullscreen() {
          var element= document.documentElement;
          if(element.requestFullscreen) {
            element.requestFullscreen();
          } else if(element.mozRequestFullScreen) {
            element.mozRequestFullScreen();
          } else if(element.webkitRequestFullscreen) {
            element.webkitRequestFullScreen();
          } else if(element.msRequestFullscreen) {
            if (element === document.documentElement) { //check element
                element = document.body; //overwrite the element (for IE)
            }
            element.msRequestFullscreen();
          }
    }
        
    function exitFullscreen() {
          if(document.exitFullscreen) {
            document.exitFullscreen();
          } else if(document.mozCancelFullScreen) {
            document.mozCancelFullScreen();
          } else if(document.webkitExitFullscreen) {
            document.webkitExitFullscreen();
          } else if(document.msExitFullscreen) {
            document.msExitFullscreen();
          }
    }

    function get_status() {
        
        if (publish=='prepare') {
            return;
        }
        
        if (flag_status==1) {
            return;
        }
        flag_status = 1;

        $.ajax({
            'url': '/mooc/irs/irs_status.php',
            'type': 'POST',
            'data': {'action': 'get_status','course_id':csid,'exam_id':exam,'qti_type':type},
            'dataType': "json",
            'success': function (res) {
                $('#submit_num').html(res.submit);    
                // $('#start_num').html(res.start);   
                // $('#submit_rate').html(res.submit_rate); 
                $('#major_count').html(res.major);
                $('#start_rate').html(res.start_rate);
                flag_status = 0;
                clearTimeout(t_status); 
                t_status = setTimeout('get_status()', 30000);  
            },
            'error': function () {
                alert('push Ajax Error.');
            }
        });
    }
    
    function showQrcode() {
        get_status();
        $('#qrcode').show();
        $('#stastic').hide();
        $('#people').hide();
    }
    
    function sec2timestamp(sec) {
        var s = parseInt(sec), r;
        var ret = '';
        var head = '';
        if (s == 0) return 0;
        for(var i =0; i<2; i++)
        {
            r = new String(s % 60);
            if (r.length < 2) r = '0' + r;
            ret = ':' + r + ret; // sprintf(':%02d', s % 60)
            if (sec < 60) head = '00:';
            if ((s = Math.floor(s / 60)) == 0) return head + ret.substr(1);
        }
        return s + ret;
    }
    
    function countDowm(){
        if (total_seconds == -1) {clearInterval(tc); window.onmouseout=null; return;}
        total_seconds++;
        sec = sec2timestamp(total_seconds);
        $('.time').html(sec);
    }
    
    function active() {
        tc = setInterval('countDowm()', 1000);
        $('#qrcode .prepare').hide();
        $('#qrcode .active').show();
        $('.head .prepare').hide();
        $('.head .active').show();
        get_status();
        t_status = setTimeout('get_status()', 30000);
        t_result = setTimeout('get_result()', 30000);
        t_people = setTimeout('getPeople()', 30000);
    }
    
    function start() {

        close_fancy();
        $.ajax({
            'url': '/mooc/irs/irs_status.php',
            'type': 'POST',
            'data': {'action': 'start_active','course_id':csid,'exam_id':exam,'qti_type':type},
            'dataType': "json",
            'success': function (res) {
                if(res.code==1) {
                    publish = 'action';
                    opener.location.reload();
                    active();
                    SGWebSocket.sendQuestionnaire(
                        csid,
                        [],
                        exam,
                        type
                    );
                }
            },
            'error': function () {
                alert('push Ajax Error.');
            }
        });
 
    }
    
    function over() {
    
        
            $.ajax({
                'url': '/mooc/irs/irs_status.php',
                'type': 'POST',
                'data': {'action': 'over_active','course_id':csid,'exam_id':exam,'qti_type':type},
                'dataType': "json",
                'success': function (res) {
                    if(res.code==1) {
                        opener.location.reload();
                        SGWebSocket.closeQuestion();
                        save = true;
                        window.close();
                    }
                },
                'error': function () {
                    alert('push Ajax Error.');
                }
            });
        

    }
    
    function get_result() {

        if (publish=='prepare') {
            return;
        }
        
        if (flag_result==1) {
            return;
        }
        flag_result = 1;
        
        $.ajax({
            'url': '/mooc/irs/irs_status.php',
            'type': 'POST',
            'data': {'action': 'get_result','course_id':csid,'exam_id':exam,'qti_type':type,'forGuest':forGuest},
            'dataType': "json",
            'success': function (res) {
                var items = res.stastic;
                if (items) {
                    Object.keys(items).forEach(function(key){
                        for (var i = 0, len = items[key].length; i < len; i++) {
                            $('#'+key+'_'+i+' .people_num').html(items[key][i]);
                            
                            var total = res.total[key];
                            if (total>0) {
                                var rate = Math.round(items[key][i]/total*100,1);
                            } else {
                                var rate = 0;
                            }
                            $('#'+key+'_'+i+' .progress-bar').css({"width":rate+"%"});
                            $('#'+key+'_'+i+' .rate').html(rate+"%");
                            
                        }
                    });
                }
                
                var ans = res.ans;
                if (ans) {
                    Object.keys(ans).forEach(function(key){
                        var txt = '';
                        Object.keys(ans[key]).forEach(function(key1){
                            ans[key][key1][0] = ans[key][key1][0].replace(/\r\n/g, "<br>");
                            txt += '<div class="row" style="margin-top:20px;">';
                            txt += '<div class="col-md-1 img-circle people_small" style="background-color:'+ans[key][key1][3]+'">'+ans[key][key1][2]+'</div>';
                            txt += '<div class="col-md-11"><span style="color:'+ans[key][key1][3]+'">'+ans[key][key1][1]+'</span><br><span style="font-size:22px;">'+ans[key][key1][0]+'</span></div>';

                            txt += '</div>';
                            
                        });
    
                        $('#'+key+'').html(txt);
    
                    });
                }
                
                flag_result = 0;
                clearTimeout(t_result); 
                t_result = setTimeout('get_result()', 30000);
            },
            'error': function () {
                alert('push Ajax Error.');
            }
        });
    
    }
        
    function showStastic() {
        get_result();
        $('#qrcode').hide();
        $('#people').hide();
        $('#stastic').show();
        for (var i = 1; i <= items; i++) {
            $('#item'+i).removeClass('show');
            $('#item'+i).addClass('hidden');
        }
        $('#item1').removeClass('hidden');
        $('#item1').addClass('show');
    }
    
    function next(now) {
        var next = now+1;
        if (next<=items) {
            $('#item'+now).addClass('hidden');
            $('#item'+next).removeClass('hidden');
            $(window).scrollTop(0);
        }
    }

    function prev(now) {
        var prev = now-1;
        if (prev!=0) {
            $('#item'+now).addClass('hidden');
            $('#item'+prev).removeClass('hidden');
            $(window).scrollTop(0);
        }
    }

    function getPeople() {
        
        if (publish=='prepare') {
            return;
        }
        
        if (flag_people==1) {
            return;
        }
        flag_people = 1;
        
        $.ajax({
            'url': '/mooc/irs/irs_status.php',
            'type': 'POST',
            'data': {'action': 'get_people','course_id':csid,'exam_id':exam,'qti_type':type,'forGuest':forGuest},
            'dataType': "json",
            'success': function (res) {
                $('#p_submit').html(res.submit);
                $('#p_nosubmit').html(res.nosubmit); 
                $('#p_list').html(res.html);
                
                flag_people = 0;
                clearTimeout(t_people); 
                t_people = setTimeout('getPeople()', 30000);
            },
            'error': function () {
                alert('push Ajax Error.');
            }
        });
    
    }
    
    function showPeople() {
        getPeople();
        $('#qrcode').hide();
        $('#stastic').hide();
        $('#people').show();
    }
    
    function close_fancy() {
            $.fancybox.close();
    }
    
    function show_qrcode() {
        $("#div_code").hide();
        $("#div_qrcode").show();
    }
    
    function show_code() {
        $("#div_qrcode").hide();
        $("#div_code").show();
    }
    
    $(document).ready(function() {   
        $('#stastic').hide();
        $('#people').hide();
        $('#qrcode .active').hide();
        $('.head .active').hide();
        if (publish=='action') active();
        
        $("#over_button").fancybox({
            'padding': 0,
            'margin': 0,
            'modal': true
        });
        
        $("#start_button").fancybox({
            'padding': 0,
            'margin': 0,
            'modal': true
        });

        SGWebSocket.registerJoinCourseSuccessHandler(function () {
            // console.log('QQQQQQQ');
        });

        SGWebSocket.setUpIRSCourse(
            // 連線WebSocket Url
            sysWebsocketHost,
            username,
            // 設定身份，1: 老師, 2: 學生
            1,
            // 房間ID(course id, ex: 10000001)
            csid,
            function () {
                console.log('connection success');
                // SGWebSocket.sendQuestionnaire();
            },
            function () {
                console.log('connection fail');
            }
        );
    });
    
    $(window).resize(function() {
        $('#all').width($(window).width());
        if ($(window).width() < 1280) {
            $('.clearboth').width(1280);
        } else {
            $('.clearboth').width($(window).width());
        }
    });

    window.onbeforeunload = function() {
        if (!save) {
            return '你確定要關閉視窗？';
        }
    };

    {/literal}
 </script>
</head>
<body>
    <div id="all">
        <div style="background:#333333;position:fixed;z-index:99;top:0">
            <div class="head">

                <div style="width:220px;margin-top:5px;float:left;padding-left:20px">
                    {*<img src="/public/images/irs/logo_white.png">*}
                </div>
                <div class="title" style="width:530px;float:left">
                    {$course_name}
                </div>
                <div class="prepare" style="width:530px;float:left;padding-right:20px">
                    <span class="time" style="float:right">00:00</span>
                    <a href="#" onclick="touchfullscreen();" title="{'title_full'|WM_Lang}"><div class="icon_full"></div></a>
                    <a href="#" onclick="changecss();" title="{'title_bgchange'|WM_Lang}"><div class="icon_bgchange"></div></a>
                </div>
                <div class="active" style="width:530px;float:left;padding-right:20px">
                    <span class="time" style="float:right">00:00</span>
                    <a href="#" onclick="showPeople();"><div class="icon_people" title="{'title_people'|WM_Lang}"></div></a>
                    <a href="#" onclick="showStastic();"><div class="icon_stastic" title="{'title_stastic'|WM_Lang}"></div></a>
                    <a href="#" onclick="showQrcode();"><div class="icon_qrcode" title="{'title_qrcode'|WM_Lang}"></div></a>
                    <a href="#" onclick="touchfullscreen();"><div class="icon_full" title="{'title_full'|WM_Lang}"></div></a>
                    <a href="#" onclick="changecss();"><div class="icon_bgchange" title="{'title_bgchange'|WM_Lang}"></div></a>
                </div>

            </div>
            <div class="clearboth"></div>
        </div>
        <div class="head"></div>
        <div class="content">
            {* qrcode頁面 *} 
            <div id="qrcode">
                {include file = "irs/exam_qrcode.tpl"}
            </div>
            {* 統計頁面 *}
            <div id="stastic">
                {include file = "irs/exam_stastic.tpl"}
            </div>
            {* 學生狀態 *}
            <div id="people">
                {include file = "irs/exam_people.tpl"}
            </div>
        </div>
    </div>
</body>    
</html>