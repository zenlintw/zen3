{config_load file=test.conf section="setup"}
{include file = "common/tiny_header.tpl"}
{include file = "common/site_header.tpl"}
<div class="row">&nbsp;</div>
<div class="container esn-container">
    <div class="panel block-center">
        <form method="post" action="" class="well form-horizontal message-pull-center">
            <fieldset>
                <div class="input block-center">
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
                                {$buttons}
                            </div>
                        </div>
                    </div>
                </div>
            </fieldset>
        </form>
    </div>
</div>
{include file = "common/version_footer.tpl"}