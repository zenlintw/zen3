{include file = "common/tiny_header.tpl"}
{include file = "common/site_header.tpl"}
<style>
{literal}
#loginBlockContainer {
    margin-top: 10px;
    text-align: center;
    min-height: calc(100vh - 381px);
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

@media (min-width: 1320px) {
    #loginBlockContainer {
        margin-top: 30px;
        text-align: center;
        min-height: calc(100vh - 401px);
    }
}

{/literal}
</style>

<div id="loginBlockHeader">
    <div class="container" style="max-width: 360px;">
        <div class="row" style="margin: 0 auto;">
            <div class="col-md-12 col-xs-12" style="text-align: center;border-bottom: 1px solid #dfdfdf;">
                <span class="t28">{'titleresend'|WM_Lang}</span></div>
        </div>
    </div>
</div>
<form onsubmit="return checkData();" id="formResend" action="{$appRoot}/mooc/resend_p.php" method="post" name="formResend" accept-charset="UTF-8" lang="zh-tw">
<input type="hidden" name="token" value="{$csrfToken}" />
<div id="loginBlockContainer">
    <div class="container">
    {if $message neq ''}
    <div id="message">{$message}</div>
    {/if}
    </div>
    <div id="divLoginByAccount" class="container">
        {if $username neq '' && $email neq ''}
            <div class="row" style="margin:0 auto;">
                <input type="text" class="t14_b input-text" placeholder="{'th_username'|WM_Lang}" value="{$username}" disabled>
                <input type="hidden" id="resendto" name="resendto" value="{$username}">
	        </div>
	        <input type="hidden" id="oemail" name="oemail" value="{$email}">
            <input type="hidden" id="oemail" name="encemail" value="{$encemail}">
	        <div class="row" style="margin:0 auto;">
	            <input type="text" id="email" name="email" class="t14_b input-text" placeholder="{'ex_email'|WM_Lang}" value="{$email}">
	        </div>
        {else}
	        <div class="row" style="margin:0 auto;">
	            <input type="text" id="resendto" name="resendto" class="t14_b input-text" placeholder="{'th_username'|WM_Lang}" value="{$post.resendto}">
	        </div>
	        <div class="row" style="margin:0 auto;">
	            <input type="text" id="email" name="email" class="t14_b input-text" placeholder="{'ex_email'|WM_Lang}" value="{$post.email}">
	        </div>
        {/if}
        <div class="row" style="margin:0 auto;margin-top:15px;">
            <button type="submit" class="t18_w" style="width:100%;height:45px;border:1px #15bc97 solid;border-radius:3px;background-color:#15bc97;">{'btn_submit'|WM_Lang}</button>
        </div>
    </div>
</div>
</form>


{include file = "common/site_footer.tpl"}
<script type="text/javascript">{$msg}var sysAccountMinLen = {$sysAccountMinLen}; var sysAccountMaxLen = {$sysAccountMaxLen}; var Account_format = {$Account_format}; var mail_rule = {$mail_rule}</script>
<script type="text/javascript" src="{$appRoot}/mooc/public/js/resend.js?{$smarty.now}"></script>
<script type="text/javascript">
    username = '{$post.username}', email = '{$post.email}';
</script>