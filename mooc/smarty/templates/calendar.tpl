<style>
{literal}
@media (max-width: 767px) {
    .footer {
        min-width: 220px;
        margin-top: 0px;
        height: initial;
        background-image: initial;
    }
    
    #header-type {
    position: unset;

}
{/literal}
</style>
<div class="alert alert-error"></div>
<div id="calendar_info" class="{$rawdata.type}">
    <div class="header-div type-color text-center">
        <span id="header-type"><span><span class="icon_calendartype {$rawdata.type}"></span>{$calendarType}</span></span>
        <span id="header-eventdate"></span>
        <span>{$eventDateTime}</span>
        <span id="header-icon">
        {if $rawdata.alert_type!=''}<i class="icon_bell" title="{$eventBeforeString}"></i>{/if}
        </span>
    </div>
    <hr />
    <div class="content">
        <h4 class="type-color">{$rawdata.subject|escape}</h4>
        {$rawdata.content|escape}
    </div>
    <hr />
    {if $rawdata.type==$editable && $calLmt=='N'}
        {if $rawdata.relative_type!='null' && $rawdata.relative_type ne ''}
            <div class="footer text-center">{$show_mseeege}</div>
        {else}
            <div class="footer text-center">
                <button class="btn btn-danger edit" type="button">{'btn_edit'|WM_Lang}</button>
                <button class="btn btn-danger delete" type="button">{'btn_delete'|WM_Lang}</button>
            </div>
        {/if}
    {/if}
</div>

<div id="calendar_edit" style="width:550;">
    <form class="form-horizontal">
        <input type="hidden" name="ticket" value="{$ticket}" />
        <input type="hidden" name="action" value="{$action}" />
        <input type="hidden" name="idx" value="{$rawdata.idx}" />
        <input type="hidden" name="relative_type" value="{$rawdata.relative_type}" />
        <input type="hidden" name="relative_id" value="{$rawdata.relative_id}" />
        {*<div class="control-group">
            <div id="calendar_type_btngroup" class="btn-group">
                <button type="button" class="btn btn-small span2 left type"><span class="icon_calendartype {$rawdata.type}"></span>{$calendarType}</button>
                <button type="button" class="btn btn-small dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>
                <ul class="dropdown-menu">
                    {if $editable=="person"}<li data-target="person"><span class="icon_calendartype person"></span>{'flag_personal'|WM_Lang}</span></li>{/if}
                    {if $editable=="course"}<li data-target="course"><span class="icon_calendartype course"></span>{'flag_course'|WM_Lang}</span></li>{/if}
                    {if $editable=="school"}<li data-target="school"><span class="icon_calendartype school"></span>{'flag_school'|WM_Lang}</span></li>{/if}
                </ul>
            </div>
            <input type="hidden" name="calendar_type" value="{$rawdata.type}" />
            <div class="simple pull-right"><a href="javascript: void(0);" onclick="showAdvanced()">{'btn_advanced'|WM_Lang}</a></div>
        </div>*}
        <input type="hidden" name="calendar_type" value="{$rawdata.type}" />
        <div class="control-group" style="margin-top: 0.8em;">
            <label class="control-label">{'title_time'|WM_Lang}</label>
            <div class="controls">
                <input class="input-small text-center" style="margin-right: 0.3em;" type="text" name="memo_date" id="memo_date" readonly="readonly" value="{$rawdata.memo_date}" />
             </div>
        </div>
        <div class="control-group">
            <label class="control-label">{'title_time'|WM_Lang}</label>
            <div class="controls">
                <div class="inline">
                    <label><input type="radio" name="repeat_choice" value="0" {if $rawdata.repeat==none}checked="checked"{/if} />{'title_repeat_single'|WM_Lang}</label>
                    <label><input type="radio" name="repeat_choice" value="1" {if $rawdata.repeat!=none}checked="checked"{/if} />{'title_repeat_period'|WM_Lang}</label>
                    <span id="repeat_frequency_span"></span>
                    <input type="hidden" name="repeat_frequency" value="{$rawdata.repeat}" />
                    <input type="hidden" name="repeat_end" value="{$rawdata.repeat_end}" />
                </div>
                <div class="inline">
                    <label><input type="radio" name="time_choice" value="0" {if $eventAllDay}checked="checked"{/if} />{'allday'|WM_Lang}</label>
                    <label><input type="radio" name="time_choice" value="1" {if !$eventAllDay}checked="checked"{/if} />{'notallday'|WM_Lang}</label>
                    <span id="time_span"></span>
                    <input type="hidden" name="time_begin" value="{$rawdata.time_begin|substr:0:5}" />
                    <input type="hidden" name="time_end" value="{$rawdata.time_end|substr:0:5}" />
                </div>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{'title_subject'|WM_Lang}</label>
            <div class="controls">
                <input class="input-xlarge" type="text" name="subject" value="{$rawdata.subject}"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{'title_content'|WM_Lang}</label>
            <div class="controls">
                <textarea name="content" class="input-xlarge" rows="5">{$rawdata.content}</textarea>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label">{'title_alert'|WM_Lang}</label>
            <div class="controls inline">
                <label class="checkbox inline">
                    <input type="checkbox" id="alert_check" name="alert_check" value="1" {if $rawdata.alert_type!=''}checked="checked"{/if}/>
                    {'title_enable_alert'|WM_Lang}
                </label>
                &nbsp;
                <select id="alert_before" name="alert_before" class="input-mini" style="display:none">
                {foreach from=$beforeAry key=k item=v}
                    <option value="{$k}" {if $rawdata.alert_before==$k}selected="selected"{/if}>{$v}</option>
                {/foreach}
                </select>
                {if $isMobile eq '1'}<br>&nbsp;&nbsp;{/if}
                &nbsp;
                <label class="checkbox inline" style="display:none" id="alert_login_field">
                    <input type="checkbox" id="alert_login" name="alert_login" value="1" {if strpos($rawdata.alert_type, "login") !== false}checked="checked"{/if}/>
                    {'title_login_alert'|WM_Lang}
                </label>
                {if $isMobile eq '1'}<br>&nbsp;&nbsp;{/if}
                &nbsp;
                <label class="checkbox inline" style="display:none" id="alert_email_field">
                    <input type="checkbox" id="alert_email" name="alert_email" value="1" {if strpos($rawdata.alert_type, "email") !== false}checked="checked"{/if}/>
                    {'title_email_alert'|WM_Lang}
                </label>
                
            </div>
        </div>
        <hr />
        <div class="control-group text-center">
            <button class="btn btn-danger confirm" type="button">{$editBtnText}</button>
        </div>
    </form>
</div>

<div id="calendar_repeat_edit">
    <h5>{'title_repeat_period'|WM_Lang}</h5>
    <form class="form-horizontal">
        <div class="control-group">
            <label class="control-label" style="width: 86px;">{'title_repeat_begin'|WM_Lang}</label>
            <div class="controls" style="margin-left: 94px;">
                <input class="input-small text-center" type="text" name="memo_repeat_date" id="memo_repeat_date" readonly="readonly" />
                <div>
                    <label class="radio">
                        <input type="radio" name="repeat_frequency" value="day" />{'title_repeat_day'|WM_Lang}
                    </label>
                    <label class="radio">
                        <input type="radio" name="repeat_frequency" value="week" />{'title_repeat_week'|WM_Lang}
                    </label>
                    <label class="radio">
                        <input type="radio" name="repeat_frequency" value="month" />{'title_repeat_month'|WM_Lang}
                    </label>
                </div>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label" style="width: 86px;">{'title_repeat_end'|WM_Lang}</label>
            <div class="controls" style="margin-left: 94px;">
                <input class="input-small text-center" type="text" name="memo_repeat_end" id="memo_repeat_end" readonly="readonly" />
            </div>
         </div>
        <hr />
        <div class="control-group text-center">
            <button class="btn btn-danger confirm" type="button">{'btn_confirm'|WM_Lang}</button>
            <button class="btn btn-danger cancel" type="button">{'btn_cancel'|WM_Lang}</button>
        </div>
    </form>
</div>

{if $isMobile eq '0'}
<div id="calendar_time_edit">
{else}    
<div id="calendar_time_edit" style="position: relative; top: 2em;">
{/if}
    {if $isMobile eq '0'}
    <div class="relativebar">
    {else}
    <div class="relativebar" style="position: initial;">
    {/if}
        <h5 class="pull-left">{'title_time_interval'|WM_Lang}</h5>
        <div class="timeinput pull-left">
            {if $isMobile eq '0'}
                <input class="input-mini text-center" type="text" name="time_begin" readonly="readonly" />
                &nbsp;{'to'|WM_Lang}&nbsp;
                <input class="input-mini text-center" type="text" name="time_end" readonly="readonly" />
            {else}
                <input class="input-mini text-center" type="text" name="time_begin" />
                &nbsp;{'to'|WM_Lang}&nbsp;
                <input class="input-mini text-center" type="text" name="time_end" />
            {/if}
        </div>
        <div class="pull-right">
            <button class="btn btn-danger confirm" type="button">{'btn_confirm'|WM_Lang}</button>
            <button class="btn btn-danger cancel" type="button">{'btn_cancel'|WM_Lang}</button>
        </div>
    </div>
    {if $isMobile eq '0'}
    <table class="table table-bordered" id="timerowTable">
        {section name=hour start=0 loop=24}
            <tr><td rowspan="2" class="span1">{'%02d'|sprintf:$smarty.section.hour.index}{if $smarty.section.hour.index<=12}am{else}pm{/if}</td><td data-start-target="{'%02d'|sprintf:$smarty.section.hour.index}:00" data-end-target="{'%02d'|sprintf:$smarty.section.hour.index}:30"></td></tr>
            <tr><td data-start-target="{'%02d'|sprintf:$smarty.section.hour.index}:30" data-end-target="{'%02d'|sprintf:$smarty.section.hour.index+1}:00"></td></tr>
        {/section}
    </table>
    {/if}
</div>

<div id="calendar_delete_modal" class="modal hide fade">
    <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3>{'btn_delete'|WM_Lang}</h3>
    </div>
    <div class="modal-body">
        <p>{'confirm_delete'|WM_Lang}</p>
    </div>
    <div class="modal-footer">
        <button class="btn btn-danger confirm">{'btn_delete'|WM_Lang}</button>
        <button class="btn" data-dismiss="modal" aria-hidden="true">{'btn_cancel'|WM_Lang}</button>
    </div>
</div>
<script>
    var action={$action|@json_encode};
    var ticket={$ticket|@json_encode};
    var idx="{$rawdata.idx}";
    var school_id="{$rawdata.school_id}";
    var repeatTypes={$repeatTypes|@json_encode};
    var beforeAry={$beforeAry|@json_encode};
    var Lang_stop='{'stop'|WM_Lang}';
    var Lang_btn_edit='{'btn_edit'|WM_Lang}';
    var Lang_to='{'to'|WM_Lang}';
    var Lang_msg_repeat_error='{'msg_repeat_error'|WM_Lang}';
    var Lang_msg_repeat_error1='{'msg_repeat_error1'|WM_Lang}';
    var Lang_msg_repeat_error2='{'msg_repeat_error2'|WM_Lang}';
    var Lang_msg_time_error='{'msg_time_error'|WM_Lang}';
    var Lang_msg_subject_fill='{'msg_subject_fill'|WM_Lang}';
    var Lang_msg_alert_fill='{'msg_alert_fill'|WM_Lang}';
    var Lang_msg_add_fail='{'msg_add_fail'|WM_Lang}';
    var Lang_msg_add_success='{'msg_add_success'|WM_Lang}';
    var Lang_msg_update_fail='{'msg_msg_update_fail'|WM_Lang}';
    var Lang_msg_update_success='{'msg_update_success'|WM_Lang}';
    var Lang_msg_del_success='{'msg_del_success'|WM_Lang}';
    var Lang_msg_del_fail='{'msg_del_fail'|WM_Lang}';
    var Lang_title_repeat_day='{'title_repeat_day'|WM_Lang}';
    var Lang_title_repeat_week='{'title_repeat_week'|WM_Lang}';
    var Lang_title_repeat_month='{'title_repeat_month'|WM_Lang}';
    var Lang_day='{'day'|WM_Lang}';
    var Lang_repeat_day_end='{'repeat_day_end'|WM_Lang}';
    var jCalendarEdit=$("#calendar_edit");
    var jCalendarInfo=$("#calendar_info");
    var jRepeatEdit=$("#calendar_repeat_edit");
    var jTimeEdit=$("#calendar_time_edit");
    var jAlertError=$('.alert-error');
    var LangWeekDay=[
        '{'sunday'|WM_Lang}',
        '{'monday'|WM_Lang}',
        '{'tuesday'|WM_Lang}',
        '{'wednesday'|WM_Lang}',
        '{'thursday'|WM_Lang}',
        '{'friday'|WM_Lang}',
        '{'saturday'|WM_Lang}'
    ];
    var isMobile = '{$isMobile}';
    {literal}
    // Calendar_setup("memo_date" , "%Y-%m-%d", "btn_memo_date" , false);
    //Calendar_setup("memo_repeat_date" , "%Y-%m-%d", "btn_memo_repeat_date" , false);
    //Calendar_setup("memo_repeat_end" , "%Y-%m-%d", "btn_memo_repeat_end" , false);
    $('#memo_date').datepicker({
        changeMonth: true,
        changeYear: true,
        numberOfMonths:1,
        dateFormat: 'yy-mm-dd',
        showOn: 'button',
        buttonText: "&nbsp;"
    }).next(".ui-datepicker-trigger").addClass("icon_calendar");
    $('#memo_repeat_date').datepicker({
        changeMonth: true,
        changeYear: true,
        numberOfMonths:1,
        dateFormat: 'yy-mm-dd',
        showOn: 'button',
        buttonText: "&nbsp;"
    }).next(".ui-datepicker-trigger").addClass("icon_calendar");
    $('#memo_repeat_end').datepicker({
        changeMonth: true,
        changeYear: true,
        numberOfMonths:1,
        dateFormat: 'yy-mm-dd',
        showOn: 'button',
        buttonText: "&nbsp;"
    }).next(".ui-datepicker-trigger").addClass("icon_calendar");
    
    if(action=="create"){
        jCalendarInfo.hide();
        jCalendarEdit.show();
        $("#calendar_edit").addClass('simple');
        initialEditLayout();
        $.fancybox.update();
    }else{
        var repeatChoice=jCalendarEdit.find('[name="repeat_choice"]:checked').val();
        var repeatFrequency=jCalendarEdit.find('[name="repeat_frequency"]').val();
        var repeatBegin=jCalendarEdit.find('[name="memo_date"]').val();
        var repeatEnd=jCalendarEdit.find('[name="repeat_end"]').val();
        var eventDateStr=repeatBegin;
        var repeatFrequencyHtml="";
        if(repeatChoice==1){
            if(repeatFrequency!="none") {
                repeatFrequencyHtml=getRepeatFrequency(repeatFrequency,repeatBegin,repeatEnd);
            }
            eventDateStr+=repeatFrequencyHtml;
        }
        $("#header-eventdate").html(eventDateStr);
    }
    $("#alert_check").on('click',function(){
        if($(this).prop("checked")){
            $("#alert_before").show();
            $("#alert_login_field").show();
            $("#alert_email_field").show();

        }else{
            $("#alert_before").hide();
            $("#alert_login_field").hide();
            $("#alert_email_field").hide();
        }
    });
    $("#calendar_type_btngroup").find('li').on('click',function(){
        $("#calendar_type_btngroup").find('.type').html($(this).html());
        var type=$(this).data('target');
        jCalendarEdit.find('[name="calendar_type"]').val(type)

    });
    jCalendarEdit.find('[name="repeat_choice"]').on('change',function(){
        if($(this).val()==1) {
            var repeatEnd=jCalendarEdit.find('[name="repeat_end"]').val();
            if( !repeatEnd || repeatEnd=="0000-00-00" ){
                repeatEdit();
            }else{
                editRepeatChoiceDisplay();
            }
        }else{
            $("#repeat_frequency_span").hide();
        }
    });
    jCalendarEdit.find('[name="time_choice"]').on('change',function(){
        if($(this).val()==1) {
            var timeBegin=jCalendarEdit.find('[name="time_begin"]').val();
            if( !timeBegin || timeBegin=="null" ){
                timeEdit();
            }else{
                editTimeDisplay();
            }
        }else{
            $("#time_span").hide();
        }
    });
    jCalendarInfo.find('.edit').on('click',function(){
        jCalendarInfo.hide();
        jCalendarEdit.show();
        initialEditLayout();
        $.fancybox.update();
    });
    jCalendarInfo.find('.delete').on('click',function(){
        if (isMobile === '1') {
            if (confirm(MSG_CONFIRM_DELETE)) {
                $("#calendar_delete_modal").find('.confirm').trigger('click');
            }
        } else {
            $("#calendar_delete_modal").modal('show');
            $("#calendar_delete_modal").appendTo("body");
        }
    });
    jRepeatEdit.find('.confirm').on('click',function(){
        var repeatFrequency=jRepeatEdit.find('[name="repeat_frequency"]:checked').val();
        var repeatDate=$("#memo_repeat_date").val();
        var repeatEnd=$("#memo_repeat_end").val();
        if( !repeatFrequency ){
            jAlertError.html(Lang_msg_repeat_error2).show();
            return;
        }
        if( repeatEnd=="" || repeatEnd=="0000-00-00" ){
            jAlertError.html(Lang_msg_repeat_error1).show();
            return;
        }
        if( moment(repeatEnd).format("X")-moment(repeatDate).format("X")<=0 ){
            jAlertError.html(Lang_msg_repeat_error).show();
            return;
        }
        jAlertError.hide();
        $("#memo_date").val($("#memo_repeat_date").val());
        jCalendarEdit.find('[name="repeat_frequency"]').val(jRepeatEdit.find('[name="repeat_frequency"]:checked').val());
        jCalendarEdit.find('[name="repeat_end"]').val($("#memo_repeat_end").val());
        jRepeatEdit.hide();
        $(".fancybox-close").show();
        jCalendarEdit.show();
        editRepeatChoiceDisplay();
        $.fancybox.update();
    });
    jRepeatEdit.find('.cancel').on('click',function(){
        jAlertError.hide();
        jRepeatEdit.hide();
        $(".fancybox-close").show();
        jCalendarEdit.show();
        var repeatFrequency=jCalendarEdit.find('[name="repeat_frequency"]').val();
        if( repeatFrequency=="none" ){
            jCalendarEdit.find('[name="repeat_choice"][value="0"]').prop("checked",true);
        }
        editRepeatChoiceDisplay();
        $.fancybox.update();
    });
    jTimeEdit.find('.confirm').on('click',function(){
        var pattern = /^[\d]{2}:[0|3]0$/;
        var val_flag = pattern.test(jTimeEdit.find('[name="time_begin"]').val());        
        if (val_flag === false) {
            alert(Lang_msg_time_error);
            return false;
        }
        
        var val_flag = pattern.test(jTimeEdit.find('[name="time_end"]').val());        
        if (val_flag === false) {
            alert(Lang_msg_time_error);
            return false;
        }
        
        jCalendarEdit.find('[name="time_begin"]').val(jTimeEdit.find('[name="time_begin"]').val());
        jCalendarEdit.find('[name="time_end"]').val(jTimeEdit.find('[name="time_end"]').val());
        jTimeEdit.hide();
        $(".fancybox-close").show();
        jCalendarEdit.show();
        editTimeDisplay();
        $.fancybox.update();
    });
    jTimeEdit.find('.cancel').on('click',function(){
        jAlertError.hide();
        jTimeEdit.hide();
        $(".fancybox-close").show();
        jCalendarEdit.show();
        var timeBegin=jCalendarEdit.find('[name="time_begin"]').val();
        if( !timeBegin || timeBegin=="null" ){
            jCalendarEdit.find('[name="time_choice"][value="0"]').prop("checked",true);
        }
        editTimeDisplay();
        $.fancybox.update();
    });
    jCalendarEdit.find('.confirm').on('click',function(){
        jCalendarEdit.find('.confirm').attr('disabled', true);
        var subject=jCalendarEdit.find('[name="subject"]').val();
        if( subject=="" ){
            jAlertError.html(Lang_msg_subject_fill).show();
            jCalendarEdit.find('.confirm').attr('disabled', false);
            return;
        }

        if ($("#alert_check").attr('checked')) {
            if (!$("#alert_login").attr('checked') && !$("#alert_email").attr('checked')) {
                jAlertError.html(Lang_msg_alert_fill).show();
                jCalendarEdit.find('.confirm').attr('disabled', false);
                return;
            }
        }
        jAlertError.hide();
        $.ajax({
            type: "POST",
            url: "/learn/newcalendar/calendar_save.php",
            data: jCalendarEdit.find('form').serialize(),
            dataType: "json",
            success: function(result) {
                if(result.error){
                    alert(window["Lang_"+result.error]);
                }else{
                    $.fancybox.close();
                    var view = $('#newcalendar').fullCalendar('getView');
                    view.triggerRender();
                }
            },
            error: function(){
                alert('ajax error handing here');
            }
        });
    });
    $("#calendar_delete_modal").find('.confirm').on('click',function(){
        $.ajax({
            type: "POST",
            url: "/learn/newcalendar/calendar_save.php",
            data: {action:"delete",ticket:ticket,school_id:school_id,idx:idx},
            dataType: "json",
            success: function(result) {
                if(result.error){
                    alert(window["Lang_"+result.error]);
                }else{
                    $("#calendar_delete_modal").modal('hide');
                    $.fancybox.close();
                    var view = $('#newcalendar').fullCalendar('getView');
                    view.triggerRender();
                    location.reload();
                }
            },
            error: function(){
                alert('ajax error handing here');
            }
        });
    });
    $("#timerowTable").selectable({
        filter:'tbody td:not(.span1)',
        start : function( event, ui ) {
            $(this).tooltip({
                trigger:'manual',
                placement: function (tooltip, trigger) {
                    window.setTimeout(function () {
                        $(tooltip).addClass('bottom');
                        var top=$("#timerowTable").find("td.ui-selecting:last").offset().top
                                -$("#timerowTable").offset().top
                                +$("#timerowTable").find("td.ui-selecting:last").outerHeight()
                                +tooltip.offsetHeight;
                        $(tooltip).css({top: top, left: "250px"}).addClass('in');
                    }, 0);
                }
            }).tooltip('show');
        },
        selecting: function( event, ui ) {
            var firstTime=$("#timerowTable").find("td.ui-selecting:first").data('start-target');
            var lastTime=$("#timerowTable").find("td.ui-selecting:last").data('end-target');
            $(this).data('tooltip').options.title=firstTime+Lang_to+lastTime;
            $(this).tooltip('show');
        },
        unselecting : function( event, ui ) {
            var firstTime=$("#timerowTable").find("td.ui-selecting:first").data('start-target');
            var lastTime=$("#timerowTable").find("td.ui-selecting:last").data('end-target');
            $(this).data('tooltip').options.title=firstTime+Lang_to+lastTime;
            $(this).tooltip('show');
        },
        stop: function(event, ui){
            var firstTime=$("#timerowTable").find("td.ui-selected:first").data('start-target');
            var lastTime=$("#timerowTable").find("td.ui-selected:last").data('end-target');
            jTimeEdit.find('[name="time_begin"]').val(firstTime);
            jTimeEdit.find('[name="time_end"]').val(lastTime);
            $(this).tooltip('hide');
        }
    }).on("mousedown",'tbody td:not(.span1)', function (e) {
        // prevent ctrl for multiselect
        e.metaKey = false;
        e.ctrlKey = false;
    });
    function showAdvanced(){
        $("#calendar_edit").removeClass('simple');
        initialEditLayout();
        $.fancybox.update();
    }
    function editRepeatChoiceDisplay(){
        var repeatChoice=jCalendarEdit.find('[name="repeat_choice"]:checked').val();
        var repeatFrequency=jCalendarEdit.find('[name="repeat_frequency"]').val();
        var repeatBegin=jCalendarEdit.find('[name="memo_date"]').val();
        var repeatEnd=jCalendarEdit.find('[name="repeat_end"]').val();
        var repeatFrequencyHtml="";
        if(repeatChoice==1){
            if(repeatFrequency!="none") {
                repeatFrequencyHtml=getRepeatFrequency(repeatFrequency,repeatBegin,repeatEnd);
            }
            repeatFrequencyHtml+="&nbsp;<a href='javascript: void(0);' onclick='repeatEdit()'>"+Lang_btn_edit+"</a>";
        }
        $("#repeat_frequency_span").html(repeatFrequencyHtml).show();
    }
    function getRepeatFrequency(repeatFrequency,repeatBegin,repeatEnd){
        var repeatFrequencyHtml="";
            switch (repeatFrequency){
                case "day":
                    repeatFrequencyHtml="("+repeatTypes[repeatFrequency]+"/"+repeatEnd+"&nbsp;"+Lang_stop+")";
                    break;
                case "week":
                    repeatFrequencyHtml+="("+repeatTypes[repeatFrequency]+LangWeekDay[moment(repeatBegin).format("d")]+"/"+repeatEnd+"&nbsp;"+Lang_stop+")";
                    break;
                case "month":
                    repeatFrequencyHtml+="("+repeatTypes[repeatFrequency]+moment(repeatBegin).format("D")+Lang_repeat_day_end+"/"+repeatEnd+"&nbsp;"+Lang_stop+")";
                    break;
                default :
            }
            return repeatFrequencyHtml;
    }
    function editTimeDisplay(){
        var timeChoice=jCalendarEdit.find('[name="time_choice"]:checked').val();
        var timeBegin=jCalendarEdit.find('[name="time_begin"]').val();
        var timeEnd=jCalendarEdit.find('[name="time_end"]').val();
        var timeHtml="";
        if(timeChoice==0){
            timeHtml="";
        }else if(timeChoice==1){
            if(timeBegin && timeBegin!="null") timeHtml+="(&nbsp;"+timeBegin+"&nbsp;"+Lang_to+"&nbsp;"+timeEnd+"&nbsp;"+")&nbsp;"
            timeHtml+="<a href='javascript: void(0);' onclick='timeEdit()'>"+Lang_btn_edit+"</a>";
        }
        $("#time_span").html(timeHtml).show();
    }
    function initialEditLayout(){
        editRepeatChoiceDisplay();
        editTimeDisplay();
        $("#alert_check").triggerHandler('click');
    }
    function repeatEdit(){
        jAlertError.hide();
        jCalendarEdit.hide();
        $("#memo_repeat_date").val($("#memo_date").val());
        if(jCalendarEdit.find('[name="repeat_frequency"]').val()!="none"){
            jRepeatEdit.find('[name="repeat_frequency"][value="'+jCalendarEdit.find('[name="repeat_frequency"]').val()+'"]').prop("checked",true);
        }
        $("#memo_repeat_end").val(jCalendarEdit.find('[name="repeat_end"]').val());
        jRepeatEdit.show();
        $(".fancybox-close").hide();
        $.fancybox.update();
    }

    function timeEdit(){
        
        jAlertError.hide();
        jCalendarEdit.hide();
        $(".fancybox-close").hide();
        var timeBegin=jCalendarEdit.find('[name="time_begin"]').val();
        var timeEnd=jCalendarEdit.find('[name="time_end"]').val();
        if( !timeBegin || timeBegin=="null" ){
            jTimeEdit.find('[name="time_begin"]').val(moment().format("HH:00"));
        }else{
            jTimeEdit.find('[name="time_begin"]').val(timeBegin);
        }
        if( !timeEnd || timeEnd=="null" ){
            // check if add one hour over a day
            var nowDuration=moment.duration(moment().format("HH:00"));
            var onehourDuration=moment.duration(moment().add(1, 'h').format("HH:00"));

            if(onehourDuration.subtract(nowDuration).asSeconds()>0){
                jTimeEdit.find('[name="time_end"]').val(moment().add(1, 'h').format("HH:00"));
            }else{
                jTimeEdit.find('[name="time_end"]').val(moment().format("HH:30"));
            }
        }else{
            jTimeEdit.find('[name="time_end"]').val(timeEnd);
        }
        var selectableRow=$("#timerowTable").find('td:not(.span1)');
        selectableRow.removeClass('ui-selected');
        var timeBeginRow=selectableRow.filter('[data-start-target="'+jTimeEdit.find('[name="time_begin"]').val()+'"]');
        var timeEndRow=selectableRow.filter('[data-end-target="'+jTimeEdit.find('[name="time_end"]').val()+'"]');
        if( timeBeginRow.length>0 && timeEndRow.length>0 ){
            var startIndex=selectableRow.index(timeBeginRow[0]);
            var endIndex=selectableRow.index(timeEndRow[0]);
            selectableRow.slice(startIndex,endIndex+1).addClass('ui-selected');
        }
          
        jTimeEdit.show();
        $.fancybox.update();
        if(timeBeginRow.length>0){
            var startScrollTop=timeBeginRow.position().top;
            setTimeout(function() {
                $(".fancybox-inner").scrollTop(startScrollTop-40);
            },300);
        }
    }
    {/literal}
</script>
