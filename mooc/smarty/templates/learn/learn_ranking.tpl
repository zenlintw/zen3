<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/common.css?{$smarty.now}" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css?{$smarty.now}" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css?{$smarty.now}" rel="stylesheet" />
<script type="text/javascript" src="/public/js/common.js?{$smarty.now}"></script>
<a name="content2"></a>
<div class="box1" style="max-width:1100px;">
    <div class="title">{'title'|WM_Lang}</div>
    <div class="content">
        <div class="box2">
            <div class="content" style="padding-top:1em;">
                <div class="data1">
                    <div class="content">{$msgUpdate}</div>
                </div>
            </div>
            <div class="title-bar">
                <div class="data2">
                    <table class="table subject">
                        <tbody>
                            <tr>
                                <td>
                                    <div class="t1 text-left" style="margin-left: 0.5em;;width:50px; overflow: visible;">{'title15'|WM_Lang}</div>
                                </td>
                                <td class="" style="width:160px;">
                                    <div class="text-center">
                                        <a href="javascript:;" onclick="sort_data(1);return false;">{'title1'|WM_Lang}</a>
                                        {if $sort == 'M.username'}
                                            {if $order == 'desc'}
                                            <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                            {else}
                                            <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                            {/if}
                                        {/if}
                                    </div>
                                </td>
                                {if $sort == 'M.role'}
                                <td class="t3 hidden-xs">
                                {else}
                                <td class="t2 hidden-xs">
                                {/if}
                                    <div class="text-center">
                                        <a href="javascript:;" onclick="sort_data(2);return false;">{'title2'|WM_Lang}</a>
                                        {if $sort == 'M.role'}
                                            {if $order == 'desc'}
                                            <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                            {else}
                                            <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                            {/if}
                                        {/if}
                                    </div>
                                </td>
                                <td class="t6 hidden-xs">
                                    <div class="text-center">
                                        <a href="javascript:;" onclick="sort_data(3);return false;">{'title13'|WM_Lang}</a>
                                        {if $sort == 'M.last_login'}
                                            {if $order == 'desc'}
                                            <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                            {else}
                                            <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                            {/if}
                                        {/if}
                                    </div>
                                </td>
                                {if $sort == 'M.login_times'}
                                <td class="t4 hidden-xs">
                                {else}
                                <td class="t3 hidden-xs">
                                {/if}
                                <div class="text-center" style="overflow: visible;">
                                        <a href="javascript:;" onclick="sort_data(4);return false;">{'title3'|WM_Lang}</a>
                                        {if $sort == 'M.login_times'}
                                            {if $order == 'desc'}
                                            <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                            {else}
                                            <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                            {/if}
                                        {/if}
                                    </div>
                                </td>
                                {if $sort == 'M.post_times'}
                                <td class="t4 hidden-xs">
                                {else}
                                <td class="t3 hidden-xs">
                                {/if}
                                    <div class="text-center">
                                        <a href="javascript:;" onclick="sort_data(5);return false;">{'title4'|WM_Lang}</a>
                                        {if $sort == 'M.post_times'}
                                            {if $order == 'desc'}
                                            <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                            {else}
                                            <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                            {/if}
                                        {/if}
                                    </div>
                                </td>
                                {if $sort == 'M.dsc_times'}
                                <td class="t4 hidden-xs">
                                {else}
                                <td class="t3 hidden-xs">
                                {/if}
                                    <div class="text-center" style="overflow: visible;">
                                        <a href="javascript:;" onclick="sort_data(6);return false;">{'title5'|WM_Lang}</a>
                                        {if $sort == 'M.dsc_times'}
                                            {if $order == 'desc'}
                                            <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                            {else}
                                            <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                            {/if}
                                        {/if}
                                    </div>
                                </td>
                                {if $sort == 'rss'}
                                <td class="t4 hidden-xs">
                                {else}
                                <td class="t3 hidden-xs">
                                {/if}
                                    <div class="text-center">
                                        <a href="javascript:;" onclick="sort_data(7);return false;">{'title6'|WM_Lang}</a>
                                        {if $sort == 'rss'}
                                            {if $order == 'desc'}
                                            <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                            {else}
                                            <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                            {/if}
                                        {/if}
                                    </div>
                                </td>
                                {if $sort == 'page'}
                                <td class="t4 hidden-xs">
                                {else}
                                <td class="t3 hidden-xs">
                                {/if}
                                    <div class="text-right" style="margin-right: 0.5em;">
                                        <a href="javascript:;" onclick="sort_data(8);return false;">{'title7'|WM_Lang}</a>
                                        {if $sort == 'page'}
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
                        <tr{if $profile.username eq $v.username} style="background-color:#ffbc1c;"{/if}>
                        <td>
                            <div class="t1 text-left" style="margin-left: 0.5em;">{$v.rank}</div>
                        </td>
                        <td class="" style="width:160px;">
                            <div class="text-left">{$v.userShow}</div>
                        </td>
                        <td class="t2 hidden-xs">
                            <div class="text-center">
                                {if $v.role&512}
                                <span class="dot" style="background-color: #62a900;">師</span>
                                {elseif $v.role&128}
                                <span class="dot" style="background-color: #bbb;">講</span>
                                {elseif $v.role&64}
                                <span class="dot" style="background-color: #c04e00;">助</span>
                                {/if}
                            </div>
                        </td>
                        <td class="t6 hidden-xs">
                            <div class="text-center">{$v.last_login}</div>
                        </td>
                        <td class="t3 hidden-xs">
                            <div class="text-center">{$v.login_times}</div>
                        </td>
                        <td class="t3 hidden-xs">
                            <div class="text-center">{$v.post_times}</div>
                        </td>
                        <td class="t3 hidden-xs">
                            <div class="text-center">{$v.dsc_times}</div>
                        </td>
                        <td class="t3 hidden-xs">
                            <div class="text-center">{$v.rss}</div>
                        </td>
                        <td class="t3 hidden-xs">
                            <div class="text-right" style="margin-right: 0.5em;">{$v.page}</div>
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
<form id="sortFm" name="sortFm" action="learn_ranking.php" method="post" style="display:inline">
<input type="hidden" name="sortby" value="{$sort}" />
<input type="hidden" name="order" value="{$order}" />
</form>
<script type="text/javascript">
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
    }
    
    function last10(c){
        window.open('last10.php?class_id='+c,'','top=100,left=400,width=650,length=400,toolbar=0,status=0,menubar=0,scrollbars=1,resizable=0');
    }

    window.onload = function() {
        if (detectIE() === 13) {
            $('.title-bar, .content .subject td').css('border-radius', '0 0 0 0');
        }
    };
    {/literal}
</script>