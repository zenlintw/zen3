<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/forum.css" rel="stylesheet" />
<a name="content2"></a>
<div class="box1">
    <div class="title">
        <div class="bread-crumb">
            <span class="home">{'home'|WM_Lang}</span>
            <span>&gt;</span>
        </div>
        {'topics_discussed'|WM_Lang}
    </div>
    <div class="content">
        <div class="box2">
            <div class="title-bar">
                <div class="data2">
                    <table class="table subject">
                        <tbody>
                            <tr>
                                <td>
                                    <div class="title" style="margin-left: 0.4em;">{'discussion_group'|WM_Lang}</div>
                                </td>
                                <td class="t1 hidden-phone">
                                    <div class="text-center">{'post'|WM_Lang}</div>
                                </td>
                                <td class="t1 hidden-phone">
                                    <div class="text-center">{'hit'|WM_Lang}</div>
                                </td>
                                <td class="t5 hidden-phone">
                                    <div class="date">{'period'|WM_Lang}</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>            
            <div class="content">
                <div class="data2">
                    <table class="table subject">
                        {foreach from=$forumList key=k item=v}
                        {if $v.canRead == 'Y'}
                            <tr data-bid="{$v.board_id}">
                        {else}
                            <tr data-bid="-1">
                        {/if}
                            <td class="t2">
                                {if $v.read_flag eq false}
                                    <div class="status new"></div>
                                {else}
                                    <div class="status"></div>
                                {/if}
                            </td>
                            <td>
                                <div class="title">{$v.board_name}</div>
                            </td>
                            <td class="t1 hidden-phone">
                                <div class="text-center">{$v.subject_cnt}</div>
                            </td>
                            <td class="t1 hidden-phone">
                                <div class="text-center">{$v.read_cnt}</div>
                            </td>
                            <td class="t5 hidden-phone">
                                <div>
                                    <div>{'from'|WM_Lang} {if $v.open_time eq '0000-00-00 00:00'}{'rightnow'|WM_Lang}{else}{$v.open_time}{/if}</div>
                                    <div>{'to'|WM_Lang} {if $v.close_time eq '0000-00-00 00:00'}{'forever'|WM_Lang}{else}{$v.close_time}{/if}</div>
                                </div>
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
<form name="node_list" method="POST" style="display: none;">
    <input type="hidden" name="token" value="{$csrfToken}" />
    <input type="hidden" name="cid">
    <input type="hidden" name="bid">
    <input type="hidden" name="nid">
</form>
<script type="text/javascript">
    var nowlang = '{$nowlang}',
        cid = '{$cid}',
        username = '{$profile.username}',
        msg = {$msg|@json_encode};
</script>
<script type="text/javascript" src="{$appRoot}/lib/kc-paginate.js"></script>
<script type="text/javascript" src="{$appRoot}/public/js/common.js"></script>
<script type="text/javascript" src="{$appRoot}/public/js/forum/forum.js"></script>
<script type="text/javascript" src="{$appRoot}/public/js/forum/board_list.js"></script>