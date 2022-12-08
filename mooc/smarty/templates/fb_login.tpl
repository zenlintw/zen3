{include file = "common/tiny_header.tpl"}
{include file = "common/site_header.tpl"}
<div class="row">&nbsp;</div>
<style>
{literal}
#main {
    min-height: calc(100vh - 381px);
}
{/literal}
</style>
<div id="main">
<div class="container esn-container" id="registerBox">
    <div class="panel block-center">
        <div class="titlebar">
            <span class="title">{'msg_first_login'|WM_Lang}</span>
            <span class="pull-right lcms-text-import">* {'required'|WM_Lang}</span>
        </div>
        <form method="post" action="{$appRoot}/mooc/fb_save.php" class="well form-horizontal" id="registerForm" name="registerForm" onsubmit="return checkRegister();">
            <div class="alert alert-info">
                <button type="button" class="close" data-dismiss="alert">×</button>
                {'fbbasicinformation'|WM_Lang}
            </div>
            <fieldset>
                <div class="wm-table-row">
                    <div class="wm-table-cell">
                        <div class="control-group">
                            <label class="control-label" for="username"><span class="lcms-red-starmark">* </span>{'th_username'|WM_Lang}</label>
                            <div class="controls">
                                <input type="text" id="fb_username" name="username" class="input-large" placeholder="{'msg_fill_username'|WM_Lang}" value="{$fbUsername}">
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="first_name">{'theading_realname'|WM_Lang}</label>
                            <div class="controls">
                                <input type="text" name="first_name" class="input-large" placeholder="" value="{$fbRealname}">
                            </div>
                        </div>
                        {*<div class="control-group">
                            <label class="control-label" for="email">{'ex_email'|WM_Lang}</label>
                            <div class="controls">
                                <input type="text" id="email" name="email" class="input-large" placeholder="" value="{$post.email}">
                            </div>
                        </div>*}
                        <div class="control-group">
                            <div class="controls">
                                <div class="lcms-left">
                                    <button type="submit" class="btn btn-primary btn-blue btnNormal margin-right-15">{'btn_set'|WM_Lang}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="wm-table-cell split">&nbsp;</div>
                    <div class="wm-table-cell last-cell">
                        <div class="control-group">
                            <span class="font-small">{'alreadyhaveaccount2'|WM_Lang}</span>
                            <a href="#" class="btn" id="btnConfirm">{'verification'|WM_Lang}</a>
                        </div>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div>
<div class="container esn-container" id="confirmBox" style="display: none;">
    <div class="panel block-center">
        <div class="titlebar">
            <span class="title">{'accountintegration'|WM_Lang}</span>
            <span class="pull-right lcms-text-import">* {'required'|WM_Lang}</span>
        </div>
        <form method="post" action="{$appRoot}/mooc/fb_confirm.php" class="well form-horizontal" id="loginForm" name="loginForm" onsubmit="return false;">
            <input type="hidden" name="login_key" value="{$loginKey}">
            <input type="hidden" name="encrypt_pwd" value="">
            <div class="alert alert-info">
                <button type="button" class="close" data-dismiss="alert">×</button>
                {'fbbinding'|WM_Lang}
            </div>
            <fieldset>
                <div class="wm-table-row">
                    <div class="wm-table-cell">
                        <div class="control-group">
                            <label class="control-label" for="username"><span class="lcms-red-starmark">* </span>{'th_username'|WM_Lang}</label>
                            <div class="controls">
                                <input type="text" id="username" name="username" class="input-large" placeholder="{'msg_fill_username'|WM_Lang}" value="">
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label" for="password"><span class="lcms-red-starmark">* </span>{'th_password'|WM_Lang}</label>
                            <div class="controls">
                                <input type="password" id="password" name="password" class="input-large" placeholder="{'msg_fill_password'|WM_Lang}">
                            </div>
                        </div>
                        <div class="control-group">
                            <div class="controls">
                                <div class="lcms-left">
                                    <button type="submit" class="btn btn-primary btn-blue btnNormal margin-right-15" id="btnCombine">{'verify'|WM_Lang}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="wm-table-cell split">&nbsp;</div>
                    <div class="wm-table-cell last-cell">
                        <div class="control-group">
                            <span class="font-small">{'no_account'|WM_Lang}？</span>
                            <a href="#" class="btn" id="btnRegister">{'setting'|WM_Lang}</a>
                        </div>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div>
</div>
<script type="text/javascript">
    {literal}var
        MSG = {{/literal}
            'empty_account': '{'empty_account'|WM_Lang}',
            'user_rule'    : '{$user_limit}',
            'reduplicate'  : '{'msg_account_reduplicate'|WM_Lang}',
            'reserved'     : '{'system_reserved'|WM_Lang}'
    {literal}},{/literal}
        MSG_NEED_USERNAME = '{'msg_fill_username'|WM_Lang}',
        MSG_NEED_PASSWORD = '{'msg_fill_password'|WM_Lang}';

</script>
<script language="javascript" src="{$appRoot}/lib/md5.js"></script>
<script language="javascript" src="{$appRoot}/lib/des.js"></script>
<script language="javascript" src="{$appRoot}/lib/base64.js"></script>
<script language="javascript" src="{$appRoot}/sys/tpl/login.js"></script>
<script type="text/javascript" src="{$appRoot}/mooc/public/js/fb_login.js"></script>
{include file = "common/site_footer.tpl"}