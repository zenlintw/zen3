<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
<a name="content2"></a>
<div class="box1">
    <div class="title">{'course_ranking'|WM_Lang}</div>
    <div class="content">
        <div class="box2">
            <div class="content" style="padding-top:1em;">
                <div class="data1">
                    <div class="content">
                        {$msgUpdate}
                    </div>
                </div>
            </div>
            <div class="title-bar">
                <div class="data2">
                    <table class="table subject">
                        <tbody>
                            <tr>
                                <td>
                                    <div class="text-left" style="margin-left: 0.5em;">{'course_name'|WM_Lang}</div>
                                </td>
                                <td class="t4 hidden-phone">
                                    <div class="text-center"><a href="javascript:;" onclick="sort_data('stud');return false;">{'student_amount'|WM_Lang}</a>
                                    {if $sort == 'stud'}
                                        {if $order == 'desc'}
                                        <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                        {else}
                                        <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                        {/if}
                                    {/if}
                                    </div>
                                </td>
                                <td class="t4 hidden-phone">
                                    <div class="text-center"><a href="javascript:;" onclick="sort_data('audi');return false;">{'auditor_amount'|WM_Lang}</a>
                                    {if $sort == 'audi'}
                                        {if $order == 'desc'}
                                        <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                        {else}
                                        <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                        {/if}
                                    {/if}
                                    </div>
                                </td>
                                <td class="t4 hidden-phone">
                                    <div class="text-center"><a href="javascript:;" onclick="sort_data('login_times');return false;">{'to_class_times'|WM_Lang}</a>
                                    {if $sort == 'login_times'}
                                        {if $order == 'desc'}
                                        <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                        {else}
                                        <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                        {/if}
                                    {/if}
                                    </div>
                                </td>
                                <td class="t4 hidden-phone">
                                    <div class="text-center">
                                        <a href="javascript:;" onclick="sort_data('post_times');return false;">{'post_times'|WM_Lang}</a>
                                        {if $sort == 'post_times'}
                                            {if $order == 'desc'}
                                            <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                            {else}
                                            <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                            {/if}
                                        {/if}
                                    </div>
                                </td>
                                <td class="t4 hidden-phone">
                                    <div class="text-center">
                                        <a href="javascript:;" onclick="sort_data('dsc_times');return false;">{'discuss_times'|WM_Lang}</a>
                                        {if $sort == 'dsc_times'}
                                            {if $order == 'desc'}
                                            <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                            {else}
                                            <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                            {/if}
                                        {/if}
                                    </div>
                                </td>
                                <td class="t4">
                                    <div class="text-right" style="margin-right: 0.5em;"><a href="javascript:;" onclick="sort_data('rss');return false;">{'read_time'|WM_Lang}</a>
                                    {if $sort == 'rss'}
                                        {if $order == 'desc'}
                                        <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                        {else}
                                        <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                        {/if}
                                    {/if}
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>            
            <div class="content">
                <div class="data2">
                    <table class="table subject">
                        {foreach from=$datalist key=k item=v}
                            <tr>
                            <td>
                                <div  class="text-left" style="margin-left: 0.5em; white-space: inherit;">{$v.caption}</div>
                            </td>
                            <td class="t4 hidden-phone">
                                <div class="text-center">{$v.stud}</div>
                            </td>
                            <td class="t4 hidden-phone">
                                <div class="text-center">{$v.audi}</div>
                            </td>
                            <td class="t4 hidden-phone">
                                <div class="text-center">{$v.login_times}</div>
                            </td>
                            <td class="t4 hidden-phone">
                                <div class="text-center">{$v.post_times}</div>
                            </td>
                            <td class="t4 hidden-phone">
                                <div class="text-center">{$v.dsc_times}</div>
                            </td>
                            <td class="t4">
                                <div class="text-right" style="margin-right: 0.5em;">{$v.rss}</div>
                            </td>
                        </tr>
                        {/foreach}
                    </table>
                </div>
            </div>
            <div id="pageToolbar" class="paginate"></div>            
        </div>
    </div>
</div>
<form id="sortFm" name="sortFm" action="course_ranking.php" method="post" style="display:inline">
    <input type="hidden" name="sortby" value="{$sort}" />
    <input type="hidden" name="order" value="{$order}" />
</form>
<form name="actFm" id="actFm" action="course_ranking.php" method="post" enctype="multipart/form-data" style="display: none;">
    <input type="hidden" name="sortby" value="{$sort}" />
    <input type="hidden" name="order" value="{$order}" />
    <input type="hidden" name="page" value="{$page_no}" />
</form>
<script type="text/javascript" src="{$appRoot}/lib/kc-paginate.js"></script>
{include file = "common/paginate_jsdeclare.tpl"}
<script type="text/javascript">
    {$inlineSchoolJS}
    var sysGotoLabel = '{$label}';
    {literal}
    function sort_data(val){
        var obj = document.sortFm;
        if (obj.order.value == 'asc'){
            obj.order.value = 'desc';
        }else{
            obj.order.value = 'asc';
        }

        obj.sortby.value = val;
        obj.submit();
    };
    
    $(function () {
        // ¤À­¶¤u¨ã¦C
        $('#pageToolbar').paginate({
            'total': 0,
            'pageNumber': 1,
            'showPageList': false,
            'showRefresh': false,
            'showSeparator': false,
            'btnTitleFirst': btnTitleFirst,
            'btnTitlePrev': btnTitlePrev,
            'btnTitleNext': btnTitleNext,
            'btnTitleLast': btnTitleLast,
            'btnTitleRefresh': btnTitleRefresh,
            'beforePageText': beforePageText,
            'afterPageText': afterPageText,
            'beforePerPageText': beforePerPageText,
            'afterPerPageText': afterPerPageText,
            'displayMsg': displayMsg,
            'buttonCls': '',
            'onSelectPage': function (num, size) {
                if (page_no == 0) return;
                if (num == 0) return;
                if (num == page_no){
                    return;
                } 
                page_no = num;
                document.actFm.page.value = num;
                document.actFm.submit();
            }
        });

        $('#pageToolbar').paginate('refresh', {
                    'total': total_count,
                    'pageSize': page_size
                });
                
        $('#pageToolbar').paginate('select', page_no);
    });
    {/literal}
</script>