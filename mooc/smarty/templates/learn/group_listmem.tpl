<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
<script type="text/javascript" src="group_listmem.js"></script>
<a name="content2"></a>
<div class="box1" style="max-width: 670px;">
    <div class="title">{$group_name} &gt; {'mem_list'|WM_Lang}</div>
    <div class="content" id="groupMemList" style="padding: 0em 2em 1em 2em;">
        <div class="box2" style="margin-top: 1.7em;">
            <div class="title-bar" style="margin-top: 2.3em;">
                <div class="data2">
                    <table class="table subject">
                        <tbody>
                            <tr>
                                {if ($action eq 'mail')}
                                <td style="width: 1em;">
                                    <div class="text-center" style="margin-left: 0.5em;"><input type="checkbox" value="" title="{'td_alt_sel'|WM_Lang}" class="member"></div>
                                </td>
                                {/if}
                                <td class="t1 hidden-phone" style="width: 2em;">
                                    <div class="text-center" style="margin-left: 0.5em;">{'serial'|WM_Lang}</div>
                                </td>
                                <td class="t4">
                                    <div class="text-center">{'realname'|WM_Lang}</div>
                                </td>
                                <td class="t4 hidden-phone">
                                    <div class="text-center">{'account'|WM_Lang}</div>
                                </td>
                                <td class="t1 hidden-phone">
                                    <div class="text-center" style="margin-right: 0.5em;">{'capacity'|WM_Lang}</div>
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
                            {if ($action eq 'mail')}
                            <td style="width: 1em;">
                                <div class="text-center" style="margin-left: 0.5em;">
                                    {if ($v.email ne '')}
                                    <input type="checkbox" name="target[]" value="{$v.username}" class="member">
                                    {/if}
                                </div>
                            {/if}
                            </td>
                            <td class="t1 hidden-phone" style="width: 2em;">
                                <div class="text-center" style="margin-left: 0.5em;">{$v.serial}</div>
                            </td>
                            <td class="t4">
                                <div class="text-center">
                                    {if ($v.email ne '')}
                                        <a href="mailto:{$v.email}" title="{'mails'|WM_Lang}">{$v.realname}</a>
                                    {else}
                                        {$v.realname}
                                    {/if}
                                </div>
                            </td>
                            <td class="t4 hidden-phone">
                                <div class="text-center">{$v.username}</div>
                            </td>
                            <td class="t1 hidden-phone">
                                <div class="text-center" style="margin-right: 0.5em;">
                                    {if ($v.username eq $v.captain)}
                                        {'captain'|WM_Lang}
                                    {else}
                                        {'members'|WM_Lang}
                                    {/if}
                                </div>
                            </td>
                        </tr>
                        {/foreach}
                    </table>
                    <div class="text-right">
                        {if ($action eq 'list')}
                            <button value="{'close'|WM_Lang}" class="btn btn-blue" onclick="parent.$.fancybox.close();">{'close'|WM_Lang}</button>
                        {else}
                            <button value="{'cancel'|WM_Lang}" class="btn" onclick="history.back();">{'cancel'|WM_Lang}</button>
                            <button value="{'step1'|WM_Lang}" class="btn btn-blue" onclick="mailTo();">{'step1'|WM_Lang}</button>
                        {/if}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 
<form id="mailForm" name="mailForm" action="group_mail_write.php" style="display: inline;" method="POST">
    <input type="hidden" name="to" value="" id="to">
    <input type="hidden" name="tid" value="{$team_id}" id="tid">
    <input type="hidden" name="gid" value="{$group_id}" id="gid">
</form>
<script>
    nowlang = '{$nowlang}',
    msg = {$msg|@json_encode};
</script>