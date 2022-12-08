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
    background-color: #7070B8 !important;
}

.data-title {
    padding-left: 4px;
    padding-right: 0px;
    font-size: 16px;
    line-height: 34px;
    letter-spacing: 0px;
    color: #ffffff;
    background-color: #7070B8;
    /*margin:auto;*/
}

.data-value {
    background-color: rgb(236, 236, 236);
    font-size: 16px;
    line-height: 34px;
    letter-spacing: 0px;
    word-break: break-all;
}

#div-user-detail .row {
    display: flex;
    border: 1px solid #fff;
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
    <div class="title">{'query_people'|WM_Lang}</div>
    <div class="content">
        <div class="box2 container" id="div-user-detail" style="display:none;">
            <div class="row"><div class="col-xs-4 data-title">{'username'|WM_Lang}</div><div id="field-username" class="col-xs-8 data-value">&nbsp;</div></div>
            <div class="row"><div class="col-xs-4 data-title">{'realname'|WM_Lang}</div><div id="field-realname" class="col-xs-8 data-value">&nbsp;</div></div>
            <div class="row"><div class="col-xs-4 data-title">{'title55'|WM_Lang}</div><div id="field-gender" class="col-xs-8 data-value">&nbsp;</div></div>
            {*<div class="row">
                <div class="col-xs-4 data-title">{'title_account_enable'|WM_Lang}</div>
                <div id="field-enable-status" class="col-xs-4 data-value">&nbsp;</div>
                <div class="col-xs-4"><button id="btn_enable" type="button" class="btn btn-orange btnNormal" onclick="enableThisUser();" style="display:none;">{'value_account_enabled'|WM_Lang}</button></div>
            </div>*}
            <div class="row"><div class="col-xs-4 data-title">{'title132'|WM_Lang}</div><div id="field-enable-during" class="col-xs-8 data-value">&nbsp;</div></div>
            {*<div class="row"><div class="col-xs-4 data-title">{'lbl_unit_inout'|WM_Lang}</div><div id="field-fda-member" class="col-xs-8 data-value">&nbsp;</div></div>*}
            <div class="row"><div class="col-xs-4 data-title">{'title90'|WM_Lang}</div><div id="field-department" class="col-xs-8 data-value">&nbsp;</div></div>
            <div class="row"><div class="col-xs-4 data-title">{'title89'|WM_Lang}</div><div id="field-title" class="col-xs-8 data-value">&nbsp;</div></div>
            <div class="row"><div class="col-xs-4 data-title">{'title127'|WM_Lang}</div><div id="field-email" class="col-xs-8 data-value">&nbsp;</div></div>
            <div class="row"><div class="col-xs-4 data-title">{'title82'|WM_Lang}</div><div id="field-last-login" class="col-xs-8 data-value">&nbsp;</div></div>
            <div class="row"><div class="col-xs-4 data-title">{'title83'|WM_Lang}</div><div id="field-login-times" class="col-xs-8 data-value">&nbsp;</div></div>
            <div class="row"><div class="col-xs-4 data-title">{'title84'|WM_Lang}</div><div id="field-major-count" class="col-xs-8 data-value">&nbsp;</div></div>
            <div class="row"><div class="col-xs-4 data-title">{'title86'|WM_Lang}</div><div id="field-teach-count" class="col-xs-8 data-value">&nbsp;</div></div>
            <div class="row" style="text-align: center;margin-top:20px;border:0;">
                <div class="col-xs-12" style="min-height: 36px;">
                    <button type="button" class="btn btn-blue btnNormal" onclick="go_list();">{'title130'|WM_Lang}</button>
                </div>
            </div>
        </div>
        <div class="box2" id="div-user-list" style="display:block;">
            <div class="content container">
                <div class="data1 row div-user-data">
                    <div class="col-xs-12">
                        <form action="" method="POST">
                            <div id="BlockSearch" class="container" style="background-color: #F5F5F5;padding:15px;">
                                <div class="row" style="padding-bottom: 10px;">
                                    <div class="col-md-12 block-input-title">搜尋：
                                        <select id="sType" name="sType" style="color:#7b7b7b;font-size:14px;width:100px;height:35px;border:1px solid #c9c9c9;" >
                                            {foreach from=$search_ary item=item key=key}
                                                <option value="{$key}"{if $sType eq $key} selected{/if}>{$item}</option>
                                            {/foreach}
                                        </select>
                                        <input type="text" id="sWord" value="{$sWord}" placeholder="請輸入關鍵字" style="color:#7b7b7b;font-size:14px;min-width:140px;height:35px;border:1px solid #c9c9c9;"></div>
                                        <input type="hidden" id="sBindtype" name="sBindtype" value="" />
                                </div>
                                <div class="row" style="padding-bottom: 10px;">
                                    <input type="hidden" name="page" / >
                                    <div class="col-md-12 block-input-title"><button type="button" class="btn btn-blue btnNormal margin-right-15" id="btnSearch" onclick="queryUser();">搜 尋</button></div>
                                </div>
                            </div>
                        </form>
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
                                <td style="width:110px;">
                                    <div class="t1 text-left">{'th_username'|WM_Lang}</div>
                                </td>
                                <td class="" style="">
                                    <div class="text-center">{'th_name'|WM_Lang}</div>
                                </td>
                                <td class="">
                                    <div class="text-center">{'title56'|WM_Lang}</div>
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
                        <td style="width:110px;">
                            <div class="t1 text-left" style="width:105px;;word-break: break-all;">{$v.username}</div>
                        </td>
                        <td class="">
                            <div class="text-center">{$v.realname}</div>
                        </td>
                        <td class="">
                            <div class="text-center">
                            <i class="fa fa-address-card-o" aria-hidden="true" style="font-size:20px;"  onclick="showUserDetail('{$v.encUsername}');"></i>
                            </div>
                        </td>
                        </tr>
                        {/foreach}
                    </table>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12" style="text-align: center;">
                    <div id="pageToolbar" class="paginate" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
</div>
{* 搜尋 *}
<form id="queryFm" name="queryFm" method="post" action="stud_query.php" enctype="multipart/form-data" style="display:none">
<input type="hidden" name="searchkey" value="{$sType}" id="searchkey" />
<input type="hidden" name="keyword" value="{$sWord}" id="keyword" />
<input type="hidden" name="page_num" value="{$page_num}" id="page_num" />
<input type="hidden" name="page" value="{$current_page}" id="page" />
<input type="hidden" name="bindtype" value="{$sBindtype}" id="bindtype" />
</form>

{* 學員資訊 *}
<form id="actFm" name="actFm" method="post" action="stud_query1.php" enctype="multipart/form-data" style="display:none">
<input type="hidden" name="msgtp" value="" />
<input type="hidden" name="user" value="" />
</form>

<script type="text/javascript" src="{$appRoot}/lib/kc-paginate.js"></script>
{include file = "common/paginate_jsdeclare.tpl"}
<script type="text/javascript">
    {$inlineJS}
    var totalUserCount = parseInt('{$totalUserCount}');
    var current_page = parseInt('{$current_page}');
    var current_username = '';
    {literal}
    function doSearch(page) {
        if (page == current_page) return;
        $("#page").val(page);
        queryUser();
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

    function showUserDetail(user) {
        current_username = user;
        var fields = ['username','realname','gender','field-enable-status','enable-during','fda-member','department','title','email','last-login','login-times','major-count','teach-count'];
        for(var i=0; i<fields.length; i++) {
            $('#field-'+fields[i]).html('&nbsp;');
        }
        $.ajax({
            'type': 'POST',
            'dataType': 'json',
            'data': {'action' : 'getUserDetail', 'user' : user},
            'url': '/mooc/controllers/user_ajax.php',
            'success': function (response) {
                if (response.code == '1') {
                    $('#btn_enable').hide();
                    $.each(response.data, function(key,value){
                        $('#field-'+key).html(value);
                        if ((key == 'enable')&&(value=='N')){
                            $('#btn_enable').show();
                        }
                    })
                    $('#div-user-detail').show();
                    $('#div-user-list').hide();
                }else{
                    alert(response);
                }
            },
            'error': function () {
                if (window.console) {
                    console.log('Ajax Error!');
                }
            }
        });

        
    }

    function enableThisUser(){
        if (current_username.length){
            $.ajax({
                'type': 'POST',
                'dataType': 'json',
                'data': {'action' : 'enableUser', 'user' : current_username},
                'url': '/mooc/controllers/user_ajax.php',
                'success': function (response) {
                    if (response.code == '1') {
                        showUserDetail(current_username);
                    }else{
                        alert(response.data);
                    }
                },
                'error': function () {
                    if (window.console) {
                        console.log('Ajax Error!');
                    }
                }
            });
        }
    }
    function go_list() {
        $('#div-user-detail').hide();
        $('#div-user-list').show();
    }
    {/literal}
</script>