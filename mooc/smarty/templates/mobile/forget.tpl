<div id="wrap">
    {include file = "mobile/common/site_header.tpl"}
    <h4>{'btn_query_password'|WM_Lang}</h4>
    <div class="btn-group pull-right" role="group">
        {if 'Y'|in_array:$canReg}
            <a href="{$appRoot}/mooc/register.php" class="btn btn-default">{'btn_register'|WM_Lang}</a>
        {/if}
        <a href="{$appRoot}/mooc/login.php" class="btn btn-default">{'login'|WM_Lang}</a>
    </div>
    <form onsubmit="return checkData();" id="formForget" action="{$appRoot}/mooc/forget_p.php" method="post" class="well form-horizontal" name="formForget" accept-charset="UTF-8" lang="zh-tw">
        <div id="message">{$message}</div>
        <div class="form-group">
            <label for="username" class="col-sm-2 control-label-c">{'th_username'|WM_Lang}</label>
            <div class="col-sm-10">
                <input type="text" id="username" name="username" class="form-control" placeholder="{'msg_fill_username'|WM_Lang}" value="{$post.username}">
            </div>
        </div>
        <div class="form-group">
            <label for="email" class="col-sm-2 control-label-c">{'ex_email'|WM_Lang}</label>
            <div class="col-sm-10">
                <input type="text" id="email" name="email" class="form-control" placeholder="{'msg_fill_email'|WM_Lang}" value="{$post.email}">
                <br><span class="lcms-text-import">{'accountoremail'|WM_Lang}</span>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" class="btn btn-primary btn-blue btnNormal">{'btn_submit'|WM_Lang}</button>
            </div>
        </div>
        {if 'FB'|in_array:$canReg}
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    {include file = "fb_login_btn.tpl"}
                </div>
            </div>
        {/if}
    </form>
</div>
{include file = "mobile/common/site_footer.tpl"}
<script type="text/javascript">{$msg}var sysAccountMinLen = {$sysAccountMinLen}; var sysAccountMaxLen = {$sysAccountMaxLen}; var Account_format = {$Account_format}; var mail_rule = {$mail_rule}</script>
<script type="text/javascript" src="{$appRoot}/mooc/public/js/forget.js"></script>
<script type="text/javascript">
    username = '{$post.username}';
    email = '{$post.email}';
</script>