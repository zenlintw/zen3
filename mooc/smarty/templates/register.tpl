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

.input-text {
    width:266px;
    border:1px #edf1f2 solid;
    border-radius:3px;
    background-color:#edf1f2;
    margin-top: 5px;
    margin-bottom: 5px;
}

#main {
    min-height: calc(100vh - 310px);
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
                <span class="t28">{'btn_register'|WM_Lang}</span></div>
        </div>
    </div>
</div>
<form onsubmit="return checkData();" id="formRegister" action="{$appRoot}/mooc/register_p.php" method="post" name="formRegister" accept-charset="UTF-8" lang="zh-tw">
<input type="hidden" name="ticket" id="ticket" value="{$ticket}" />
<div id="loginBlockContainer">
    <div class="container">
    {if $message neq ''}
    <div id="message">{$message}</div>
    {/if}
    </div>
    {if 'Y'|in_array:$canReg || 'C'|in_array:$canReg}
    <div id="divLoginByAccount" class="container">
        <div class="row" style="margin:0 auto;">
            <input type="text" id="username" name="username" class="t14_b input-text" placeholder="{'th_username'|WM_Lang}" value="{$post.username}" onBlur="check_reg_username();">
        </div>
        <div class="row" style="margin:0 auto;">
            <input type="password" id="password" name="password" class="t14_b input-text" placeholder="{'th_password'|WM_Lang}">
        </div>
        <div class="row" style="margin:0 auto;">
            <input type="password" id="repassword" name="repassword" class="t14_b input-text" placeholder="{'repassword'|WM_Lang}">
        </div>
        <div class="row" style="margin:0 auto;">
            <input type="text" id="first_name" name="first_name" class="t14_b input-text" placeholder="{'theading_realname'|WM_Lang}" value="{$post.first_name}">
        </div>
        <div class="row" style="margin:0 auto;">
            <input type="text" id="email" name="email" class="t14_b input-text" placeholder="{'ex_email'|WM_Lang}" value="{$post.email}">
        </div>
        {if $sysEnableCaptcha eq '1'}
        <div class="row" style="margin:0 auto;">
            <div id="captcha-picture">
                <img src="/sys/reg/captcha.php" align="absmiddle" onclick="this.src=this.src;">
            </div>
        </div>
        <div class="row" style="margin:0 auto;">
            <input type="text" id="captcha" name="captcha" class="t14_b input-text" placeholder="{'msg_fill_captcha'|WM_Lang}" value="">
        </div>
        {/if}
        <div class="row t13_g" style="margin:0 auto;">
            {'agreecomply'|WM_Lang} 
            <span class="t13_o">{if $profile.isPhoneDevice}<a href="#" onclick="window.open('{$appRoot}{$policy}')">{else}<a class="privacy-service" data-fancybox-type="iframe" href="{$appRoot}{$policy}">{/if}{'privacyservice'|WM_Lang}</a></span>{* {'and'|WM_Lang} 
            <span class="t13_o"><a href="#" target="_blank">{'user_terms'|WM_Lang}</a></span>*}
        </div>
        <div class="row" style="margin:0 auto;margin-top:15px;">
            <button type="submit" class="t18_w" style="width:100%;height:45px;border:1px #15bc97 solid;border-radius:3px;background-color:#15bc97;" id="btnRegister">{'btn_register'|WM_Lang}</button>
        </div>
    </div>
    {/if}
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
<script type="text/javascript">
    {$msg}
    var sysAccountMinLen = {$sysAccountMinLen};
    var sysAccountMaxLen = {$sysAccountMaxLen};
    var Account_format = {$Account_format};
    var mail_rule = {$mail_rule}
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
<script type="text/javascript" src="{$appRoot}/mooc/public/js/register.js"></script>
<script type="text/javascript" src="{$appRoot}/mooc/public/js/password_strong.js"></script>
<script type="text/javascript" src="{$appRoot}/lib/xmlextras.js"></script>
<script type="text/javascript" src="{$appRoot}/lib/filter_spec_char.js"></script>
{include file = "common/site_footer.tpl"}