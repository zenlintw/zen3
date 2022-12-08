{if !$profile.isPhoneDevice}
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
{/if}
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
<a name="content2"></a>
<div class="box1">
    <div class="title">{'tabs_chat_list'|WM_Lang}</div>
    <div class="content">
        <div class="box2">
            <div class="title-bar" style="margin-top: 2.3em;">
                <div class="data2">
                    <table class="table subject">
                        <tbody>
                            <tr>
                                <td>
                                    <div class="text-left" style="margin-left: 0.5em;">{'th_room_name'|WM_Lang}</div>
                                </td>
                                <td class="t6 hidden-xs">
                                    <div class="text-center"><a href="javascript:;" onclick="sort_data(1);return false;">{'th_open_time'|WM_Lang}</a>
                                        {if $sort == 'open_time'}
                                            {if $order == 'desc'}
                                            <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                            {else}
                                            <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                            {/if}
                                        {/if}
                                    </div>
                                </td>
                                <td class="t6 hidden-xs">
                                    <div class="text-center"><a href="javascript:;" onclick="sort_data(2);return false;">{'th_close_time'|WM_Lang}</a>
                                        {if $sort == 'close_time'}
                                            {if $order == 'desc'}
                                            <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                            {else}
                                            <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                            {/if}
                                        {/if}
                                    </div>
                                </td>
                                <td class="t4 hidden-xs">
                                    <div class="text-center"><a href="javascript:;" onclick="sort_data(3);return false;">{'th_status'|WM_Lang}</a>
                                        {if $sort == 'state'}
                                            {if $order == 'desc'}
                                            <img src="/theme/default/learn/dude07232001down.gif" align="absmiddle" border="0">
                                            {else}
                                            <img src="/theme/default/learn/dude07232001up.gif" align="absmiddle" border="0">
                                            {/if}
                                        {/if}
                                    </div>
                                </td>
                                <td class="t6">
                                    <div class="text-center" style="margin-right: 0.5em;">{'th_action'|WM_Lang}</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="content">
                <div class="data2">
                    <table class="table subject">
                        <iframe id="ifrm_joinnet" src="about:blank" style="display:none" width="100" height="36"></iframe>
                        {if $datalist|@count >= 1}
                        {foreach from=$datalist key=k item=v}
                        <tr>
                            <td>
                                {if $v.onclick != ''}
                                <div  class="text-left" style="margin-left: 0.5em;"><img src="/theme/default/learn/chat/meeting.gif">{$v.title}</div>                
                                {else}
                                <div  class="text-left" style="margin-left: 0.5em;">{$v.title}</div>
                                {/if}
                            </td>
                            <td class="t6 hidden-xs">
                                <div class="text-center">{$v.open_time_view}</div>
                            </td>
                            <td class="t6 hidden-xs">
                                <div class="text-center">{$v.close_time_view}</div>
                            </td>
                            <td class="t4 hidden-xs">
                                <div class="text-center">{$v.state_view}</div>
                            </td>
                            <td class="t6">
                                <div class="text-center" style="margin-right: 0.5em;">
                                    {if $v.action eq 'enable'}
                                        <input type="button" value="{'btn_enter'|WM_Lang}" class="btn btn-gray" onclick="goChat('{$v.rid}');">
                                    {else}
                                        {if $v.onclick != ''}
                                            <input type="button" value="{'btn_enter'|WM_Lang}" class="btn btn-gray" onclick="{$v.onclick}">
                                        {else}
                                            <input type="button" value="{'btn_enter'|WM_Lang}" class="btn btn-gray" disabled>
                                        {/if}
                                    {/if}
                                </div>
                            </td>
                        </tr>
                        {/foreach}
                        {else}
                        <tr>
                            <td colspan="5">
                                <div  class="text-left" style="margin-left: 0.5em;">{'msg_no_chatroom'|WM_Lang}</div>
                            </td>
                        </tr>
                        {/if}
                    </table>
                </div>
            </div>
            <div id="pageToolbar" class="paginate"></div>
        </div>
    </div>
</div>

<form id="sortFm" name="sortFm" action="chat_list.php" method="post" style="display: inline;">
    <input type="hidden" name="sortby" value="{$sort}" />
    <input type="hidden" name="order" value="{$order}" />
</form>

<form id='joinFm' action='http://{$meeting_ip}/Conf/jsp/conference/enterMeetingAction.do' method='POST' target='_blank'>
  <input type='hidden' name='nickname' value='{$nickname}'>
  <input type='hidden' name='username' value='guest@192.168.10.224'>
  <input type='hidden' name='userpass' value='guest'>
  <input type='hidden' name='confpass' value='student123'>
  <input type='hidden' name='confid' value='{$confid}@slavemcu_1.machine1.v2c'>
  <input type='hidden' name='cid' value='{$confid}'>
  <input type='hidden' name='conftype' value='publicchat'>
  <input type='hidden' name='encrypt' value='0'>
  <input type='hidden' name='parmeter' value='go_entermeeting'>
  <input type='hidden' name='email' value='@'>
  <input type='hidden' name='entertype' value='auto'>
  <input type='hidden' name='serverid' value='1:{$meeting_ip}:80:443'>
</form>

<script type="text/javascript">
    {literal}
    function sort_data(val){
        var obj = document.sortFm;
        if (obj.order.value === 'asc'){
            obj.order.value = 'desc';
        }else{
            obj.order.value = 'asc';
        }
    
        obj.sortby.value = val;
        obj.submit();
    }

    function joinMeeting(){
        $('#joinFm').submit();
    }
    {/literal}
</script>
<script type="text/javascript" src="/public/js/common.js"></script>
<script type="text/javascript" src="{$appRoot}/public/js/learn/chat_list.js"></script>