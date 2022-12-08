{include file = "common/tiny_header.tpl"}
{include file = "common/site_header.tpl"}
<style>
{literal}
    #main {
        min-height: calc(100vh - 300px);
    }
{/literal}
</style>
<div id="main">
<div class="row">&nbsp;</div>
<div class="container esn-container">
    <div class="panel block-center">
        <form method="post" action="{$appRoot}/mooc/{$page}" class="well form-horizontal message-pull-center" id="formForget">
            <input type="hidden" name="token" value="{$csrfToken}">
            <input type="hidden" name="username" value="{$post.username}">
            <input type="hidden" name="email" value="{$post.email}">
            <fieldset>
                <div class="block-center">
                    <div class="row">&nbsp;</div>
                    <div class="control-group">
                        <div class="message">
                            <div id="message">{$message}</div>
                        </div>
                    </div>
                    <div class="row">&nbsp;</div>
                    <div class="control-group">
                        <div class="controls" style="margin-left: 0;">
                            <div class="lcms-left">
                                {if $resend eq 'Y'}
                                    <input type="hidden" name="resendto" value="{$resendto}">
                                    <button type="submit" class="btn btn-primary btnNormal margin-right-10 btn-blue" id="btnHome">重發驗證信</button>
                                {else}
                                    <a href="#" class="btn btn-primary aNormal margin-right-10 btn-blue" id="btnRegister" onclick="GoForget();">回上一頁</a>
                                {/if}
                                <a href="index.php" class="btn btn-primary aNormal margin-right-10 btn-gray" id="btnHome">回首頁</a>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div>
</div>
{include file = "common/site_footer.tpl"}
<script type="text/javascript" src="{$appRoot}/mooc/public/js/forget_p.js"></script>