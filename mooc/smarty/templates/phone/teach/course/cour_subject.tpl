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

.btn {
    color: #ffffff;
    background: #05ABAB;
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
    <div class="title">{'subject_title'|WM_Lang}</div>
    <div class="content">
        <div class="box2">
            <div class="col-xs-12" style="text-align: right;padding-right: 0px;margin-bottom: 15px;">
                <button id="btnAdd" type="button" class="btn btn-blue btnNormal" onclick="setSubject('')">{'btm_add'|WM_Lang}</button>
            </div>
            <div class="title-bar">
                <div class="data2">
                    <table class="table subject">
                        <tbody>
                            <tr>
                                <td style="">
                                    <div class="text-left" style="overflow: visible;">{'title_subject'|WM_Lang}</div>
                                </td>
                                <td class="" style="">
                                    <div class="text-center">{'title_status'|WM_Lang}</div>
                                </td>
                                <td class="">
                                    <div class="text-right" style="margin-right: 0.5em;">{'title_action'|WM_Lang}</div>
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
                            <div class="text-left" style="overflow: visible;">{$v.bname|WM_Title}</div>
                        </td>
                        <td class="">
                            <div class="text-center">{$v.state}</div>
                        </td>
                        <td class="">
                            <div class="text-right" style="margin-right: 0.5em;">
                            <button name="btnModify" type="button" class="btn" onclick="setSubject('{$v.node_id}');">{'btm_modify'|WM_Lang}</button>
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
<form id="editFm" name="editFm" method="POST" action="cour_subject_property.php" enctype="multipart/form-data" style="display:none">
<input type="hidden" name="nid" value="" />
<input type="hidden" name="ticket" value="{$ticket}" />
</form>
<script type="text/javascript">
    {literal}
    /**
     * 設定議題討論版
     * @param string val : 議題討論版編號
     **/
    function setSubject(val) {
        var obj = document.getElementById("editFm");
        if ((typeof(obj) != "object") || (obj == null)) return false;
        obj.nid.value = val;
        obj.submit();
    }
    {/literal}
</script>