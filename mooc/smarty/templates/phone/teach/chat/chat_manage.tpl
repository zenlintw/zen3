<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
<style>
{literal}
.block-input-title {
    font-size: 16px;
    color: #393939;
}

.btn {
    color: #ffffff;
    background: #05ABAB;
}

.box2 > .title-bar > .data2 > .subject > tbody > tr {
    background-color: #0db9bb !important;
}

@media (max-width: 767px) {
    .data1 {
        padding: 0px;
    }

    .container {
        min-width: initial;
        padding: 5px;
    }

    .div-user-data {
        border-radius: 4px;
    }

    .lbl-user-data {
        white-space: nowrap;
    }

    .value-user-data {
        color: #0088D2;
    }

    .box2 > .title-bar {
        margin-top: initial;
    }
}
{/literal}
</style>
<div class="box1" style="">
    <div class="title">{$pageTitle}</div>
    <div class="content">
        <div class="box2">
            <div class="col-xs-12" style="text-align: right;padding-right: 0px;margin-bottom: 15px;">
                <button id="btnAdd" type="button" class="btn btn-blue btnNormal" onclick="setChat('')">{'btn_new'|WM_Lang}</button>
            </div>
            <div class="title-bar">
                <div class="data2">
                    <table class="table subject">
                        <tbody>
                            <tr>
                                <td style="">
                                    <div class="text-left" style="overflow: visible;">{'th_room_name'|WM_Lang}</div>
                                </td>
                                <td class="" style="">
                                    <div class="text-center">{'th_status'|WM_Lang}</div>
                                </td>
                                <td class="">
                                    <div class="text-right" style="margin-right: 0.5em;">{'th_action'|WM_Lang}</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="content">
                <div class="data2">
                    <table class="table subject">
                        {if $datalist|@count eq 0}
                        <tr><td colspan="3" style="text-align: center;">查無相關資料</td></tr>
                        {/if}
                        {foreach from=$datalist key=k item=v}
                        <tr>
                        <td style="">
                            <div class="text-left" style="overflow: visible;">{$v.title|WM_Title}</div>
                        </td>
                        <td class="">
                            <div class="text-center">{$v.state}</div>
                        </td>
                        <td class="">
                            <div class="text-right" style="margin-right: 0.5em;">
                            <button name="btnEdit" type="button" class="btn" onclick="setChat('{$v.rid}');">{'btn_edit'|WM_Lang}</button>
                            {if $v.sessionCount>0}
                            <button name="btnCancelSession" type="button" class="btn" onclick="cancelSession('{$v.rid}');">{'btn_cancel_session'|WM_Lang}</button>
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
<form id="editFm" name="editFm" method="POST" action="chat_property.php" enctype="multipart/form-data" style="display:none">
<input type="hidden" name="chat_id" value="" />
<input type="hidden" name="ticket" value="{$ticket}" />
</form>
<script type="text/javascript">
{literal}
    /**
     * 設定聊天室
     * @param string val : 聊天室編號
     **/
    function setChat(val) {
        var obj = document.getElementById("editFm");
        if ((typeof(obj) != "object") || (obj == null)) return false;
        obj.chat_id.value = val;
        obj.submit();
    }

    // 清除現行會議的人員，並將會議紀錄產出
    function cancelSession(rid)
    {
        if(!confirm(MSG_CANCEL))
            return false;
        var txt;

        if ((typeof(xmlHttp) != "object") || (xmlHttp == null)) xmlHttp = XmlHttp.create();
        if ((typeof(xmlVar) != "object") || (xmlDoc == null)) xmlVar = XmlDocument.create();

        txt = "<manifest>";
        txt +=  "<exit>"+user_exit+"</exit>";
        txt += "<cancel>true</cancel>";
        txt += "<rid>" + rid + "</rid>";
        txt += "</manifest>";
        // alert(txt);

        xmlHttp = XmlHttp.create();
        xmlVar.loadXML(txt);
        xmlHttp.open("POST", "/learn/chat/chat_logout.php", false);
        xmlHttp.send(xmlVar);

        alert(xmlHttp.responseText);
    }

{/literal}
</script>