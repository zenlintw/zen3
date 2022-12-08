<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="{$appRoot}/theme/default/bootstrap/css/bootstrap-responsive.min.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/common.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/layout.css" rel="stylesheet" />
<link href="{$appRoot}/public/css/component.css" rel="stylesheet" />
<link href="{$appRoot}/theme/default/learn_mooc/newcalendar.css" rel="stylesheet" />
<script type="text/javascript" src="/public/js/common.js"></script>
<style type="text/css">
{literal}
    .icon_calendartype {
        width : 10px;
    }
    
    .date {
       background-color: #cccccc;
       color : #D95155;
       font-size: 1.5em;
       height: 40px;
       line-height: 40px;
    }
    
    .box {
       border: 1px solid #C4C4C4;
       border-radius: 4px;
       box-shadow: rgba(0, 0, 0, 0.05) 0 -1px 2px 0;
       margin-bottom : 10px;
       background-color: #ffffff;
    }
    
    .content {
        border-top: 1px solid #C4C4C4;
    }
    
    .time {
        width:19%;
        display: inline-block;
        background-color: #ffffff;
        text-align:center;
        vertical-align: top;
    }
    
    .type {
        display: inline-block;
        width: 10px;
    }

    .main {
        display: inline-block;
        background-color: #ffffff;
        margin : 3px 3px;
        //padding-left:5px;
        width:77%;
        padding-left: 10px;
        word-break:break-all;
    }
    
    .main.person {
        border-left: 10px solid #218B85;
    }
    
    .main.course {
        border-left: 10px solid #D95155;
    }
    
    .main.school {
        border-left: 10px solid #F39C1C;
    }
    
    .fancybox-inner::-webkit-scrollbar-track
{
	-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0);
	background-color: #F9F9F9;
}

.fancybox-inner::-webkit-scrollbar
{
	width: 6px;
	background-color: #F9F9F9;
}

.fancybox-inner::-webkit-scrollbar-thumb
{
	background-color: #7F7F7F;
}
        
{/literal}
</style>
<a name="content2"></a>
<div class="box1" style="width:800px">
    <div class="title">
    {'calender_alert'|WM_Lang}
    <span class="icon_calendartype person"></span><span style="font-size:0.8em;">{'flag_personal'|WM_Lang}</span>
    <span class="icon_calendartype course"></span><span style="font-size:0.8em;">{'flag_course'|WM_Lang}</span>
    <span class="icon_calendartype school"></span><span style="font-size:0.8em;">{'flag_school'|WM_Lang}</span>
    </div>
    {foreach from=$datalist key=k item=arr_data}
    <div class="box">
        <div class="date">{$k}{if $smarty.now|date_format:"%Y-%m-%d"==$k}({'today1'|WM_Lang}){/if}</div>
        {foreach from=$arr_data key=k1 item=val}
            
            
                <div class="content">    
                    <div class="time">{if $val.time!='' && $val.during==''}<i class="icon_bell"></i>&nbsp;{$val.time}{/if}</div>
                    <div class="main {$val.type}">
                    {if $val.course_name!=''} <span style="color:#D95155;font-size: 17.5px;">{$val.course_name}</span><br>{/if}
                   <span style="font-weight:bold;">{'title_subject'|WM_Lang}</span>：{$val.subject}
                   <br><span style="font-weight:bold;">{'title_content'|WM_Lang}</span>：{$val.content}
                   {if $val.during!=''}<br><span style="font-weight:bold;">{'title_during'|WM_Lang}</span>：{$val.during}{/if}
                   </div>
                </div>
            
            
        {/foreach}
    </div>
    {/foreach}
</div>
<script type="text/javascript">
    var sysGotoLabel = '{$label}';
    {literal}
    window.onload = function() {
        if (detectIE() === 13) {
            $('.title-bar, .content .subject td').css('border-radius', '0 0 0 0');
        }
    };
    {/literal}
</script>