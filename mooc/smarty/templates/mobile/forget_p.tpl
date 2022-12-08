<div id="wrap">
    {include file = "mobile/common/site_header.tpl"}
    <form method="post" action="{$appRoot}/mooc/{$page}" class="well form-horizontal message-pull-center" id="formForget">
        {foreach from=$post key=k item=v}
            <input type="hidden" name="{$k}" value="{$v}">
        {/foreach}
        <div id="message" class="col-sm-offset-2 col-sm-8">{$message}</div>
        <div style="text-align: center; margin-top: 1em;">
            {if $resend eq 'Y'}
                <input type="hidden" name="resendto" value="{$resendto}">
                <button type="submit" class="btn btn-primary btnNormal margin-right-10 btn-blue" id="btnHome">重發驗證信</button>
            {else}
                <a href="#" class="btn btn-primary aNormal margin-right-10 btn-blue" id="btnRegister" onclick="GoForget();">回上一頁</a>
            {/if}
            <a href="index.php" class="btn btn-primary aNormal margin-right-10 btn-gray" id="btnHome">回首頁</a>
        </div>
    </form>
</div>
{include file = "mobile/common/site_footer.tpl"}
<script type="text/javascript" src="{$appRoot}/mooc/public/js/forget_p.js"></script>