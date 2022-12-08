<html>
    <head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=Edge,IE=10,IE=8">
        <script type="text/javascript" src="/lib/jquery/jquery.new.min.js?20200113"></script>
        <script type="text/javascript" src="/theme/default/fancybox/jquery.fancybox.pack.js"></script>
        <link href="/theme/default/bootstrap/css/bootstrap.min.css" rel="stylesheet" />
        <link href="/theme/default/bootstrap//css/bootstrap-responsive.css" rel="stylesheet">
        <link href="/theme/default/fancybox/jquery.fancybox.css" rel="stylesheet" type="text/css" media="screen">
        
        <link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
        <link href="/public/css/forum.css" rel="stylesheet" />
        <!-- <link href="/public/css/cour_path.css" rel="stylesheet" /> -->
        <title>{$anntMsg.title}</title>
    </head>

    <body>
        <div class="d-layout" id="annt">
            <div class="box1">
                <div class="title">
                    {$anntMsg.title}
                </div>
                <div class="operate">
                    <div>                        
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
                    </div>
                </div>
                
                <!-- content -->
                <div class="content">                    
                    <div id="message"></div>    
                    <div id="searchResult">
                        <form id="formResult">
                            <input type="hidden" id="selectPage" name="selectPage" value="10">
                            <input type="hidden" id="inputIssuesPerPage" name="inputIssuesPerPage" value="" />
                        </form>
                    </div>
                            
                    <form id="post1" method="post" action="/forum/m_edit.php?bTicket={$ticket}">
                        <input type="hidden" name="bid" value="{$anntMsg.bid}"/>
                        <input type="hidden" name="mnode" value/>
                    </form>
                    <div id='pageToolbar'></div>
                </div>
            </div>
        </div>
    </body>
</html>
<link href="{$appRoot}/theme/default/learn_mooc/peer.css" rel="stylesheet" />
<link href="{$appRoot}/theme/default/learn/peer.css" rel="stylesheet" />
<script type="text/javascript" src="{$appRoot}/lib/kc-paginate.js"></script>
{include file = "common/paginate_jsdeclare.tpl"}

<script>
    var attach = '{$anntMsg.attach}',
        msgDelPost = '{'msg_del_confirm'|WM_Lang}', msgDelSuccess = '{'msg_del_success'|WM_Lang}', msgDelFail = '{'msg_del_fail'|WM_Lang}',
        bTicket = '{$bTicket}', bid = '{$anntMsg.bid}',
        doEdit, doDelete,
        msg = {$msg|@json_encode},
        nowlang = '{$nowlang}',
        cid= '{$anntMsg.cid}',
        nid= '{$anntMsg.nid}';            
</script>
<script type="text/javascript" src="{$appRoot}/mooc/public/js/m_forum_news_annt.js"></script>