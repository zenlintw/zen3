<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/forum.css" rel="stylesheet" />

<a name="content2"></a>
{* 用來解決ie10+首頁討論版文章點選後，麵包屑被fy遮住*}
{literal}
    <style>
        _:-ms-lang(x), .box1 {
            width: 95%;
        }
    </style>
{/literal}
<div class="box1 navbar-fixed-top" style="padding-bottom: 0; background-color: #ECECEC; position: fixed;">
    <div class="title">
        <div class="bread-crumb">
            {if $isBreadCrumb eq '0'}
                <span class="home" style="display: none;">{'home'|WM_Lang}</span>
                <span style="display: none;">&gt;</span>
                <span class="path" style="display: none;">{'topics_discussed'|WM_Lang}</span>
                <span style="display: none;">&gt;</span>
                <span class="path2 now">{$forumName}</span>   
            {else} 
                <span class="home">{'home'|WM_Lang}</span>
                <span>&gt;</span>
                {if $isGroupForum}
                    <span class="pathGroup">{'group_discussed'|WM_Lang}</span>
                {else}
                    <span class="path">{'topics_discussed'|WM_Lang}</span>
                {/if}
                <span>&gt;</span>
                <span class="path2 now">{$forumName}</span>
            {/if}
        </div>
    </div>
    <div class="operate">
        <div>
            <form class="search-form" data-refer="N" onsubmit="return false;">
                <input type="hidden" name="token" value="{$csrfToken}" />
                <input type="text" class="search-keyword" name="search-keyword" style="width: 95%; visibility: hidden;"/>
                <input type="hidden" name="search-bid"/>
                <button class="search-btn" type="button">
                <i class="icon-search"></i>
                </button>
            </form>
        </div>
        {if $postFlag eq 1}
            <a class="btn btn-blue add-article" href="#" style="letter-spacing: 0.5em;"><i class="icon-plus icon-white" style="position: relative; top: 0.1em;"></i> {'post'|WM_Lang}</a>
        {/if}
        {if $moreDisplay === true}
            {if $subscribeEnable === true}
            <a class="btn" href="javascript:;" id="subscribe" onclick="doSubscribe();"> {$mySubscribe}</a>
            {/if}
        {/if}
    </div>
</div>
<div class="box1 list">
    <div class="content">
        <div id="searchResult">
            <form id="formResult">
                <input type="hidden" name="token" value="{$csrfToken}" />
                <input type="hidden" id="selectPage" name="selectPage" value="1">
                <input type="hidden" id="inputPerPage" name="inputPerPage" value="10"/>
            </form>
        </div>
        <div class="box2">
            {if $forumNote neq ''}
            <div class="title">
                {'ann'|WM_Lang}
            </div>
            <div class="content">
                <div class="data1">
                    <div class="content">
                        {$forumNote|escape|nl2br}
                    </div>
                </div>
            </div>
            {/if}
            <div class="title">
            </div>
            <div class="title-bar">
                <div class="data2">
                    <table class="table subject">
                        <tbody>
                            <tr>
                                <td>
                                    <div class="title" style="margin-left: 0.4em;">{'topic'|WM_Lang}</div>
                                </td>
                                <td class="t1 hidden-phone">
                                    <div class="text-center">{'hit'|WM_Lang}</div>
                                </td>
                                <td class="t1 hidden-phone">
                                    <div class="text-center">{'push'|WM_Lang}</div>
                                </td>
                                <td class="t1 hidden-phone">
                                    <div class="text-center">{'response'|WM_Lang}</div>
                                </td>
                                <td class="t5 hidden-phone">
                                    <div class="">
                                        {'posterandposttime'|WM_Lang}
                                    </div>
                                </td>
                                <td class="t3 hidden-phone">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>            
            <div class="content">
                <div class="data2">
                    <ul class="nav nav-tabs" style="display: none;">
                        <li class="active">
                            <a class="select-show" href="javascript:;" data-id="#news-tpc">{'sort_by_date'|WM_Lang}</a>
                        </li>
                    </ul>
                    <table id="news-tpc"  class="table subject">
                        {*foreach from=$forumList key=k item=v}
                        <tr data-bid="{$v.boardid}" data-nid="_{$v.node}" data-sid="{$v.s}">
                            <td class="t2">
                                {if $v.readflag eq 0}
                                <div class="status new"></div>
                                {else}
                                <div class="status"></div>
                                {/if}
                            </td>
                            <td>
                                <div class="title">{$v.subject}</div>
                            </td>
                            <td class="t1 hidden-phone">
                                {if $v.reply ge 1}
                                <div class="amount"><i class="icon-pencil"></i> {$v.reply}</div>
                                {else}
                                <button class="btn btn-gray first-push">成為第一個回覆者</button>
                                {/if}
                            </td>
                            <td class="t1 hidden-phone">
                                {if $v.push >= 1}
                                {assign var=push value=""}
                                {assign var=firstPush value="display: none;"}
                                {else}
                                {assign var=push value="display: none;"}
                                {assign var=firstPush value=""}
                                {/if}
                                <div class="like" style="{$push}">
                                    {if $v.pushflag eq 1}
                                    <div class="icon-like"></div>
                                    <span>{$v.push}</span>
                                    {else}
                                    <div class="icon-unlike"></div>
                                    <span>{$v.push}</span>
                                    {/if}
                                </div>
                                <button class="btn btn-gray first-push" style="{$firstPush}">成為第一個按讚</button>
                            </td>
                            <td class="t1 hidden-phone">
                                <div class="date" title="張貼時間：{$v.postdate}">{$v.postdatelen}</div>
                            </td>
                            <td class="t1 hidden-phone">
                                <div class="icon-subject-go"></div>
                            </td>
                        </tr>
                        {/foreach*}
                    </table>
                    <table id="hot-tpc" class="table subject" style="display: none;"></table>
                    <table id="push-tpc" class="table subject" style="display: none;"></table>
                </div>
            </div>
            <div id="pageToolbar" class="paginate" style="display: none;"></div>
        </div>
    </div>
</div>
<!--
<form name="node" method="POST" style="display: none;">
    <input type="hidden" name="bid">
    <input type="hidden" name="nid">
</form>
-->
<form id="formAction" method="post" action="" style="display: none;">
    <input type="hidden" name="token" value="{$csrfToken}" />
    <input type="hidden" name="cid" value=""/>
    <input type="hidden" name="bid" value=""/>
    <input type="hidden" name="nid" value=""/>
    <input type="hidden" name="mnode" value=""/>
    <input type="hidden" name="subject" value=""/>
    <input type="hidden" name="content" value=""/>
    <input type="hidden" name="awppathre" value=""/>
    <input type="hidden" name="nowpage" value="">
</form>
<form id="formSearch" style="display: none;">
    <input type="hidden" name="token" value="{$csrfToken}" />
    <input type="hidden" name="bid" id="bid" value="{$bid}">
    <input type="hidden" name="curtab" id="curtab" value="">
    {if $nowpage >= 1}
        <input type="hidden" name="nowpage" id="nowpage" value="{$nowpage}">
    {/if}
</form>
<script type="text/javascript">
    var ticket = '{$ticket}',
        cid = '{$cid}',
        bid = '{$bid}',
        bltBid = '{$bltBid}',
        nowlang = '{$nowlang}',
        username = '{$profile.username}',
        postFlag = '{$postFlag}',
        msg = {$msg|@json_encode};
        $(function(){ldelim}
            var forumTitleHeight = $(".box1.navbar-fixed-top").outerHeight();
            if (forumTitleHeight > 55) {ldelim}
                $(".box1.list").css("padding-top",forumTitleHeight+10);
            {rdelim}
        {rdelim});
</script>
<script type="text/javascript" src="{$appRoot}/lib/kc-paginate.js"></script>
{include file = "common/paginate_jsdeclare.tpl"}
<script type="text/javascript" src="{$appRoot}/public/js/common.js"></script>
<script type="text/javascript" src="{$appRoot}/public/js/forum/forum.js?{$forumJsFTime}"></script>
<script type="text/javascript" src="{$appRoot}/public/js/forum/node_list.js?{$nodelistJsFTime}"></script>