<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/theme/default/bootstrap336/css/bootstrap.min.css">
<link href="/theme/default/fancybox/jquery.fancybox.css" rel="stylesheet" type="text/css" media="screen">
<title>愛上互動</title>
<style type="text/css">
{literal}

    body {
        font-family: 微軟正黑體,Arial, Helvetica, sans-serif;
    }

    .container {
        max-width:900px;
        font-weight:Bold;
        margin-top:4rem;
        margin-bottom:6rem;
    }
    
    .type {
        background:#E8483F;
        width:9.83rem;
        height:3.75rem;
        font-size: 1.67rem;
        color: #FFFFFF;
        line-height:3.75rem;
        text-align:center;
    }
    
    .quest {
        margin-top:2rem;
        margin-bottom:1rem;
        font-size:2rem;
        color: #000000;
        letter-spacing:0;
        line-height: 2rem;
    }
    
    hr {
        border:0.1rem solid #E8E8E8;
    }
    
    .choice_item {
        background:#F2F2F2;
        border-radius:4px;
        min-height:5rem;
        margin:1rem auto;
        font-size:1.67rem;
        color:#313131;
        text-align:left;
        /*line-height:5rem;*/
        display:flex;
    }
    
    .choice_item_select {
        background:#E8483F;
        border-radius:4px;
        min-height:5rem;
        margin:1rem auto;
        font-size:1.67rem;
        color:#FFFFFF;
        text-align:left;
    }
    
    .radio_icon {
        width:3rem;
        height:3rem;
        background-repeat:no-repeat;
        /*background-size:2rem;*/
        background-image:url('/public/images/irs/radio_quiz_phone.svg');
        margin: 2.5rem 0.5rem 1.5rem 1.5rem;
    }
    
    .radio_icon_select {
        width:3rem;
        height:3rem;
        background-repeat:no-repeat;
        /*background-size:2rem;*/
        background-image:url('/public/images/irs/radio_quiz_select_phone.svg');
        margin: 2.5rem 0.5rem 1.5rem 1.5rem;
    }
    
    .correct_item {
        background:#F2F2F2;
        border-radius:4px;
        min-height:5rem;
        margin:1rem auto;
        font-size:1.67rem;
        color:#313131;
        text-align:left;
        display:flex;
    }
    
    .true_icon {
        width:3rem;
        height:3rem;
        background-repeat:no-repeat;
        /*background-size:2rem;*/
        background-image:url('/public/images/irs/ans_o_phone.svg');
        margin:2.5rem 0.5rem 1.5rem 1.5rem;
    }
    
    .false_icon {
        width:3rem;
        height:3rem;
        background-repeat:no-repeat;
        /*background-size:2rem;*/
        background-image:url('/public/images/irs/ans_x_phone.svg');
        margin:2.5rem 0.5rem 1.5rem 1.5rem;
    }

    banner {
        max-width:900px;
        height:100px;
        background-image:url('/public/images/irs/banner_quiz_phone.png');
        background-repeat:no-repeat;
        background-size: 100%;
    }
    
    textarea {
       width:100%;
       height:30%;
       background: #F2F2F2;
       border-radius: 3px;
       border:0;
       resize : none;
       font-size:2rem;
    }
    
    .attach {
        height:15rem;
        background-repeat:no-repeat;
        background-position: center center;
        background-size: contain;
    }

    #exam {
        color: #ffffff;
        text-shadow: 0px 2px 4px #240f06;
        font-size: 26px;
        margin:auto 10%;
        font-weight: bold;
    }
    
    #banner {
        max-width:900px;
        height:160px;
        background-image:url('/public/images/irs/banner_quiz_phone.png');
        background-repeat:no-repeat;
        background-size: cover;
        display:flex;
    }

    .over-box {
        width:300px;
        height:260px;
        display:none;
        font-size:20px;
    } 

    .icon_warn {
        background-image:url('/public/images/irs/ic-warning.png');
        background-repeat:no-repeat;
        width:95px;height:95px;
        background-position: center center;
        margin:28px auto;
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
        border-radius: 4px;
        width:128px;
        height:38px;
        line-height:38px;
        text-align:center;
        margin: 20 auto;
        text-decoration:none;
    }
    
    
    @media all and (-ms-high-contrast: none), (-ms-high-contrast: active) {
    
        #exam {
            position: absolute;
            top: 50%;
            margin-top: -25px;        
        }
        
        .choice_text {
            height:100%;
        }
    }
    
    @media screen and (max-width:640px) { 
        #banner {
            height:80px;
        }
        
        #exam {
            font-size: 20px;
        }
    
    }

    
{/literal}
</style>
<script type="text/javascript" src="/lib/jquery/jquery.min.js"></script>
<script type="text/javascript" src="/theme/default/fancybox/jquery.fancybox.pack.js"></script>
<script type="text/javascript">
    var forGuest = '{$forGuest}';
    {literal}

    $(window).on('load',function() {
        var height = $('#banner').height();
        $('#clear').height(height);
    });
    
    $(window).bind('orientationchange', function(e){
	    var height = $('#banner').height();
        $('#clear').height(height);
	});
    
    function next(now) {
        var next = now+1;
        $('#item'+now).addClass('hidden');
        $('#button'+now).addClass('hidden');
        $('#item'+next).removeClass('hidden');
        $('#button'+next).removeClass('hidden');
        $(window).scrollTop(0);
    }

    function prevnext(now) {
        var prev = now-1;
        $('#item'+now).addClass('hidden');
        $('#button'+now).addClass('hidden');
        $('#item'+prev).removeClass('hidden');
        $('#button'+prev).removeClass('hidden');
        $(window).scrollTop(0);
    }
    
    function submit_answer() {
         $('#responseForm').submit();
    }
    
    function select(item,num) {
        $('.s_choice'+num).find(".choice_item_select").removeClass('choice_item_select');
        $('.s_choice'+num).find(".radio_icon_select").removeClass('radio_icon_select');
        $(item).addClass('choice_item_select');
        $(item).find(".radio_icon").addClass('radio_icon_select');
        $(item).find("input[type=radio]").prop('checked', true);
    }
    
    function correct(item,num) {
        $('.correct'+num).css('background', '#F2F2F2');
        $(item).css('background', '#E8483F');
        $(item).find("input[type=radio]").prop('checked', true);
    }
    
    function select_m(item) {
        if($(item).find("input[type=checkbox]").attr('checked')=='checked') {
            $(item).removeClass('choice_item_select');
            $(item).find(".radio_icon_select").removeClass('radio_icon_select');
            $(item).find("input[type=checkbox]").prop('checked', false);
        } else {
            $(item).addClass('choice_item_select');
            $(item).find(".radio_icon").addClass('radio_icon_select');
            $(item).find("input[type=checkbox]").prop('checked', true);
        }
    }
    
    function close() {
        alert('byebye');
        window.open(location, '_self').close();
    }

    function modify() {
        var input_name = $("#nick_name").val();
        if (input_name.trim()=='') return;
        $("#nickname").val(input_name);
        close_fancy();
    }

    function close_fancy() {
        $.fancybox.close();
    }

    $(document).ready(function() {   
        $("#start_button").fancybox({
            'padding': 0,
            'margin': 0,
            'modal': true
        });
        
        
        if (forGuest) $("#start_button").click();
        
        
    });
    
    {/literal}
 </script>
</head>
<body>
<a id="start_button" href="#modify-box" style="display: none"></a>
<div class="over-box" id="modify-box">
    <div style="background: #FFFFFF;border-bottom-left-radius: 15px;border-bottom-right-radius: 15px;height: 260px;">
    <div style="background:#E8483F;height:6px;"></div>
    
    <div style="margin-left:100px;margin-bottom:20px;margin-top:30px;"><span style="font-weight:bold;line-height:45px;">暱稱</span></div>
    <div style="text-align:center;"><input name="nick_name" id="nick_name" type="text" class="input-search" placeholder="" maxlength="4" style="width:200px;height:35px;font-size:20px"></div>
    <div>
        <a href="#" onclick="modify();"><div class="true_button">{'ok'|WM_Lang}</div></a>
    </div>
    </div>
</div>

<nav class="navbar-fixed-top">
    <div id="banner" class="center-block">
        <div id="exam">{$title}</div>
    </div>
</nav>

<div id="clear"></div>

