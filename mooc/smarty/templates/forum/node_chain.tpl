<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/forum.css?{$forum_css}" rel="stylesheet" />
<link rel="stylesheet" href="{$appRoot}/sys/tpl/vendor/font-awesome/css/font-awesome.min.css">
{* 用來解決ie10+首頁討論版文章點選後，麵包屑被fy遮住*}
{literal}
    <style>
        _:-ms-lang(x), .box1 {
            width:95%;
        }
    </style>
{/literal}
<div class="box1 navbar-fixed-top" style="padding-bottom: 0; background-color: #ECECEC; position: fixed;">
    <div class="title" style="width:100%;">
        {if $isBreadCrumb eq '0'}
            <div class="bread-crumb">
                <span class="home" style="display: none;">{'home'|WM_Lang}</span>
                <span style="display: none;">&gt;</span>
                <span class="path" style="display: none;">{'topics_discussed'|WM_Lang}</span>
                <span style="display: none;">&gt;</span>
                <span class="path2 now">{$forumName}</span>
            </div> 
        {else} 
            <div class="bread-crumb">
                <span class="home">{'home'|WM_Lang}</span>
                <span>&gt;</span>

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
                                        <div class="like" style="line-height: 1em;">
                                            {if $main.pushflag eq 1}
                                                <div class="icon-like" style="display:inline-block;" title="{'cancel'|WM_Lang}{'push'|WM_Lang}"></div>
                                            {else}
                                                <div class="icon-unlike" style="display:inline-block;" title="{'push'|WM_Lang}"></div>
                                            {/if}
                                            <div style="display:inline-block;" class="cnt">{$push_cnt}</div>
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
                                    {if ($teacherCourses|@count gt 0)}
                                        <i class="fa fa-share-square-o icon-repost" aria-hidden="true" title="{'repost'|WM_Lang}" data-toggle="modal" data-target="#repostModal"></i>
                                        <div class="modal fade" id="repostModal" tabindex="-1" role="dialog" aria-labelledby="repostModalLabel" style="position: fixed;">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                        <h4 class="modal-title" id="repostModalLabel">{'repost_content'|WM_Lang}</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form> 
                                                            <div class="form-group">
                                                                <div class="alert alert-success" role="alert" style="line-height: 1.5em; display: none;">{'repost'|WM_Lang}{'successful'|WM_Lang}</div>  
                                                                <div class="alert alert-danger" role="alert" style="line-height: 1.5em; display: none;">{'repost'|WM_Lang}{'failed'|WM_Lang}</div> 
                                                            </div> 
                                                            <div class="form-group">
                                                                <label for="to-cid" class="control-label">{'repost_cname'|WM_Lang}</label>
                                                                <select id="to_cid" name="to_cid" class="form-control">
                                                                    {foreach from=$teacherCourses key=k item=v}
                                                                        <option value="{$k}">{$v}</option>
                                                                    {/foreach}
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="to-bid" class="control-label">{'repost_board'|WM_Lang}</label>
                                                                <select id="to_bid" name="to_bid" class="form-control">
                                                                    {foreach from=$firstCourseForums key=k item=v}
                                                                        <option value="{$k}">{$v.board_name}</option>
                                                                    {/foreach}
                                                                </select>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-blue" id="repost">{'repost'|WM_Lang}</button>
                                                        <button type="button" class="btn btn-default" data-dismiss="modal" id="close">{'close_window'|WM_Lang}</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>                                     
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
                                <div id="inline-ln-{$main.n}" class="inline-ln">
                                    <form class="well">
                                        <div>{'linesharenote'|WM_Lang}</div>
                                    </form>
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
<input type="hidden" name="bTicket" value="{$ticket}"/>
<form id="formSearch" style="display: none;">
    <!--因應資安，表單中需有元素-->
    <input type="hidden" name="token" value="{$csrfToken}" />
</form>
<form id="formAction" method="post" action="">
    <input type="hidden" name="token" value="{$csrfToken}" />
    <input type="hidden" name="cid" value=""/>
    <input type="hidden" name="bid" value=""/>
    <input type="hidden" name="nid" value=""/>
    <input type="hidden" name="mnode" value=""/>
    <input type="hidden" name="subject" value=""/>
    <input type="hidden" name="content" value=""/>
    <input type="hidden" name="awppathre" value=""/>
    <input type="hidden" name="selectPage" value=""/>
</form>

{if $FB_APP_ID ne '' && $fb_comment eq 'Y'}
<meta content='1687220946' property='fb:admins'/><!--https://www.facebook.com/profile.php?id=**********-->
<meta content='{$FB_APP_ID}' property='fb:app_id'/>
<!-- FB 留言框 -->
<b:if cond='data:blog.pageType == "item"'>
<div id="fb-comments" class="fb-comments" data-href="{$appRoot}/forum/m_node_chain.php?cid={$cid}&bid={$main.boardid}&nid={$main.n}" data-colorscheme="light" data-numposts="5" data-width="100%"></div>
<script>
{literal}    
//<![CDATA[
(function() {
    var targetId = "comments", // 留言框出現的位置 id
        ver = "2.8", // API 版本
        url = "//connect.facebook.net/zh_TW/sdk.js#xfbml=1&version=v" + ver,
        script = document.createElement("script"),
        elem = document.getElementById("fb-comments"),
        target = document.getElementById(targetId);
        elem["data-href"] = "http://" + location.hostname + location.pathtname;
    if (target) {
        target.parentNode.insertBefore(elem, target);
    }
    script.src = url;
    document.getElementsByTagName("head")[0].appendChild(script);
})();
//]]>
{/literal}    
</script>
</b:if>
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
        selectPage = '{$nowpage}',
        bltBid = '{$bltBid}',
        postFlag = {$postFlag},
        managerFlag = {$managerFlag},
        nowlang = '{$nowlang}',
        msg = {$msg|@json_encode};
        $(function(){ldelim}
            var forumTitleHeight = $(".box1.navbar-fixed-top").outerHeight();
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
<script type="text/javascript" src="{$appRoot}/public/js/forum/node_chain.js?{$node_chain_js}"></script>
<script type="text/javascript" src="{$appRoot}/theme/default/fancybox/jquery.fancybox.pack.js"></script>