<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
<script type="text/javascript" src="/public/js/common.js"></script>
<a name="content2"></a>
<div class="box1">
    <div class="title">{'title'|WM_Lang}</div>
    <div class="content">
        <div class="box2">
            <div class="content" style="padding-top:1em;">
                <div class="data1">
                    <div class="content">
                        <table width="760" border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td nowrap><img src="/theme/default/learn/my_left.gif" width="9" height="11" border="0" align="absmiddle" />{$profile.realname}{$loginInfo.count}</td>
                            <td nowrap><img src="/theme/default/learn/my_left.gif" width="9" height="11" border="0" align="absmiddle" />{$loginInfo.last}</td>
                        </tr><tr>
                            <td nowrap><img src="/theme/default/learn/my_left.gif" width="9" height="11" border="0" align="absmiddle" />{$loginInfo.from}</td>
                            <td nowrap><img src="/theme/default/learn/my_left.gif" width="9" height="11" border="0" align="absmiddle" />{$loginInfo.sum}</td>
                        </tr>
                        </table>
                    </div>
                </div>
            </div>
            <div class="title-bar">
                <div class="data2">
                    <table class="table subject">
                        <tbody>
                            <tr>
                                <td>
                                    <div class="text-left" style="margin-left: 0.5em;">
                                    <a href="javascript:;" onclick="sort_data(1);return false;">{'title1'|WM_Lang}</a>
                                    {if $sort == 'CS.caption'}
                                        {if $order == 'desc'}
                                        <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                        {else}
                                        <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                        {/if}
                                    {/if}
                                    </div>
                                </td>
                                <td class="t4 hidden-phone">
                                    <div class="text-center"><a href="javascript:;" onclick="sort_data(3);return false;">{'title3'|WM_Lang}</a>
                                    {if $sort == 'MJ.login_times'}
                                        {if $order == 'desc'}
                                        <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                        {else}
                                        <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                        {/if}
                                    {/if}
                                    </div>
                                </td>
                                <td class="t4 hidden-phone">
                                    <div class="text-center"><a href="javascript:;" onclick="sort_data(4);return false;">{'title4'|WM_Lang}</a>
                                    {if $sort == 'MJ.post_times'}
                                        {if $order == 'desc'}
                                        <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                        {else}
                                        <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                        {/if}
                                    {/if}
                                    </div>
                                </td>
                                <td class="t4 hidden-phone">
                                    <div class="text-center"><a href="javascript:;" onclick="sort_data(5);return false;">{'title5'|WM_Lang}</a>
                                    {if $sort == 'MJ.dsc_times'}
                                        {if $order == 'desc'}
                                        <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                        {else}
                                        <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                        {/if}
                                    {/if}
                                    </div>
                                </td>
                                <td class="hidden-phone" style="width:140px;">
                                    <div class="text-center">
                                        <a href="javascript:;" onclick="sort_data(2);return false;">{'title2'|WM_Lang}</a>
                                        {if $sort == 'MJ.last_login'}
                                            {if $order == 'desc'}
                                            <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                            {else}
                                            <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                            {/if}
                                        {/if}
                                    </div>
                                </td>
                                <td class="t4">
                                    <div class="text-right" style="margin-right: 0.5em;"><a href="javascript:;" onclick="sort_data(6);return false;">{'title6'|WM_Lang}</a>
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
                                <div  class="text-left" style="margin-left: 0.5em;">{$v.caption}</div>
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
                            <td class="hidden-phone" style="width:140px;">
                                <div class="text-center">{$v.last_login}</div>
                            </td>
                            <td class="t4">
                                <div class="text-right" style="margin-right: 0.5em;"><a href="javascript:last10({$v.course_id});" class="cssAnchor">{$v.rss}</a></div>
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
<form id="sortFm" name="sortFm" action="learn_stat.php" method="post" style="display:inline">
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