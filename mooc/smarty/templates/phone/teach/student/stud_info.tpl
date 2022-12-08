<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
<style>
{literal}
.row_high_light {
    background-color:#ffbc1c !important;
}

.box2 > .title-bar > .data2 > .subject > tbody > tr {
    background-color: #0db9bb !important;
}

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
    <div class="title">{'student_info'|WM_Lang}</div>
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
                            <option value="1"{if $sort == 'T.username'} selected{/if}>{'account'|WM_Lang}</option>
                            <option value="5"{if $sort == 'T.login_times'} selected{/if}>{'title3'|WM_Lang}</option>
                            <option value="6"{if $sort == 'T.last_login'} selected{/if}>{'title13'|WM_Lang}</option>
                            <option value="7"{if $sort == 'T.post_times'} selected{/if}>{'title4'|WM_Lang}</option>
                            <option value="8"{if $sort == 'T.dsc_times'} selected{/if}>{'title5'|WM_Lang}</option>
                            <option value="9"{if $sort == 'rss'} selected{/if}>{'title6'|WM_Lang}</option>
                            <option value="10"{if $sort == 'page'} selected{/if}>{'title7'|WM_Lang}</option>
                        </select>
                    </div>
                    <div style="clear: both;"></div>
                    <div class="col-xs-3 lbl-user-data">{'select_role'|WM_Lang}</div>
                    <div class="col-xs-9">
                        <select name="role" onchange="chgRole(this.value);">
                            <option value="auditor"{if $role == 'auditor'} selected{/if}>{'auditor'|WM_Lang}</option>
                            <option value="student"{if $role == 'student'} selected{/if}>{'student'|WM_Lang}</option>
                            <option value="assistant"{if $role == 'assistant'} selected{/if}>{'assistant'|WM_Lang}</option>
                            <option value="instructor"{if $role == 'instructor'} selected{/if}>{'instructor'|WM_Lang}</option>
                            <option value="teacher"{if $role == 'teacher'} selected{/if}>{'teacher'|WM_Lang}</option>
                        </select>
                    </div>
                    <div style="clear: both;"></div>
                    {if $datas|@count gt 0}
                    <div class="col-xs-3 lbl-user-data">{'title1'|WM_Lang}</div><div id="now-data-user-show" class="col-xs-9 value-user-data">{$userLearnData.userShow}</div>
                    <div class="col-xs-3 lbl-user-data">{'title15'|WM_Lang}</div><div id="now-data-rank" class="col-xs-3 value-user-data">{$userLearnData.rank}</div>
                    <div class="col-xs-3 lbl-user-data">{'title3'|WM_Lang}</div><div id="now-data-login-times" class="col-xs-3 value-user-data">{$userLearnData.login_times}</div>
                    <div class="col-xs-3 lbl-user-data">{'title4'|WM_Lang}</div><div id="now-data-post-times" class="col-xs-3 value-user-data">{$userLearnData.post_times}</div>
                    <div class="col-xs-3 lbl-user-data">{'title5'|WM_Lang}</div><div id="now-data-dsc-times" class="col-xs-3 value-user-data">{$userLearnData.dsc_times}</div>
                    <div class="col-xs-3 lbl-user-data">{'title6'|WM_Lang}</div><div id="now-data-rss" class="col-xs-3 value-user-data">{$userLearnData.rss}</div>
                    <div class="col-xs-3 lbl-user-data">{'title7'|WM_Lang}</div><div id="now-data-page" class="col-xs-3 value-user-data">{$userLearnData.page}</div>
                    <div class="col-xs-4 lbl-user-data">{'title13'|WM_Lang}</div><div id="now-data-last-login" class="col-xs-8 value-user-data">{$userLearnData.last_login}</div>
                    {/if}
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
                                <td class="" style="">
                                    <div class="text-center">{'account'|WM_Lang}</div>
                                </td>
                                {if $sort == 'T.last_login'}
                                <td class="">
                                    <div class="text-right" style="margin-right: 0.5em;">{'title13'|WM_Lang}</div>
                                </td>
                                {elseif $sort == 'T.login_times'}
                                <td class="">
                                    <div class="text-right" style="margin-right: 0.5em;">{'title3'|WM_Lang}</div>
                                </td>
                                {elseif $sort == 'T.post_times'}
                                <td class="">
                                    <div class="text-right" style="margin-right: 0.5em;">{'title4'|WM_Lang}</div>
                                </td>
                                {elseif $sort == 'T.dsc_times'}
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
                        {if $datalist|@count eq 0}
                        <tr><td colspan="{if $sort == 'T.username'}3{else}4{/if}" style="text-align: center;">查無相關資料</td></tr>
                        {/if}
                        {foreach from=$datalist key=k item=v}
                        <tr id="stud_info_row_{$v.rank}"{if $userLearnData.username eq $v.username} class="row_high_light"{/if} onclick="showDetailInfo({$v.rank});">
                        <td style="width:56px;">
                            <div name="data-rank" class="t1 text-left" style="width:50px; overflow: visible;">{$v.rank}</div>
                        </td>
                        <td class="">
                        <div name="data-user-show" class="text-left">{$v.userShow}</div>
                        </td>
                        <td style="display: {if $sort == 'T.last_login'}initial{else}none{/if}">
                            <div name="data-last-login" class="text-right" style="margin-right: 0.5em;">{$v.last_login}</div>
                        </td>
                        <td style="display: {if $sort == 'T.login_times'}initial{else}none{/if}">
                            <div name="data-login-times" class="text-right" style="margin-right: 0.5em;">{$v.login_times}</div>
                        </td>
                        <td style="display: {if $sort == 'T.post_times'}initial{else}none{/if}">
                            <div name="data-post-times" class="text-right" style="margin-right: 0.5em;">{$v.post_times}</div>
                        </td>
                        <td style="display: {if $sort == 'T.dsc_times'}initial{else}none{/if}">
                            <div name="data-dsc-times" class="text-right" style="margin-right: 0.5em;">{$v.dsc_times}</div>
                        </td>
                        <td style="display: {if $sort == 'rss'}initial{else}none{/if}">
                            <div name="data-rss" class="text-right" style="margin-right: 0.5em;">{$v.rss}</div>
                        </td>
                        <td style="display: {if $sort == 'page'}initial{else}none{/if}">
                            <div name="data-page" class="text-right" style="margin-right: 0.5em;">{$v.page}</div>
                        </td>
                        </tr>
                        {/foreach}
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<form id="sortFm" name="sortFm" action="stud_info.php" method="post" style="display:inline">
<input type="hidden" name="sortby" value="{$sortVal}" />
<input type="hidden" name="order" value="{$order}" />
<input type="hidden" name="role" value="{$role}" />
</form>
<script type="text/javascript">
    {literal}
    var currentShowRankNo = 1;
    function showDetailInfo(rankNo) {
        if (rankNo == currentShowRankNo) return false;
        $("#stud_info_row_"+currentShowRankNo).removeClass("row_high_light");
        $("#stud_info_row_"+rankNo).addClass("row_high_light");
        $("#stud_info_row_"+rankNo).find("td>div").each(function(){
            $('#now-'+$(this).attr("name")).html($(this).html());
        });
        currentShowRankNo = rankNo;
        $('body').animate({
            scrollTop: 0
        });
    }

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
    function chgRole(val){
        var obj = document.sortFm;
        obj.role.value = val;
        obj.submit();
    }
    {/literal}
</script>