{config_load file=test.conf section="setup"}
{include file = "common/tiny_header.tpl"}
{include file = "common/site_header.tpl"}
<style>
{literal}
#main {
    min-height: calc(100vh - 300px);
}

.form-horizontal  .control-label {
    display:inline-block;
    text-align:right;
}

.form-horizontal  .controls {
    display:inline-block;
    margin-left: 10px;
}

#formResetPwd {
    border: 1px solid #C5C5C5;
    border-radius: 4px;
    padding-top:25px;
    padding-bottom:25px;
}

.control-group {
    margin-left: -30px;
}

{/literal}
</style>
<div id="main">
<div class="row">&nbsp;</div>
<div class="container esn-container">
    <div class="panel block-center">
        <h4>重設密碼</h4>
        <form onsubmit="return checkData();" id="formResetPwd" action="{$appRoot}/mooc/resetpwd_p.php" method="post" class="form-horizontal message-pull-center" name="formResetPwd" accept-charset="UTF-8" lang="zh-tw">
            <input type="hidden" name="idx" id="idx" value="{$idx}">
            <input type="hidden" name="check" id="check" value="">
            <input type="hidden" name="check_msg" id="check_msg" value="">
            <div id="message">{$message}</div>
            <fieldset>
                <div class="input block-center">
                    <div class="control-group">
                        <label class="control-label" for="username">帳號</label>
                        <div class="controls">
                            <input type="text" id="username" name="username" class="input-large" placeholder="請輸入帳號" value="{$post.username}">
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="password">密碼</label>
                        <div class="controls">
                            <input type="password" id="password" name="password" class="input-large" placeholder="請輸入新密碼"  value="">
                        </div>
                    </div>
                    <div class="control-group">
                        <label class="control-label" for="repassword">確認密碼</label>
                        <div class="controls">
                            <input type="password" id="repassword" name="repassword" class="input-large" placeholder="請輸入確認密碼"  value="">
                        </div>
                    </div>
                    <div class="control-group">
                        
                            <div class="lcms-right" style="margin-right: 67px;">
                                <button type="submit" class="btn btn-primary btn-blue btnNormal" id="btnResetPwd">送出</button>
                            </div>
                        
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div>
</div>
{include file = "common/site_footer.tpl"}
<script type="text/javascript">
    {$msg}
    var sysAccountMinLen = {$sysAccountMinLen};  
    var sysAccountMaxLen = {$sysAccountMaxLen};
    var Account_format = {$Account_format};
    var pwdFocus = '{$pwdfocus}';
</script>
<script type="text/javascript" src="{$appRoot}/mooc/public/js/password_strong.js"></script>
<script type="text/javascript" src="{$appRoot}/mooc/public/js/resetpwd.js"></script>