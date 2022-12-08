{config_load file=test.conf section="setup"}
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
        <form method="post" action="{$appRoot}/mooc/resetpwd.php?idx={$idx}" class="well form-horizontal message-pull-center" id="formResetPwd">
            {foreach from=$post key=k item=v}
                <input type="hidden" name="{$k}" value="{$v}">
            {/foreach}
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
                        <div class="controls">
                            <div class="lcms-left">
                                 <a href="#" class="btn btn-primary aNormal margin-right-10 btn-blue" id="btnRegister" onclick="GoResetPwd();">回上一頁</a>
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
<script type="text/javascript" src="{$appRoot}/mooc/public/js/resetpwd_p.js"></script>