<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="/theme/default/bootstrap336/css/bootstrap.min.css">
<title>愛上互動</title>
<style type="text/css">
{literal}
    body {
        background-color: #FFFFFF;
        font-family: 微軟正黑體;
    }
    #login_top {
        height: 210px;
        max-width: 500px;
        margin: 0 auto;
    }
    .logo-block{
        position: relative;top: -200px;text-align: center;
    }
    .logo-image{
        width:139px;
        height: 139px;
    }

    .logo-font {
        color:#FFFFFF;
        font-size: 28px;
        line-height: 35px;
        font-weight: bolder;
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
    .input-large{
        font-size: 18px;
        line-height: 23px;
        color: #000000;
        font-family: 微軟正黑體;
        border: 0px;
        margin-bottom: 10px;
    }

    input:focus{
        border: 0px;
        outline: none;
    }

    ::placeholder { /* Chrome, Firefox, Opera, Safari 10.1+ */
        color: black;
        opacity: 1; /* Firefox */
    }

    :-ms-input-placeholder { /* Internet Explorer 10-11 */
        color: black;
    }

    ::-ms-input-placeholder { /* Microsoft Edge */
        color: black;
    }
    .button-login {
        background: #29933A;
        border: 1px solid #FFFFFF;
        line-height: 40px;
        color: #FFFFFF;
        font-weight: bold;
        margin: 0 auto;
        border-radius: 100px;
        font-size:2rem;
        letter-spacing:-0.38px;
        text-align:center;
    }

    .alert{
        color: red;
        font-size: 16px;
    }
{/literal}
</style>
<script type="text/javascript" src="/lib/jquery/jquery.new.min.js?20200113"></script>
<script type="text/javascript" src="/theme/default/bootstrap336/js/bootstrap.min.js"></script>
</head>
<body>
    <div id="login_top">
        <img src="/public/images/irs/login_top_phone.png" style="width:100%;height: 210px; max-width: 500px;">
        <div class="logo-block">
            <div><img src="/public/images/irs/pic_login_phone.png" class="logo-image" /></div>
            <div class="logo-font">{'iSunFuDon'|WM_Lang}</div>
        </div>
    </div>

    <div style="height:40px;" />
    <div style="max-width: 500px;text-align: center;margin: 0 auto;">
        <form method="post" action="{$appRoot}/login.php" id="loginForm" name="loginForm" style="max-width: 500px;margin: 0 auto;">
            <input type="hidden" name="reurl" value="{$reurl}">
            <input type="hidden" name="login_key" value="{$loginKey}">
            <input type="hidden" name="encrypt_pwd" value="">
            <input type="hidden" name="irsGoto" value="{$irsGoto}">
            {if $message neq ''}
            <div class="alert alert-error">
                {$message}
            </div>
            {/if}
            <div class="container" style="max-width: 500px;">
                <div class="row" style="{if $message eq ''}margin-top:5rem;{/if}margin-bottom:5rem;text-align: center;">
                    <div class="col-md-12" style="margin-top: 15px;">
                        <div class="col-xs-8 input-line" style="">
                        <img src="/public/images/irs/ic_id_phone.svg" style="padding-bottom: 5px;width:20px;height:25px;"/>
                        <input type="text" id="username" name="username" class="input-large" placeholder="{'msg_username'|WM_Lang}" value="{$username}">
                        </div>
                    </div>
                    <div class="col-md-12" style="margin-top: 15px;">
                        <div class="col-xs-8 input-line" style="">
                        <img src="/public/images/irs/ic_sn_phone.svg" style="padding-bottom: 5px;width:20px;"/>
                        <input type="password" id="password" name="password" class="input-large" placeholder="{'msg_password'|WM_Lang}" value="">
                        </div>
                    </div>
                </div>
                <div class="row" style="margin-top:2.5rem;">
                    <div class="col-md-3 col-xs-2"></div>
                    <div class="col-md-6 col-xs-8 button-login" onclick="doLogin();">登入</div>
                    <div class="col-md-3 col-xs-2"></div>
                </div>
            </div>
        </form>
    </div>
<script language="javascript" src="{$appRoot}/lib/md5.js"></script>
<script language="javascript" src="{$appRoot}/lib/des.js"></script>
<script language="javascript" src="{$appRoot}/lib/base64.js"></script>
<script language="javascript" src="{$appRoot}/sys/tpl/login.js"></script>
<script language="javascript">
    var
        MSG_NEED_USERNAME = '{'msg_fill_username'|WM_Lang}';
        MSG_NEED_PASSWORD = '{'msg_fill_password'|WM_Lang}';
        MSG_NEED_CAPTCHA  = '{'msg_fill_captcha'|WM_Lang}';
        MSG_CAPTCHA_ERROR = '{'captcha_error'|WM_Lang}';

    {literal}
    $(function () {
        $('#username').focus();
    });

    function doLogin(){
        if (formSubmit()){
            document.getElementById("loginForm").submit();
        }
    }

    function formSubmit() {
        $('.alert').remove();
        var check = checkLogin();
        if (check === false) {
            return false;
        }
        return true;
    }

    {/literal}
</script>
</body>
</html>
