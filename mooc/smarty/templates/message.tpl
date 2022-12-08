{config_load file=test.conf section="setup"}
{include file = "common/tiny_header.tpl"}
{include file = "common/site_header.tpl"}
<style type="text/css">
{literal}
    #main {
        min-height: calc(100vh - 300px);
    }
 
    /*手機尺寸*/
    @media (max-width: 767px) {
        .esn-container {
            width: 100%;
        }

        .esn-container > .panel {
            width: 100%;
        }

        .form-horizontal .control-label {
            width: 60px;
        }

        .form-horizontal .controls {
            margin-left: 0px;
        }

        .wm-table-row .last-cell {
            padding-left: 0px;
        }

        #btnSignIn {
            display: initial;
            width: 100px;
        }

        .message-pull-center .input {
            width: initial;
        }

        .form-horizontal .controls {
            margin-left: 0px;
        }
    }

    /*平板直向、平板橫向*/
    @media (min-width: 768px) and (max-width: 992px) {
    }

    /*large Desktop*/
    @media (min-width: 1200px) {
    }
{/literal}
</style>
<div id="main">
	<div class="row">&nbsp;</div>
	<div class="container esn-container">
	    <div class="panel block-center">
	        <form method="post" class="well form-horizontal message-pull-center">
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
	                        <div class="controls" style="margin-left: 0;">
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
</div>
{include file = "common/site_footer.tpl"}
<script type="text/javascript">
{literal}
    // 三秒後執行 跳轉回課程資訊頁面
    $(function(){
        var type = getURLParameter("type");
        var courseid = getURLParameter("cid");
        if(type == "16" &&  courseid >= 1){ 
            setTimeout("document.location.href = '/info/"+ courseid+"';",3000);
        }
    });
{/literal}
</script>