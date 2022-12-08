<link href="{$appRoot}/theme/default/learn_mooc/newcalendar.css" rel="stylesheet" />
<style type="text/css">
{literal}

    .date {
       background-color: #cccccc;
       color : #D95155;
       font-size: 1.5em;
       height: 40px;
       line-height: 40px;
       border-radius: 4px;
       margin-top:5px;
    }
    
    .box1 > .content {
        padding:0;
    }
    
    .content {
        border-top: 1px solid #C4C4C4;
        margin-top:5px;
    }
    
    .content.person {
        border-left: 10px solid #218B85;
    }
    
    .content.course {
        border-left: 10px solid #D95155;
    }
    
    .content.school {
        border-left: 10px solid #F39C1C;
    }
    
    .time {
        border-bottom: 1px solid #C4C4C4;
    }
    
    .type {
        border-bottom: 1px solid #C4C4C4;
    }

    .name {
        border-bottom: 1px solid #C4C4C4;
    }
    
    .topic {
        border-bottom: 1px solid #C4C4C4;
    }
    
    .context {
        border-bottom: 1px solid #C4C4C4;
    }
    
    .content  .title {
        display:inline-block;
        width:20%;
        text-align:center;
        vertical-align:top;
    }
    
    .content  .main {
        display:inline-block;
        width:80%;
        word-break:break-all;
    }

    .fancybox-inner::-webkit-scrollbar-track {
	    -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0);
	    background-color: #F9F9F9;
    }

	.fancybox-inner::-webkit-scrollbar {
		width: 6px;
		background-color: #F9F9F9;
	}

    .fancybox-inner::-webkit-scrollbar-thumb {
	    background-color: #7F7F7F;
    }
        
{/literal}
</style>
<a name="content2"></a>
<div class="box1" style="min-width:200px;">
    <div class="title">
    {'calender_alert'|WM_Lang}
    
    </div>
    {foreach from=$datalist key=k item=arr_data}
    
        <div class="date">{$k}{if $smarty.now|date_format:"%Y-%m-%d"==$k}({'today1'|WM_Lang}){/if}</div>
        {foreach from=$arr_data key=k1 item=val}
            
            
                <div class="content {$val.type}">
                    <div class="type"><div class="title">{'titel_type'|WM_Lang}</div><div class="main">{if $val.type=='person'}{'flag_personal'|WM_Lang}{elseif $val.type=='course'}{'flag_course'|WM_Lang}{else}{'flag_school'|WM_Lang}{/if}</div></div>    
                    {if $val.course_name!=''}<div class="name"><div class="title">{'title_name'|WM_Lang}</div><div class="main" style="color:#D95155;vertical-align:top;">{$val.course_name}</div></div>{/if}
                    {if $val.time!='' && $val.during==''}<div class="time"><div class="title">{'title_time'|WM_Lang}</div><div class="main">{$val.time}</div></div>{/if}
                    <div class="topic"><div class="title">{'title_subject'|WM_Lang}</div><div class="main">{$val.subject}</div></div>   
                    <div class="context"><div class="title">{'title_content'|WM_Lang}</div><div class="main">{$val.content}</div></div>   
                    {if $val.during!=''}<div class="during"><div class="title">{'title_during'|WM_Lang}</div><div class="main">{$val.during}</div></div>{/if}
                </div>
            
            
        {/foreach}
    
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