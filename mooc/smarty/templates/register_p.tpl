{config_load file=test.conf section="setup"}
{include file = "common/tiny_header.tpl"}
{include file = "common/site_header.tpl"}
<div class="row">&nbsp;</div>
<div class="container esn-container">
    <div class="panel block-center">
        <form method="post" action="{$appRoot}/mooc/register.php" class="well form-horizontal message-pull-center" id="formRegister">
            
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
                                 <a href="#" class="btn btn-primary aNormal margin-right-10 btn-blue" id="btnRegister" onclick="GoReg();">{'edit'|WM_Lang}</a>
                                 <a href="index.php" class="btn btn-primary aNormal margin-right-10 btn-gray" id="btnHome">{'return_home'|WM_Lang}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div>
<script type="text/javascript" src="{$appRoot}/mooc/public/js/save.js"></script>
{include file = "common/site_footer.tpl"}