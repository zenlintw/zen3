<style>
{literal}
.form-horizontal .control-label {
    /* width: initial;*/ 
}
@media (min-width: 768px) {
    .form-horizontal .control-label-c {
        padding-top: 7px;
        margin-bottom: 0;
        text-align: right;
    }
    .col-sm-2 {
        width: 16.66666667%;
    }
}
{/literal}
</style>
<div id="wrap">
    {include file = "mobile/common/site_header.tpl"}
    <h4>{'memberlogin'|WM_Lang}</h4>
    {if 'Y'|in_array:$canReg}
        <a href="{$appRoot}/mooc/register.php" class="btn btn-default pull-right" id="btnRegister">{'btn_register'|WM_Lang}</a>
    {/if}
    <form method="post" action="{$appRoot}/login.php" class="well form-horizontal" id="loginForm" name="loginForm" onsubmit="return formSubmit();">
        <input type="hidden" name="reurl" value="{$reurl}">
        <input type="hidden" name="login_key" value="{$loginKey}">
        <input type="hidden" name="encrypt_pwd" value="">
        {if $message neq ''}
            <div class="alert alert-danger">
                {$message}
                <button type="button" class="close" data-dismiss="alert">×</button>
            </div>
        {/if}
        <div class="form-group">
            <label for="username" class="col-sm-2 control-label-c">{'th_username'|WM_Lang}</label>
            <div class="col-sm-10">
                <input type="text" id="username" name="username" class="form-control" placeholder="{'msg_fill_username'|WM_Lang}" value="{$username}">
            </div>
        </div>
        <div class="form-group">
            <label for="password" class="col-sm-2 control-label-c">{'th_password'|WM_Lang}</label>
            <div class="col-sm-10">
                <input type="password" id="password" name="password" class="form-control"  placeholder="{'msg_fill_password'|WM_Lang}">
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                <button type="submit" id="btnSignIn" class="btn btn-blue">{'login'|WM_Lang}</button>
                <span class="font-small"><a href="/mooc/forget.php">{'btn_query_password'|WM_Lang}?</a></span>
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
    
    <form method="post" action="message.php?type=5" id="msgForm" name="msgForm">
        <input type="hidden" name="action" value="resend">
        <input type="hidden" name="login_key" value="{$loginKey}">
        <input type="hidden" name="username" value="">
        <input type="hidden" name="email" value="">
        <input type="hidden" name="encemail" value="">
    </form>
</div>
{include file = "mobile/common/site_footer.tpl"}
<script language="javascript" src="{$appRoot}/lib/md5.js"></script>
<script language="javascript" src="{$appRoot}/lib/des.js"></script>
<script language="javascript" src="{$appRoot}/lib/base64.js"></script>
<script language="javascript" src="{$appRoot}/sys/tpl/login.js"></script>
<script language="javascript">
    var
        MSG_NEED_USERNAME = '{$needUsername}',
        MSG_NEED_PASSWORD = '{$needPassword}';

    {literal}
    $(function () {
        $('#username').focus();
    });

    function formSubmit() {
        $("#btnSignIn").addClass('disabled').prop('disabled', true);
        var check = checkLogin();
        if (check === false) {
            $("#btnSignIn").removeClass('disabled').prop('disabled', false);
            return false;
        } else {
            var check2 = checkTmpAccount();
            if (check2 === false) {
                $('#msgForm').submit();
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
                    case 2:
                        rn = true;
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