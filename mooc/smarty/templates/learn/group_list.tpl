{if !$profile.isPhoneDevice}
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
{/if}
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
<script type="text/javascript" src="/public/js/common.js"></script>
{literal}
<style>
    .box2 {
        margin-top: 1.7em;
    }

    .title-bar {
        margin-top: 2.3em;
    }
    /*手機尺寸*/
    @media (max-width: 767px) {
        .title-bar {
            margin-top: initial;
        }

        .data1 {
            padding: initial;
        }

        .box2 {
            margin-top: initial;
        }
    }
</style>
{/literal}
<script type="text/javascript" src="group_list.js"></script>
<div class="box1">
    <div class="title">{'group_div'|WM_Lang}</div>
    <div class="content">
        <div class="box2" style="margin-top: 1.7em;">
            <div class="content">
                <div class="data1">
                    <div class="content">
                        <table border="0" cellspacing="0" cellpadding="0">
                        <tr>
                            <td>
                                <span style="margin: 0.3em;"><img src="/theme/default/learn/my_left.png" width="9" height="11" border="0" align="absmiddle" /></span>
                                <span style="">{'select_div'|WM_Lang}</span>
                                <span style="line-height: 2em; margin: 0.3em;">
                                    <select size="1" onchange="location.replace('group_list.php?tid='+this.value)">
                                    {foreach from=$teams key=k item=v}
                                        <option value="{$k}" {if ($assign_team eq $k)}selected="selected"{/if}>{$v}</option>
                                    {/foreach}
                                </span>
                            </td>
                        </tr>
                        </table>
                    </div>
                </div>
            </div>
                <div class="title-bar" style="margin-top: 2.3em;">
                <div class="data2">
                    <table class="table subject">
                        <tbody>
                            <tr>
                                <td class="t4">
                                    <div class="text-left" style="margin-left: 0.5em;">{'group_name'|WM_Lang}</div>
                                </td>
                                <td class="t6 hidden-xs">
                                    <div class="text-center">{'captain'|WM_Lang}</div>
                                </td>
                                <td class="t2 hidden-xs">
                                    <div class="text-center">{'peoples'|WM_Lang}</div>
                                </td>
                                <td class="t3 ">
                                    <div class="text-center">{'board'|WM_Lang}</div>
                                </td>
                                <td class="t3">
                                    <div class="text-center">{'discussion'|WM_Lang}</div>
                                </td>
                                <td class="t3 hidden-xs">
                                    <div class="text-center">{'mail_mem'|WM_Lang}</div>
                                </td>
                                <td class="t3 hidden-xs">
                                    <div class="text-center" style="">{'attri'|WM_Lang}</div>
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
                            <td class="t4">
                                <div class="text-left" style="margin-left: 0.5em;">{$v.group_name}</div>
                            </td>
                            <td class="t6 hidden-xs">
                                <div class="text-center">{$v.leader_name}</div>
                            </td>
                            <td class="t2 hidden-xs">
                                <div class="text-center">
                                    <a class="list-peoples" data-fancybox-type="iframe" href="group_mail_listmem.php?action=list&team_id={$assign_team}&group_id={$v.group_id}" title="{'peoples'|WM_Lang}">{$v.peoples}</a>
                                </div>
                            </td>
                            <td class="t3">
                                <div class="text-center">
                                    {if $v.basic_permission eq '1'}
                                    <a class="btn btn-gray forum" title="{'board'|WM_Lang}" data-cid="{$cid}" data-bid="{$v.board_id}">{'go'|WM_Lang}</a>
                                    {else}
                                    <a class="btn btn-gray" title="{'board'|WM_Lang}" disabled>{'go'|WM_Lang}</a>
                                    {/if}
                                </div>
                            </td>
                            <td class="t3">
                                <div class="text-center">
                                    {if $v.basic_permission eq '1'}
                                    <a class="btn btn-gray" title="{'discussion'|WM_Lang}" onclick="goChat('{$v.rid}');">{'which1_7'|WM_Lang}</a>
                                    {else}
                                    <a class="btn btn-gray" title="{'discussion'|WM_Lang}" disabled>{'which1_7'|WM_Lang}</a>
                                    {/if}
                                </div>
                            </td>
                            <td class="t3 hidden-xs">
                                <div class="text-center">
                                    {if $v.basic_permission eq '1'}
                                    <a class="btn btn-gray list-peoples" href="group_mail_listmem.php?action=mail&team_id={$assign_team}&group_id={$v.group_id}" title="{'mail_mem'|WM_Lang}">{'mails'|WM_Lang}</a>
                                    {else}
                                    <a class="btn btn-gray" title="{'mail_mem'|WM_Lang}" disabled>{'mails'|WM_Lang}</a>
                                    {/if}
                                </div>
                            </td>
                            <td class="t3 hidden-xs">
                                <div class="text-center" style="">
                                    {if $v.adv_permission eq '1'}
                                    <a class="btn btn-gray" title="{'manage'|WM_Lang}" onclick="manage({$assign_team}, {$v.group_id},{$v.board_id});">{'manage'|WM_Lang}</a>
                                    {else}
                                    <a class="btn btn-gray" title="{'manage'|WM_Lang}" disabled>{'manage'|WM_Lang}</a>
                                    {/if}
                                </div>
                            </td>
                        </tr>
                        {/foreach}
                    </table>             
                </div>
            </div>              
        </div>
    </div>
</div>
<form id="node_list" name="node_list" style="display: inline;" method="POST">
    <input type="hidden" name="cid" value="" id="cid">
    <input type="hidden" name="bid" value="" id="bid">
    <input type="hidden" name="teamgroup" value="Y" id="teamgroup">
</form>
<script type="text/javascript">
    // 訊息
    var msg01 = "{'group_name'|WM_Lang}";
    var msg02 = "{'serial'|WM_Lang}";
    var msg03 = "{'realname'|WM_Lang}";
    var msg04 = "{'account'|WM_Lang}";
    var msg05 = "{'capacity'|WM_Lang}";
    var msg06 = "{'captain'|WM_Lang}";
    var msg07 = "{'members'|WM_Lang}";
    var msg08 = "{'close'|WM_Lang}";
    var msg09 = "{'chose_mem'|WM_Lang}";
    {$inlineJS}
{literal}
	function goJoinnet(val, m, n) {
        var obj = document.getElementById("ifrm_joinnet");
        var url = "/webmeeting/joinmeeting.php?" + m + "+" + n;
        obj.src = url;
    }
	
	/**
	* 寄信給組員
	* m:寄信給第幾組次
	* n:寄信給第幾組
	**/
	function mailMember(m, n) {
            window.location.replace("group_mail_listmem.php?" + m + '+' + n);
	}

	/**
	* 管理
	* m:寄信給第幾組次
	* n:寄信給第幾組
	* b:討論板板號
	**/
	function manage(m, n, b) {
            window.location.replace("group_manage_set.php?" + m + '+' + n + '+' + b);
	}

	function manage1(m, n, b) {
            window.location.replace("/webmeeting/oh_set_group.php?" + m + '+' + n + '+' + b); // 小組預約
        }

        function manage2(m, n, b) {
            window.location.replace("/webmeeting/meet_record_list.php?" + m + '+' + n + '+' + b); // 預約列表
	}

	function freezeBox() {
            var obj = document.getElementById('showMembers');
            // 對話框左邊對齊 = [捲動的左座標] + [該 Frame 的寬度] - [對話框寬度(480)] 再左移 10 個 pixel
            obj.style.left = document.body.scrollLeft + document.body.offsetWidth - 460;
            // 對話框上緣對齊 = [捲動的上座標] 下移 10 個 pixel
            obj.style.top = document.body.scrollTop + 60;
	}


	/**
	* 進入聊天室
	* m:寄信給第幾組次
	* n:寄信給第幾組
	**/
	/*function goChat(m, n) {
            // window.location.replace("group_chat.php?"+m+'+'+n);
            var GroupChat = window.open("group_chat.php?" + m + '+' + n, "_blank", "width=800,height=500,toolbar=0,location=0,status=0,menubar=0,directories=0,scrollbars=0,resizable=1");
	}*/
    function goChat(val) {
        if (typeof(parent.c_sysbar) == "object") {
            if (typeof(parent.c_sysbar.goChatroom) == "function") parent.c_sysbar.goChatroom(val);
        } else if (typeof(parent.s_sysbar) == "object") {
            if (typeof(parent.s_sysbar.goChatroom) == "function") parent.s_sysbar.goChatroom(val);
        } else if (typeof(parent.sysbar) == "object") {
            if (typeof(parent.sysbar.goChatroom) == "function") parent.sysbar.goChatroom(val);
        } else {
            if (typeof(window.goChatroom) == "function") window.goChatroom(val);
        }
    }

	/**
	  * 查看組員
	  * m:查看第幾組
	  **/
	function viewMember(m) {
            var cla = 'cssTrOdd';
            obj = document.getElementById("showMember");
            var NEWIM;
            var IM = '<table width="100%" border="0" cellspacing="1" cellpadding="3" >' +
             '<tr class="cssTrEvn" >' +
             '<td class="cssTd" colspan="4" nowrap >' + msg01 + '>' + curGroups[m][1] + '</td>' +
             '</tr>' +
             '<tr class="bg02 font01" >' +
             '<td class="cssTd" nowrap >' + msg02 + '</td>' +
             '<td class="cssTd" nowrap >' + msg03 + '</td>' +
             '<td class="cssTd" nowrap >' + msg04 + '</td>' +
             '<td class="cssTd" nowrap >' + msg05 + '</td>' +
             '</tr>';
            for (var i = 0; i < curGroups[m][3].length; i++) {
             cla = cla == 'cssTrEvn' ? 'cssTrOdd' : 'cssTrEvn';
             uname = curGroups[m][3][i].split(/\\t/, 2);
             msg = (uname[0] == curGroups[m][2]) ? msg06 : msg07;
             IM += '<tr class="' + cla + '" >' +
                     '<td class="cssTd" nowrap >' + (i + 1) + '</td>' +
                     '<td class="cssTd" nowrap >' + ((uname[1]) ? uname[1] : "") + '</td>' +
                     '<td class="cssTd" nowrap >' + uname[0] + '</td>' +
                     '<td class="cssTd" nowrap >' + msg + '</td>' +
                     '</tr>';
             NEWIM += '<tr>' +
                     '<td>' + (i + 1) + '</td>' +
                     '<td>' + ((uname[1]) ? uname[1] : "") + '</td>' +
                     '<td>' + uname[0] + '</td>' +
                     '<td>' + msg + '</td>' +
                     '</tr>';

            }
            cla = cla == 'cssTrEvn' ? 'cssTrOdd' : 'cssTrEvn';
            IM += '<tr class="' + cla + '" >' +
             '<td class="cssTd" colspan="4" nowrap align="center">' +
             '<input type="button" value="' + msg08 + '" class="cssBtn" onclick="document.getElementById(\'showMembers\').style.display=\'none\';" >' +
             '</tr>' +
             '</table>';
            // obj.innerHTML = IM;
            $('#myModal tbody').empty();
            $('#myModal tbody').append(NEWIM);
            // document.getElementById('showMembers').style.display = 'block';
            $('#myModal').modal('show');
            // freezeBox();
    }

{/literal}
</script>