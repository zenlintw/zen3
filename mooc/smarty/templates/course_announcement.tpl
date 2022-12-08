 <a name="top" id="top" style="top:-30px; position: relative;"></a>
<div style="min-width: 600px; margin: auto auto; padding-left: 2em; padding-right: 2em;">
    <ul class="bar" id="peer-page-title">
        <li class="left">
            <span>{$anntMsg.title}</span>
        </li>

        <li class="right">
            {if $moreDisplay === true}
                <div class="dropdown right pull-right" style="font-size: 0.5em; right: 15px;">
                    <a class="dropdown-toggle icon-more" data-toggle="dropdown" href="#"></a>
                    <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                        {if $subscribeEnable === true}
                            <li><a tabindex="-1" id="subscribe" href="javascript:;" onclick="doSubscribe();">{$mySubscribe}</a></li>
                        {/if}
                        {if $manageEnable === true}
                            <li><a tabindex="-1" href="javascript:parent.chgCourse({$anntMsg.cid}, 1, 2, 'SYS_02_03_010');">{$anntMsg.manage}</a></li>
                        {/if}
                    </ul>
                </div>
            {/if}
            {if $manageEnable === true}
                <div class="btnSubscribe right" onclick="doPost();" style="font-size: 0.5em; cursor: pointer;right: 15px;">
                    <div class="icon-new"></div>
                    <span class="right" style="height: 32px; line-height: 3em;">{$anntMsg.post}</span>
                </div>
            {/if}
            <form id="formSearch" class="form-search form-horizontal pull-right" style="top: 0px; padding: 0;" onsubmit="return false;">
                <div class="input-append">
                    <input type="text" class="search-query" name="inputKeyword" id="inputKeyword" style="height: 26px; font-size: 14px; padding-right: 3px; padding-right: 4px \9; padding-left: 3px; padding-left: 4px \9; /* IE7-8 doesn't have border-radius, so don't indent the padding */ margin-bottom: 0; -webkit-border-radius: 3px; -moz-border-radius: 3px; border-radius: 3px;">
                    <button type="button" class="searchBtn btn" id="searchBtn" style="border: 0; background: none; /** belows styles are working good */ padding: 3px 5px; margin-top: 2px; position: relative; left: -28px; /* IE7-8 doesn't have border-radius, so don't indent the padding */ margin-bottom: 0; -webkit-border-radius: 3px; -moz-border-radius: 3px; border-radius: 3px;"><i class="icon-search"></i></button>
                    <input type="hidden" name="bid" id="bid" value="{$anntMsg.bid}">
                    <input type="hidden" name="page" id="page" value="1">
                    <input type="hidden" name="limit" id="limit" value="1">
                    <input type="hidden" name="sort" id="sort" value="postDate">
                    <input type="hidden" name="order" id="order" value="desc">
                </div>
            </form>
        </li>
    </ul>
    <div class="navbar-form"></div>
    <div id="message"></div>
    <div class="box box-padding-t-1 box-padding-lr-3">
        <div id="searchResult">
            <form id="formResult">
                <input type="hidden" id="selectPage" name="selectPage" value="10">
                <input type="hidden" id="inputIssuesPerPage" name="inputIssuesPerPage" value="" />
            </form>
        </div>
        <div id='pageToolbar'></div>
        <form id="post1" method="post" action="/forum/m_edit.php?bTicket={$ticket}">
            <input type="hidden" name="bid" value="{$anntMsg.bid}"/>
            <input type="hidden" name="mnode" value/>
        </form>
    </div>
</div>
<div class="form-footer-space"></div>
<link href="{$appRoot}/theme/default/learn_mooc/peer.css" rel="stylesheet" />
<link href="{$appRoot}/theme/default/learn/peer.css" rel="stylesheet" />
<script type="text/javascript" src="{$appRoot}/lib/kc-paginate.js"></script>
{include file = "common/paginate_jsdeclare.tpl"}
<script>
    var attach = '{$anntMsg.attach}',
        msgDelPost = '{'msg_del_confirm'|WM_Lang}', msgDelSuccess = '{'msg_del_success'|WM_Lang}', msgDelFail = '{'msg_del_fail'|WM_Lang}',
        bTicket = '{$bTicket}', bid = {$anntMsg.bid},
        doEdit, doDelete,
        msg = {$msg|@json_encode},
        nowlang = '{$nowlang}';
    {if $manageEnable === true}{*如果有編輯權限才給這些 function *}
        {literal}
        doDelete = function() {
            var post = $(this).parent().parent().find('.rating-title');
            if (confirm(msgDelPost +'?')){
                var a = bid + ','+ (post.data('n')).substr(1) +',' + post.data('s');
                $.ajax({'url': '/forum/520,'+ a +'.php',
                        'type': 'POST',
                        dataType:"text",
                        'success': function () {
                            alert(msgDelSuccess);
                            // 重整
                            doSearch();
                        },
                        'error': function () {
                            alert(msgDelFail);
                        }
                });
            }
        }

        doEdit = function() {
            var post = $(this).parent().parent().find('.rating-title');
            $('#post1').find('input[name="mnode"]').val((post.data('n')).substr(1));
            $('#post1').submit();
        }

        function doPost() {

            // location.replace('/forum/m_write.php?bTicket=' + bTicket);

            // 清空表單
            resetForm($('#post1'));

            $('#post1').prop('action', '/forum/m_write.php?bTicket=' + bTicket);
            $('#post1').find('input[name="bid"]').val($('#bid').val());

            $('#post1').submit();
        }
        {/literal}
    {/if}
</script>
<script type="text/javascript" src="{$appRoot}/mooc/public/js/course_announcement.js"></script>
<script type="text/javascript" src="{$appRoot}/public/js/forum/forum.js"></script>