<link href="{$appRoot}/public/css/common.css?{$smarty.now}" rel="stylesheet" />
<link href="{$appRoot}/public/css/forum.css?{$smarty.now}" rel="stylesheet" />
{* 用來解決ie10+首頁討論版文章點選後，麵包屑被fy遮住*}
{literal}
    <style>
        _:-ms-lang(x), .box1 {
            width:95%;
        }

        /*手機尺寸*/
        @media (max-width: 767px) {
            .node-info {
                background-color: #F8F8F8;
                font-size: 14px;
            }

            .node-info .title {
                color: #337ab7;
            }

            .list {
                padding-top: 0px;
            }

            .box2 > .title {
                display: initial;
                font-size: 18px;
                line-height: 28px;
                white-space: initial;
                overflow: auto;
            }

            .photo-l {
                height: auto;
            }
            
            .photo-l > img {
                height: 52px;
            }
            
            .photo-s {
                padding: 0px;
            }
            
            .photo-s > img {
                height: 24px;
            }

            .data3 .main > .top-tmp, .data3 .reply > .top-tmp, .data3 .note > .top-tmp {
                margin-left: 5em;
                line-height: initial;
            }

            .data3 .main > .top-tmp > .post-time {
                margin-left: 0px;
            }

            .data3 .main > .bottom-tmp, .data3 .reply > .bottom-tmp, .data3 .note > .bottom-tmp {
                margin-left: 0px;
                padding-top: 10px;
            }

            .data3 .reply > .top-tmp > .operate > .default > div{
                display: inline-flex;
            }
        }

    </style>
{/literal}
<div class="box1" style="padding-bottom: 0;">
    <div class="title" style="width:100%;">
        {if $isBreadCrumb eq '0'}
            <div class="bread-crumb">
                <span class="path" style="display: none;">{'topics_discussed'|WM_Lang}</span>
                <span style="display: none;">&gt;</span>
                <span class="path2 now">{$forumName}</span>
            </div> 
        {else} 
            <div class="bread-crumb">
                {if $isGroupForum}
                <span class="pathGroup">{'group_discussed'|WM_Lang}</span>
                {else}
                <span class="path">{'topics_discussed'|WM_Lang}</span>
                {/if}
                <span>&gt;</span>
                <span class="path2 now">{$forumName}</span>
            </div>
        {/if}

        <div class="bread-navi" style="float:right">
            <table cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td>
                        {if $postFlag eq 1}
                            <a class="btn btn-blue doReply" href="#" style="letter-spacing: 0.3em; font-size: 1em; font-weight: normal; line-height: 1.4em;" title="{'reply'|WM_Lang}"><i class="icon-share-alt icon-white rotate-180" style="position: relative; top: 0.1em;"></i> {'reply'|WM_Lang}</a>
                        {/if}
                        <a class="btn" href="#" style="margin-left: 0.3em; font-size: 1em; font-weight: normal; line-height: 1.5em; width: 5em;" title="{'return list'|WM_Lang}" onclick="goForum();"> {'return list'|WM_Lang}</a>
                    </td>
                    {if (isset($prevNodeId) || isset($nextNodeId))}
                    <td>
                        <div class="btn-group">
                            {if isset($prevNodeId)}
                                <button class="btn" style="margin-left: 0.5em; line-height: 1.5em;" onclick="goAnotherTopic('{$cid}','{$bid}','{$prevNodeId}');" title="{'prev_rec'|WM_Lang}"><i class="fa fa-chevron-left" aria-hidden="true"></i></button>
                            {/if}
                            {if isset($nextNodeId)}
                                {if (isset($prevNodeId) && isset($nextNodeId))}
                                    {assign var=style_margin value=''}
                                {else}
                                    {assign var=style_margin value='margin-left: 0.5em;'}
                                {/if}
                            <button class="btn" style="{$style_margin} line-height: 1.5em;" onclick="goAnotherTopic('{$cid}','{$bid}','{$nextNodeId}');" title="{'next_rec'|WM_Lang}"><i class="fa fa-chevron-right" aria-hidden="true"></i></button>
                            {/if}
                        </div>
                    </td>
                    {/if}
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="box1 list">
    <div class="content">
        <div class="box2">
            <!-- 主題 -->
            <div class="title">
                <div class="icon-blue-info"></div>{$main.subject}
            </div>
            <div class="content">
                <div id="" class="data3">
                    <div class="main node-info" data-sid="{$main.s}" data-bid="{$main.boardid}" data-nid="_{$main.n}" data-encnid="{$main.encnid}" data-title="{$main.subject}">
                        <div class="author-pic">
                            <div class="photo-l">
                                <img src="{$appRoot}/co_showuserpic.php?a={$main.cpic}" onerror="">
                            </div>
                        </div>
                        <div class="top-tmp">
                            <div class="author-name">{$main.poster} ( {$main.realname} )</div>
                            <div class="post-time" title="{'post_time'|WM_Lang}：{$main.postdate}">{$main.postdate}</div>
                            <div class="operate">
                                <div class="default">
                                    {if $pushPermission eq 1}
                                        {if $main.push >= 1}
                                            {assign var=push_cnt value=$main.push}
                                        {else}
                                            {assign var=push_cnt value=0}
                                        {/if}
                                        <div class="like" style="line-height: 1em;display:inline-flex">
                                            {if $main.pushflag eq 1}
                                                <div class="icon-like" title="{'cancel'|WM_Lang}{'push'|WM_Lang}"></div>
                                            {else}
                                                <div class="icon-unlike" title="{'push'|WM_Lang}"></div>
                                            {/if}
                                            <span class="cnt">{$push_cnt}</span>
                                        </div>
                                    {/if}
                                    {if $socialShare|@count >= 1}
                                    <div class="icon-share" title="{'mooc_share'|WM_Lang}"></div>
                                    {/if}
                                    <div class="icon-mailto" title="{'send_mail'|WM_Lang}"></div>
                                    {if ($profile.username eq $main.poster || $updRight eq 1 || $managerFlag eq true)}
                                        <div class="icon-edit" title="{'btn_edit'|WM_Lang}"></div>
                                        <div class="icon-delete" title="{'del'|WM_Lang}"></div>
                                    {/if}
                                </div>
                                <div class="share" style="display: none;">
                                    {if 'FB'|in_array:$socialShare}
                                        <div class="pic">
                                            <a href="javascript: void(window.open('http://www.facebook.com/share.php?u='.concat(encodeURIComponent('{$appRoot}/forum/m_node_chain.php?cid={$cid}&bid={$main.boardid}&nid={$main.n}'))));"><div class="fb"></div></a>
                                        </div>
                                    {/if}
                                    {if 'PLURK'|in_array:$socialShare}
                                        <div class="pic">
                                            <a href="javascript: void(window.open('http://www.plurk.com/?qualifier=shares&status='.concat(encodeURIComponent('{$v.caption}')).concat(' ').concat(encodeURIComponent('{$appRoot}/forum/m_node_chain.php?cid={$cid}&bid={$main.boardid}&nid={$main.n}'))));"><div class="plk"></div></a>
                                        </div>
                                    {/if}
                                    {if 'TWITTER'|in_array:$socialShare}
                                        <div class="pic">
                                            <a href="javascript: void(window.open('http://twitter.com/home/?status='.concat(encodeURIComponent('{$v.caption}')) .concat(' ').concat(encodeURIComponent('{$appRoot}/forum/m_node_chain.php?cid={$cid}&bid={$main.boardid}&nid={$main.n}'))));"><div class="tw"></div></a>
                                        </div>
                                    {/if}
                                    {if 'LINE'|in_array:$socialShare}
                                        <div class="pic">
                                            <a id="share-ln-{$main.n}" href="#inline-ln-{$main.n}" title="{'note'|WM_Lang}"><div class="ln"></div></a>
                                        </div>
                                    {/if}
                                    {if 'WECHAT'|in_array:$socialShare}
                                        
                                        <div class="pic">
                                            <a id="share-wct-{$main.n}" data-fancybox-type='iframe' href="{$url}" title="{'wechatsharenote'|WM_Lang}"><div class="wct"></div></a>
                                        </div>
                                    {/if}
                                </div>
                            </div>
                        </div>
                        <div class="bottom-tmp">
                            <div class="content">
                                {$main.postcontent}<br>
                                <br>
                            </div>
                            <div style="clear: both; display: block;"></div>
                            <a href="javascript:;" class="show-content">{'view_all'|WM_Lang}</a>
                            {if $main.postfilelink >= '0'}
                            <div class="file">
                                <div>{'attached_file'|WM_Lang}</div>
                                <div>
                                    {$main.postfilelink}
                                </div>
                            </div>
                            {/if}
                        </div>
                    </div>
                        <div class="message">
                        {if $main.reply|@count >= 1}
                                {$main.reply} {'replies'|WM_Lang}
                        {/if}
                        </div>
                        <div class="divider-horizontal"></div>
                        <div id="searchResult">
                            <form id="formResult">
                                <input type="hidden" id="selectPage" name="selectPage" value="1">
                                <input type="hidden" id="inputPerPage" name="inputPerPage" value="10"/>
                            </form>

                            <!-- 回覆1 -->
                        </div>
                </div>
            </div>
        <div id="pageToolbar" class="paginate" style="display: none;"></div>
    </div>
</div>
<form id="formSearch" style="display: none;"></form>
<input type="hidden" name="bTicket" value="{$ticket}"/>
<form id="formAction" method="post" action="">
    <input type="hidden" name="cid" value=""/>
    <input type="hidden" name="bid" value=""/>
    <input type="hidden" name="nid" value=""/>
    <input type="hidden" name="mnode" value=""/>
    <input type="hidden" name="subject" value=""/>
    <input type="hidden" name="content" value=""/>
    <input type="hidden" name="awppathre" value=""/>
</form>
{if $FB_APP_ID ne '' && $fb_comment eq 'Y'}
    <div id="fb-root"></div>
    <script>
    var clientId = '{$FB_APP_ID}';
    
    {literal}
    (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s);
        js.id = id;
        js.src = "//connect.facebook.net/zh_TW/sdk.js#xfbml=1&version=v2.6&appId=" + clientId;
        fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));
    {/literal}
    </script>
    <div class="fb-comments" data-href="{$appRoot}/forum/m_node_chain.php?cid={$cid}&bid={$main.boardid}&nid={$main.n}" data-width="100%" data-numposts="5"></div>
{/if}

<script type="text/javascript" src="{$appRoot}/lib/kc-paginate.js"></script>
{include file = "common/paginate_jsdeclare.tpl"}
<script type="text/javascript">
    var msgDelPost = '{'msg_del_confirm'|WM_Lang}',
        msgDelSuccess = '{'msg_del_success'|WM_Lang}',
        msgDelFail = '{'msg_del_fail'|WM_Lang}',
        whisperNothingFail = '{'whisper_nothing_fail'|WM_Lang}',
        loginCpic = '{$loginCpic}',
        email = '{$email}',
        sysMailsRule = {$sysMailsRule},
        MSG_EMAIL = '{'write_to_msg'|WM_Lang}',
        socialShare = {$socialShare|@json_encode};
        updRight = '{$updRight}';
        username = '{$profile.username}',
        realname = '{$profile.realname}',
        cid = '{$cid}',
        bid = '{$main.boardid}',
        bltBid = '{$bltBid}',
        postFlag = {$postFlag},
        managerFlag = {$managerFlag},
        nowlang = '{$nowlang}',
        msg = {$msg|@json_encode};
        $(function(){ldelim}
            var forumTitleHeight = $(".box1.navbar-fixed-top").outerHeight();
            $("img").addClass('img-responsive');
            if (forumTitleHeight > 53) {ldelim}
                $(".box1.list").css("padding-top",forumTitleHeight+10);
            {rdelim}
        {rdelim});
        
        //    043884 (B)
        var page={$page};
        //    043884 (E)
</script>
<script type="text/javascript" src="{$appRoot}/lib/common.js"></script>
<script type="text/javascript" src="{$appRoot}/public/js/common.js"></script>
<script type="text/javascript" src="{$appRoot}/public/js/forum/forum.js?{$forumJsFTime}"></script>
<script type="text/javascript" src="{$appRoot}/public/js/forum/node_chain.js"></script>
<script type="text/javascript" src="{$appRoot}/theme/default/fancybox/jquery.fancybox.pack.js"></script>