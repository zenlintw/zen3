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
    <div class="title">{'title39'|WM_Lang}</div>
    <div class="content">
        <div class="box2">
            <div class="content container">
                <div class="data1 row div-user-data">
                    <form name="actForm" id="actForm" action="teacher_save.php" method="POST">
                        <input type="hidden" name="ticket" value="{$ticket}" />
                        <div class="col-xs-12 block-input-title">{'add_teacher'|WM_Lang}：</div>
                        <div class="col-xs-12" style="height: 42px;">
                            <input type="text" name="username" value="" placeholder="請輸入帳號" style="color:#7b7b7b;font-size:14px;width:100%;height:35px;border:1px solid #c9c9c9;">
                        </div>
                        <div class="col-xs-6">
                            <select name="level" style="color:#7b7b7b;font-size:14px;width:100%;height:35px;">
                                <option value="assistant" selected>{'assistant'|WM_Lang}</option>
                                <option value="instructor">{'instructor'|WM_Lang}</option>
                            </select>
                        </div>
                        <div class="col-xs-6" style="text-align: center;">
                        <button id="btn_submit" type="button" class="btn btn-blue btnNormal" onclick="checkData();">{'store'|WM_Lang}</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="title-bar">
                <div class="data2">
                    <table class="table subject">
                        <tbody>
                            <tr>
                                <td style="width:56px;">
                                    <div class="t1 text-left" style="width:50px; overflow: visible;">{'user_account'|WM_Lang}</div>
                                </td>
                                <td class="" style="">
                                    <div class="text-center">{'real_name'|WM_Lang}</div>
                                </td>
                                <td class="" style="">
                                    <div class="text-center">{'status'|WM_Lang}</div>
                                </td>
                                <td class="">
                                    <div class="text-right" style="margin-right: 0.5em;">{'delete'|WM_Lang}</div>
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
                        <tr><td colspan="4" style="text-align: center;">查無相關資料</td></tr>
                        {/if}
                        {foreach from=$datalist key=k item=v}
                        <tr>
                        <td style="width:56px;">
                            <div class="t1 text-left" style="width:50px; overflow: visible;">{$v.username}</div>
                        </td>
                        <td class="">
                            <div class="text-center">{$v.realname}</div>
                        </td>
                        <td class="">
                            <div class="text-center">{$v.role}</div>
                        </td>
                        <td class="">
                            <div class="text-right" style="margin-right: 0.5em;">
                            {if $self_level eq 'teacher'}
                                {if $v.level eq 'teacher'}
                                &nbsp;
                                {else}
                                <button name="btnDelAssistant" type="button" class="btn" onclick="editTeacher('{$v.username}','{$v.level}');">{'delete'|WM_Lang}</button>
                                {/if}
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
        </div>
    </div>
</div>
<script type="text/javascript">
</script>