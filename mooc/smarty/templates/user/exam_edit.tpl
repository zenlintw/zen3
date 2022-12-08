{include file = "common/tiny_header.tpl"}

<script type="text/javascript" src="/theme/default/bootstrap/js/bootstrap-switch.js"></script>
<link href="/theme/default/bootstrap/css/bootstrap-switch.css" rel="stylesheet" />
<style type="text/css">
{literal}

    .input-name {
        height:40px;
        width:300px;
        border: 1px solid #e3e3e3;
        /*box-shadow: inset 0 1px 2px 0 #989898;*/
        border-radius: 6px;

    }
    
    .over-box {
        width:500px;
        height:260px;
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

    .exam input[type="text"] {
        font-size: 1.67rem;
        padding: 0 8px 1px 8px;
        color: #666666;
    }
    
    /*手機尺寸*/
    @media (max-width: 767px) {
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
        text-decoration: none;
    }
    
    /* selected link */
    a:active {
        color: #455868;
    }
    
    .title {
        background-color: #455868;
        text-align:center;
        color:#FFFFFF;
        border-radius: 10px 10px 0px 0px;
        height:48px;
        line-height:48px;
        font-size: 22px;
        font-weight: bold;

    }
    
    .exam {
        text-align:left;
        background-color: #E9E9E9;
        height: 60px;
        line-height: 59px;
        font-size:22px;
        font-weight: bold;
        color:#333333;
        padding-left: 55px;
    }
    
    .exam_info {
        text-align: right;
        background-color: #E9E9E9;
        height: 60px;
        line-height: 60px;
        font-size: 1.67rem;
        font-weight: bold;
        padding-right: 55px;
    }
    
    .noname {
        height: 60px;
        line-height: 60px;
        font-size: 1.67rem;
        font-weight: bold;
        color: #777;
        margin-left: 20px;
    }
    .people_button {
        text-align:center;
        background-color: #7ED8BE;
        height: 60px;
        line-height: 60px;
        font-size:22px;
        font-weight: bold;
        color:#FFFFFF;
    }
    
    .people {
        line-height:150px;
        font-size:26px;
        text-align:center;
        width:150px;
        height:150px;
        margin:20px 20px;
        background-color:#767171;
        overflow : hidden;
        text-overflow : ellipsis;
        white-space : nowrap;
        color:#FFFFFF;
        font-weight: bold;
    }

    #people { 
        line-height:40px;
        padding: 20px;
        height: 600px;
        overflow-y: auto;
        overflow-x: hidden;
        width: 100%;
    }
    
    #stastic {
        line-height:30px;
        
    }
    
    .select_font {
        font-size:20px;
        font-weight:bold;
    }

    .people_small {
        line-height:80px;
        font-size:50px;
        text-align:center;
        width:80px;
        height:80px;
        background-color:#767171;
    }
    
    .progress {
        margin: 10px auto;
        overflow: hidden;
        background-color: #f5f5f5;
        border-radius: 4px;
        -webkit-box-shadow: inset 0 1px 2px rgba(0,0,0,.1);
        box-shadow: inset 0 1px 2px rgba(0,0,0,.1);
        height:20px;
        background:#afabaa;
    }
    
    .question {
        line-height:30px;
        font-size:24px;
        font-weight:Bold;
        padding:5px;
        margin-bottom: 15px;
    }
    
    .tool {
        padding:10px;
    }
    
    .row {
        margin-left:0;
        margin-right:0;
    }
    
    .scrollbar-primary::-webkit-scrollbar {
        width: 8px;
        background: rgba(255,255,255,0.60);
        border-radius: 15px; 
    }
    
    .scrollbar-primary::-webkit-scrollbar-thumb {
        border-radius: 10px;
        -webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.1);
        
        background: #afabaa;
         
    }

    #back {
        background: rgba(255,255,255,0.80);
        border-radius: 15px;
    }
    
    #main {
        /*margin:10px 20px;*/
    }
    
    #content {
        color:#333333;
        /*height: 600px;
        overflow-y: auto;
        overflow-x: hidden;
        width: 100%;*/
    }
    
    .items {
        overflow-y: auto;
        overflow-x: hidden;
        width: 100%;
        padding:30px 0;    
    }
    
    .title_num {
        color:#ce2016;
        font-weight:bold;
    }
    
    .back {
        padding: 15px 15px 0 15px;
    }
    
    .correct {
        text-align: right;
        margin: auto 0;
        height:100%;
    }
    
    #result {
        height:80px;
        line-height:80px;
        font-weight:bold;
        font-size:20px;
        color:#333333;
        /*border-bottom: 2px solid #cdcdcd;*/
    }
    
    #result>div {
        text-align: center;
    }
    
    .num_font {
        font-size:48px;
    }
    
    .title_num {
        text-align:right;
        padding: 15px;
    }
    
    .edit_button {
        font-size: 1.67rem;
        padding: 0 30px;
        background: #ce2016;
        height: 32px;
        margin: 8px auto;
        border-radius: 32px;
        position: absolute;
        right: 35px;
        line-height: 32px;
    }
    
    .pen {
        color: #000000;
        position: absolute;
        top: 10px;
        left: 25px;
    }
    
    body {
        overflow:hidden;
        height:100%;
    }
    
    #p_submit,#p_nosubmit {
        font-size: 48px;
    }
    
    .type {
        background: #E8483F;
        width: 6rem;
        height: 35px;
        font-size: 22px;
        color: #FFFFFF;
        line-height: 35px;
        text-align: center;
        border-radius: 5px;
    }
    
    .attach {
        height:15rem;
        background-repeat:no-repeat;
        background-position: center center;
        background-size: contain;
    }
    
    .icon_add {
        font-size: 24px;
        position: relative;
        top: 4px;
        color: #3b5869;
        padding-left: 10px;
        border-left: 1px solid #bcbcbc;
    }

    .icon_view {
        font-size: 24px;
        position: relative;
        top: 4px;
        color: #3b5869;
    }
    
    .tool {
        background: #455868;
        font-size: 1.67rem;
        color: #FFFFFF;
        text-align: center;
        display: inline;
        border-radius: 5px;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        -ms-border-radius: 5px;
        -o-border-radius: 5px;
        text-decoration: none !important;
    }

    a:hover .tool {
        background: #E8483F;
        text-decoration: none;
    }
    
    .select_type {
        height: 35px;
        width: 100px;
        border-radius: 20px;
        line-height: 35px;
        font-size: 18px;
        color: #3b5869;
        font-weight: bold;
        background-color: #f8f8f8;
        border: 1px solid #e9e9e9;
    }
    .bar {
        font-size: 1.67rem;
        color: #666666;
        height: 35px;
        font-weight: 400;
        display: inline-block;
        border-left: 1px solid #e9e9e9;
        padding-left: 10px;
        margin-left: 10px;
    }
    
    .item_tool {
        height: 35px;
        line-height: 35px;
        font-size: 22px;
        font-weight: bold;
        color: #333;
        /* padding: 0px 12px; */
    }

    .score {
        color: #E8483F;
        font-size: 18px;
        font-weight: bold;
        height: 35px;
        width: 70px;
        border: 1px solid #e9e9e9;
        border-radius: 20px;
        text-align: center;
    }
    
    .radio_icon {
        width:3rem;
        height:3rem;
        background-repeat:no-repeat;
        /*background-size:2rem;*/
        background-image:url('/public/images/irs/radio_quiz_phone.svg');
        margin: 9px auto;
    }
    
    .radio_icon_select {
        width:3rem;
        height:3rem;
        background-repeat:no-repeat;
        /*background-size:2rem;*/
        background-image:url('/public/images/irs/radio_quiz_select_phone2.svg');
        margin: 9px auto;
    }
    
    .tip, .list, .view {
        display:none;
    }
    
    .itemlist .icon-rm {
        font-size: 28px;
        position: absolute;
        right: 0;
        top: -13px;
        cursor: pointer;
    }

    .noitem {
        display: none;
    }

    .exam_info .item_num, .exam_info .total_score {
        color: #27596a;
        font-size: 24px;
        margin-right: 15px;
    }

    .itemlist {
        margin: 0 40px 15px 40px;
        background: #fdfdfd;
        border-radius: 5px;
        border: 1px solid #dddddd;
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        -ms-border-radius: 5px;
        -o-border-radius: 5px;
    }

    .toolbar a i {
        background-color: transparent;
        border-radius: 3px;
        color: #B4B4B4;
        font-size: 16px;
        height: 28px;
        line-height: 28px;
        text-align: center;
        width: 28px;
        -webkit-border-radius: 3px;
    }

    .toolbar a:hover i,
    .toolbar a:focus i{
        background-color: #eeeeee;
        color: #B4B4B4;
        text-decoration: none;
    }

    .exam_info a:hover i {
        color: #ce2016;
    }

    :focus {
        outline:none;
    }

    .question .form-control {
        font-size: 24px;
        color: #3b5869;
        background-color: #f2f2f2;
        border: 0;
    }

    .item_m .form-control {
        background-color: #f2f2f2;
        border: 0;
    }

    .tool_box {
        height: 56px;
        border-bottom: 1px solid #ddd;
        padding:10px 18px;
    }

    .item_m a i {
        font-size: 16px;
        color: #7D8A95;
        width: 28px;
        height: 28px;
        text-align: center;
        line-height: 28px;
    }

    .item_m a:hover i,
    .item_m a:focus i {
        background-color: #eeeeee;
        text-decoration: none;
        border-radius: 2px;
        -webkit-border-radius: 2px;
        -moz-border-radius: 2px;
        -ms-border-radius: 2px;
        -o-border-radius: 2px;
    }

    .switch {
        position: relative;
        top: -2px;
        margin-left: 10px;
        display:inline-block;
    }

    .bootstrap-switch .bootstrap-switch-handle-on.bootstrap-switch-primary, .bootstrap-switch .bootstrap-switch-handle-off.bootstrap-switch-primary, .bootstrap-switch .bootstrap-switch-handle-off.bootstrap-switch-default {
        color: #fff;
        background: #E8483F;
    }
   
    @media screen and (-ms-high-contrast: active), (-ms-high-contrast: none) {
        .number {
            height:100%;
        }
        
        .tool {
            height:100%;
        }
    }
    
{/literal}
</style>
<div id="BlockContainer" class="container">
    {*<div style="height:40px;">
        <div class="row">
            <div class="block-title-font col-md-12" style="">
                &nbsp;<a href="/mooc/user/myteaching.php">{'mycourse'|WM_Lang}</a>&nbsp;>&nbsp;<a href="#" onclick="goManage('{$courseData.enc_course_id}');">{$courseData.caption|WM_Title}</a>&nbsp;>&nbsp;{$title}
            </div>
        </div>
    </div>*}

    <div id="back"> 
        <div class="title">
            <div class="col-xs-6">
            <a href="#" style="color:#FFFFFF" onclick="goManage('{$courseData.enc_course_id}');"><div style="float:left;position:absolute;">&nbsp;&nbsp;&nbsp;&nbsp;<i class="fas fa-arrow-left"></i>&nbsp;&nbsp;{'return'|WM_Lang}</div></a>
            </div>
            <div class="col-xs-6">
                <a href="#" style="color:#FFFFFF" onclick="save();"><div class="edit_button" title="儲存">儲存</div></a>
            </div>
        </div>
        <form method="post" action="{$appRoot}/mooc/user/exam_save.php" name="editForm" id="editForm" style="margin-bottom: 0px;">
        <div id="main">
            <div style="height:60px;">
            <div class="col-xs-8 exam">
                {if $type == 'exam'}<img src="/theme/default/irs/icon_q1.svg" style="width:32px;height:32px;" />{else}<img src="/theme/default/irs/icon_quiz.png" style="width:32px;height:32px;" />{/if}&nbsp;
                <input class="input-name" type="text" name="exam_info[title]" value="{$title}" maxlength="20" id="title" onchange="change_status();" placeholder="請輸入測驗/問卷名稱" style="line-height: 38px;">
                {if $type == 'exam'}<a class="average" href="#modify-box"><div class="tool" title="平均配分">平均配分</div></a><div class="tool noitem" title="平均配分">平均配分</div>{/if}
                <a href="#" onclick="goSort('{$exam_id}','{$type}');"><div class="tool" title="調整順序">調整順序</div></a>
                {*<a href="#" onclick="showItem();"><div class="tool" title="引用題庫">引用題庫</div></a>*}
                {if $type != 'exam'}
                    <span class="noname">
                        作答方式
                        <div class="switch switch-mini">
                            <input type="checkbox" id="forGuest" name="forGuest" value="1" {if $forGuest}checked{/if}>
                        </div>
                    </span>
                {/if}
            </div>
            <div class="col-xs-4 exam_info">
                <i class="fas fa-stop" style="color:#FFC000;font-size:18px;"></i>&nbsp;題數：<span class="item_num mr-sm">{$items}</span>{if $type == 'exam'}&nbsp;&nbsp;<i class="fas fa-stop" style="color:#FFC000;font-size:18px;"></i>&nbsp;總分：<span class="total_score">{$total_score}</span>{/if}&nbsp;<a href="#" style="text-decoration: none;" onclick="add_item();"><i class="fas fa-plus-circle icon_add" title="新增題目"></i></a>&nbsp;{if $exam_id!=''}<a href="#" style="text-decoration: none;" onclick="goView('{$exam_id}','{$type}');" class="ml-sm"><i class="fas fa-eye icon_view" title="預覽"></i></a>{/if}</div>
            </div>
            
            <div id="content">
                
                <input type="hidden" name="exam_id" id="exam_id" value="{$exam_id}">
                <input type="hidden" name="exam_type"  value="{$type}">
                <input type=file name="file[]" id="file" style="display:none">
                <div id="stastic" class="items scrollbar-primary">
                    {assign var=num value=$item|@count}
                    {foreach from=$item key=k item=v}
                        <div id="item{$k+1}" class="itemlist">
                            <input type="hidden" class="item_id" name="question_info[{$k}][item_id]" value="{$v.item_id}">
                            <div style="font-size:30px">
                            {assign var=att_num value=0}
                                <div class="tool_box">
                                    <div class="row item_tool">
                                        <div class="col-md-1 type">Q{$k+1}</div>
                                        <div class="col-md-5">
                                            <div class="bar">
                                                                                               題型
                                                <select class="select_type" disabled>
                                                    {foreach from=$arr_type key=k1 item=v1}
                                                        <option value="{$k1}" {if $k1==$v.type}selected{/if}>{$v1}題</option>
                                                    {/foreach}
                                                </select>
                                                <input type="hidden" name="question_info[{$k}][type]" value="{$v.type}" >
                                            </div>
                                            {if $type == 'exam'}
                                            <div class="bar">
                                                                                    配分
                                              <input class="score" name="question_info[{$k}][score]" value="{$v.score|floatval}" maxlength="3" onchange="adjust_score()">
                                            </div>
                                            {/if}
                                        </div>
                                        <div class="">
                                           <div class="toolbar" style="float: right;">
                                              <a href="#" onclick="copy_item('item{$k+1}',{$k},'{$v.item_id}');"><i class="fas fa-clone" title="複製"></i></a>
                                              <a href="#" onclick="upload('item{$k+1}',{$k});"><i class="fas fa-folder" title="選擇檔案"></i></a>
                                              <a href="#" onclick="remove_item('item{$k+1}');"><i class="fas fa-trash-alt" title="移除題目"></i></a>
                                              {*<a href="#"><i class="fas fa-caret-down"></i></a>*}                                         
                                           </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row back" style="">
                                    
                                    <div class="col-md-12 question">
                                         <textarea class="form-control"  style="font-size: 22px;font-weight: bold;resize:none" rows="1" name="question_info[{$k}][title]">{$v.text|strip_tags:true}</textarea>
                                    </div>
                                    
                                    <div class="upload_area_img">
                                    {if ($v.attaches|@count)>0}
                                        {foreach from=$v.attaches key=a_k item=a_v}
                                            {if ($a_v.href|pathinfo:$smarty.const.PATHINFO_EXTENSION|in_array:$img_arr)}
                                                <div class="col-md-4 col-xs-6"><i class="fas fa-times-circle icon-rm" onclick="rm_att(this,'{$a_v.filename}',{$k})"></i><a href="{$a_v.href}" name="{$a_v.filename}" target="_blank"><div class="thumbnail attach" style="background-image:url('{$a_v.href}');"></div></a></div>
                                            {/if}
                                        {/foreach}
                                    {/if}    
                                    </div>
                                    
                                    <div style="clear:both"></div>
                                    <div style="font-size:20px;margin-left: 10px;">
                                        <div class="upload_area_att">
                                        {if ($v.attaches|@count)>0}
                                        {foreach from=$v.attaches key=a_k item=a_v}
                                            {if !($a_v.href|pathinfo:$smarty.const.PATHINFO_EXTENSION|in_array:$img_arr)}
                                                <div style="display:inline"><a href="{$a_v.href}" name="{$a_v.filename}" target="_blank"><img src="/public/images/icon_file.png">{$a_v.filename}</a><i class="fas fa-times-circle" style="cursor:pointer;margin-left:8px;" onclick="rm_att(this,'{$a_v.filename}',{$k})"></i>&nbsp;&nbsp;</div>
                                            {/if}
                                        {/foreach}
                                        {/if}
                                        </div>
                                    </div>
                                    
                                    {if $v.type neq 5}
                                    <div class="col-md-12" style="margin: 15px 15px 0 15px;border-left: 5px solid #88cf20;font-size: 18px;">選項</div>
                                    {if $type == 'exam'}<div class="col-md-12" style="margin: 0px 15px;border-left: 5px solid transparent;font-size: 18px;">答案</div>{/if}
                                    {/if}
                                </div>
                                {if $v.type eq 2 || $v.type eq 3}
                                    <div class="row">
                                        {assign var=i value=0}
                                        {foreach from=$v.optionals key=k1 item=v1}
                                        {if $i eq 10}
                                        {assign var=i value=0}
                                        {/if}
                                        <div id="{$v.item_id}-{$k1}" class="row choice" style="padding:10px 0;">
                                            <div class="col-md-12 select_font" style="display:block;{if $type != 'exam'}margin-left: 35px;{/if}">
                                                <div class="item_s" style="float:left;width:5%;">
                                                
                                                {if $v.type eq 2}
                                                    <div class="radio_icon {if $i+1|in_array:$v.quizAnswer}radio_icon_select{/if} {if $type neq 'exam'}hide{/if}" onclick="select(this,'item{$k+1}')">
                                                    <input type="radio" value="{$i+1}" name="question_info[{$k}][answer]" {if $i+1|in_array:$v.quizAnswer}checked{/if} style="display:none;"/>
                                                    </div>
                                                {else}
                                                    <div class="radio_icon {if $i+1|in_array:$v.quizAnswer}radio_icon_select{/if} {if $type neq 'exam'}hide{/if}" onclick="select_m(this)">
                                                    <input type="checkbox" value="{$i+1}" name="question_info[{$k}][answer][]" {if $i+1|in_array:$v.quizAnswer}checked{/if} style="display:none;"/>
                                                    </div>
                                                {/if}
                                                
                                                </div>
                                                <div class="item_m" style="float:left;width:85%;">
                                                    <textarea class="form-control"  style="font-size: 20px;font-weight: bold;resize:none" rows="1" name="question_info[{$k}][options][{$i}][text]">{$v1.text}</textarea>
                                                </div>
                                                <div class="item_m" style="float:left;width:10%;font-size: 18px;color:#455868">
                                                    <div style="margin:9px 5px">
                                                    <a href="#" onclick="add_file('{$v.item_id}-{$k1}',{$k});"><i class="fas fa-folder"></i></a>
                                                    <a href="#" onclick="add_choice('{$v.item_id}-{$k1}','item{$k+1}',{$k},{$v.type});"><i class="fas fa-plus"></i></a>
                                                    <a href="#" onclick="remove_choice('{$v.item_id}-{$k1}','item{$k+1}',{$k},{$v.type});"><i class="fas fa-minus"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            
                                            {if ($v1.attaches|@count)>0}
                                            {foreach from=$v1.attaches key=a_k1 item=a_v1}
                                                {if ($a_v1.href|pathinfo:$smarty.const.PATHINFO_EXTENSION|in_array:$img_arr)}
                                                    <div class="col-md-4 col-xs-6" style="margin-left: 85px;margin-top: 15px;"><i class="fas fa-times-circle icon-rm" onclick="rm_choice_att(this,'{$a_v1.filename}',{$k})"></i><a href="{$a_v1.href}" name="{$a_v1.filename}" target="_blank"><div class="thumbnail attach" style="background-image:url('{$a_v1.href}');"></div></a></div>
                                                {else}
                                                    <div style="font-size:20px;margin-left: 95px;"><img src="/public/images/icon_file.png"><a href="{$a_v1.href}" name="{$a_v1.filename}" target="_blank">{$a_v1.filename}</a><i class="fas fa-times-circle" style="cursor:pointer;margin-left: 8px;" onclick="rm_choice_att(this,'{$a_v1.filename}',{$k})"></i></div>
                                                {/if}
                                            {/foreach}                                   
                                            {/if}
                                            
                                        </div>

                                        {assign var=i value=$i+1}
                                        {/foreach}
                                    </div>
                                {elseif $v.type eq 1}
                                
                                    <div class="row">
                                        <div id="{$v.item_id}_0" class="row" style="padding:10px 0;">
                                            <div class="col-md-12 select_font" style="display:block;{if $type != 'exam'}margin-left: 35px;{/if}">
                                                
                                                <div class="item_s" style="float:left;width:5%;">
                                                    {if $type == 'exam'}
                                                    <div class="radio_icon {if $v.quizAnswer.0=='O'}radio_icon_select{/if}" onclick="correct(this,'item{$k+1}')">
                                                        <input type="radio" value="O" name="question_info[{$k}][answer]" {if $v.quizAnswer.0=='O'}checked{/if} style="display:none;"/>
                                                    </div>
                                                    {/if}
                                                </div>
                                                <div class="item_m" style="float:left;width:94%;">
                                                    <textarea class="form-control" style="font-size: 20px;font-weight: bold;resize:none" rows="1" disabled>{'yes'|WM_Lang}</textarea>
                                                </div>
                                                <div class="item_m" style="float:left;width:10%;font-size: 30px;">

                                                </div>
                                                                           
                                            </div>
                                        </div>
                                        <div id="{$v.item_id}_1" class="row" style="padding:10px 0;">
                                            <div class="col-md-12 select_font" style="display:block;{if $type != 'exam'}margin-left: 35px;{/if}">
                                                
                                                <div class="item_s" style="float:left;width:5%;">
                                                    {if $type == 'exam'}
                                                    <div class="radio_icon {if $v.quizAnswer.0=='X'}radio_icon_select{/if}" onclick="correct(this,'item{$k+1}')">
                                                        <input type="radio" value="X" name="question_info[{$k}][answer]" {if $v.quizAnswer.0=='X'}checked{/if} style="display:none;"/>
                                                    </div>
                                                    {/if}
                                                </div>
                                                <div class="item_m" style="float:left;width:94%;">
                                                    <textarea class="form-control" style="font-size: 20px;font-weight: bold;resize:none" rows="1" disabled>{'no'|WM_Lang}</textarea>
                                                </div>
                                                <div class="item_m" style="float:left;width:10%;font-size: 30px;">

                                                </div>
                                                                             
                                            </div>
                                        </div>
                                    </div>
                                {elseif $v.type eq 5}

                                    <div class="row" style="margin-top:20px;padding-left: 20px;">    
                                        <div id="{$v.item_id}" style="padding:10px 30px;">
                            
                                        </div>
                                    </div>    
                                {/if}
                            </div>
                        </div>
                    {/foreach}
                    
                </div>
                
            </div>
            
         </div>     
         </form>
    </div>
    
    
</div>

<div class="over-box" id="modify-box">
    <form name="formModify" id="formModify" target="empty" style="margin-bottom: 0px;">
    <input type="hidden" name="eid" value="">
    <input type="hidden" name="type" value="">
    <div style="background: #FFFFFF;border-bottom-left-radius: 15px;border-bottom-right-radius: 15px;height: 260px;">
    <div style="background:#E8483F;height:6px;"></div>
    
    <div style="margin-left:100px;margin-top:30px;"><span style="font-weight:bold;line-height:45px;">總分</span></div>
    <div style="text-align:center;"><input name="quiz_name" id="average" type="text" class="input-search" placeholder="請輸入總分" maxlength="3" style="width:300px;height:35px;font-size:20px"></div>
    <div>
        <a href="#" onclick="modify();"><div class="true_button">{'ok'|WM_Lang}</div></a><a href="#" onclick="close_fancy();"><div class="false_button">{'cancel'|WM_Lang}</div></a>
    </div>
    </div>
    </form>
</div>

<a class="view" href="#view-box"></a>
<div class="over-box" id="view-box">
    <div style="background: #FFFFFF;border-bottom-left-radius: 15px;border-bottom-right-radius: 15px;height: 260px;">
    <div style="background:#E8483F;height:6px;"></div>
    <div class="icon_warn"></div>
    <div style="text-align:center;">目前有異動，是否儲存?</div>
    <div>
        <a href="#" onclick="save('view');"><div class="true_button">{'ok'|WM_Lang}</div></a><a href="#" onclick="goView2();"><div class="false_button">{'cancel'|WM_Lang}</div></a>
    </div>
    </div>
</div>

<a class="list" href="#list-box"></a>
<div class="over-box" id="list-box">
    <div style="background: #FFFFFF;border-bottom-left-radius: 15px;border-bottom-right-radius: 15px;height: 260px;">
    <div style="background:#E8483F;height:6px;"></div>
    <div class="icon_warn"></div>
    <div style="text-align:center;">目前有異動，是否儲存?</div>
    <div>
        <a href="#" onclick="save('list');"><div class="true_button">{'ok'|WM_Lang}</div></a><a href="#" onclick="goManage2();"><div class="false_button">{'cancel'|WM_Lang}</div></a>
    </div>
    </div>
</div>

<a class="tip" href="#alert-box"></a>
<div class="over-box" id="alert-box">
    <div style="background: #FFFFFF;border-bottom-left-radius: 15px;border-bottom-right-radius: 15px;height: 260px;">
        <div style="background:#E8483F;height:6px;"></div>
        <div class="icon_warn"></div>
        <div style="text-align:center;">請先儲存目前的異動</div>
        <div>
            <a href="#" onclick="close_fancy();"><div class="alert_button">{'ok'|WM_Lang}</div></a>
        </div>
    </div>
</div>

<form name="formGoManage" method="post" action="/mooc/user/activities.php">
<input type="hidden" name="course_id" value="" />
</form>

<form name="formGoEdit" method="post" action="/mooc/user/exam_edit.php">
<input type="hidden" name="course_id" value="{$courseData.course_id}" />
<input type="hidden" name="exam_id" value="" />
<input type="hidden" name="exam_type" value="" />
</form>

<form name="formGoView" method="post" action="/mooc/user/exam_view.php">
<input type="hidden" name="course_id" value="{$courseData.course_id}" />
<input type="hidden" name="exam_id" value="" />
<input type="hidden" name="exam_type" value="" />
</form>

<form name="formGoSort" method="post" action="/mooc/user/exam_sort.php">
<input type="hidden" name="course_id" value="{$courseData.course_id}" />
<input type="hidden" name="exam_id" value="" />
<input type="hidden" name="exam_type" value="" />
</form>

<script type="text/javascript">
    var course_id = '{$courseData.course_id}';
    var enc_course_id ='{$courseData.enc_course_id}';
    var uploadMaxFilesize ='{$uploadMaxFilesize}';
    
    var exam_id = '{$exam_id}';
    var exam_type = '{$type}';
    var items= {$items};
    var max_items = {$items};
    var now_item = 1;
    var is_change = 0;
    var type_array = {$arr_type|@json_encode};
    var uploading = 0;
    var forGuest = '{$forGuest}';
    

    {literal}
    
    $(document).ready(function() {
        // showStastic();
        adjust();
        
        $(".average").fancybox({
            'padding': 0,
            'margin': 0,
            'modal': true
        });
        
        $(".tip").fancybox({
            'padding': 0,
            'margin': 0,
            'modal': true
        });
        
        $(".view").fancybox({
            'padding': 0,
            'margin': 0,
            'modal': true
        });
        
        $(".list").fancybox({
            'padding': 0,
            'margin': 0,
            'modal': true
        });

        $('textarea').on('change', function () {
            $(this).html($(this).context.value);
            is_change = 1;
        })

        $('input').on('change', function () {
            $(this).attr('value',$(this).context.value);
            is_change = 1;
        })

        $("textarea").css("overflow","hidden").bind("keydown keyup", function(){  
            var tmp = $(this).prop("scrollHeight")-12;
            $(this).height('0px').height(tmp+"px");  
         }).keydown();

        
         if (items == 0) {
             $(".average").hide();
             $(".noitem").css({'display':'inline'});
         }
        
        if (exam_type!='exam') {
            //$('.switch input').bootstrapSwitch('state',forGuest);
            if(forGuest){
                tmp_text = '記名';
            } else {
                tmp_text = '匿名';
            }
            $('.switch input').bootstrapSwitch({
                onText:'匿名',
                offText:'記名',
                labelText:tmp_text,
                onSwitchChange:function(event,state){
                    if(state){
                        $('.bootstrap-switch-label').html('記名');
                    } else {
                        $('.bootstrap-switch-label').html('匿名');
                    } 
                }
            })

            $('.switch input').bootstrapSwitch('state',forGuest);  
        }
        
        // IE 檔案上傳第一次選檔案不會觸發file輸入框change事件，改用原生語法
        document.getElementById('file').onchange = onChange;
        function onChange(event) {
            type = $('input[type=file]').data('type');
            if (window.console) {console.log('type', type);}
            
            switch(type) {
                // 題目
                case 'fileUpload':
                    fileUpload(event);
                    break;
                
                // 答案選項
                case 'choicefileUpload':
                    choicefileUpload(event);
                    break;
            }
        }        
    });    
    
    $(window).resize(function() {
        adjust();
    });   
    
    function rm_att(t,name,key){
        var input = '<input type="hidden" name="question_info['+key+'][attaches_rm][]" value="'+name+'" >';
        $(".upload_area_img").append(input);
        $(t).parent().remove();
        change_status();
    }
    
    function rm_choice_att(t,name,key){
        var input = '<input type="hidden" name="question_info['+key+'][choice_attaches_rm][]" value="'+name+'" >';
        
        var num =  key + 1;
        var id = 'item'+num;
        $("#"+id).append(input);
        $(t).parent().remove();
        change_status();
    }
    
    function rm_tmpatt(t){
        $(t).parent().remove();
    }
    
    // 題目上傳附件
    function fileUpload(event) {
        if (window.console) {console.log('fileUpload()');}
        
        event.stopPropagation();
        id = $('input[type=file]').data('item_id');
        k  = $('input[type=file]').data('num');
        if (window.console) {console.log('id', id);}
        if (window.console) {console.log('k', k);}
        j = parseInt(id.replace('item', ''), 10) - 1;
        
        //取得檔案物件
        var files = event.target.files;
        var upload = 1;
        var formData = new FormData();
        formData.append('action','upload');
        for(var i = 0 ; i < files.length ; i++) {
            if (files[i].size > uploadMaxFilesize) {
                alert('上傳檔案超過單一檔案大小限制');
                $('input[type=file]').val('');
                upload = 0;
                return;
            }
            formData.append('file[]',files[i]);
        }
        if (upload > 0　&& files.length > 0) {
            uploading = 1;
        $.ajax({
            'url': '/mooc/user/upload.php',
            'type': 'POST',
            'data': formData,
            'dataType': "json",
            'cache': false,
            'processData': false,
            'contentType': false,
            'success': function (res) {
                
                const dateTime = Date.now();
                const timestamp = Math.floor(dateTime / 1000);
                var html = '';
                if (res.isimage) {
                    html = '<div class="col-md-4 col-xs-6"><i class="fas fa-times-circle icon-rm" onclick="rm_tmpatt(this)"></i><input type="hidden" name="question_info['+j+'][attaches][]" value="'+res.src+'" ><a href="'+res.src+'" target="_blank"><div class="thumbnail attach" style="background-image:url('+res.src+'?'+timestamp+');"></div></a></div>';
                    $("#"+id+" .upload_area_img").append(html);
                } else {
                    html = '<div style="display:inline"><input type="hidden" name="question_info['+j+'][attaches][]" value="'+res.src+'" ><a href="'+res.src+'" target="_blank"><img src="/public/images/icon_file.png">'+res.name+'</a><i class="fas fa-times-circle" style="cursor:pointer;margin-left:8px;" onclick="rm_tmpatt(this)"></i>&nbsp;&nbsp;</div>';
                    $("#"+id+" .upload_area_att").append(html);
                }
                uploading = 0;
                change_status();

            },
            'error': function () {
                alert('push Ajax Error.');
            }
        }); 

        }    
        
        $('input[type=file]').val('');   
    }
    
    // 點選題目的上傳圖示
    function upload(id,key){
        if (window.console) {console.log('upload()', id,key);}
        
        if (uploading == 0) {   
            $('input[type=file]').data('type', 'fileUpload');     
            $('input[type=file]').data('item_id', id);
            $('input[type=file]').data('num', key);
            $("#file").click();    
            
        } else {
            alert('檔案上傳中');
        }

    };
    
    // 答案選項上傳附件
    function choicefileUpload(event) {
        
        if (window.console) {console.log('choicefileUpload', event);}
        event.stopPropagation();
        choice_id = $('input[type=file]').data('choice_id');
        item_key  = $('input[type=file]').data('item_key');
        if (window.console) {console.log('choice_id', choice_id);}
        if (window.console) {console.log('item_key', item_key);}
        if (window.console) {console.log('choice_id', choice_id, choice_id.indexOf('WM_ITEM1_'));}
        j = item_key;
        if (choice_id.indexOf('WM_ITEM1_') === -1) {
            j = parseInt(choice_id.replace('item', '').replace('-' + item_key, ''), 10) - 1;
        }
        if (window.console) {console.log('j', j);}
        
        var val = $("#"+choice_id+" input").val()-1;

        //取得檔案物件
        var files = event.target.files;
        var upload = 1;
        var formData = new FormData();
        formData.append('action','choice-upload');
        for(var i = 0 ; i < files.length ; i++) {
            if (files[i].size > uploadMaxFilesize) {
                alert('上傳檔案超過單一檔案大小限制');
                $('input[type=file]').val('');
                upload = 0;
                return;
            }
            formData.append('file[]',files[i]);
        }
        if (upload > 0 && files.length > 0) {
            uploading = 1;
        $.ajax({
            'url': '/mooc/user/upload.php',
            'type': 'POST',
            'data': formData,
            'dataType': "json",
            'cache': false,
            'processData': false,
            'contentType': false,
            'success': function (res) {
                const dateTime = Date.now();
                const timestamp = Math.floor(dateTime / 1000);
                var html = '';
                if (res.isimage) {
                    html = '<div class="col-md-4 col-xs-6" style="margin-left: 85px;margin-top: 15px;"><i class="fas fa-times-circle icon-rm" onclick="rm_tmpatt(this)"></i><input type="hidden" name="question_info['+j+'][options]['+val+'][attaches]" value="'+res.src+'" ><a href="'+res.src+'" target="_blank"><div class="thumbnail attach" style="background-image:url('+res.src+'?'+timestamp+');"></div></a></div>';
                    $("#"+choice_id).append(html);
                } else {
                    html = '<div style="font-size:20px;margin-left: 95px;"><input type="hidden" name="question_info['+j+'][options]['+val+'][attaches]" value="'+res.src+'" ><a href="'+res.src+'" target="_blank"><img src="/public/images/icon_file.png">'+res.name+'</a><i class="fas fa-times-circle" style="cursor:pointer;margin-left:8px;" onclick="rm_tmpatt(this)"></i>&nbsp;&nbsp;</div>';
                    $("#"+choice_id).append(html);
                }
                uploading = 0;
                change_status();
            },
            'error': function () {
                alert('push Ajax Error.');
            }
        });
        }    
        
        $('input[type=file]').val('');    
    }
    
    // 點選答案選項的上傳圖示
    function add_file(id,key){
        if (window.console) {console.log('add_file', id, key);}
        if (uploading == 0) {
            var cnt = $("#"+id).find(".fa-times-circle").length;
            
            if(cnt>0){
                alert('已上傳附檔');
                return;
            }
            
            $('input[type=file]').data('type', 'choicefileUpload');  
            $('input[type=file]').data('choice_id', id);
            $('input[type=file]').data('item_key', key);
                
            $("#file").click();
        } else {
            alert('檔案上傳中');
        }

    };
    
    function correct(item,id) { 
        $('#'+id).find(".radio_icon_select").removeClass('radio_icon_select');
        $(item).addClass('radio_icon_select');
        $(item).find("input[type=radio]").prop('checked', true);
        change_status();
    } 
    
    function select(item,id) { 
        $('#'+id).find(".radio_icon_select").removeClass('radio_icon_select');
        $(item).addClass('radio_icon_select');
        $(item).find("input[type=radio]").prop('checked', true);
        change_status();
    }
    
    function select_m(item) {
        if($(item).find("input[type=checkbox]").attr('checked')=='checked') {
            $(item).removeClass('radio_icon_select');
            $(item).find("input[type=checkbox]").prop('checked', false);
            $(item).find("input[type=checkbox]").removeAttr('checked');
        } else {
            $(item).addClass('radio_icon_select');
            $(item).find("input[type=checkbox]").prop('checked', true);
            $(item).find("input[type=checkbox]").attr('checked','checked');
        }
        change_status();
    }
    
    function change_status() {
        is_change = 1;
    }
    
    function save(kind) {
        $("input").prop("disabled",false);

        if (kind!='') {
            close_fancy();
        }
        
        var err = 0;
        var msg = '';
        

        $(".choice textarea").each(function (index, val) {
            if($(this).context.value.trim()=='') {
                err = 1;
                msg = '有選項未正確填寫';
                return false;
            }
        });

        $(".question textarea").each(function (index, val) {
            if($(this).context.value.trim()=='') {
                err = 1;
                msg = '有題目未正確填寫';
                return false;
            }
        });

        if (exam_type == 'exam') {
            $(".score").each(function (index, val) {
                if($(this).context.value.trim()=='') {
                    err = 1;
                    msg = '有配分未正確填寫';
                    return false;
                }
            });
        }

        if ($("#title").val().trim() =='') {
            err = 1;
            msg = '請輸入測驗/問卷名稱';
        }

        if (err == 1) {
            alert(msg);
            return;
        } else {

            $.ajax({
                'url': '/mooc/user/exam_save.php',
                'type': 'POST',
                'data': $('#editForm').serialize(),
                'dataType': "json",
                'success': function (res) {
                    if(res.code==1) {
                        $("#exam_id").val(res.id);
                        exam_id = res.id;
                        is_change = 0;
                        alert('儲存成功');
                        if (kind=='view') {
                            document.formGoView.exam_id.value = exam_id;
                            document.formGoView.exam_type.value = exam_type;
                            document.formGoView.submit();
                        } else if (kind=='list') {
                            document.formGoManage.course_id.value = enc_course_id;
                            document.formGoManage.submit();
                        } else {
                            goEdit(exam_id,exam_type);
                        }

                    } else if (res.code==2) {
                        alert('沒有任何題目，無法儲存成功!');
                    }
                },
                'error': function () {
                    if (window.console) {console.log('push Ajax Error.');}
                }
            });
        }
        
        //document.getElementById("editForm").submit();
    }
    
    function adjust() {
        var back = $(window).height()-40;

        $("#back").css({"height":back});
        var scol =  back-115;
        $("#content").css({"height":scol});
        $("#stastic").css({"height":scol});

    }
    
    function copy_item(id,num,itemid) {
        var newnum = items;
        items = items+1;
        var html = $("#"+id).prop("outerHTML");
        var newid = 'item'+items;
        html = html.replace(new RegExp(id,'g'), newid);
        html = html.replace(new RegExp(itemid,'g'), newid);

        var re = 'Q/'+newid;
        var newpath = 'Q/'+itemid;
        html = html.replace(new RegExp(re,'g'), newpath);

        var old_val = 'question_info\\['+num+'\\]';
        var new_val = 'question_info['+newnum+']';

        html = '<div id="add_'+newid+'">'+html.replace(new RegExp(old_val,'g'), new_val)+'</div>';
        
        $("#stastic").append(html);
        items_update();
        
        $("#"+newid+" .type").html('Q'+items);
        $("#"+newid+" .item_id").remove();
        
        var clone_parent = $("#"+newid+" .fa-clone").parent();
        clone_parent.after('<a style="display: none;"><i class="fas fa-clone"></i></a>');
        clone_parent.remove();

        $("#"+newid+" .upload_area_img .icon-rm").each(function (index, val) {
            var href = $(this).parent().find("a").attr('href');
            var name = $(this).parent().find("a").attr('name');
            if(href != undefined) {
                var inp = '<input type="hidden" name="question_info['+newnum+'][attaches][]" value="'+href+'_0_'+name+'" >';
                var inp = '<input type="hidden" name="question_info['+newnum+'][attaches][]" value="'+href+'" >';
                $(this).parent().append(inp);
                $(this).attr('onclick','rm_tmpatt(this)');
            }
        });    

        $("#"+newid+" .upload_area_att .fa-times-circle").each(function (index, val) {
            var href = $(this).parent().find("a").attr('href');
            var name = $(this).parent().find("a").attr('name');
            if(href != undefined) {
                var inp = '<input type="hidden" name="question_info['+newnum+'][attaches][]" value="'+href+'_0_'+name+'" >';
                var inp = '<input type="hidden" name="question_info['+newnum+'][attaches][]" value="'+href+'" >';
                $(this).parent().append(inp);
                $(this).attr('onclick','rm_tmpatt(this)');
            }
        });    

        $("#"+newid+" .choice").each(function (index, val) {
            var href = $(this).find(".fa-times-circle").parent().find("a").attr('href');
            var name = $(this).find(".fa-times-circle").parent().find("a").attr('name');
            if(href != undefined) {
                var inp = '<input type="hidden" name="question_info['+newnum+'][options]['+index+'][attaches]" value="'+href+'_0_'+name+'" >';
                var inp = '<input type="hidden" name="question_info['+newnum+'][options]['+index+'][attaches]" value="'+href+'" >';
                $(this).find(".fa-times-circle").parent().append(inp);
                $(this).find(".fa-times-circle").attr('onclick','rm_tmpatt(this)');
            }
            
        });

        $("#stastic").animate({
            scrollTop: $('#stastic')[0].scrollHeight
        }, 500);

        adjust_score();
        
        /*$("#"+newid+" select").prop("disabled",false);
        
        $("#"+newid+" select").change(function() {
            select_type('+items+',this.value);
        });*/

    }
    
    function items_update() {
        $(".item_num").html(items);
        if (items == 0) {
             $(".average").hide();
             $(".noitem").css({'display':'inline'});
         } else {
             $(".average").show();
             $(".noitem").css({'display':'none'});
         }
        change_status();
    }
    
    function add_choice(id,itemid,k,type) {
        var num = $("#"+itemid+" .choice").length;
        var newnum = num + 1;
        var newid = itemid+'-'+newnum;
        var now_key = itemid.substring(4) - 1;
        var show = '';
        var qcss = '';
        if (exam_type!='exam') {
            show = 'hide';
            qcss = 'margin-left:35px';
        }

        var new_choice = '<div id="'+newid+'" class="row choice" style="padding:10px 0;">'
                            +'<div class="col-md-12 select_font" style="display:block;'+qcss+'">'
                                +'<div class="item_s" style="float:left;width:5%;">';

                         if (type == 2) {
                             new_choice+='<div class="radio_icon '+show+'" onclick="select(this,\''+itemid+'\')"><input type="radio" value="" name="question_info['+k+'][answer]" style="display:none;"/></div>';
                         } else {
                             new_choice+='<div class="radio_icon '+show+'" onclick="select_m(this)"><input type="checkbox" value="" name="question_info['+k+'][answer][]" style="display:none;"/></div>';
                         }
                     
                                    
                     new_choice+='</div>'
                                +'<div class="item_m" style="float:left;width:85%;">'
                                    +'<textarea class="form-control"  style="font-size: 20px;font-weight: bold;resize:none" rows="1" name=""></textarea>'
                                +'</div>'
                                +'<div class="item_m" style="float:left;width:10%;font-size: 18px;color:#455868">'
                                    +'<div style="margin:9px 4px" class="choice-btn">'
                                        +'<a href="#" onclick="add_file(\''+newid+'\',\''+k+'\');"><i class="fas fa-folder"></i></a>'
                                        +'&nbsp;<a href="#" onclick="add_choice(\''+newid+'\',\''+itemid+'\','+k+','+type+');"><i class="fas fa-plus"></i></a>'
                                        +'&nbsp;<a href="#" onclick="remove_choice(\''+newid+'\',\''+itemid+'\','+k+','+type+');"><i class="fas fa-minus"></i></a>'
                                    +'</div>'
                                +'</div>'
                            +'</div>'
                        +'</div>';
    
        $("#"+id).after(new_choice);
        
        
        $("#"+itemid+" .choice").each(function (index, val) {
            var text = index + 1;
            if (type == 2) {
                $(this).find("input[type=radio]").val(text);
                $(this).find("input[type=radio]").attr('name','question_info['+now_key+'][answer]');
            } else {
                $(this).find("input[type=checkbox]").val(text);
                $(this).find("input[type=checkbox]").attr('name','question_info['+now_key+'][answer]');
            }
            $(this).find("textarea").attr('name','question_info['+now_key+'][options]['+index+'][text]');
        });

        $("textarea").css("overflow","hidden").bind("keydown keyup", function(){  
            var tmp = $(this).prop("scrollHeight")-12;
            $(this).height('0px').height(tmp+"px");  
         }).keydown();
        change_status();
    }
    
    function remove_choice(id,item,k,type) {
        var cnt = $("#"+item+" .choice").length;
        if(cnt==1) {
            alert('已無選項不可移除');
            return;
        }
        
        $("#"+id).find(".fa-times-circle").click();
        $("#"+id).remove();
        
        var now_key = item.substring(4) - 1;

        $("#"+item+" .choice").each(function (index, val) {
            var text = index + 1;
            if (type == 2) {
                $(this).find("input[type=radio]").val(text);
            } else {
                $(this).find("input[type=checkbox]").val(text);
            }
            $(this).find("textarea").attr('name','question_info['+now_key+'][options]['+index+'][text]');
        });
        
        change_status();
    }
    
    function remove_item(id) {
        $("#"+id).remove();
        items = items-1;
        items_update();
        
        $('.type').each(function (index, val) {
            var num = index+1;
            $(this).html('Q'+num);
        });        

        adjust_score();
        
    }
    
    function adjust_score() {
        var total = 0;
        $('.score').each(function (index, val) {
            tmp = parseFloat($(this).context.value);
            if (isNaN(tmp)) {
                tmp = 0;
            }
            total = total + tmp;
            if (isNaN(total)) {
                total = 0;
            }
        });
        
        total = Math.round(total);

        $(".total_score").html(total);
        change_status();
    }
    
    
    function modify() {
        var score = $("#average").val();
        if (score == '' || score == null || score.search(/^[0-9]+(\.[0-9]+)?$/) !== 0) return;
        score = parseFloat(score);
        var ave = Math.floor((score / items) * Math.pow(10,2)) / Math.pow(10,2);        
        $(".score").val(ave);
    
        close_fancy();
        adjust_score(); 
    }
    
    function showItem() {
        $.fancybox({
            href: "/mooc/user/item_select.php",
            type: "ajax",
            ajax: {
                type: "POST",
                data: {
                'course_id':course_id,'exam_id': exam_id,'exam_type':exam_type
                }
            },
            helpers: {
                overlay : {closeClick: false}
            }
        });
    }

    function goManage(course_id) {
        if (is_change==1) {
            $(".list").click();
        } else {
            document.formGoManage.course_id.value = course_id;
            document.formGoManage.submit();
        }
    }
    
    function goManage2() {
        document.formGoManage.course_id.value = enc_course_id;
        document.formGoManage.submit();
    }

    function close_fancy() {
        $.fancybox.close();
    }
    
    function goEdit(id,type) {
        document.formGoEdit.exam_id.value = id;
        document.formGoEdit.exam_type.value = type;
        document.formGoEdit.submit();
    }
    
    function goView(id,type) {
        if (is_change==1) {
            $(".view").click();
        } else {
            document.formGoView.exam_id.value = id;
            document.formGoView.exam_type.value = type;
            document.formGoView.submit();
        }
    }
    
    function goView2() {
        document.formGoView.exam_id.value = exam_id;
        document.formGoView.exam_type.value = exam_type;
        document.formGoView.submit();
    }
    
    function goSort(id,type) {
        var itemnum = $(".itemlist").length;
        
        if (itemnum == 0) {
            alert('無任何題目');
        } else if (is_change==1) {
            $(".tip").click();
        } else {
            document.formGoSort.exam_id.value = exam_id;
            document.formGoSort.exam_type.value = exam_type;
            document.formGoSort.submit();

            /*$.fancybox({
                margin:40,
                href: "/mooc/user/exam_sort.php",
                type: "ajax",
                ajax: {
                    type: "POST",
                    data: {
                    'exam_id': exam_id,'exam_type':exam_type
                    }
                },
                helpers: {
                    overlay : {closeClick: false}
                }
            });

            $.fancybox({
                type: 'iframe',
                modal: true,
                width: '40%',
                height: '30%',
                'autoScale': true,
                'transitionIn': 'fade',
                'transitionOut': 'fade',
                'showCloseButton' : true,
                'href': '/mooc/user/exam_sort.php?cid='+course_id+'&exam_id='+exam_id+'&exam_type='+exam_type,
                afterShow : function() {
                    $('.fancybox-skin').append('<a title="Close" class="fancybox-item fancybox-close" href="javascript:jQuery.fancybox.close();"></a>');
                }
            });*/
        }
    }
    
    function select_type(itemNow,type) {
        var html = get_Template(type,itemNow);
        $("#add_item"+itemNow).html(html);
        $('.type').each(function (index, val) {
            var num = index+1;
            $(this).html('Q'+num);
        }); 
        change_status();
        adjust_score();
    }
    
    function add_item() {
        items = items+1;
        max_items = max_items+1;
        var html = get_Template(2,max_items);
        $("#stastic").append(html);
        $('.type').each(function (index, val) {
            var num = index+1;
            $(this).html('Q'+num);
        });   
        $("#stastic").animate({
            scrollTop: $('#stastic')[0].scrollHeight
        }, 500);
        $("textarea").css("overflow","hidden").bind("keydown keyup", function(){  
            var tmp = $(this).prop("scrollHeight")-12;
            $(this).height('0px').height(tmp+"px");  
         }).keydown();
        items_update();
    }
    
    function get_Template(type,itemNow) {
        var temp_key = itemNow-1;
        var chk = '';
        var opt = ''; 
        for (var key in type_array){
            if (key == type) {
                chk = 'selected';
            } else {
                chk = '';
            }
            opt += '<option value="'+key+'" '+chk+'>'+type_array[key]+'題</option>';
        }
        var itemid = 'item'+itemNow;
        var temp = '<div id="add_item'+itemNow+'"><div id="'+itemid+'" class="itemlist">'
                       +'<div style="font-size:30px">'
                           +'<div class="tool_box">'   
                               +'<div class="row item_tool">'
                                   +'<div class="col-md-1 type"></div>'
                                   +'<div class="col-md-5">'
                                       +'<div class="bar">'
                                           +'題型&nbsp;'
                                           +'<select class="select_type" onchange="select_type('+itemNow+',this.value)" name="question_info['+temp_key+'][type]">'
                                           +opt    
                                           +'</select>&nbsp;'
                                       +'</div>';
                                       if (exam_type=='exam') {
                                        temp += '<div class="bar">'
                                           +'配分&nbsp;<input class="score" name="question_info['+temp_key+'][score]" maxlength="3" value="" onchange="adjust_score()">'
                                       +'</div>' 
                                       } 
                                   temp += '</div>'
                                   +'<div class="">'
                                       +'<div class="toolbar" style="float:right">'
                                           +'<a><i class="fas fa-clone" title="複製"></i></a>&nbsp;<a href="#" onclick="upload(\'item'+itemNow+'\','+temp_key+');"><i class="fas fa-folder" title="選擇檔案"></i></a>&nbsp;<a href="#" onclick="remove_item(\'add_item'+itemNow+'\');"><i class="fas fa-trash-alt" title="移除題目"></i></a>'                                        
                                       +'</div>'
                                   +'</div>'  
                               +'</div>'
                           +'</div>'
                           +'<div class="row back" style="">'   
                               +'<div class="col-md-12 question">'
                                   +'<textarea class="form-control"  style="font-size: 22px;font-weight: bold;resize:none" rows="1" name="question_info['+temp_key+'][title]" placeholder="請輸入題目"></textarea>'
                               +'</div>'
                               
                               +'<div class="upload_area_img">'
                               +'</div>'
                               +'<div style="clear:both"></div>'
                               +'<div style="font-size:20px;margin-left: 10px;">'
                                   +'<div class="upload_area_att">'
                                   +'</div>'
                               +'</div>';
                               if ( type != 5) {
                                    temp += '<div class="col-md-12" style="margin: 15px 15px 0 15px;border-left: 5px solid #88cf20;font-size: 18px;">選項</div>';
                                    if (exam_type=='exam') {
                                           temp += '<div class="col-md-12" style="margin: 0px 15px;border-left: 5px solid transparent;font-size: 18px;">答案</div>';
                                    }
                               }    
                    temp += '</div>';
                    
                if (type == 2 || type == 3) {
                    temp +='<div class="row">';

                    var show = '';
                    var qcss = '';
                    if (exam_type!='exam') {
                        show = 'hide';
                        qcss = 'margin-left:35px';
                    }
                    
                    for (var i = 0; i < 4; i++) {
                         var val = i + 1;
                         var newid = itemid+'-'+i;

                         temp +='<div id="'+newid+'" class="row choice" style="padding:10px 0;">'
                                   +'<div class="col-md-12 select_font" style="display:block;'+qcss+'">'
                                       +'<div class="item_s" style="float:left;width:5%;">';
                                       
                                 if (type == 2) {
                                     temp +='<div class="radio_icon '+show+'" onclick="select(this,\''+itemid+'\')"><input type="radio" value="'+val+'" name="question_info['+temp_key+'][answer]" style="display:none;"/></div>';
                                 } else {
                                     temp +='<div class="radio_icon '+show+'" onclick="select_m(this)"><input type="checkbox" value="'+val+'" name="question_info['+temp_key+'][answer][]" style="display:none;"/></div>';
                                 }
                                 
   
                                temp +='</div>'
                                       +'<div class="item_m" style="float:left;width:85%;">'
                                           +'<textarea class="form-control"  style="font-size: 20px;font-weight: bold;resize:none" rows="1" name="question_info['+temp_key+'][options]['+i+'][text]"></textarea>'
                                       +'</div>'
                                       +'<div class="item_m" style="float:left;width:10%;font-size: 18px;color:#455868">'
                                           +'<div style="margin:9px 4px">'
                                               +'<a href="#" onclick="add_file(\''+newid+'\',\''+temp_key+'\');"><i class="fas fa-folder"></i></a>&nbsp;'
                                               +'<a href="#" onclick="add_choice(\''+newid+'\',\''+itemid+'\',\''+temp_key+'\','+type+');"><i class="fas fa-plus"></i></a>&nbsp;'
                                               +'<a href="#" onclick="remove_choice(\''+newid+'\',\''+itemid+'\',\''+temp_key+'\','+type+');"><i class="fas fa-minus"></i></a>'
                                           +'</div>'
                                       +'</div>'
                                   +'</div>'
                               +'</div>';
                    }
                    temp +='</div>';        
                } else if (type == 5) {
                    temp +='<div class="row" style="margin-top:20px;padding-left: 20px;">'    
                              +'<div id="" style="padding:10px 30px;"></div>'
                          +'</div>';
                } else if (type == 1) {
                    var show = '';
                    var qcss = '';
                    if (exam_type!='exam') {
                        show = 'hide';
                        qcss = 'margin-left:35px';
                    }
                    temp +='<div class="row">'    
                              +'<div id="" class="row" style="padding:10px 0;">'
                                  +'<div class="col-md-12 select_font" style="display:block;'+qcss+'">'
                                      +'<div class="item_s" style="float:left;width:5%;">'
                                          +'<div class="radio_icon '+show+'" onclick="correct(this,\''+itemid+'\')"><input type="radio" value="O" name="question_info['+temp_key+'][answer]" style="display:none;"/></div>'
                                      +'</div>'
                                      +'<div class="item_m" style="float:left;width:94%;">'
                                          +'<textarea class="form-control"  style="font-size: 20px;font-weight: bold;resize:none;" rows="1" disabled>是</textarea>'
                                      +'</div>'
                                      +'<div class="item_m" style="float:left;width:10%;font-size: 30px;"></div>'
                                                                           
                                  +'</div>'
                              +'</div>'
                              +'<div id="" class="row" style="padding:10px 0;">'
                                  +'<div class="col-md-12 select_font" style="display:block;'+qcss+'">'
                                      +'<div class="item_s" style="float:left;width:5%;">'
                                          +'<div class="radio_icon '+show+'" onclick="correct(this,\''+itemid+'\')"><input type="radio" value="X" name="question_info['+temp_key+'][answer]" style="display:none;"/></div>'
                                      +'</div>'
                                      +'<div class="item_m" style="float:left;width:94%;">'
                                          +'<textarea class="form-control"  style="font-size: 20px;font-weight: bold;resize:none;" rows="1" disabled>否</textarea>'
                                      +'</div>'
                                      +'<div class="item_m" style="float:left;width:10%;font-size: 30px;"></div>'
                                                                           
                                  +'</div>'
                              +'</div>'
                          +'</div>';
                }
                    
                    
                    
                    
                    
                     
                temp += '</div>' 
                   +'</div></div>';        
         return temp;          
    }

    

    
    {/literal}
</script>
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript"> 
    var jQuery_1_12 = $.noConflict(true); 
</script>