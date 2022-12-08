<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
<script type="text/javascript" src="/lib/xmlextras.js"></script>

<div class="box1" style="width:400px;">
    <div class="title">{'fun_tab'|WM_Lang}</div>
    <div class="content">
        <div class="box2">
            <div class="content" style="padding-top:1em;">
                <div class="data1">
                    <div class="content">
                        {$show_msg}
                    </div>
                </div>
            </div>
        </div>
		<div class="text-right">
		<button class="btn btn-blue" onclick="add_audit();">{'add_audit'|WM_Lang}</button>
		<button class="btn" onclick="parent.$.fancybox.close();">{'win_close'|WM_Lang}</button>
		</div>
    </div>
</div>
<form id="sortFm" name="sortFm" action="learn_stat.php" method="post" style="display:inline">
<input type="hidden" name="sortby" value="{$sort}" />
<input type="hidden" name="order" value="{$order}" />
</form>
<script type="text/javascript">
    {$inlineJS}
    {literal}
    {/literal}
</script>