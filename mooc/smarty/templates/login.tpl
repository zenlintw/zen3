{include file = "common/tiny_header.tpl"}
{include file = "common/site_header.tpl"}
<style>
{literal}
#loginBlockContainer {
    margin-top: 10px;
    text-align: center;
}

#loginBlockContainer > .container {
    width: 266px;
    padding-left: 0px;
    padding-right: 0px;
}

#course-tabs {
    max-width: 266px;
}

.alert {
    padding: 0px;
    margin-top: 20px;
    color: #FF0000;
}

#main {
    min-height: calc(100vh - 310px);
}

.input-text {
    width:266px;
    border:1px #edf1f2 solid;
    border-radius:3px;
    background-color:#edf1f2;
    margin-top: 5px;
    margin-bottom: 5px;
}

.input-text::placeholder { /* Chrome, Firefox, Opera, Safari 10.1+ */
    font-size: 14px;
    color: #9da0a4;
    opacity: 1; /* Firefox */
}

.input-text:-ms-input-placeholder { /* Internet Explorer 10-11 */
    font-size: 14px;
    color: #9da0a4;
}

.input-text::-ms-input-placeholder { /* Microsoft Edge */
    font-size: 14px;
    color: #9da0a4;
}

.t14_b {
    font-size: 14px !important;
    color: #9da0a4;
}

.fb-icon {
    margin-bottom: -4px;
}

#buttonLoginByAccount {
    width:100%;
    height:45px;
    border:1px #15bc97 solid;
    border-radius:3px 0px 0px 3px;
    background-color:#15bc97;
}

#buttonLoginByQrcode {
    width:100%;
    height:45px;
    border:1px #15bc97 solid;
    border-radius:0px 3px 3px 0px;
    background-color:#fff;
}

#captcha-picture {
    width:100%;
}

@media (min-width: 1320px) {
    #loginBlockContainer {
        margin-top: 30px;
        text-align: center;
    }
}

{/literal}
</style>
<div id="main">
<div id="loginBlockHeader">
    <div class="container" style="max-width: 360px;">
        <div class="row" style="margin: 0 auto;">
            <div class="col-md-12 col-xs-12" style="text-align: center;border-bottom: 1px solid #dfdfdf;">
                <span class="t28">{'login'|WM_Lang}</span></div>
        </div>
    </div>
</div>

<form method="post" action="{$appRoot}/login.php" id="loginForm" name="loginForm" onsubmit="return formSubmit();" autocomplete="off">
<input type="hidden" name="reurl" value="{$reurl}">
<input type="hidden" name="login_key" value="{$loginKey}">
<input type="hidden" name="encrypt_pwd" value="">
<div id="loginBlockContainer">
    <div class="container">
        {if $qrCodeLoginUrl neq ''}
        <div id="course-tabs" class="row" style="margin:0 auto;">
            <div class="row" style="padding: 0px; margin: 0px;">
                <div class="col-md-6 col-xs-6" style="padding:0px;max-width:133px;"><input id="buttonLoginByAccount" type="button" class="t16_w_b" style="" value="{'sign_account'|WM_Lang}" onclick="showLoginByAccount();"></div>
                <div class="col-md-6 col-xs-6" style="padding:0px;max-width:133px;"><input id="buttonLoginByQrcode" type="button" class="t16_b" style="" value="{'Scan_login'|WM_Lang}" onclick="showLoginByQrcode();"></div>
            </div>
        </div>
        {/if}
            {if $message neq ''}
            <div class="alert alert-error">
                {$message}
                <button type="button" class="close" data-dismiss="alert">×</button>
            </div>
            {/if}
    </div>
    <div id="divLoginByAccount" class="container">
        <div class="row" style="margin:0 auto;">
            <input type="text" id="username" name="username" class="t14_b input-text" placeholder="{'th_username'|WM_Lang}" value="{$username}">
        </div>
        <div class="row" style="margin:0 auto;">
            <input type="password" id="password" name="password" class="t14_b input-text" placeholder="{'th_password'|WM_Lang}">
        </div>
        
        {if $sysEnableCaptcha eq '1'}
            <div class="control-group">
                <label class="control-label" for="captcha">
                    <div id="captcha-picture">
                        <img src="/sys/reg/captcha.php" align="absmiddle" onclick="this.src=this.src;">
                    </div>
                </label>
                <div class="row" style="margin:0 auto;">
                    <input type="text" id="captcha" name="captcha" class="t14_b input-text" placeholder="{'msg_fill_captcha'|WM_Lang}" value="">
                </div>
            </div>
        {/if}

        <div class="row" style="margin:0px;margin-top:15px;text-align: left;">
                                        <span style="text-align:left"><input type="checkbox" name="persist_login" value="1" style="margin:0 2px;" /></span>
                                        <span>{'msg_stay_signed_in'|WM_Lang}</span>
        </div>
        <div class="row" style="margin:0px;margin-top:10px;text-align: left;">
            <span class="t13_o">{'msg_stay_signed_in_notes'|WM_Lang}</span>
        </div>
        <div class="row" style="margin:0 auto;margin-top:15px;">
            <button type="submit" class="t18_w" style="width:100%;height:45px;border:1px #15bc97 solid;border-radius:3px;background-color:#15bc97;" id="btnSignIn">{'login'|WM_Lang}</button>
        </div>
        <div class="row" style="margin:0 auto;margin-top:15px;">
            <div class="col-md-5 col-xs-5" style="padding-left:0px;text-align: left;">
                <a href="/mooc/forget.php" class="t13_o">{'btn_query_password'|WM_Lang}?</a>
            </div>
            <div class="col-md-7 col-xs-7 t13_b" style="text-align:right;">
            {if 'Y'|in_array:$canReg}
                    {'no_account'|WM_Lang}<a href="{$appRoot}/mooc/register.php" class="t13_o" id="btnRegister" style="margin-left:5px;">{'btn_register'|WM_Lang}</a>
            {/if}
            </div>
        </div>
    </div>
    <div id="divLoginByQrcode" class="container" style="margin-top: 30px;margin-bottom: 30px;display: none;">
        <div class="row" style="width:228px;margin:0 auto;">
            <div class="col-md-12 col-xs-12" style="width:228px;height:228px;border:8px solid #DFDFDF;padding:0px;">
                <iframe id="iframe-qrcode" src="about:blank" scrolling="no" style="padding:0px;width:214px;height:214px;border:0px;margin:0px;"></iframe>
            </div>
        </div>
    </div>
    {if 'FB'|in_array:$canReg}
    <div id="divLoginByFaceBook" class="container" style="margin-bottom: 30px;">
        <div class="row" style="margin:0 auto;">
            <div class="line">{'another_login_way'|WM_Lang}</div>
        </div>
        <div class="row" style="margin:0 auto;">
            <button type="button" class="t18_w" style="width:100%;height:45px;border:1px #274fa5 solid;border-radius:3px;background-color:#274fa5;" id="fb-btn"><i class="fb-icon"></i>{'loginwithfb'|WM_Lang}</button>
        </div>
    </div>
    {/if}
</div>
</form>
</div>
<form method="post" action="message.php?type=5" id="msgForm" name="msgForm">
    <input type="hidden" name="action" value="resend">
    <input type="hidden" name="login_key" value="{$loginKey}">
    <input type="hidden" name="username" value="">
    <input type="hidden" name="email" value="">
    <input type="hidden" name="encemail" value="">
</form>

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
        QrcodeLoginTimer = null;
        QrcodeLoginTries = 0;
</script>

{if 'FB'|in_array:$canReg}
<script language="javascript">
    var
        clientId = '{$FB_APP_ID}',
        redirectUri = '{$appRoot}/mooc/fb_login.php';

    {literal}
    $(function () {
        $('#fb-btn').click(function () {
            window.location.href = 'https://www.facebook.com/dialog/oauth?client_id=' + clientId + '&redirect_uri=' + redirectUri + '&scope=email';
        });
    });
    {/literal}
</script>
{/if}

<script language="javascript">
    {literal}
    function QrcodeLoginTimerCheck() {
        QrcodeLoginTries++;
        if (QrcodeLoginTries > 36) {
            showLoginByAccount();
            return false;
        }
        $.ajax({
            'type': 'POST',
            'dataType': 'json',
            'data': {'action' : 'checkQrcodeLogin'},
            'url': appRoot + '/mooc/controllers/user_ajax.php',
            'async': false,
            'success': function (data) {
                console.log(data.code);
                if (parseInt(data.code) == 1){
                    clearInterval(QrcodeLoginTimer);
                    document.location.replace('/mooc/index.php');
                }
            },
            'error': function () {
                clearInterval(QrcodeLoginTimer);
            }
        });
    }

    function showLoginByAccount() {
        clearInterval(QrcodeLoginTimer);
        $('#buttonLoginByAccount').css('background-color', '#15bc97');
        $('#buttonLoginByAccount').css('color', '#FFFFFF');
        $('#buttonLoginByAccount').css('font-weight', 'bold');
        $('#buttonLoginByQrcode').css('background-color', '#FFFFFF');
        $('#buttonLoginByQrcode').css('color', '#000000');
        $('#buttonLoginByQrcode').css('font-weight', 'initial');
        $('#divLoginByQrcode').hide();
        $('#divLoginByAccount').show();
    }

    function showLoginByQrcode() {
        $('#buttonLoginByAccount').css('background-color', '#FFFFFF');
        $('#buttonLoginByAccount').css('color', '#000000');
        $('#buttonLoginByAccount').css('font-weight', 'initial');
        $('#buttonLoginByQrcode').css('background-color', '#15bc97');
        $('#buttonLoginByQrcode').css('color', '#FFFFFF');
        $('#buttonLoginByQrcode').css('font-weight', 'bold');
        getLoginQrcode4meUrl();
        $('#divLoginByQrcode').show();
        $('#divLoginByAccount').hide();
    }

    function getLoginQrcode4meUrl() {
        var rtn = '';
        $.ajax({
            'type': 'POST',
            'dataType': 'json',
            'data': {'action' : 'LoginQrcode4me'},
            'url': '/mooc/controllers/user_ajax.php',
            'async': false,
            'success': function (data) {
                if (parseInt(data.code) == 1){
                    rtn = data.data;
                    $('#iframe-qrcode').attr('src', rtn);
                    QrcodeLoginTimer = setInterval(function(){ QrcodeLoginTimerCheck() }, 5000);
                    QrcodeLoginTries = 0;
                }else{
                    rtn = 'about:blank';
                }
            },
            'error': function () {
                rtn = 'about:blank';
            }
        });
    }

    $(function () {
        $('#username').focus();
        $('#iframe-qrcode').on("load", function() {
            $('#iframe-qrcode').get(0).contentWindow.document.getElementsByTagName("body")[0].style.margin = '0px';
        });
    });

    function formSubmit() {
        $('.alert').remove();
        var check = checkLogin();
        if (check === false) {
            return false;
        } else {
            var check2 = checkTmpAccount();
            if (check2 === false) {
                $('#msgForm').submit();
                return false;
            } else if (check2 === -1) {
                return false;
            } else {
                return true;
            }
        }
    }

    // 確認帳號是否驗證
    function checkTmpAccount() {
        var rn = false;
        $.ajax({
            'type': 'POST',
            'dataType': 'json',
            'data': $('#loginForm').serialize() + '&action=getTmpAccount',
            // {'action' : 'getTmpAccount', 'username' : $('#username').val()},
            'url': appRoot + '/mooc/controllers/user_ajax.php',
            'async': false,
            'success': function (data) {
                data = json2array(data);
                switch(data[0].code) {
                    case 1:
                        rn = false;
                        $("#msgForm input[name='username']").val(data[0].username);
                        $("#msgForm input[name='email']").val(data[0].email);
                        $("#msgForm input[name='encemail']").val(data[0].encemail);
                        break;
                    case -1:
                        rn = -1;
                        alert(MSG_CAPTCHA_ERROR);
                        location.href = '/mooc/login.php';
                        break;
                    case 2:
                        rn = true;
                        break;
                    case 3:
                        rn = -1;
                        location.href = '/mooc/message.php?type=1';
                        break;
                }
            },
            'error': function () {
                alert('Ajax Error!');
            }
        });
        return rn;
    }
    {/literal}
</script>
{include file = "common/site_footer.tpl"}