<div id="wrap">
    {include file = "mobile/common/site_header.tpl"}
    <form method="post" action="{$appRoot}/mooc/register.php" class="well form-horizontal message-pull-center" id="formRegister">
        <input type="hidden" name="ticket" id="ticket" value="{$loginKey}">
        {foreach from=$post key=k item=v}
            <input type="hidden" name="{$k}" value="{$v}">
        {/foreach}
        <div id="message" class="col-sm-offset-2 col-sm-8">{$message}</div>
        <div class="col-sm-offset-2 col-sm-8" style="text-align: center;"></div>
        <div style="text-align: center; margin-top: 1em;">
            <a href="#" class="btn btn-primary aNormal margin-right-10 btn-blue" id="btnRegister" onclick="GoReg();">修改</a>
            <a href="index.php" class="btn btn-primary aNormal margin-right-10 btn-gray" id="btnHome">回首頁</a>
        </div>
    </form>
</div>
{include file = "mobile/common/site_footer.tpl"}
<script type="text/javascript" src="{$appRoot}/mooc/public/js/save.js"></script>