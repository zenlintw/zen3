<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/theme/default/bootstrap336/css/bootstrap.min.css">
<link rel="stylesheet" href="/sys/tpl/vendor/font-awesome/css/font-awesome.css">
<link rel="stylesheet" href="/theme/default/fancybox/jquery.fancybox.css">
<title>{'title_code'|WM_Lang}</title>
<style type="text/css">
{literal}
    body {
        background-color: #FFFFFF;
        font-family: 微軟正黑體;
    }
    #login_top {
        margin: 0 auto;
        height:40%;
        width:100%;
        position:absolute;
    }
    .logo-block{
        position: relative;
        top: 15%;
        text-align: center;
    }
    .logo-image{
      
    }

    .logo-font {
        color:#FFFFFF;
        font-size: 36px;
        line-height: 35px;
        font-weight: bolder;
        text-shadow: 0 2px 4px #240F06;
    }
    
    .info-font {
        color:#FFFFFF;
        font-size: 20px;
        line-height: 35px;
        text-shadow: 0 2px 4px #240F06;
    }
    
    .input-line{
        text-align: center;
        border-style: none none solid none;
        border-color: #C3C3C3;
        border-width: 2px;
        float: none;
        margin: 0 auto;
        padding-left:0px;
        white-space: nowrap;
        max-width: 280px;
    }
    .input-code{
        background-image: linear-gradient(-180deg, #F2F2F2 0%, #F9F9F9 100%);
        border: 0 solid #FFFFFF;
        box-shadow: inset 0 1px 2px 0 #989898;
        border-radius: 6px;
        width:270;
        font-size: 36px;
        font-weight:bold;
        color: #F77B55;

    }

    input:focus{
        border: 0px;
        outline: none;
    }
    
    ::placeholder { /* Chrome, Firefox, Opera, Safari 10.1+ */
        color: #ABABAB;
        opacity: 1; /* Firefox */
        font-size: 28px;
        font-weight:bold;
    }
    
    ::-webkit-input-placeholder { /* Chrome/Opera/Safari */
	    color: #ABABAB;
	    font-size: 28px;
        font-weight:bold;
	}

    :-ms-input-placeholder { /* Internet Explorer 10-11 */
        color: #ABABAB;
        font-size: 28px;
        font-weight:bold;
    }

    ::-ms-input-placeholder { /* Microsoft Edge */
        color: #ABABAB;
        font-size: 28px;
        font-weight:bold;
    }
    
    :-moz-placeholder { /* Firefox 18- */
	    color: #ABABAB;
	    font-size: 28px;
        font-weight:bold;
	}
    
    .button-join {
        background: #FF7F5C;
        line-height: 60px;
        color: #FFFFFF;
        font-weight: bold;
        margin: 0 auto;
        border-radius: 6px;
        font-size:2rem;
        letter-spacing:-0.38px;
        text-align:center;
    }
    
    .title-join {
        color: #FF7F5C;
        font-weight: bold;
        font-size:3rem;
        text-align:left;
    }

    .alert{
        color: red;
        font-size:2rem;
        font-weight: bold;
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
	
	.tap {
	    /*background-image:url('/theme/default/irs/code.png');*/
	    background-image:url('/public/images/irs/pic_login_phone.png');
        background-repeat:no-repeat;
        background-size: contain;
        height:50%;
        background-position: center;
        position:relative;
        top:10%;
	}
	
	.main {
	    max-width: 300px;
	    margin: 0 auto;
	    position: absolute;
	    top: 50%;
	    left: 50%;
	    margin-left: -150;
	    margin-top: -110px;
	}
	
	.over-box {
        width:500px;
        height:270px;
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

    .background {
        background-image:url('/public/images/irs/login_top_pad.png');
        position: absolute;
        width: 100%;
        height: 105%;
        background-position: center center;
        background-repeat: no-repeat;
        background-size: cover;
    }
    
    a:hover {
	    text-decoration: none;
	}

    @media (max-width: 767px) {
            .over-box {
                width:300px;
            }
            .background {
                background-image:url('/public/images/irs/login_top_phone.png');
                height: 100%;
            }

        }
	
{/literal}
</style>
<script type="text/javascript" src="/lib/jquery/jquery.new.min.js?20200113"></script>
<script type="text/javascript" src="/theme/default/fancybox/jquery.fancybox.pack.js"></script>
<script type="text/javascript" src="/theme/default/bootstrap336/js/bootstrap.min.js"></script>
</head>
<body>
    <div id="login_top">
        <div class="background"></div>
        <div class="tap"></div>
        <div class="logo-block">
            <div class="logo-font">{'title_code'|WM_Lang}</div>
            {if $name!=''}
            <br/>
            {*<div class="info-font"><a href="/logout.php" target="_top"><i class="fa fa-sign-out" aria-hidden="true" style="margin-right:0.3em;"></i>{'logout'|WM_Lang}</a>({$name})</div>*}
            {/if}
        </div>
    </div>
    
    <div style="width:100%;height:60%;text-align:center;position:absolute;top: 40%;">
        <form method="post" action="{$appRoot}/mooc/user/join.php" id="joinForm" name="joinForm" style="max-width: 500px;margin: 0 auto;">
            <input type="hidden" name="ticket" value="{$ticket}">
            <div class="container main">
                <div class="title-join"><img src="/public/images/irs/ic_flash.svg" style="width:50px;height:50px;"/>&nbsp;{'fast_join'|WM_Lang}</div>
                <div style="margin-top:2.5rem;text-align:left;"><input type="text" id="code" value="{$code}" name="code" maxlength="6" class="input-code" placeholder="{'tip_input'|WM_Lang}"></div>
                <div class="row" style="margin-top:2.5rem;">
                    <div class="col-md-3 col-xs-2"></div>
                    <a href="#" onclick="doJoin();"><div class="col-md-6 col-xs-8 button-join">{'join'|WM_Lang}</div></a>
                    <div class="col-md-3 col-xs-2"></div>
                    <a id="start_button" href="#start-box" style="display:hidden"></a>
                </div>
            </div>
        </form>
    </div>
    
    <div class="over-box" id="start-box">
	    <div style="background: #FFFFFF;box-shadow: 1px 5px 5px 0 rgba(0,0,0,0.50);border-bottom-left-radius: 15px;border-bottom-right-radius: 15px;height: 270px;">
		    <div style="background:#E8483F;height:6px;"></div>
		    <div class="icon_warn"></div>
		    <div style="text-align:center;">{$message}</div>
		    <div>
		        <a href="#" onclick="close_fancy();"><div class="true_button">{'ok'|WM_Lang}</div></a>
		    </div>
	    </div>
    </div>
    

</div>
    
    
<script language="javascript" src="{$appRoot}/lib/md5.js"></script>
<script language="javascript" src="{$appRoot}/lib/des.js"></script>
<script language="javascript" src="{$appRoot}/lib/base64.js"></script>
<script language="javascript" src="{$appRoot}/sys/tpl/login.js"></script>
<script language="javascript">

    var MSG_TIP_INPUT = '{'tip_input'|WM_Lang}';
    var msg = '{$message}';
    {literal}

    function doJoin(){
        if (formSubmit()){
            document.getElementById("joinForm").submit();
        }
    }

    function formSubmit() {
        $('.alert').remove();
    
        if ($("#code").val() == '') {
            alert(MSG_TIP_INPUT);
            $('#code').focus();
            return false;
        }
        return true;
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
        
        if (msg!='') {
            $("#start_button").click();
        }
        
    });

    {/literal}
</script>
</body>
</html>
