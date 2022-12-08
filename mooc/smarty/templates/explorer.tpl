{include file = "common/tiny_header.tpl"}
{include file = "common/site_header.tpl"}
<style type="text/css">
{literal}
.bs-sidebar {
    top: 0;
    min-height: initial;
    margin-top: 24px;
}

.lcms-sidebar .filter .tree {
    width: auto;
}

#mainContent {
    min-height: calc(100vh - 300px);
}

/*large Desktop*/
@media (min-width: 1200px) {
}

/*平板直向、平板橫向*/
@media (min-width: 768px) and (max-width: 992px) {
}

/*5.5 手機尺寸 - 直式*/
@media (max-width: 414px) {
    .bs-sidebar {
        margin-top: 15px;
        width: initial;
        margin-left: initial;
        margin-right: initial;
        padding-left: 15px;
        padding-right: 15px;
    }

    .lcms-sidebar {
        margin-bottom: 0px;
    }

    .lcms-sidebar .group h2 {
        margin-top: 5px;
    }
}

/*5.5 手機尺寸 - 直式*/
@media (max-width: 320px) {

}


{/literal}
</style>
{* <div style="height:70px;text-align:center;">
    <div class="explorer-search">
        <div class="input-append">
            <input name="keyword" id='bar-keyword' type="text" value="{$keyword}" placeholder="{'searchcourse'|WM_Lang}">
            <button class="btn btn-gray-light" type="button" onclick="adv_search();"><i class="icon-search icon-white"></i></button>
        </div>
    </div>
</div>
 *}

<script type="text/javascript" src="{$appRoot}/mooc/public/js/search.js"></script>
<div id="mainContent" class="container">
    <div class="row">
        <div class="col-lg-2 col-md-3 col-xs-12">
        {include file = "common/course_tree.tpl"}
        </div>
        <div class="col-lg-10 col-md-9 col-xs-12">
        {include file = "common/explorer_course_list.tpl"}
        </div>
    </div>
</div>


{include file = "common/site_footer.tpl"}
<script language="javascript" src="{$appRoot}/mooc/public/js/explorer.js"></script>
<script type="text/javascript" src="{$appRoot}/theme/default/treegrid/js/jquery.treegrid.js"></script>
<link rel="stylesheet" href="{$appRoot}/theme/default/treegrid/css/jquery.treegrid.css">
<script type="text/javascript">
    var group_id = '{$group_id}',
        nocourses = '{'nocourses'|WM_Lang}',
        MSGopeningperiod = '{'openingperiod'|WM_Lang}',
        MSGSHOWMORECOURSE = '{'show_more'|WM_Lang}';
    {literal}
        $('.tree').treegrid({'initialState' : 'collapsed'});
    {/literal}
</script>