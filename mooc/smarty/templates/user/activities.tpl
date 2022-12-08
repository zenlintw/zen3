{include file = "common/tiny_header.tpl"}


<style type="text/css">
{literal}
    .table-fixed thead {
        width: 100%;
    }
    .table-fixed tbody {
        height: 600px;
        overflow-y: auto;
        width: 100%;
    }
    .table-fixed thead, .table-fixed tbody, .table-fixed tr, .table-fixed td, .table-fixed th {
        display: block;
    }
    .table-fixed tbody td, .table-fixed thead > tr> th {
        float: left;
        border-bottom-width: 0;
    }
    
    .go {
        background: #9AC83D;
        border-radius: 30px;
        text-align:center;
        font-size: 15px;
        width:113px;
        height:34px;
        line-height:34px;
        color:#FFFFFF;
        margin: 0 auto;
    }
    
    .active {
        background: #FB5442;
        border-radius: 30px;
        text-align:center;
        font-size: 15px;
        width:113px;
        height:34px;
        line-height:34px;
        color:#FFFFFF;
        margin: 0 auto;
    }
    
    .over {
        background: #7DD8BD;
        border-radius: 30px;
        text-align:center;
        font-size: 15px;
        width:113px;
        height:34px;
        line-height:34px;
        color:#FFFFFF;
        margin: 0 auto;
    }
    
    .add_q {
        background: #A1CC37;
        border-radius: 4.5px;
        font-size: 18px;
        color: #FFFFFF;
        text-align:center;
        width:138px;
        height:38px;
        line-height:38px;
        float:right;
        font-weight:bold;
    }
    
    .add_e {
        background: #455868;
        border-radius: 4.5px;
        font-size: 18px;
        color: #FFFFFF;
        text-align:center;
        width:138px;
        height:38px;
        line-height:38px;
        float:right;
        margin-left: 10px;
        font-weight:bold;
    }
    
    .over-box {
        width:500px;
        height:260px;
        display:none;
        font-size:20px;
    } 
    
    .over-box1 {
        width:500px;
        height:308px;
        display:none;
        font-size:20px;
    } 
    
    .icon_warn {
        background-image:url('/public/images/irs/ic-warning.png');
        background-repeat:no-repeat;
        width:95px;height:95px;
        background-position: center center;
        margin:28px auto;
    }
    
    .fancybox-inner {
        background: #FFFFFF;
        border-bottom-left-radius: 15px;
        border-bottom-right-radius: 15px;
    }
    
    .fancybox-skin {
        border-bottom-left-radius: 15px;
        border-bottom-right-radius: 15px;
    }
    
    .true_button {
        color:#FFFFFF;
        background:#E8483F;
        margin-top:20px;
        margin-bottom:28px;
        margin-left:117px;
        border-radius: 4px;
        width:128px;
        height:38px;
        line-height:38px;
        float:left;
        text-align:center;
    }
    
    .add_button {
        color:#FFFFFF;
        background:#E8483F;
        border-radius: 30px;
        width:128px;
        height:38px;
        line-height:38px;
        text-align:center;
    }
    
    .false_button {
        color:#FFFFFF;
        background:#B5B5B5;
        margin-top:20px;
        margin-bottom:28px;
        margin-left:10px;
        border-radius: 4px;
        width:128px;
        height:38px;
        line-height:38px;
        float:left;
        text-align:center;
    }
    
    .start {
        color:#15bc97;
        font-weight:bold;
    }
    
    .nostart {
        color:#E8483F;
        font-weight:bold;
    }
    
    .rate {
        color:#455868;
        font-weight:bold;
    }

    /*手機尺寸*/
    @media (max-width: 767px) {
        .table-fixed tbody {
          height: 400px;
        }
    }

    /*平板直向、平板橫向*/
    @media (min-width: 768px) and (max-width: 992px) {
    }

    /*large Desktop*/
    @media (min-width: 1200px) {
    }
    
    /* unvisited link */
	a:link {
	    color: #455868;
	}
	
	/* visited link */
	a:visited {
	    color: #455868;
	}
	
	/* mouse over link */
	a:hover {
	    color: #455868;
	}
	
	/* selected link */
	a:active {
	    color: #455868;
	}
	
	.scrollbar-primary::-webkit-scrollbar {
	    width: 12px;
	    background: rgba(255,255,255,0.60);
	    border-radius: 15px; 
	}
	
	.scrollbar-primary::-webkit-scrollbar-thumb {
	    border-radius: 10px;
	    -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.1);
	    background-color: #A6A6A6; 
	}

	#back {
	    background: rgba(255,255,255,0.60);
	    border-radius: 15px;
	}
	
	.alert_button {
        color:#FFFFFF;
        background:#E8483F;
        border-radius: 4px;
        width:128px;
        height:38px;
        line-height:38px;
        text-align:center;
        margin: 20px auto;
        text-decoration:none;
    }
    
    a:hover {
	    text-decoration: none;
	}

    
	
{/literal}
</style>
<div id="BlockContainer" class="container">
    <div style="height:40px;line-height:40px;margin-bottom:20px;"">

        <div class="block-title-font" style="float:left;">
           {*&nbsp;<a href="/mooc/user/myteaching.php">{'mycourse'|WM_Lang}</a>&nbsp;>&nbsp;{$courseData.caption|WM_Title}&nbsp;>*}&nbsp;{'manage'|WM_Lang}
        </div>

	    <div style="float:right;">
	        <a href="#" style="text-decoration: none;" onclick="goEdit('','questionnaire');"><div class="add_e" title="{'add_questionnaire'|WM_Lang}"><img src="/theme/default/irs/icon_q_phone.svg" style="width:20px;height:20px;margin-right: 5px;" />{'add_questionnaire'|WM_Lang}</div></a>
	        <a href="#" style="text-decoration: none;" onclick="goEdit('','exam');"><div class="add_q" title="{'add_exam'|WM_Lang}"><img src="/theme/default/irs/ic_itme_q1.svg" style="width:20px;height:20px;margin-right: 5px;" />{'add_exam'|WM_Lang}</div></a>
	    </div>
	</div> 
    <div id="back">
        <div class="table-responsive">
        <table class="table table-fixed">
          <thead>
            <tr>
              <th style="border-radius: 15px 0px 0px 0px;" class="col-xs-5 head1">{'active_name'|WM_Lang}</th>
              <th class="col-xs-2 head2">{'active_time'|WM_Lang}</th>
              <th class="col-xs-1 head3">{'answered'|WM_Lang}</th>
              <th class="col-xs-1 head4">{'unanswered'|WM_Lang}</th>
              <th class="col-xs-1 head5">{'rate'|WM_Lang}</th>
              <th class="col-xs-1 head6">{'status'|WM_Lang}</th>
              <th style="border-radius: 0px 15px 0px 0px;" class="col-xs-1 head7">{'more'|WM_Lang}</th>
            </tr>
          </thead>
          <tbody class="scrollbar-primary">
            {if $irsQuestionnaireList|@count eq 0}
                <td colspan="7" class="col-xs-12" style="text-align: center;">{'no_active'|WM_Lang}</td>
            {else}
            {foreach from=$irsQuestionnaireList key=index item=value}
                <tr>

                    {if $value.exam_type=='exam'} 
                        <td class="col-xs-5" style="text-align: left;"><img src="/theme/default/irs/icon_q1.svg" style="width:32px;height:32px;margin-right: 15px;" title="測驗" /><a href="#" style="text-decoration: none;" onclick="goView('{$value.exam_id}','{$value.exam_type}');"><span id="e_{$value.exam_id}">{$value.title}</span></a><a class="edit" href="#modify-box" onclick="edit('{$value.exam_id}','e');"><i class="fas fa-pencil-alt" style="padding: 0px 5px 0px 5px;" title="{'rename'|WM_Lang}"></i></a></td>
                    {else}
                        <td class="col-xs-5" style="text-align: left;"><img src="/theme/default/irs/icon_quiz.png" style="width:32px;height:32px;margin-right: 15px;" title="問卷" /><a href="#" style="text-decoration: none;" onclick="goView('{$value.exam_id}','{$value.exam_type}');"><span id="q_{$value.exam_id}">{$value.title}</span></a><a class="edit" href="#modify-box" onclick="edit('{$value.exam_id}','q');"><i class="fas fa-pencil-alt" style="padding: 0px 5px 0px 5px;" title="{'rename'|WM_Lang}"></i></a></td>
                    {/if}
                    
                    {if $value.begin_time=='0000-00-00 00:00:00'} 
                        <td class="col-xs-2">{'not_interacted'|WM_Lang}</td>
                        <td class="col-xs-1"><span class="start">-</span></td>
                        <td class="col-xs-1"><span class="nostart">-</span></td>
                        <td class="col-xs-1"><span class="rate">-</span></td>
                    {else}
                        <td class="col-xs-2">{$value.begin_time}</td>
                        <td class="col-xs-1"><span class="start">{$value.start}</span></td>
                        {if $value.forGuest=='1'}
                            <td class="col-xs-1"><span class="nostart">-</span></td>
                            <td class="col-xs-1"><span class="rate">-</span></td>
                        {else}
                            <td class="col-xs-1"><span class="nostart">{$value.nostart}</span></td>
                            <td class="col-xs-1"><span class="rate">{$value.start_rate}%</span></td>
                        {/if}
                    {/if}
                    
                    {if $value.status=='over'}
                        <td class="col-xs-1"><a href="#" style="text-decoration: none;" onclick="goResult('{$value.exam_id}','{$value.exam_type}');"><div class="over"><img src="/theme/default/irs/ic-count.svg" style="width:23px;height:23px;margin-right: 5px;position: relative;top: -1px;" />{'result_interaction'|WM_Lang}</div></a></td>
                    {elseif $value.status=='active'}
                        <td class="col-xs-1"><a href="#" style="text-decoration: none;" onclick="doReviewPublishIRS('{$value.goto}');"><div class="active">{'interacting'|WM_Lang}</div></a></td>
                    {else}
                        <td class="col-xs-1">{if $has == 1}<a class="tip" href="#alert-box">{else}<a href="#" style="text-decoration: none;" onclick="doPublishIRS('{$value.goto}');">{/if}<div class="go">{'go_interaction'|WM_Lang}</div></a></td>
                    {/if}
                    {if $value.publish=='action' && $value.close_time=='9999-12-31 00:00:00'}
                    <td class="col-xs-1">&nbsp;&nbsp;</td>
                    {else}
                    <td class="col-xs-1"><a class="copy" href="#copy-box" onclick="copy_exam('{$value.exam_id}','{$value.exam_type}');"><i class="fas fa-copy" title="{'active_copy'|WM_Lang}"></i></a>&nbsp;&nbsp;<a class="del" href="#del-box" onclick="del('{$value.exam_id}','{$value.exam_type}');"><i class="fas fa-trash-alt" aria-hidden="true" title="{'active_del'|WM_Lang}"></i></a></td>
                    {/if}

                </tr>
            {/foreach}
            {*<td class="col-xs-12" colspan="7" style="color:#0088D2">{'no_data'|WM_Lang}</td>*}
            {/if}
          </tbody>
        </table>
      </div>
    </div>
</div>

<div class="over-box" id="modify-box">
    <form name="formModify" id="formModify" target="empty" style="margin-bottom: 0px;">
    <input type="hidden" name="eid" value="">
    <input type="hidden" name="type" value="">
    <div style="background: #FFFFFF;border-bottom-left-radius: 15px;border-bottom-right-radius: 15px;height: 260px;">
    <div style="background:#E8483F;height:6px;"></div>
    
    <div style="margin-left:100px;margin-bottom:20px;margin-top:30px;"><span style="font-weight:bold;line-height:45px;">{'rename'|WM_Lang}</span><br>{'title_input_tip'|WM_Lang}：</div>
    <div style="text-align:center;"><input name="quiz_name" id="m_name" type="text" class="input-search" placeholder="{'mod_tip'|WM_Lang}" maxlength="20" style="width:300px;height:35px;font-size:20px"></div>
    <div>
        <a href="#" onclick="modify();"><div class="true_button">{'ok'|WM_Lang}</div></a><a href="#" onclick="close_fancy();"><div class="false_button">{'cancel'|WM_Lang}</div></a>
    </div>
    </div>
    </form>
</div>

<div class="over-box1" id="del-box">
    <form name="formDel" id="formDel" target="empty" style="margin-bottom: 0px;">
    <input type="hidden" name="eid" value="">
    <input type="hidden" name="type" value="">
    <div style="background: #FFFFFF;border-bottom-left-radius: 15px;border-bottom-right-radius: 15px;height: 308px;">
    <div style="background:#E8483F;height:6px;"></div>
    <div class="icon_warn"></div>
    <div style="padding: 0px 10px;"><span id="del_tip"></span></div>
    <div>
        <a href="#" onclick="delQuiz();"><div class="true_button">{'ok'|WM_Lang}</div></a><a href="#" onclick="close_fancy();"><div class="false_button">{'cancel'|WM_Lang}</div></a>
    </div>
    </div>
    </form>
</div>

<div class="over-box" id="alert-box">
    <div style="background: #FFFFFF;border-bottom-left-radius: 15px;border-bottom-right-radius: 15px;height: 260px;">
	    <div style="background:#E8483F;height:6px;"></div>
	    <div class="icon_warn"></div>
	    <div style="text-align:center;">{'click_tip'|WM_Lang}</div>
	    <div>
	        <a href="#" onclick="close_fancy();"><div class="alert_button">{'ok'|WM_Lang}</div></a>
	    </div>
    </div>
</div>

<form name="formGoResult" method="post" action="/mooc/user/result.php">
<input type="hidden" name="course_id" value="{$courseData.course_id}" />
<input type="hidden" name="exam_id" value="" />
<input type="hidden" name="exam_type" value="" />
</form>

<form name="formGoView" method="post" action="/mooc/user/exam_view.php">
<input type="hidden" name="course_id" value="{$courseData.course_id}" />
<input type="hidden" name="exam_id" value="" />
<input type="hidden" name="exam_type" value="" />
</form>

<form name="formGoEdit" method="post" action="/mooc/user/exam_edit.php">
<input type="hidden" name="course_id" value="{$courseData.course_id}" />
<input type="hidden" name="exam_id" value="" />
<input type="hidden" name="exam_type" value="" />
</form>

<script type="text/javascript">
    var ticket = '{$ticket}';
    var referer = '{$referer}';
    var cid = '{$cid}';
    var msg_mod_tip = "{'mod_tip'|WM_Lang}";
    var msg_del_tip = "{'delete_tip'|WM_Lang}";
    var winISunFunDon = null;
    {literal}

    $(document).ready(function() {
        $(".edit").fancybox({
            'padding': 0,
            'margin': 0,
            'modal': true
        });
        
        $(".add").fancybox({
            'padding': 0,
            'margin': 0,
            'modal': true
        });
        
        $(".del").fancybox({
            'padding': 0,
            'margin': 0,
            'modal': true
        });
        
        $(".tip").fancybox({
            'padding': 0,
            'margin': 0,
            'modal': true
        });
        
        adjust();
        
        var ret = $('.scrollbar-primary').hasScrollBar();
        
        if(ret) {
           $('.head1').css({'padding-right':'12px'});
           $('.head2').css({'padding-right':'20px'});
           $('.head3').css({'padding-right':'24px'});
           $('.head4').css({'padding-right':'24px'});
           $('.head5').css({'padding-right':'28px'});
           $('.head6').css({'padding-right':'30px'});
           $('.head7').css({'padding-right':'32px'});
        }
        
        // setTimeout("location.reload();",20000);
    });   
    
    $(window).resize(function() {
        adjust();
    });    
    
    (function($) {
	    $.fn.hasScrollBar = function() {
	        return this.get(0).scrollHeight > this.height();
	    }
	})(jQuery);
	
	(function($) {
	    $.fn.setfocus = function()
	    {
	        return this.each(function()
	        {
	            var dom = this;
	            setTimeout(function()
	            {
	                try { dom.focus(); } catch (e) { } 
	            }, 0);
	        });
	    };
	})(jQuery);
    
    function adjust() {
        var back = $(window).height()-90;
        $("#back").css({"height":back});
        var scol =  back-60;
        $(".table-fixed tbody").css({"height":scol});
    }
    
    function doReviewPublishIRS(goto){
	    if (winISunFunDon == null){
	        doPublishIRS(goto);
	    }else if (winISunFunDon.closed){
	        winISunFunDon = null;
	        doPublishIRS(goto);
	    }else{
	        winISunFunDon.focus();
	    }
	}
    
    function doPublishIRS(goto){
	    var dualScreenLeft = window.screenLeft != undefined ? window.screenLeft : screen.left;
	    var dualScreenTop = window.screenTop != undefined ? window.screenTop : screen.top;
	
	    var width = window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width;
	    var height = window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height;
	
	    var w = parseInt(screen.availWidth/3*2);
	    if (w < 1280) w = 1280;
	    var h = parseInt(screen.availHeight/3*2);
	    if (h < 668) h = 668;
	    var left = ((width / 2) - (w / 2)) + dualScreenLeft;
	    var top = ((height / 2) - (h / 2)) + dualScreenTop;
	
	    winISunFunDon = window.open('/mooc/irs/exam_publish.php?goto='+goto, 'iSunFunDo', 'scrollbars=yes,resizable=yes, width=' + w + ', height=' + h + ', top=' + top + ', left=' + left);
	
	}
    
    function edit(exam_id,type) {
        document.formModify.eid.value = exam_id;
        document.formModify.type.value = type;
        caption = $("#"+type+"_"+exam_id).html();
        document.formModify.quiz_name.value = caption;
        $('#m_name').setfocus();
    }
    
    function modify()
	{
	    
	    var eid = document.formModify.eid.value;
        var type = document.formModify.type.value;
        var name = document.formModify.quiz_name.value.trim();
        
        if (name == '') {
	        document.formModify.quiz_name.focus();
	        return;
	    }
	    
	    close_fancy();
	    
	    $.ajax({
            'url': '/mooc/user/act_modify.php',
            'type': 'POST',
            'data': {'action': 'modify','eid':eid,'type':type,'quiz_name':name,'ticket':ticket,'referer':referer,'cid':cid},
            'dataType': "json",
            'success': function (res) {
                if(res.code==1) {
                    $("#"+type+"_"+eid).html(name);
                }
            },
            'error': function () {
                alert('push Ajax Error.');
            }
        });
        		
	}
	
	
	function del(exam_id,type)
	{
	    document.formDel.eid.value = exam_id;
        document.formDel.type.value = type;
        
        var new_type = type.substring(0,1);
        
        caption = $("#"+new_type+"_"+exam_id).html();
	    caption = msg_del_tip.replace("#name#", '<font color="red">'+caption+'</font>');
	    $("#del_tip").html(caption);
	}
	
	function copy_exam(exam_id,type)
	{
	    $.ajax({
            'url': '/mooc/user/act_modify.php',
            'type': 'POST',
            'data': {'action': 'copy','eid':exam_id,'type':type,'ticket':ticket,'referer':referer,'cid':cid},
            'dataType': "json",
            'success': function (res) {
                if(res.code==1) {
                    location.reload();
                }
            },
            'error': function () {
                alert('push Ajax Error.');
            }
        });
	}
	
	function delQuiz()
	{
	    close_fancy();
        var eid = document.formDel.eid.value;
        var type = document.formDel.type.value;
	    $.ajax({
            'url': '/mooc/user/act_modify.php',
            'type': 'POST',
            'data': {'action': 'delete','eid':eid,'type':type,'ticket':ticket,'referer':referer,'cid':cid},
            'dataType': "json",
            'success': function (res) {
                if(res.code==1) {
                    location.reload();
                }
            },
            'error': function () {
                alert('push Ajax Error.');
            }
        });
        		
	}
	
	function close_fancy() {
            $.fancybox.close();
    }
    
    function goResult(id,type) {
        document.formGoResult.exam_id.value = id;
        document.formGoResult.exam_type.value = type;
        document.formGoResult.submit();
    }
    
    function goView(id,type) {
        document.formGoView.exam_id.value = id;
        document.formGoView.exam_type.value = type;
        document.formGoView.submit();
    }
    
    function goEdit(id,type) {
        document.formGoEdit.exam_id.value = id;
        document.formGoEdit.exam_type.value = type;
        document.formGoEdit.submit();
    }
    
    {/literal}
</script>