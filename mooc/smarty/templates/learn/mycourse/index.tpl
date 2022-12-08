<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/lib/jquery/css/jquery-ui-1.8.22.custom.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/settings.css" rel="stylesheet" />
<script type="text/javascript">
    {$inlineJS}
    {literal}
    {/literal}
</script>
<script type="text/javascript" src="/public/js/common.js"></script>
<script type="text/javascript" src="index.js?{$smarty.now}"></script>
<div class="box1">
    <div class="content" style="padding: 0;">
        <div class="data6">
            <div class="title">
                <ul id="pager-switch" class="nav nav-tabs nav-orange">
                    <li{if $tabs == 1} class="active"{/if}><a href="#" onclick="chgTabs(1);return false;">{'tabs_course'|WM_Lang}</a></li>
                    {if $isTeacher == 1}
                    <li{if $tabs == 2} class="active"{/if}><a href="#" onclick="chgTabs(2);return false;">{'tabs_office'|WM_Lang}</a></li>
                    {/if}
                    <li{if $tabs == 3} class="active"{/if}><a href="#" onclick="chgTabs(3);return false;">{'tabs_school'|WM_Lang}</a></li>
                </ul>
            </div>
        </div>
        {if $tabs == 1} 
            {include file = "learn/mycourse/major.tpl"}
        {elseif $tabs == 2}
            {include file = "learn/mycourse/teacher.tpl"}
        {elseif $tabs == 3}
            {include file = "learn/mycourse/school.tpl"}
        {/if}
    </div>
</div>
<form name="actFm" id="actFm" action="index.php" method="post" enctype="multipart/form-data" style="display:none">
<input type="hidden" name="sortby" value="{$sort}" />
<input type="hidden" id="CourseName_SORT" name="CourseName_SORT" value="{$post.CourseName_SORT}" />
<input type="hidden" name="order" value="{$order}" />
<input type="hidden" name="page" value="{$page_no}" />
<input type="hidden" name="course_name" value="" />
<input type="hidden" name="teacher" value="" />
<input type="hidden" name="en_begin" value="" />
<input type="hidden" name="en_end" value="" />
<input type="hidden" name="st_begin" value="" />
<input type="hidden" name="st_end" value="" />
<input type="hidden" name="advqy" value="" />
<input type="hidden" name="isquery" value="{$isquery}" />
<input type="hidden" name="tabs" value="{$tabs}" />
</form>

<form name="chgFm" id="chgFm" action="index.php" method="post" enctype="multipart/form-data" style="display:none">
<input type="hidden" name="tabs" value="{$tabs}" />
</form>

<form name="gpFm" id="gpFm" action="index.php" method="post" enctype="multipart/form-data" style="display:none">
<input type="hidden" name="chgp" value="66" />
<input type="hidden" name="tabs" value="{$tabs}" />
</form>

{if $profile.username == 'guest'}
    <iframe id="s_catalog" name="s_catalog" src="course_tree_hid.php" style="display: none"></iframe>
{/if}
