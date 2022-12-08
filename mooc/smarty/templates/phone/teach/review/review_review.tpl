<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
{literal}
<style>
    .box2 {
        border: 1px solid #C4C4C4;
        background: #FFFFFF;
        text-align: left;
        margin: 0px;
        margin-bottom: 15px;
    }

    .box2 > .row {
        background-color: #0db9bb;
    }

    .container {
        min-width: initial;
    }

    .row {
        margin-left: -15px;
    }

    .data-title {
        padding-left: 4px;
        padding-right: 0px;
        background-color: #0db9bb;
        font-size: 16px;
        line-height: 32px
        letter-spacing: 0px;
        color: #ffffff;
    }

    .data-value {
        background-color: #FFFFFF;
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
<div class="box1">
    <div class="title">{'tabs_teach_main'|WM_Lang}</div>
    <div class="content">
        <div class="container" id="review_no_data" style="display:{if $datas|@count eq 0}block{else}none{/if}">{'msg_no_data'|WM_Lang}</div>
        {foreach from=$datas key=index item=value}
            <div class="box2 container" id="review_idx_{$value.idx}">
                <div class="row"><div class="col-xs-3 data-title">{'th_username'|WM_Lang}</div><div class="col-xs-9 data-value">&nbsp;{$value.username}</div></div>
                <div class="row"><div class="col-xs-3 data-title">{'th_realname'|WM_Lang}</div><div class="col-xs-9 data-value">&nbsp;{$value.realname}</div></div>
                {*<div class="row"><div class="col-xs-3 data-title">{'lbl_unit_inout'|WM_Lang}</div><div class="col-xs-9 data-value">&nbsp;{if $value.CO_fda_member eq 'Y'}{'lbl_unit_in'|WM_Lang}{else}{'lbl_unit_out'|WM_Lang}{/if}</div></div>*}
                <div class="row"><div class="col-xs-3 data-title">{'department'|WM_Lang}</div><div class="col-xs-9 data-value">&nbsp;{$value.department}</div></div>
                <div class="row"><div class="col-xs-3 data-title">{'title'|WM_Lang}</div><div class="col-xs-9 data-value">&nbsp;{$value.title}</div></div>
                {if $smarty.server.REQUEST_URI eq '/mooc/academic/review/review_review.php'}
                <div class="row"><div class="col-xs-3 data-title">{'th_sel_course'|WM_Lang}</div><div class="col-xs-9 data-value">&nbsp;{$value.course_name}</div></div>
                {/if}
                <div class="row"><div class="col-xs-3 data-title">{'th_create_time'|WM_Lang}</div><div class="col-xs-9 data-value">&nbsp;{$value.create_time}</div></div>
                <div class="row">
                    <div class="col-xs-3 data-title" style="min-height: 36px;">{'th_action'|WM_Lang}</div>
                    <div class="col-xs-9 data-value" style="min-height: 36px;">
                        <button type="button" class="btn btn-blue btnNormal" onclick="doSendReview({$value.idx},'ok');">{'btn_agree'|WM_Lang}</button>
                        <button type="button" class="btn btnNormal" onclick="doSendReview({$value.idx},'deny');">{'btn_deny'|WM_Lang}</button>
                    </div>
                </div>
            </div>
        {/foreach}
    </div>
</div>
<script type="text/javascript">
// 訊息
{literal}
function doSendReview(seq, result){
    $.ajax({
        'type': 'POST',
        'dataType': 'json',
        'data': {'action' : 'reviewStudentMajor', 'did' : seq, 'pass' : result},
        'url': '/mooc/controllers/course_ajax.php',
        'success': function (response) {
            if (response == 'FINISH') {
                if (result == 'ok'){
                    alert('已完成"同意修課"。');
                }else{
                    alert('已完成"不同意修課"。');
                }
                $('#review_idx_'+seq).hide();

                var nodata = true;
                $(".box2").each(function() {
                  if ($(this).is(":visible")) nodata=false;
                });

                if (nodata) {
                    $('#review_no_data').show();
                }
            }
        },
        'error': function () {
            if (window.console) {
                console.log('Ajax Error!');
            }
        }
    });
}
{/literal}
</script>