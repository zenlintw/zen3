{config_load file=test.conf section="setup"}
{include file = "common/tiny_header.tpl"}
{include file = "common/site_header.tpl"}
<div class="row">&nbsp;</div>
<div class="container esn-container">
    <div class="panel block-center">
        <form method="post" action="" class="well form-horizontal message-pull-center" id="formResend">
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
                        <div class="controls" style="margin-left: 0;">
                            <div class="lcms-left">
                                {if $verified neq '' && $verified neq null }
                                    <a href="login.php" class="btn btn-primary aNormal margin-right-10 btn-blue" id="btnResend">登入</a>
                                {elseif $$changed neq '' && $$changed neq null }
                                     <a href='resend.php?{$resendurl}' class="btn btn-primary aNormal margin-right-10 btn-blue" id="btnResend">回上頁</a>
                                {else}
                                    <a href="resend.php" class="btn btn-primary aNormal margin-right-10 btn-blue" id="btnResend">回上頁</a>
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
{include file = "common/site_footer.tpl"}