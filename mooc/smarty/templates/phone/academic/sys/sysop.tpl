<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
<style>
{literal}
.block-input-title {
    font-size: 16px;
    color: #393939;
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
    <div class="title">{'tabs_title'|WM_Lang}</div>
    <div class="content">
        <div class="box2">
            <div class="content container">
                <div class="data1 row div-user-data">
                    <div class="col-xs-12" style="text-align: right;">
                        <button id="btn_add_admin" type="button" class="btn btn-blue btnNormal" onclick="addAdmin();">{'btn_add_admin'|WM_Lang}</button>
                    </div>
                    <form name="fmProperty" id="fmProperty" action="" method="POST" style="display:none;">
                        <input type="hidden" name="ticket" value="{$ticket}" />
                        <input type="hidden" name="opnSName" value="{$schoolId}" />
                        <div class="col-xs-12 block-input-title">{'tabs_modify'|WM_Lang}：</div>
                        <div class="col-xs-12" style="height: 42px;">
                            <input type="text" name="opnName" value="" placeholder="請輸入帳號" style="color:#7b7b7b;font-size:14px;width:100%;height:35px;border:1px solid #c9c9c9;">
                        </div>
                        <div class="col-xs-12">
                            <select id="opnPermit" name="opnPermit" style="color:#7b7b7b;font-size:14px;width:100%;height:35px;">
                            {if $profile.isManager}
                            <option value="0">{'permit_manager'|WM_Lang}</option>
                            {/if}
                            {if $profile.isManager}
                            <option value="1">{'permit_administrator'|WM_Lang}</option>
                            {/if}
                            {if $profile.username eq 'root'}
                            <option value="2">{'permit_root'|WM_Lang}</option>
                            {/if}
                            </select>
                        </div>
                        <div class="col-xs-12">{'th_limit_ip'|WM_Lang}：</div>
                        <div class="col-xs-12 block-input-title">
                            <textarea name="opnIP" cols="25" rows="4">*</textarea>
                        </div>
                        <div class="col-xs-12">{'th_help_limit_ip'|WM_Lang}</div>
                        <div class="col-xs-12" style="text-align: center;">
                        <button id="btn_submit" type="button" class="btn btn-blue btnNormal" onclick="saveAdmin()">{'btn_ok'|WM_Lang}</button>
                        <button id="btn_cancel" type="button" class="btn btnNormal" onclick="showEditUI(false)">{'btn_cancel'|WM_Lang}</button>
                        </div>
                    </form>
                </div>
            </div>
            <div id="data-header" class="title-bar">
                <div class="data2">
                    <table class="table subject">
                        <tbody>
                            <tr>
                                <td style="width:56px;">
                                    <div class="t1 text-left" style="width:50px; overflow: visible;">{'th_username'|WM_Lang}</div>
                                </td>
                                <td class="" style="">
                                    <div class="text-center">{'th_name'|WM_Lang}</div>
                                </td>
                                <td class="" style="">
                                    <div class="text-center">{'th_permit'|WM_Lang}</div>
                                </td>
                                <td class="">
                                    <div class="text-right" style="margin-right: 0.5em;">{'th_action'|WM_Lang}</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div id="data-body" class="content">
                <div class="data2">
                    <table class="table subject">
                        {if $datalist|@count eq 0}
                        <tr><td colspan="4" style="text-align: center;">查無相關資料</td></tr>
                        {/if}
                        {foreach from=$datalist key=k item=v}
                        <tr>
                        <td style="width:56px;">
                            <div class="t1 text-left" style="width:50px; overflow: visible; word-break: break-all;">{$v.username}</div>
                        </td>
                        <td class="">
                            <div class="text-center" style="word-break: break-all;">{$v.realname}</div>
                        </td>
                        <td class="">
                            <div class="text-center">{$v.levelshow}</div>
                        </td>
                        <td class="">
                            <div class="text-right" style="margin-right: 0.5em;">
                            {if $v.canModify}
                            <button name="btnModify" type="button" class="btn" onclick="editAdmin('{$v.username}','{$v.school_id}');">{'btn_modify'|WM_Lang}</button>
                            <button name="btnModify" type="button" class="btn" onclick="delAdmin('{$v.username}','{$v.school_id}');" style="margin-top:5px;">{'btn_delete'|WM_Lang}</button>
                            {else}
                            &nbsp;
                            {/if}
                            </div>
                        </td>
                        </tr>
                        {/foreach}
                    </table>
                </div>
            </div>
            <div id="page_tool" class="row">
                <div class="col-md-12" style="text-align: center;">
                    <div id="pageToolbar" class="paginate" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<form id="queryFm" name="queryFm" method="post" action="sysop.php" enctype="multipart/form-data" style="display:none">
<input type="hidden" name="page_num" value="{$page_num}" id="page_num" />
<input type="hidden" name="page" value="{$current_page}" id="page" />
</form>
<script type="text/javascript" src="{$appRoot}/lib/kc-paginate.js"></script>
{include file = "common/paginate_jsdeclare.tpl"}
<script type="text/javascript">

    var MSG_INPUT_USERNAME = "{'msg_need_username'|WM_Lang}";
    var MSG_INPUT_IP = "{'msg_need_ip'|WM_Lang}";
    var MSG_ADD_SUCCESS = "{'msg_add_success'|WM_Lang}";
    var MSG_UPDATE_SUCCESS = "{'msg_update_success'|WM_Lang}";
    var MSG_DELELT_CONFIRM = "{'msg_del_help'|WM_Lang}";
    var msgDeleteFinish = "{'msg_del_success'|WM_Lang}";
    var xmlDocs = null;
    var totalUserCount = parseInt('{$totalUserCount}');
    var current_page = parseInt('{$current_page}');

    {literal}
    function doSearch(page) {
        if (page == current_page) return;
        $("#page").val(page);
        $("#queryFm").submit();
    }
    function showEditUI(bl) {
        if (bl) {
            $('#btn_add_admin').hide();
            $('#data-header').hide();
            $('#data-body').hide();
            $('#page_tool').hide();
            $('#fmProperty').show();
        }else{
            $('#btn_add_admin').show();
            $('#data-header').show();
            $('#data-body').show();
            $('#page_tool').show();
            $('#fmProperty').hide();
        }
    }
    
    $(function(){
        // 分頁工具列
        $('#pageToolbar').paginate({
            'total': totalUserCount,
            'showPageList': false,
            'showRefresh': false,
            'showSeparator': false,
            'btnTitleFirst': btnTitleFirst,
            'btnTitlePrev': btnTitlePrev,
            'btnTitleNext': btnTitleNext,
            'btnTitleLast': btnTitleLast,
            'btnTitleRefresh': btnTitleRefresh,
            'beforePageText': beforePageText,
            'afterPageText': afterPageText,
            'beforePerPageText': '&nbsp;' + beforePerPageText,
            'afterPerPageText': afterPerPageText,
            'displayMsg': displayMsg,
            'buttonCls': '',
            'onSelectPage': function (num, size) {
                $('#selectPage').val(num);
                doSearch(num);
            }
        });

        // 顯示分頁列
        $('#pageToolbar').show();

        $('#pageToolbar').paginate('select', current_page);
    });

    function getAdmin(uname, sid) {

    }

    var editMode = false;
    /**
     * 新增管理者
     **/
    function addAdmin() {
        var obj = document.getElementById("fmProperty");

        editMode = false;
        showEditUI(true);
        obj.reset();
    }

    /**
     * 修改管理者
     * @param string  uname : 帳號
     * @param integer sid   : 學校編號
     **/
    var orgsid = 0;
    function editAdmin(uname, sid) {
        var txt = "";

        txt  = "<manifest>";
        txt += "<ticket></ticket>";
        txt += "<username>" + uname + "</username>";
        txt += "<sid>" + sid + "</sid>";
        txt += "</manifest>";
        $.ajax({
            'type': 'POST',
            'dataType': 'xml',
            'data': txt,
            'url': 'sysop_get.php',
            'success': function (response) {
                var obj = document.getElementById("fmProperty");
                xmlDocs = $(response);
                editMode = true;
                obj.opnName.value = xmlDocs.find('uname').text();
                obj.opnPermit.value = parseInt(xmlDocs.find('permit').text());
                obj.opnIP.value = xmlDocs.find('limit_ip').text();
                showEditUI(true);
            },
            'error': function () {
                if (window.console) {
                    console.log('Ajax Error!');
                }
            }
        });
    }

    /**
     * 儲存資料
     **/
    function saveAdmin() {
        var nodes = null;
        var obj = document.getElementById("fmProperty");
        var uname, sid, uPermit, uIP;
        var txt = "";

        uname = obj.opnName.value;
        if (uname == "") {
            alert(MSG_INPUT_USERNAME);
            return false;
        }
        uIP = obj.opnIP.value;
        if (uIP == "") {
            alert(MSG_INPUT_IP);
            return false;
        }
        uPermit = obj.opnPermit.value;

        txt  = "<manifest>";
        txt += "<ticket></ticket>";
        txt += "<mode>" + (editMode ? 'edit' : 'add') + "</mode>";
        txt += "<username>" + uname + "</username>";
        txt += "<sid>" + obj.opnSName.value + "</sid>";
        txt += "<osid>" + obj.opnSName.value + "</osid>";
        txt += "<permit>" + uPermit + "</permit>";
        txt += "<ip>" + uIP + "</ip>";
        txt += "</manifest>";
        $.ajax({
            'type': 'POST',
            'dataType': 'xml',
            'data': txt,
            'url': 'sysop_save.php',
            'success': function (response) {
                txt = editMode ? MSG_UPDATE_SUCCESS : MSG_ADD_SUCCESS;
                alert(txt);
                location.reload();
                return true;
            },
            'error': function (response) {
                alert(response.responseText);
                showEditUI(false);
            }
        });
    }

    function delAdmin(uname, sid) {
        if (confirm(MSG_DELELT_CONFIRM)) {
            $.ajax({
                'type': 'POST',
                'dataType': 'json',
                'data': {'username' : uname, 'sid' : sid},
                'url': 'sysop_del.php',
                'success': function (response) {
                    if (response == 'OK') {
                        alert(msgDeleteFinish);
                    }else{
                        alert(response);
                    }
                    location.reload();
                    return true;
                },
                'error': function (response) {
                    alert('AJAX Fail!');
                }
            });
        }
    }

    {/literal}
</script>