<div id="wrap">
    {include file = "mobile/common/site_header.tpl"}
    <form method="post" action="{$appRoot}/sign/login" class="well form-horizontal message-pull-center">
        <input type="hidden" name="ticket" id="ticket" value="{$loginKey}">
        <input type="hidden" name="encrypt" id="encrypt" value="">
        <div id="message" class="col-sm-offset-2 col-sm-8">{$message}</div>
        <div style="text-align: center; margin-top: 1em;">
            {$buttons}
        </div>
    </form>
</div>
{include file = "mobile/common/site_footer.tpl"}
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