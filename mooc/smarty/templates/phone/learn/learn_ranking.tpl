<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
<style>
{literal}
@media (max-width: 767px) {
    .data1 {
        padding: 0px;
    }

    .container {
        min-width: initial;
        padding: 5px;
    }

    .div-user-data {
        border-radius: 4px;
    }

    .lbl-user-data {
        white-space: nowrap;
    }

    .value-user-data {
        color: #0088D2;
    }

    .box2 > .title-bar {
        margin-top: initial;
    }
}
{/literal}
</style>
<div class="box1" style="">
    <div class="title">{'title'|WM_Lang}</div>
    <div class="content">
        <div class="box2">
            <div class="content container">
                <div class="data1">
                    <div class="content">{$msgUpdate}</div>
                </div>
            </div>
            <div class="content container">
                <div class="data1 row div-user-data">
                    <div class="col-xs-3 lbl-user-data">{'select_sort_options'|WM_Lang}</div>
                    <div class="col-xs-9">
                        <select name="selOrder" onchange="sort_data(this.value);">
                            <option value="1"{if $sort == 'M.username'} selected{/if}>{'title1'|WM_Lang}</option>
                            <option value="3"{if $sort == 'M.last_login'} selected{/if}>{'title13'|WM_Lang}</option>
                            <option value="4"{if $sort == 'M.login_times'} selected{/if}>{'title3'|WM_Lang}</option>
                            <option value="5"{if $sort == 'M.post_times'} selected{/if}>{'title4'|WM_Lang}</option>
                            <option value="6"{if $sort == 'M.dsc_times'} selected{/if}>{'title5'|WM_Lang}</option>
                            <option value="7"{if $sort == 'rss'} selected{/if}>{'title6'|WM_Lang}</option>
                            <option value="8"{if $sort == 'page'} selected{/if}>{'title7'|WM_Lang}</option>
                        </select>
                    </div>
                    <div class="col-xs-3 lbl-user-data">{'title1'|WM_Lang}</div><div class="col-xs-9 value-user-data">{$userLearnData.userShow}</div>
                    <div class="col-xs-3 lbl-user-data">{'title15'|WM_Lang}</div><div class="col-xs-3 value-user-data">{$userLearnData.rank}</div>
                    <div class="col-xs-3 lbl-user-data">{'title3'|WM_Lang}</div><div class="col-xs-3 value-user-data">{$userLearnData.login_times}</div>
                    <div class="col-xs-3 lbl-user-data">{'title4'|WM_Lang}</div><div class="col-xs-3 value-user-data">{$userLearnData.post_times}</div>
                    <div class="col-xs-3 lbl-user-data">{'title5'|WM_Lang}</div><div class="col-xs-3 value-user-data">{$userLearnData.dsc_times}</div>
                    <div class="col-xs-3 lbl-user-data">{'title6'|WM_Lang}</div><div class="col-xs-3 value-user-data">{$userLearnData.rss}</div>
                    <div class="col-xs-3 lbl-user-data">{'title7'|WM_Lang}</div><div class="col-xs-3 value-user-data">{$userLearnData.page}</div>
                    <div class="col-xs-4 lbl-user-data">{'title13'|WM_Lang}</div><div class="col-xs-8 value-user-data">{$userLearnData.last_login}</div>
                </div>
            </div>
            <div class="title-bar">
                <div class="data2">
                    <table class="table subject">
                        <tbody>
                            <tr>
                                <td style="width:56px;">
                                    <div class="t1 text-left" style="width:50px; overflow: visible;">{'title15'|WM_Lang}</div>
                                </td>
                                {if $sort == 'M.username'}
                                <td class="">
                                    <div class="text-right" style="margin-right: 0.5em;">{'title1'|WM_Lang}</div>
                                </td>
                                {else}
                                <td class="" style="">
                                    <div class="text-center">{'title1'|WM_Lang}</div>
                                </td>
                                {/if}
                                {if $sort == 'M.last_login'}
                                <td class="">
                                    <div class="text-right" style="margin-right: 0.5em;">{'title13'|WM_Lang}</div>
                                </td>
                                {elseif $sort == 'M.login_times'}
                                <td class="">
                                    <div class="text-right" style="margin-right: 0.5em;">{'title3'|WM_Lang}</div>
                                </td>
                                {elseif $sort == 'M.post_times'}
                                <td class="">
                                    <div class="text-right" style="margin-right: 0.5em;">{'title4'|WM_Lang}</div>
                                </td>
                                {elseif $sort == 'M.dsc_times'}
                                <td class="">
                                    <div class="text-right" style="margin-right: 0.5em;">{'title5'|WM_Lang}</div>
                                </td>
                                {elseif $sort == 'rss'}
                                <td class="">
                                    <div class="text-right" style="margin-right: 0.5em;">{'title6'|WM_Lang}</div>
                                </td>
                                {elseif $sort == 'page'}
                                <td class="">
                                    <div class="text-right" style="margin-right: 0.5em;">{'title7'|WM_Lang}</div>
                                </td>
                                {/if}
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

                        <td style="width:56px;">
                            <div class="t1 text-left" style="width:50px; overflow: visible;">{$v.rank}</div>
                        </td>
                        {if $sort == 'M.username'}
                        <td class="">
                            <div class="text-right" style="margin-right: 0.5em;">{$v.userShow}</div>
                        </td>
                        {else}
                        <td class="" style="">
                            <div class="text-left">{$v.userShow}</div>
                        </td>
                        {/if}
                        {if $sort == 'M.last_login'}
                        <td class="">
                            <div class="text-right" style="margin-right: 0.5em;">{$v.last_login}</div>
                        </td>
                        {elseif $sort == 'M.login_times'}
                        <td class="">
                            <div class="text-right" style="margin-right: 0.5em;">{$v.login_times}</div>
                        </td>
                        {elseif $sort == 'M.post_times'}
                        <td class="">
                            <div class="text-right" style="margin-right: 0.5em;">{$v.post_times}</div>
                        </td>
                        {elseif $sort == 'M.dsc_times'}
                        <td class="">
                            <div class="text-right" style="margin-right: 0.5em;">{$v.dsc_times}</div>
                        </td>
                        {elseif $sort == 'rss'}
                        <td class="">
                            <div class="text-right" style="margin-right: 0.5em;">{$v.rss}</div>
                        </td>
                        {elseif $sort == 'page'}
                        <td class="">
                            <div class="text-right" style="margin-right: 0.5em;">{$v.page}</div>
                        </td>
                        {/if}
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
        if (val == 1) {
            obj.order.value = 'asc';
        }else{
            obj.order.value = 'desc';
        }

        obj.sortby.value = val;
        obj.submit();
    }
    
    function last10(c){
        window.open('last10.php?class_id='+c,'','top=100,left=400,width=650,length=400,toolbar=0,status=0,menubar=0,scrollbars=1,resizable=0');
    }
    {/literal}
</script>