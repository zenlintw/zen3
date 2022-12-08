<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/forum.css" rel="stylesheet" />

<div class="box1">
    <div class="title">
        <div class="bread-crumb">
            <span>{$news_title}</span>
        </div>
    </div>
    <div class="content">
                <div class="box2">
                        <div class="title">
                            <div class="icon-blue-info"></div><span title='{$main.subject}'>{$main.subject|truncate_utf8:55}</span>
                        </div>
                        <div class="content">
                            <div class="data1">
                                <div class="date">
                                    {$main.postdate}
                                </div>
                                <div class="content">
                                    {$main.postcontent}
                                </div>
                                {if $main.postfilelink >= '0'}
                                <div class="file">  
                                    <div>
                                        {'attached_file'|WM_Lang}
                                    </div>
                                    <div>
                                        {$main.postfilelink}
                                    </div>
                                </div>
                               {/if}
                            </div>
                        </div>
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
<script type="text/javascript" src="{$appRoot}/lib/kc-paginate.js"></script>
{include file = "common/paginate_jsdeclare.tpl"}
<script type="text/javascript">
    var msgDelPost = '{'msg_del_confirm'|WM_Lang}',
        msgDelSuccess = '{'msg_del_success'|WM_Lang}',
        msgDelFail = '{'msg_del_fail'|WM_Lang}',
        loginCpic = '{$loginCpic}',
        email = '{$email}',
        sysMailsRule = {$sysMailsRule},
        MSG_EMAIL = '{'write_to_msg'|WM_Lang}',
        socialShare = {$socialShare|@json_encode};
        //updRight = {$updRight};
        username = '{$profile.username}',
        realname = '{$profile.realname}',
        cid = '{$main.cid}',
        bid = '{$main.boardid}',
        bltBid = '{$bltBid}',
        postFlag = {$postFlag},
        managerFlag = {$managerFlag},
        nowlang = '{$nowlang}',
        msg = {$msg|@json_encode};
</script>
<script type="text/javascript" src="{$appRoot}/lib/common.js"></script>
<script type="text/javascript" src="{$appRoot}/public/js/common.js"></script>
<script type="text/javascript" src="{$appRoot}/public/js/forum/forum.js"></script>
<script type="text/javascript" src="{$appRoot}/public/js/forum/node_chain.js"></script>
<script type="text/javascript" src="{$appRoot}/theme/default/fancybox/jquery.fancybox.pack.js"></script>