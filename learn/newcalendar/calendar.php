<?php
/**
 * 行事曆
 *
 * 建立日期：2003/03/13
 * @author  ShenTing Lin
 * @version $Id: calendar.php,v 1.1 2010/02/24 02:39:04 saly Exp $
 * @copyright 2003 SUNNET
 **/
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_initialize.php');
require_once(sysDocumentRoot . '/mooc/common/useSmarty.php');
require_once(sysDocumentRoot . '/mooc/common/common.php');
require_once(sysDocumentRoot . '/lib/interface.php');
require_once(sysDocumentRoot . '/lib/jscalendar/calendar.php');
require_once(sysDocumentRoot . '/lib/lib_calendar.php');
require_once(sysDocumentRoot . '/lang/calendar.php');
require_once(sysDocumentRoot . '/lib/acl_api.php');

switch ($calEnv) {
    case 'academic':
        $sysSession->cur_func = '2300300400';
        $ownerid              = $sysSession->school_id;
        $editable             = 'school';
        $interface            = 'school';
        break;
    case 'teach':
        $sysSession->cur_func = '2300200400';
        $ownerid              = $sysSession->course_id;
        $editable             = 'course';
        $interface            = 'course';
        break;
    default:
        $sysSession->cur_func = '2300100400';
        $ownerid              = $sysSession->username;
        $calEnv               = 'learn';
        $editable             = 'person';
        $interface            = 'person';
        break;
}
if (!aclVerifyPermission($sysSession->cur_func, aclPermission2Bitmap('enable,visible,readable,writable,modifiable,uploadable,removable'))) {
}
$isGuest = $sysSession->username == 'guest' ? 1 : 0; // 沒有登入guest不能切換顯示行事曆 永遠顯示學校行事曆
if (!$isGuest) {
    setTicket(); // 登入的時候(非guest)才去設定$sysSession->ticket作為calendar的驗證
    $sysSession->restore();
}
$ticket = md5($sysSession->username . 'newCalendar' . $sysSession->ticket . $sysSession->school_id);
$personalInfoTicket = md5($sysSession->username . $sysSession->school_id . $sysSession->ticket);
$str    = date('Y-n-j', time());
$date   = explode('-', $str);
$date[1]--;

$calLmt                 = (isset($calLmt) ? $calLmt : 'N');
$colCnt                 = ($calLmt == 'N' ? 6 : 4);
$NewCalendarDispalyType = json_encode($MyCalendarSettings);
$js                     = <<< BOF
    var env        = "{$calEnv}";
    var ownerid    = "{$ownerid}";
    var editable    = "{$editable}";
    var interface    = "{$interface}";
    var ticket  = "{$ticket}";        
    var theYear = {$date[0]}, theMonth = {$date[1]}, theDay = {$date[2]};
    var theme   = "/theme/{$sysSession->theme}/{$sysSession->env}/";
    var calLmt  = "{$calLmt}";
    var colCnt  = {$colCnt};
    var isGuest = {$isGuest};
    var NewCalendarLang={'Big5':'zh-tw','GB2312':'zh-cn','en':'en','EUC-JP':'ja','user_define':'$sysSession->lang'};
    var NewCalendarType = {person:"{$MSG['flag_personal'][$sysSession->lang]}",course:"{$MSG['flag_course'][$sysSession->lang]}",school:"{$MSG['flag_school'][$sysSession->lang]}"};
    var NewCalendarDispalyType={$NewCalendarDispalyType};
    var LangWeekDay=[
        '{$MSG['sunday'][$sysSession->lang]}',
        '{$MSG['monday'][$sysSession->lang]}',
        '{$MSG['tuesday'][$sysSession->lang]}',
        '{$MSG['wednesday'][$sysSession->lang]}',
        '{$MSG['thursday'][$sysSession->lang]}',
        '{$MSG['friday'][$sysSession->lang]}',
        '{$MSG['saturday'][$sysSession->lang]}'
    ];
    var isMobile = '{$profile[isPhoneDevice]}';
    MSG_CONFIRM_DELETE = '{$MSG['confirm_delete'][$sysSession->lang]}';
    
    // IPHONE SAFARI 不使用 localStorage
    if (isMobile !== '1') {
        /* 寫入localStorage，以利 我的設定和其他頁面都能正常運作 */
        localStorage.setItem('personal-info', '{$personalInfoTicket}');
    }
        
    var repeatTypes = {
        day: "{$MSG['title_repeat_day1'][$sysSession->lang]}",
        week: "{$MSG['title_repeat_week1'][$sysSession->lang]}",
        month: "{$MSG['title_repeat_month1'][$sysSession->lang]}"
    };
    var slotLabelFormat="{$MSG['slotLabelFormat'][$sysSession->lang]}";
    var timeFormat="{$MSG['timeFormat'][$sysSession->lang]}";
    function getEvent(act,date,rawdata){
        $.fancybox({
            href: "/learn/newcalendar/calendar_ajax.php",
            type: "ajax",
            ajax: {
                type: "POST",
                data: {
                    act: act,
                    env: env,
                    date: date,
                    calLmt: calLmt,
                    rawdata: rawdata
                }
            },
            helpers: {
                overlay : {closeClick: false,locked: false}
            }
        });
    }
    $(function() {
        if(interface!="person"){
            $("#newcalendar_flag").find('li').not('[data-target="'+interface+'"]').hide();
        }

        $('#newcalendar').fullCalendar({
            lang: NewCalendarLang['{$sysSession->lang}'],
            firstDay: 0,
            header: {
                left: 'prev,next today',
                center: 'title',
                right: 'month,basicWeek,agendaDay'
            },
            slotLabelFormat :slotLabelFormat,
            views: {
                day: {
                    timeFormat: timeFormat
                }
            },
            eventLimit: true,
            eventRender: function(event, element) {
                if( $.inArray(event.type, NewCalendarDispalyType)===-1 )  return false;;
                element.find('.fc-content').attr("id", "event-" + event.id);
            },
            eventMouseover: function(calEvent, jsEvent, view) {
                if (view.name !== 'agendaDay') {
                    var rawdata=calEvent.rawdata;
                    var eventDate=rawdata.repeat!="none"&&rawdata.repeat_begin!="0000-00-00"?rawdata.repeat_begin:rawdata.memo_date;
                    var eventRepeatString="";
                    if(rawdata.repeat!="none"){
                        switch(rawdata.repeat){
                            case "day":
                            eventRepeatString="("+repeatTypes[rawdata.repeat]+"/"+rawdata.repeat_end+"{$MSG['stop'][$sysSession->lang]})";
                            break;
                        case "week":
                            eventRepeatString="("+repeatTypes[rawdata.repeat]+LangWeekDay[moment(rawdata.repeat_begin).format("d")]+"/"+rawdata.repeat_end+"{$MSG['stop'][$sysSession->lang]})";
                            break;
                        case "month":
                            eventRepeatString="("+repeatTypes[rawdata.repeat]+moment(rawdata.repeat_begin).format("D")+"{$MSG['repeat_day_end'][$sysSession->lang]}"+"/"+rawdata.repeat_end+" {$MSG['stop'][$sysSession->lang]})";
                            break;
                        
                        }
                    }
                    var eventDateTime=rawdata.time_begin&&rawdata.time_begin!=null?rawdata.time_begin.substr(0,5)+"{$MSG['to'][$sysSession->lang]}":"";
                    if (rawdata.time_end&&rawdata.time_end!=null) {
                        eventDateTime= eventDateTime+rawdata.time_end.substr(0,5);
                    }
                    $(jsEvent.target).attr('title', eventDate+eventRepeatString+" "+eventDateTime);
                }
            },
            eventClick: function(calEvent, jsEvent, view) {
                var rawdata=calEvent.rawdata;
                $("#calendar_delete_modal").find('.confirm').unbind('click');
                getEvent('edit',rawdata.memo_date,rawdata);
                return false;
            },
            viewRender :function( view, element ){
                switch(view.name){
                    case 'month':
                        var startDate=view.start.format('YYYY-MM-DD');
                        var endDate=view.end.format('YYYY-MM-DD');
                        var jsonData={ticket:ticket,action:'month',calEnv:env,start:startDate,end:endDate};
                    break;
                    case 'basicWeek':
                        var startDate=view.start.format('YYYY-MM-DD');
                        var endDate=view.end.format('YYYY-MM-DD');
                        var jsonData={ticket:ticket,action:'week',calEnv:env,start:startDate,end:endDate};
                    break;
                    case 'agendaDay':
                        var dayDate=view.start.format('YYYY-MM-DD');
                        var jsonData={ticket:ticket,action:'day',calEnv:env,day:dayDate};
                    break;
                }
                $("#newcalendar").fullCalendar('removeEvents');
                getNewCalendar(jsonData,'person');
                getNewCalendar(jsonData,'course');
                getNewCalendar(jsonData,'school');
            }
        });
        $("#newcalendar_flag").find("li").on('click',function(){
            if(isGuest) return; // 沒有登入guest不能切換顯示行事曆
            $(this).toggleClass('grey');
            var type=$(this).data('target');
            if($(this).hasClass('grey')){
                var index = $.inArray(type, NewCalendarDispalyType);
                NewCalendarDispalyType.splice(index, 1);
            }else{
                if( $.inArray(type, NewCalendarDispalyType)===-1 ) NewCalendarDispalyType.push(type);
            }
            saveCalendarSetting(ticket,NewCalendarDispalyType);
            $('#newcalendar').fullCalendar('rerenderEvents');
        });

        var prevMouseOverDayTarget=null;
        $("#newcalendar").find(".fc-view-container").on('mousemove','.fc-day,.fc-content-skeleton',function(e){
            if( !$(this).hasClass('fc-day') ){
                $(this).hide();
                 var element = $.elementFromPoint(e.clientX, e.clientY);
                 if( $(element).hasClass('fc-day') ) {
                    if( element!=prevMouseOverDayTarget ) {
                        $(prevMouseOverDayTarget).find('.icon_add').remove();
                        $(prevMouseOverDayTarget).removeClass('hover');
                    }
                    prevMouseOverDayTarget=element;
                    if( !isGuest && calLmt=='N') $(element).append('<i class="icon_add"><img src="'+theme+'icon_add.png" /></i>').addClass('hover').on('touchstart click',function() {
     var date=$(this).closest('.fc-day').data('date');
     getEvent('create',date);
});
                 }
                 $(this).show();
            }else{
                if( this!=prevMouseOverDayTarget ) {
                    $(prevMouseOverDayTarget).find('.icon_add').remove();
                    $(prevMouseOverDayTarget).removeClass('hover');
                }
                prevMouseOverDayTarget=this;
                if( !isGuest && calLmt=='N') $(this).append('<i class="icon_add"><img src="'+theme+'icon_add.png" /></i>').addClass('hover').on('touchstart click',function() {
     var date=$(this).closest('.fc-day').data('date');
     getEvent('create',date);
});
            }
        }).on('mouseleave',function(e){
            if( prevMouseOverDayTarget ) {
                $(prevMouseOverDayTarget).find('.icon_add').remove();
                $(prevMouseOverDayTarget).removeClass('hover');
            }
        }).on('click','.fc-content-skeleton',function(e){
             $(this).hide();
             var element = $.elementFromPoint(e.clientX, e.clientY);
             if( $(element).hasClass('icon_add') ) {
                $(element).trigger('click');
             }
             $(this).show();
        }).on('touchstart','.fc-content',function(e){
            var eventId = $(this).attr('id');
            $('#' + eventId).trigger('click');
        });

        $("#btn-add-calendar").on('click',function(e){
            getEvent('create',moment().format('YYYY-MM-DD'));
        });
        
        if (isMobile === '1') {
            $('.relativebar').css('position', 'initial');
            $('#calendar_time_edit').css('position', 'relative').css('top', '2em');
        }
    });
BOF;

if ($profile['isPhoneDevice']){
    $smarty->display('common/tiny_header.tpl');
    if (($calEnv == 'academic') || ($calEnv == 'learn')){
        $smarty->display('common/site_header.tpl');
    }else{
        $smarty->display('common/course_header.tpl');
    }
}else{
    if ($isGuest) {
        // 沒有登入時顯示為mooc模式
        $smarty->display('common/tiny_header.tpl');
        $smarty->display('common/site_header.tpl');
    } else {
        showXHTML_head_B($MSG['heml_title'][$sysSession->lang]);
    }
    showXHTML_CSS('include', "/theme/{$sysSession->theme}/bootstrap/css/bootstrap.min.css");
}
showXHTML_CSS('include', "/theme/{$sysSession->theme}/{$sysSession->env}/wm.css");
$calendar = new DHTML_Calendar('/lib/jscalendar/', $sysSession->lang, 'calendar-system');
$calendar->load_files();
showXHTML_CSS('include', "/lib/jquery/css/jquery-ui-1.8.22.custom.css");
// showXHTML_CSS('include', "/theme/{$sysSession->theme}/bootstrap/css/bootstrap.min.css");
showXHTML_CSS('include', "/theme/default/fancybox/jquery.fancybox.css");
showXHTML_CSS('include', "/lib/fullcalendar/fullcalendar.css");
showXHTML_CSS('include', "/public/css/common.css");
showXHTML_CSS('include', "/theme/{$sysSession->theme}/learn_mooc/newcalendar.css");
$css                     = <<< BOF
@media (max-width: 767px) {
    #newcalendar_container {
        padding: 0px;
        margin: 35px 180px 0px 10px;
    }

    .pull-right {
        display: none;
    }
    .fc-basic-view .fc-body .fc-row {
        min-height: 5em;
    }

    #calendar_edit {
        min-width: initial;
    }

    .form-horizontal .controls {
        margin-left: 50px;
    }

    .form-horizontal .control-label {
        width: initial;
        float: left;
        padding-top: 5px;
        text-align: right;
    }
    
    #calendar_repeat_edit {
        padding: 0px 15px;
    }

    .fc-month-button {
        display:none;
    }
    
    #calendar_time_edit {
        min-width: 300px;
    }
    
    #calendar_time_edit .relativebar {
        width: 300px;
    }
    
    #calendar_time_edit .pull-right {
        display: block;
        margin-right: 30px;
        margin-top: 5px;
    }
    
    .timeinput input {
        width:25%;
    }

    #calendar_time_edit {
        margin-top: -30px;
        height: 140px;
    }
}

@media (max-width: 375px) {
    input.input-xlarge {
        width: 180px;
    }

    textarea.input-xlarge {
        width: 180px;
    }
}

@media (max-width: 360px) {
    #newcalendar {
        min-width: 340px;
    }
}
BOF;
showXHTML_CSS('inline', $css);
showXHTML_script('include', '/lib/xmlextras.js');
showXHTML_script('include', '/lib/dragLayer.js');
showXHTML_script('include', '/lib/common.js');
showXHTML_script('include', '/lib/jquery/jquery.min.js');
showXHTML_script('include', '/lib/jquery/jquery-ui.custom.min.js');
showXHTML_script('include', '/theme/default/bootstrap/js/bootstrap.min.js');
showXHTML_script('include', "/theme/default/fancybox/jquery.fancybox.pack.js");
showXHTML_script('include', '/lib/fullcalendar/lib/moment.min.js');
showXHTML_script('include', '/learn/newcalendar/calendar.js');
showXHTML_script('include', '/lib/fullcalendar/fullcalendar.js');
showXHTML_script('include', '/lib/fullcalendar/lang-all.js');
showXHTML_script('inline', $js);

if (!$isGuest) {
    showXHTML_head_E('');
    showXHTML_body_B('');
}

$person_grey        = !in_array("person", $MyCalendarSettings) ? 'class="grey"' : '';
$course_grey        = !in_array("course", $MyCalendarSettings) ? 'class="grey"' : '';
$school_grey        = !in_array("school", $MyCalendarSettings) ? 'class="grey"' : '';
$person_course_hide = $isGuest ? 'style="display:none;"' : '';
echo <<<BOF
                <div id='newcalendar_container'>
                    <div id='newcalendar'></div>
                    <div id='newcalendar_leftNav' class="pull-right">
                        <table class="table table-bordered">
                            <tr><td>{$MSG['title_calendar_nav'][$sysSession->lang]}</td></tr>
                            <tr><td>
                                <ul id="newcalendar_flag">
                                    <li data-target="person" {$person_grey} {$person_course_hide}><span class="icon_calendartype person"></span>&nbsp;<span>{$MSG['flag_personal'][$sysSession->lang]}<span></li>
                                    <li data-target="course" {$course_grey} {$person_course_hide}><span class="icon_calendartype course"></span>&nbsp;<span>{$MSG['flag_course'][$sysSession->lang]}<span></li>
                                    <li data-target="school" {$school_grey}><span class="icon_calendartype school"></span>&nbsp;<span>{$MSG['flag_school'][$sysSession->lang]}<span></li>
                                </ul>
                            </td></tr>
                        </table>
BOF;
if (!$isGuest && $calLmt == 'N')
    echo '<button id="btn-add-calendar" class="btn btn-danger" type="button">' . "{$MSG['calendar_add'][$sysSession->lang]}" . '</button>';
echo <<<BOF
                    </div>
                </div>
                <br />
BOF;
if ($isGuest) {
    // 沒有登入時顯示為mooc模式
    $smarty->display('common/site_footer.tpl');
} else {
    showXHTML_body_E('');
}