<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/theme/default/bootstrap336/css/bootstrap.min.css">
<link rel="stylesheet" href="/theme/default/fancybox/jquery.fancybox.css">
<title>{$appTitle}</title>
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
        line-height:60px;
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
        background-image:url('/public/images/irs/qrcode-default-black.png');
        background-repeat:no-repeat;
        width:433px;height:530px;
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
        background-image:url('/public/images/irs/rollcall-wh.svg');
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
    }

    /* selected link */
    a:active {
        color: #FFFFFF;
    }
    
    .head1 {
        width:220px;
        margin-top:5px;
        float:left;
        padding-left:20px;
    }
    
    .head2 {
        width:530px;
        float:left;
    }
    
    .head3{
        width:530px;
        float:left;
        padding-right:20px;
    }
    
    .qrcode_left {
        width:635px;
        float:left;
    }
    
    .qrcode_right {
        width:640px;
        float:left;
        padding:0 10;
    }
    
    .active_number {
        font-size:90px;
    }
    
    @media (max-width: 1279px) {
        .head {
            width:1024px;
        }
        
        .title {
            text-align:center;
            font-size:24px;
        }
        
        .head1 {
            width:150px;
        }
        
        .head2 {
            width:450px;
        }
        
        .head3 {
            width:400px;
        }
        
        .qrcode_left {
            width:590px;
        }
        
        .qrcode_right {
            width:400px;
        }
        
        .active_number {
            font-size:60px;
        }
        
        .content {
            width:1024px;
        }
    }


{/literal}
</style>
<script type="text/javascript" src="/lib/jquery/jquery.min.js"></script>
<script type="text/javascript" src="/theme/default/fancybox/jquery.fancybox.pack.js"></script>
<script type="text/javascript">

    var color = 'black';
    var total_seconds  = 0;
    var tc;
    var csid = {$course_id};
    var type = '{$qti_type}';
    var t_status,flag_status = 0;
    var t_result,flag_result = 0;
    var t_people,flag_people = 0;
    var save = false;
    var publish = 'prepare';
    var rollcall_id = {$rollcall_id};
    var major_count = parseInt('{$major_count}');

    {literal}

    function changecss() {
        if (color=='black') {
            $('.content').css({"color":"#000000","background":"#ffffff",});
            $('body').css({"background":"#ffffff",});
            $('html').css({"background":"#ffffff",});
            $('.tip_img').css({"background-image":"url('/public/images/irs/rollcall-bl.svg')"});
            $('.refresh').attr('src', '/public/images/irs/ic_refresh_hover.png');
            $('.prev').attr('src', '/public/images/irs/ic_pre_hover.png');
            $('.next').attr('src', '/public/images/irs/ic_next_hover.png');
            $('.fa-arrow-circle-left').css({"color":"#000000"}); 
            $('.fa-arrow-circle-right').css({"color":"#000000"});
            $('.prepare_img').css({"background-image":"url('/public/images/irs/qrcode-default-white.png')"}); 
            color = 'white'; 
        } else {
            $('.content').css({"color":"#ffffff","background":"#333333",});
            $('body').css({"background":"#333333",});
            $('html').css({"background":"#333333",});
            $('.tip_img').css({"background-image":"url('/public/images/irs/rollcall-wh.svg')"});
            $('.refresh').attr('src', '/public/images/irs/ic_refresh.png');
            $('.prev').attr('src', '/public/images/irs/ic_pre.png');
            $('.next').attr('src', '/public/images/irs/ic_next.png');
            $('.fa-arrow-circle-left').css({"color":"#ffffff"}); 
            $('.fa-arrow-circle-right').css({"color":"#ffffff"});
            $('.prepare_img').css({"background-image":"url('/public/images/irs/qrcode-default-black.png')"}); 
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
            'url': '/mooc/teach/rollcall/status.php',
            'type': 'POST',
            'data': {'action': 'get_status','rollcall_id':rollcall_id},
            'dataType': "json",
            'success': function (res) {
                $('#submit_num').html(res.submit);
                $('#submit_rate').html(res.submit_rate);
                $('#major_count').html(res.major);
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

    function getQrcodeUrl() {
        $.ajax({
            'url': '/mooc/teach/rollcall/status.php',
            'type': 'POST',
            'data': {'action': 'getQrcodeUrl','rollcall_id':rollcall_id},
            'dataType': "json",
            'async': false,
            'success': function (res) {
                if(res.code==1) {
                    $("#iframeQrcode").attr('src', res.url);
                }
            },
            'error': function () {
                alert('call getQrcodeUrl: push Ajax Error.');
            }
        });
    }

    function active() {
        opener.location.reload();
        window.focus();
        getQrcodeUrl();
        tc = setInterval('countDowm()', 1000);
        $('#qrcode .prepare').hide();
        $('#qrcode .active').show();
        $('.head .prepare').hide();
        $('.head .active').show();
        get_status();
        t_status = setTimeout('get_status()', 30000);
        t_people = setTimeout('getPeople()', 30000);
    }

    function start() {
        close_fancy();
        $.ajax({
            'url': '/mooc/teach/rollcall/status.php',
            'type': 'POST',
            'data': {'action': 'start_active','course_id':csid},
            'dataType': "json",
            'async': false,
            'success': function (res) {
                if(res.code==1) {
                    publish = 'action';
                    rollcall_id = res.rid;
                    active();
                } else {
                    alert(res.errorMsg);
                    opener.location.reload();
                    window.close();
                }
            },
            'error': function () {
                alert('push Ajax Error.');
            }
        });
    }

    function over() {
        $.ajax({
            'url': '/mooc/teach/rollcall/status.php',
            'type': 'POST',
            'data': {'action': 'over_active','rollcall_id':rollcall_id},
            'dataType': "json",
            'success': function (res) {
                if(res.code==1) {
                    opener.location.reload();
                    save = true;
                    window.close();
                }
            },
            'error': function () {
                alert('push Ajax Error.');
            }
        });
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
            'url': '/mooc/teach/rollcall/status.php',
            'type': 'POST',
            'data': {'action': 'get_people','rollcall_id':rollcall_id},
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

    $(document).ready(function() {
        $('#stastic').hide();
        $('#people').hide();
        $('#qrcode .active').hide();
        $('.head .active').hide();
        if (rollcall_id > 0) {
            publish = 'action';
            active();
        }

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
        
        $('#all').width($(window).width());
        if ($(window).width() < 1280) {
            $('.clearboth').width(1024);
        } else {
            $('.clearboth').width($(window).width());
        }

    });

    $(window).resize(function() {
        $('#all').width($(window).width());
        if ($(window).width() < 1280) {
            $('.clearboth').width(1024);
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

                <div class="head1">
                    {*<img src="/public/images/irs/logo_white.png">*}
                </div>
                <div class="title head2">
                    {$course_name}
                </div>
                <div class="prepare head3">
                    <span class="time" style="float:right">00:00</span>
                    <div class="icon_people" title="{'title_people'|WM_Lang}"></div>

                    <div class="icon_qrcode" title="{'title_qrcode'|WM_Lang}"></div>
                    <a href="#" onclick="touchfullscreen();" title="{'title_full'|WM_Lang}"><div class="icon_full"></div></a>
                    <a href="#" onclick="changecss();" title="{'title_bgchange'|WM_Lang}"><div class="icon_bgchange"></div></a>
                </div>
                <div class="active head3">
                    <span class="time" style="float:right">00:00</span>
                    <a href="#" onclick="showPeople();"><div class="icon_people" title="{'title_people'|WM_Lang}"></div></a>
                    
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
            {include file = "teach/rollcall/qrcode.tpl"}
        </div>
        {* 學生狀態 *}
        <div id="people">
            {include file = "teach/rollcall/people.tpl"}
        </div>
    </div>
    </div>
</body>
</html>