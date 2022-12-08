<link rel="stylesheet" href="{$appRoot}/public/js/third_party/tagmanager/bootstrap-tagsinput.css">
<link rel="stylesheet" href="{$appRoot}/lib/jquery/css/jquery-ui-1.8.22.custom.css">
<style type="text/css">
{literal}

.title {
    color:#000000;
    font-size:32px;
    font-weight:bold;
    margin:30px auto 20px auto;
    width:1000px;
}

.exam-box {
    /*display:none;*/
    border: 1px solid #a1a1a1;
    background-color:#ffffff;
    /*height:660px;*/
    margin:60px 60px 0 60px;
    height:600px;
    width:900px;
    border-radius: 4px;
}

.set-item {
    float:left;
    width:80px;
}

.group-li div {
    display:inline-block;
}

.set-title {
    background-color:#0DB9BB;
    height:50px;
    line-height:50px;
    color:#ffffff;
    font-size:18px;
    font-weight:bold;
    border-top-left-radius:4px;
    border-top-right-radius:4px;
    padding-left:20px;
}

.set-content {
    background-color:#FFFFFF;
    /*
    border-left: 1px solid #C4C4C4;
    border-right: 1px solid #C4C4C4;
    border-bottom: 1px solid #C4C4C4;
    border-bottom-left-radius:4px;
    border-bottom-right-radius:4px;
    */
    padding-bottom: 20px;
}

.set-button {
    margin-right:17px;
    margin-top:10px;
    text-align:right;
    display:none;
}

#next_btn {
    margin-right:17px;
    margin-top:10px;
    text-align:right;
}

.course-type {
    color:#353535;
    font-size:13px;
    padding:20px 50px;
    display:none;
}

.course-group {
    color:#000000;
    font-size:16px;
    font-weight:bold;
    padding:20px 20px;
    display:none;
}

.portal-group {
    color:#000000;
    font-size:16px;
    font-weight:bold;
    padding:20px 20px;
    display:none;
}

.showlist {
    padding-top:20px;
}

.showlist > div {
    display:inline-block;
}

.showlist .left {
    color:#000000;
    font-size:16px;
    font-weight:bold;
    width:115px;
    text-align:right;
}

.showlist .center {
    color:#353535;
    font-size:14px;
    width:620px;
    margin:0 20px;
}

.showlist .right {
    width:105px;
    text-align:center;
}

.group-box {
    width:500px;
    display:none;
}

.group-box-content {
    margin:20px 20px 20px 20px; 
}

.group-box-title {
    background-color:#F3800F;
    height:50px;
    line-height:50px;
    color:#ffffff;
    font-size:18px;
    font-weight:bold;
    border-top-left-radius:4px;
    border-top-right-radius:4px;
    padding-left:20px;
}

.group-box-button {
    margin:20px 20px 20px 20px;
    float :right; 
}

.set-li {
    height:42px;
    width:200px;
    float:left;
    line-height:42px;
    text-align:center;
    margin:8px 8px 0 0;
}

.set-li a {
    color: #FFFFFF;
    display: block;
    border-radius:4px 4px 0 0;
}
 .set-li a:hover {
    color: #048E89;
    background-color: #ececec;
 }
.set-li-active a, .set-li-active a:hover {
    color: #048E89;
    background-color: #ffffff;
}

.course_info {
    padding:20px 20px;
    /*
    border-left: 1px solid #C4C4C4;
    border-right: 1px solid #C4C4C4;
    border-bottom: 1px solid #C4C4C4;
    border-bottom-left-radius:4px;
    border-bottom-right-radius:4px;
    */
    display:none;
}

.content-title {
    color:#000000;
    font-size:14px;
    font-weight:bold;
    width:90px;
    text-align:right;
    /* display:inline-block; */
    vertical-align: top;
    margin-top: 20px;
    /*height:40px;
    line-height:40px;*/
    float: left;
    word-break: break-word;
}

.st-title {
    color:#000000;
    font-size:14px;
    font-weight:bold;
    width:90px;
    text-align:right;
    display:inline-block;
    margin-top: 30px;
    vertical-align: top;
}

.content-main {
    /* display:inline-block;
    margin-left:20px; */
    margin-left: 110px;
    /* height:40px; */
    line-height:40px;
    padding-top: 10px;
    font-size:13px;
    vertical-align: top;
}


.content-main .chk-icon{
    vertical-align: middle;
}

.st-main {
    /* display:inline-block;
    margin-left:20px;*/
    margin-left: 110px;
    padding-top: 10px;
    font-size:13px;
    line-height:40px;
}

.course_info input,.course_info select {
    font-size:13px;
    color:#353535;
    height:30px;
    padding:0;
    margin:0;
    
}

.img-outer {
    border: 1px solid #C4C4C4;
    height:172px;
    width:740px;
    padding:0;
    margin-top: 10px;
}

#coursePictureArea,#coursePictureArea2 {
    border: 5px solid #d3d3d3;
    height:133px;
    width:236px;
    margin:15px;
}

#coursePictureArea > img,#coursePictureArea2 > img {
    height:133px;
    width:236px;
}

.info-button {
    margin-top:10px;
    text-align:right;
}

.btn-70 {
    width:70px;
}

.setup {
    background-image: url('/public/images/course_set/setup_26.png');
    background-repeat: no-repeat;
    float:right;
    border-top-left-radius:4px;
    border-top-right-radius:4px;
    height:42px;
    width:60px;
    line-height:42px;
    background-position: center center; 
    margin:8px 8px 0 0;
}

.setup-active {
    background-image: url('/public/images/course_set/setup_26_1.png');
    background-color: #ffffff;
}

#goal_area input[type=text], #audience_area input[type=text], #ref_area input[type=text] {
    padding-right: 26px;
    box-sizing: border-box;    
}

.remove_icon {
    background-image: url('/public/images/course_set/delete.png');
    height:18px;
    width:18px;
    position: relative;
    left: -24px;
    float: right;
    top: 7px;
}

.remove_icon1 {
    background-image: url('/public/images/course_set/delete.png');
    height:18px;
    width:18px;
    position: relative;
    left: -18px;
    float: right;
    top: -23px;

}

.remove_icon:hover,.remove_icon1:hover {
    background-image: url('/public/images/course_set/delete_1.png');
}

.bootstrap-tagsinput {
    box-shadow:none;
    border:none;
}

.bootstrap-tagsinput input {
    display:none;
}

.bootstrap-tagsinput .tag {
    background-color: #ffffff;
    height: 27px;
    line-height: 27px;
    color:#353535;
    font-size:13px;
    border: 1px solid #d3d3d3;
}

.bootstrap-tagsinput .tag [data-role='remove']::after {
    color:#d3d3d3;
}


{/literal}
{if ($nowlang eq 'en')}
{literal}
.content-main {
    line-height:  72px;
}

.content-title {
    margin-top: 28px;
}
{/literal}
{/if}
</style>


<div class="title">{if $actType eq 'Create'}{'title_add_course'|WM_Lang}{else}{'tabs_course_set'|WM_Lang}{/if}</div>
<div class="box" style="width:1000px; margin:auto; margin-bottom: 3em;">
    <div id="course_detail" style="margin:50px auto;width:900px; border: 1px solid #C4C4C4; border-radius: 5px;">
        <div class="set-title">
            <ul id="set-tab" style="list-style: none;margin:0;">
                <li class="set-li set-li-active" ><a href="javascript:;" onclick="show_page('info')">{'course_information_and_specifications'|WM_Lang}</a></li>
                {if $actType neq 'Create'}
                <li class="set-li" ><a href="javascript:;" onclick="show_page('intro')">{'course_introduction'|WM_Lang}</a></li>
                <li class="set-li" ><a href="javascript:;" onclick="show_page('setup')">{'privilege'|WM_Lang}</a></li>
                {/if}
                {*
                <li class="set-li" ><a href="javascript:;" onclick="show_page('goal')">{'learning_objectives'|WM_Lang}</a></li>
                *}
            </ul>
        </div>
        <!-- 錯誤訊息 -->
        <div id="error-msg-2"></div>
        <!-- 課程資訊與規格 -->
        <div class="course_info" id="info">
            <form id="saveForm1" name="saveForm1" class="breakword" style="overflow: hidden;">
                <div class="content-title"><span class="lcms-red-starmark">* </span>{'td_course_name'|WM_Lang}</div>
                <div id="course_name" class="content-main" title="{$data.lang.$nowlang}">
                    {if ($editLimit === 2 || 'caption'|in_array:$ta_can_sets)}
                        {foreach from=$lang key=k_lang item=v_lang}
                            {if $k_lang == $nowlang}
                                {'multi_lang_'|cat:$k_lang|WM_Lang}<input type="text" id="{$k_lang}" name="{$k_lang}" class="input-large" style="width: 380px;" placeholder="{$msg_fill_coursename}" value="{$data.lang.$k_lang}">&nbsp;&nbsp;<button type="button" class="btn" onclick="showOtherLangTitle(this);">{'more_lang'|WM_Lang}</button>
                            {else}
                                <div class="otherLangTitle" style="display:none;">
                                    {if $k_lang eq 'en'}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{/if}
                                    {'multi_lang_'|cat:$k_lang|WM_Lang}<input type="text" id="{$k_lang}" name="{$k_lang}" class="input-large" style="width: 380px;" placeholder="{$msg_fill_coursename}" value="{if $data.lang.$k_lang eq ''}undefined{else}{$data.lang.$k_lang}{/if}">
                                </div>
                            {/if} 
                        {/foreach}
                    {else}
                        <div style="display: inline-block;">{$data.lang.$nowlang}</div>
                    {/if}
                </div>
                <div class="content-title">{'th_teacher'|WM_Lang}</div>
                {if $editLimit === 1}
                <div class="content-main">
                    <div class="clearfix">{$data.teacher}</div>
                </div>
                {else}
                <div class="content-main">
                    <div class="clearfix"><input type="text" id="teacher" name="teacher" class="input-large" style="width: 380px;" value="{$data.teacher}" />{'th_alt_teacher'|WM_Lang}</div>
                </div>
                {/if}
                <div class="content-title"><span class="lcms-red-starmark">* </span>{'td_status'|WM_Lang}</div>
                <div class="content-main">
                    {if ($editLimit === 2 || 'status'|in_array:$ta_can_sets)}
                    <select id="status" name="status" class="" style="width: 27em;">
                        <option value="5"{if $data.status eq 5} selected{/if}>{'param_prepare'|WM_Lang}</option>
                        <option value="1"{if $data.status eq 1} selected{/if}>{'param_open_a'|WM_Lang}</option>
                        <option value="2"{if $data.status eq 2} selected{/if}>{'param_open_a_date'|WM_Lang}</option>
                        <option value="3"{if $data.status eq 3} selected{/if}>{'param_open_n'|WM_Lang}</option>
                        <option value="4"{if $data.status eq 4} selected{/if}>{'param_open_n_date'|WM_Lang}</option>
                        {if $editLimit == 2 && $actType neq 'Create'}
                        <option value="0"{if $data.status eq 0} selected{/if}>{'param_close'|WM_Lang}</option>
                        {/if}
                    </select>
                    {else}
                        {if $data.status eq 0}{'param_close'|WM_Lang}{/if}
                        {if $data.status eq 1}{'param_open_a'|WM_Lang}{/if}
                        {if $data.status eq 2}{'param_open_a_date'|WM_Lang}{/if}
                        {if $data.status eq 3}{'param_open_n'|WM_Lang}{/if}
                        {if $data.status eq 4}{'param_open_n_date'|WM_Lang}{/if}
                        {if $data.status eq 5}{'param_prepare'|WM_Lang}{/if}
                    {/if}
                </div>
                <div class="clearboth-block"></div>
                {if $actType eq 'Edit'}
                <div class="content-title">{'course_categories'|WM_Lang}</div>
                <div class="content-main">
                    {if $editLimit > 0}
                        <div style="margin-left: -0.5em;">
                            <input id="course_kind" type="hidden" name="course_kind" />
                        </div>
                    {else}
                        <div class="center">{$show_kind}</div>
                    {/if}
                </div>
                <div class="clearboth-block"></div>
                {/if}
                <!-- 教材使用 -->
                {if $actType eq 'Edit'}
                <div class="content-title">{'th_content'|WM_Lang}</div>
                <div class="content-main">
                    <div class="clearfix">
                        {if ($editLimit === 2 || 'content_id'|in_array:$ta_can_sets)}
                            <input type="checkbox" id="ck_content_id" name="ck_content_id" value="content" onclick="showContent(this.checked);" {if $content_id gt 0} checked{/if} />{'btn_enable'|WM_Lang}
                            <span id="span_content"{if $content_id eq 0} style="display: none;"{/if}>
                                <input type="text" id="QueryTxt" name="QueryTxt" value="{$content_caption}" title="{$content_caption}" style="width: 35em;" readonly />
                                <input type="hidden" id="content_id" name="content_id" value="{$content_id}">
                                <button id="btnCQuery" class="btn" type="button" onclick="select_content();">{'btn_find_content'|WM_Lang}</button>
                            </span>
                        {else}
                            {if $content_id gt 0}
                                {$content_caption}
                            {else}
                                {'btn_disable'|WM_Lang}
                            {/if}
                        {/if}
                    </div>
                </div>
                {/if}
                <!-- 報名期間 -->
                <div id="en-date-title" class="content-title">{*<span class="lcms-red-starmark">* </span>*}{'during_registration'|WM_Lang}</div>
                <div class="content-main">
                    {if ($editLimit === 2 || 'en_begin'|in_array:$ta_can_sets)}
                        <!-- 自學課時，給予隨時的選項 -->
                        <span id="en-option">
                            <input type="radio" name="en_option" value="0" {if $data.en_option eq '0'}checked="checked"{/if} onclick="switchEnployTimeSetting(this.value, true);"> {'msg_any_time'|WM_Lang}&nbsp;
                            <input type="radio" name="en_option" value="1" {if $data.en_option eq '1'}checked="checked"{/if} onclick="switchEnployTimeSetting(this.value, true);">                            
                        </span>
                        <input type="text" name="en_begin" id="en_begin" value="{if $data.en_begin neq NULL and $data.en_begin neq '0000-00-00'}{$data.en_begin}{/if}" readonly="readonly" class="cssInput enployTimeSelect" style="width:80px;text-align:center;cursor:pointer;">
                        {'to'|WM_Lang}
                        <input type="text" name="en_end" id="en_end" value="{if $data.en_end neq NULL and $data.en_end neq '0000-00-00'}{$data.en_end}{/if}" readonly="readonly" class="cssInput enployTimeSelect" style="width:80px;text-align:center;cursor:pointer;">
                    {else}
                        <span>{if $data.en_begin neq NULL and $data.en_begin neq '0000-00-00'}{$data.en_begin}{else}{'now'|WM_Lang}{/if} ~ {if $data.en_end neq NULL and $data.en_end neq '0000-00-00'}{$data.en_end}{else}{'forever'|WM_Lang}{/if}</span>
                    {/if}
                </div>
                <!-- 上課期間 -->
                <div id="st-date-title" class="content-title">{*<span class="lcms-red-starmark">* </span>*}{'during_the_class'|WM_Lang}</div>
                <div id="st-date" class="st-main">
                    {if ($editLimit === 2 || 'st_begin'|in_array:$ta_can_sets)}
                    <div style="display: inline-block;">
                        <span id="st-option">
                            <input type="radio" name="st_option" value="0" {if $data.st_option eq '0'}checked="checked"{/if} onclick="switchStudyTimeSetting(this.value, true, false);"> {'msg_any_time'|WM_Lang}&nbsp;
                            <input type="radio" name="st_option" value="1" {if $data.st_option eq '1'}checked="checked"{/if} onclick="switchStudyTimeSetting(this.value, true, false);">                            
                        </span>
                        <input type="text" name="st_begin" id="st_begin" value="{if $data.st_begin neq NULL and $data.st_begin neq '0000-00-00'}{$data.st_begin}{/if}" readonly="readonly" class="cssInput studyTimeSelect" style="width:80px;text-align:center;cursor:pointer;">
                        {'to'|WM_Lang}
                    </div>
                    <div style="display: inline-block;">
                        <input type="text" name="st_end" id="st_end" value="{if $data.st_end neq NULL and $data.st_end neq '0000-00-00'}{$data.st_end}{/if}" readonly="readonly" class="cssInput studyTimeSelect" style="width:80px;text-align:center;cursor:pointer;">
                        &nbsp;&nbsp;<input id="ck_sync_st_end" name="ck_sync_st_end" type="checkbox" checked="checked" value="1" />{'sync_to_calendar'|WM_Lang}
                    </div>
                    <div class="clearfix">
                        {'sync_to_calendar_msg'|WM_Lang}
                    </div>
                    {else}
                    <div style="display: inline-block;">
                        <span>{if $data.st_begin neq NULL and $data.st_begin neq '0000-00-00'}{$data.st_begin}{else}{'now'|WM_Lang}{/if} ~ {if $data.st_end neq NULL and $data.st_end neq '0000-00-00'}{$data.st_end}{else}{'forever'|WM_Lang}{/if}</span>
                    </div>
                    {/if}
                </div>
                <!-- 修課審核 -->
                {if $editLimit > 0}
                <div class="content-title">{'th_review_name'|WM_Lang}</div>
                <div class="content-main">
                    {if ($editLimit === 2 || 'review'|in_array:$ta_can_sets)}
                        <select id="review" name="review" class="">
                            {foreach from=$reviews key=reviewId item=reviewText}
                            <option value="{$reviewId}" title="{$reviewText}" {if $reviewId eq $review_val}selected{/if}>{$reviewText} </option>
                            {/foreach}
                        </select>
                    {else}
                        {foreach from=$reviews key=reviewId item=reviewText}
                            {if $reviewId eq $review_val}{$reviewText}{/if}
                        {/foreach}&nbsp;
                    {/if}
                </div>
                {/if}
                <!-- 正式生 -->
                <div class="content-title">{'th_student'|WM_Lang}</div>
                <div class="content-main">     
                    {if ($editLimit === 2 || 'n_limit'|in_array:$ta_can_sets)} 
                        <input type="text" id="n_limit" name="n_limit" class="input-large" style="width: 50px;" value="{$data.n_limit}">&nbsp;&nbsp;{'th_alt_student'|WM_Lang}
                    {else}
                        {$data.n_limit}
                    {/if}
                </div>
                <!-- 旁聽生 -->
                <div class="content-title">{'th_auditor'|WM_Lang}</div>
                <div class="content-main">    
                    {if ($editLimit === 2 || 'a_limit'|in_array:$ta_can_sets)} 
                        <input type="text" id="a_limit" name="a_limit" class="input-large" style="width: 50px;" value="{$data.a_limit}">&nbsp;&nbsp;{'th_alt_student'|WM_Lang}
                    {else}
                        {$data.a_limit}
                    {/if}
                </div>
                <!-- 學分數 -->
                <div class="content-title">{*<span class="lcms-red-starmark">* </span>*}{'td_credit'|WM_Lang}</div>
                <div class="content-main">
                    {if $editLimit > 0}
                    <input type="text" id="credit" name="credit" class="input-large" style="width: 50px;" value="{$data.credit}">
                    {else}
                    {if $data.credit eq NULL}0{else}{$data.credit}{/if}
                    {/if}
                </div>
                <!-- 及格成績 -->
                <div class="content-title">{'fair_grade'|WM_Lang}</div>
                <div class="content-main">
                    {if ($editLimit === 2 || 'fair_grade'|in_array:$ta_can_sets)} 
                    <input type="text" id="fair_grade" name="fair_grade" class="input-large" style="width: 50px;" value="{$data.fair_grade}">
                    {else}
                        {if $data.fair_grade eq NULL}0{else}{$data.fair_grade}{/if}
                    {/if}
                </div>
                <!-- 空間限制 -->
                <div class="content-title">{'th_quota'|WM_Lang}</div>
                <div class="content-main">
                    {if $editLimit == 2}
                    <input type="text" id="quota_limit" name="quota_limit" class="input-large" style="width: 50px;" value="{$data.quota_limit}">MB
                    {else}
                    {if $data.quota_limit eq NULL}0 MB{else}{$quota_limit_text}{/if}
                    {/if}
                </div>
                <!-- 空間使用 -->
                {if $actType eq 'Edit'}
                <div class="content-title">{'th_usage'|WM_Lang}</div>
                <div class="content-main">
                    {if $data.quota_used eq NULL}0MB{else}{$quota_usage_text} ( {$quota_usage_rate}% ){/if}
                </div>
                {/if}
                <!-- 費用 -->
                {if !($is_independent_school && !$is_portal_school) && $enablePaid}
                <div class="content-title"><span class="lcms-red-starmark">* </span>{'fee'|WM_Lang}</div>
                <div class="content-main">
                    {if $editLimit > 0}
                    <input type="radio" name="fees" value="0" {$data.fees0}>　{'free'|WM_Lang} 　<input type="radio" name="fees" value="1" {$data.fees1}>　<input type="text" id="fee" name="fee" class="input-large" style="width: 50px;" value="{$data.fee}"> NT / {'during_counseling'|WM_Lang}
                    {else}
                    {if $data.fees0 eq 'checked'}
                    {'free'|WM_Lang}
                    {else}
                    {$data.fee} NT / {'during_counseling'|WM_Lang}
                    {/if}
                    {/if}
                </div>
                {/if}
                <!-- 課程代表圖 -->
                {if $sysEnableAppCoursePicture && ($actType neq 'Create')}
                <div class="content-title">{*<span class="lcms-red-starmark">* </span>*}{'th_picture'|WM_Lang}</div>
                {*{if $editLimit > 0}  <!-- 課程圖片，不限制開課者管理員才能修改 -->*}
                <div class="content-main">
                    <button type="button" class="btn" onclick="appCoursePictureBrowseFile();">{'upload_photos'|WM_Lang}</button><span style="color:#ff6c00">　({'note1'|WM_Lang})</span>
                </div>
                <br>
                <div class="content-title"></div>
                {*{/if}*}
                <div class="content-main img-outer">
                    <div id="coursePictureArea"><img src="/lib/app_show_course_picture.php?courseId={$appCsid}" id="coursePicture" name="coursePicture" borer="0"></div>
                    <a href="javascript:;" class="dropbtn" style="position: relative; top: -13.1em; left: 18.7em; display: inline-block;">
                        <img src="/theme/default/learn_mooc/drop_1.png">
                    </a>
                </div>
                {/if}
                <div class="info-button">
                    <button type="button" class="btn btn-blue btnNormal" onclick="save_step(1)">{'btn_save'|WM_Lang}</button>
                </div>
                <input type="hidden" name="ticket" value="{$ticket}">
                <input type="hidden" name="csid" value="{$csid}">
                <input type="hidden" name="method" value="ajax">
                <input type="hidden" name="step" value="1">
            </form>
        </div>
        <!-- 課程介紹 -->
        <div class="course_info" id="intro">
            <form id="saveForm2" name="saveForm2">
                <div class="content-title">{*<span class="lcms-red-starmark">* </span>*}{'course_introduction'|WM_Lang}</div>
                <div class="content-main">
                    <a id="upload_mv" href="#upload-box"><button type="button" class="btn" >{'upload_videos'|WM_Lang}</button></a>
                    <span style="color:#ff6c00; white-space: nowrap;">
                        <img src="{$appRoot}/public/images/course_set/i.png" width="26" height="26"> ({'note2'|WM_Lang})
                    </span>
                </div>
                <br>
                <div class="content-title"></div>
                <div class="content-main img-outer">
                    <div id="coursePictureArea2"><img src="{$photoPath}?{$smarty.now}"></div>
                    <a id="delete_mv" href="#upload-box" style="position: relative; top: -13.1em; left: 18.7em; display: inline-block;" title="{'delete_course_video'|WM_Lang}">
                        <img src="/theme/default/learn_mooc/drop_1.png">
                    </a>
                </div>
                <br>
                <div class="content-title">{'subhead'|WM_Lang}</div>
                <div class="content-main"><input type="text" id="course_title" name="subhead" class="input-large" style="width: 380px;" value="{$data.subhead}"></div>
                <br>
                <div class="content-title"><span class="lcms-red-starmark">* </span>{'td_introduce'|WM_Lang}</div>
                <div class="content-main" style="height:100%;">
                    {if ($editLimit === 2 || 'content'|in_array:$ta_can_sets)} 
                        <textarea id="content" name="content" rows="6" style="width:733px;" >{$data.content}</textarea>
                    {else}
                        <div style="display: inline-block;">{$data.content|nl2br}</div>
                    {/if}
                </div>
                <br>
                <div class="content-title">{'th_book'|WM_Lang}</div>
                <div class="content-main" style="height:100%;">
                    {if ($editLimit === 2 || 'texts'|in_array:$ta_can_sets)} 
                        <textarea id="texts" name="texts" rows="6" style="width:733px;" >{$data.texts}</textarea>
                    {else}
                        <div style="display: inline-block;">{$data.texts|nl2br}</div>
                    {/if}
                </div>
                <input type="hidden" name="ticket" value="{$ticket}">
                <input type="hidden" name="csid" value="{$csid}">
                <input type="hidden" name="method" value="ajax">
                <input type="hidden" name="step" value="2">
                <div class="info-button">
                    <button type="button" class="btn btn-blue btnNormal" onclick="save_step(2)">{'btn_save'|WM_Lang}</button>
                </div>
            </form>
        </div>
        <!-- 學習目標 -->
        <div class="course_info" id="goal">
            <form id="saveForm3" name="saveForm3">
                <div class="content-title">{'learning_objectives'|WM_Lang}</div>
                <div class="content-main">
                    <input type="text" name="goal[]" value="{$data.goal.0}" style="width:558px;" >&nbsp;
                    <button type="button" class="btn btn-primary btn-gray btn-70" onclick="add_item('goal');">{'btn_add'|WM_Lang}</button>&nbsp;
                    <div class="chk-icon {if $data.chk_goal eq 'checked'}icon-check-on{else}icon-check-off{/if}"></div>
                    &nbsp;
                    <input type="checkbox" name="is_use[]" value="goal" {$data.chk_goal} style="display: none;">
                </div>
                <div id="goal_area" class="content-main" style="height:auto; display: inline-block;">
                    {foreach from=$data.goal key=k_goal item=v_goal}
                    {if $k_goal != 0}
                    <input type="text" name="goal[]" value="{$v_goal}" style="width:560px;margin-bottom:12px;" >
                    <div class="remove_icon"></div>
                    <br>
                    {/if}
                    {/foreach}
                </div>
                <br>
                <div class="content-title">{'learning_objects'|WM_Lang}</div>
                <div class="content-main">
                    <input type="text" name="audience[]" value="{$data.audience.0}" style="width:558px;" >&nbsp;
                    <button type="button" class="btn btn-primary btn-gray btn-70" onclick="add_item('audience');">{'btn_add'|WM_Lang}</button>&nbsp;
                    <div class="chk-icon {if $data.chk_audience eq 'checked'}icon-check-on{else}icon-check-off{/if}"></div>
                    &nbsp;
                    <input type="checkbox" name="is_use[]" value="audience" {$data.chk_audience} style="display: none;">
                </div>
                <div id="audience_area" class="content-main" style="height:auto; display: inline-block;">
                    {foreach from=$data.audience key=k_audience item=v_audience}
                    {if $k_audience != 0}
                    <input type="text" name="audience[]" value="{$v_audience}" style="width:560px;margin-bottom:12px;" >
                    <div class="remove_icon"></div>
                    <br>
                    {/if}
                    {/foreach}
                </div>
                <br>
                <div style="margin-top:15px;">
                    <div class="content-title" style="float:left; margin-top: 5px;">{'reference_material'|WM_Lang}</div>
                    <div id="ref_area" class="content-main" style="float:left; width:569px; height:auto; margin-left:20px; line-height: 30px; min-height: 1px;">
                        {if $data.show_ref}
                        {foreach from=$data.ref_title key=k_texts item=v_texts}
                        <input type="hidden" name="r_addr[]" value="{$data.ref_url.$k_texts}"><input type="text" name="r_title[]" value="{$v_texts}" style="width:560px;font-size:13px;color:#08b1b3;text-decoration:underline;" readonly>
                        <div class="remove_icon1"></div>
                        {/foreach}
                        {/if}
                    </div>
                    <div style="float:left;">
                        <a id="edit_ref" href="#ref-box"><button type="button" class="btn btn-primary btn-gray btn-70" >{'btn_add'|WM_Lang}</button></a>&nbsp;
                        <div class="chk-icon {if $data.chk_ref eq 'checked'}icon-check-on{else}icon-check-off{/if}" style="vertical-align: middle;"></div>
                        &nbsp;
                        <input type="checkbox" name="is_use[]" value="ref" {$data.chk_ref} style="display: none;">
                    </div>
                </div>
                <br>
                <div class="clearboth-block"></div>
                <hr style="margin: 10px 0 15px 0;">
                </hr>
                <div class="content-title" style="vertical-align: top;">{'pass_condition'|WM_Lang}</div>
                <div class="content-main" style="height:auto;">
                    <div style="width:647px;float:left;">
                        <input type="checkbox" name="sel_formal[]" value="formal_score" {$data.chk_formal_score}>　{'grade'|WM_Lang}　　　<input type="text" name="fair_grade" value="{$data.fair_grade}" placeholder="{'grade'|WM_Lang}"> {'fraction'|WM_Lang}<br>
                        <input type="checkbox" name="sel_formal[]" value="formal_time" {$data.chk_formal_time}>　{'reading_time'|WM_Lang}　<input type="text" name="formal[time]" value="{$data.formal.time}"  placeholder="{'reading_time'|WM_Lang}"> {'unit_hour'|WM_Lang}<br>
                        <input type="checkbox" name="sel_formal[]" value="formal_process" {$data.chk_formal_process}>　{'teaching_schedule'|WM_Lang}　<input type="text" name="formal[percent]" value="{$data.formal.percent}" placeholder="{'teaching_schedule'|WM_Lang}"> %
                    </div>
                    <div style="float:left;padding-top: 41px;">
                        <div class="chk-icon icon-check-off"></div>
                        &nbsp;
                        <input type="checkbox" id="chk_formal_all" onchange="check_all('formal')" style="display: none;">
                    </div>
                    <div class="clearfix"></div>
                </div>
                <hr style="margin: 10px 0 15px 0;">
                </hr>
                <div class="content-title" style="vertical-align: top;">{'novitiate'|WM_Lang}</div>
                <div class="content-main" style="height:auto;">
                    <div style="width:647px;float:left;">
                        <input type="checkbox" name="sel_gallery[]" value="gallery_time" {$data.chk_gallery_time}>　{'reading_time'|WM_Lang}　<input type="text" name="gallery[time]" value="{$data.gallery.time}" placeholder="{'reading_time'|WM_Lang}"> {'unit_hour'|WM_Lang}<br>
                        <input type="checkbox" name="sel_gallery[]" value="gallery_process" {$data.chk_gallery_process}>　{'teaching_schedule'|WM_Lang}　<input type="text" name="gallery[percent]" value="{$data.gallery.percent}"  placeholder="{'teaching_schedule'|WM_Lang}"> %
                    </div>
                    <div style="float:left;padding-top: 22px;">
                        <div class="chk-icon icon-check-off"></div>
                        &nbsp;
                        <input type="checkbox" id="chk_gallery_all" onchange="check_all('gallery')" style="display: none;">
                    </div>
                    <div class="clearfix"></div>
                </div>
                <input type="hidden" name="ticket" value="{$ticket}">
                <input type="hidden" name="csid" value="{$csid}">
                <input type="hidden" name="method" value="ajax">
                <input type="hidden" name="step" value="3">
                <div class="info-button">
                    <button type="button" class="btn btn-blue btnNormal" onclick="save_step(3)">{'btn_save'|WM_Lang}</button>
                </div>
            </form>
        </div>
        <!-- 權限設定 -->
        <div class="course_info" id="setup">
            <form id="saveForm6" name="saveForm6">
                <section>
                    <div style="font-size:16px;font-weight:bold;color:#000">{'role_privilege'|WM_Lang}</div>
                    <div style="padding-left: 2.5em;">
                        <div style="font-size:14px;font-weight:bold;color:#000;margin-top:15px;">
                            {'teacher'|WM_Lang}　
                        </div>
                        <div id="tagsTeacher" style="margin:10px 0 0 36px;">
                            <input id="teach_auth" type="hidden" name="teach_auth" {if $editLimit neq 2}disabled="disabled"{/if}/>{if $editLimit eq 2}<button type="button" class="btn btn-70" id="btnSignIn" onclick="select_teacher();">{'btn_add'|WM_Lang}</button>{/if}
                        </div>
                        <hr style="margin: 10px 0 15px 0;">
                        </hr>
                        <div style="font-size:14px;font-weight:bold;color:#000">
                            {'instructor'|WM_Lang}
                        </div>
                        <div id="tagsInstructor" style="margin:10px 0 0 36px;">
                            <input id="instructor_auth" type="hidden" name="instructor_auth" />{if ($editLimit eq 2)||($editLimit > 0 && $is_teacher eq 'Y')}<button type="button" class="btn btn-70" onclick="select_instructor();">{'btn_add'|WM_Lang}</button>{/if}
                        </div>
                        <hr style="margin: 10px 0 15px 0;">
                        </hr>
                        <div style="font-size:14px;font-weight:bold;color:#000">
                            {'assistant'|WM_Lang}
                        </div>
                        <div id="tagsAssistant" style="margin:10px 0 0 36px;">
                            <input id="assistant_auth" type="hidden" name="assistant_auth" />{if ($editLimit eq 2)||($editLimit > 0 && $is_teacher eq 'Y')}<button type="button" class="btn  btn-70" onclick="select_assistant();">{'btn_add'|WM_Lang}</button>{/if}
                        </div>
                    </div>
                </section>
                {if $env === 'academic'}
                <section style="margin-top: 2em;">
                    <div style="font-size:16px;font-weight:bold;color:#000">{'fields_privilege'|WM_Lang}</div>
                    <div style="padding-left: 2.5em; margin-top: 1em;">
                        {foreach from=$default_ta_can_sets key=k item=v}
                            <label class="checkbox" style="float: left; margin-right: 1em;">
                                {assign var=caption value=ckta_$v}
                                <input type="checkbox" name="ckta_{$v}" {if $v|in_array:$ta_can_sets}checked{/if}><span style="position: relative; top: 0.3em;">{$caption|WM_Lang}</span>
                            </label>
                        {/foreach}
                    </div>
                </section>
                <div style="clear: both;"></div>
                {/if}
                {if ($editLimit eq 2)||($editLimit > 0 && $is_teacher eq 'Y')}
                <div class="info-button">
                    <button type="button" class="btn btn-blue btnNormal" onclick="save_step(6)">{'btn_save'|WM_Lang}</button>
                </div>
                <input type="hidden" name="ticket" value="{$ticket}">
                <input type="hidden" name="csid" value="{$csid}">
                <input type="hidden" name="method" value="ajax">
                <input type="hidden" name="step" value="6">
                <input type="hidden" name="oriteach" value="{$oriteach}">
                <input type="hidden" name="oriassistant" value="{$oriassistant}">
                <input type="hidden" name="oriinstructor" value="{$oriinstructor}">
                {/if}
            </form>
        </div>
    </div>
    {if $editLimit eq 2}
    <!-- 返回列表 -->
    <form id="listFm" action="/academic/course/course_list.php" method="post" enctype="multipart/form-data" style="text-align:right;  margin: 0 auto 35px auto; width: 900px;">
        <button type="submit" class="btn btn-blue btnNormal" id="btnOpenCs">{'back_to_course_list'|WM_Lang}</button>
        <input type='hidden' name='ticket' value='{$returnList.gid}'>
        <input type='hidden' name='page'   value='{if $returnList.page neq ''}{$returnList.page}{else}1{/if}'>
        <input type='hidden' name='sortby' value='{$returnList.sortby}'>
		<input type='hidden' name='keyword' value='{$returnList.keyword}'>
    </form>
    {/if}
    <!-- 選擇分類課程視窗 -->
    <div class="group-box" id="group-box">
        <div class="group-box-title">{'select_course_categories'|WM_Lang}</div>
        <div class="group-box-content">{$group_html}</div>
        <div class="group-box-button">
            <button type="button" class="btn btn-warning btnNormal" onclick="group_tags()">{'btn_ok'|WM_Lang}</button>
            <button type="button" class="btn aNormal btnNormal margin-left-15" onclick="close_fancy()">{'btn_cancel'|WM_Lang}</button>
        </div>
    </div>
    <!-- 選擇平台分類課程視窗 -->
    <div class="group-box" id="portal-group-box">
        <div class="group-box-title">{'select_platform_course_categories'|WM_Lang}</div>
        <div class="group-box-content">{$pgroup_html}</div>
        <div class="group-box-button">
            <button type="button" class="btn btn-warning btnNormal" onclick="portal_group_tags()">{'btn_ok'|WM_Lang}</button>
            <button type="button" class="btn aNormal btnNormal margin-left-15" onclick="close_fancy()">{'btn_cancel'|WM_Lang}</button>
        </div>
    </div>
    <!-- 新增參考資料視窗 -->
    <div class="group-box" id="ref-box">
        <div class="group-box-title">{'btn_add'|WM_Lang}{'reference_material'|WM_Lang}</div>
        <div class="group-box-content">
            <input type="text" id="title"  style="width:450px;height:30px;" placeholder="{'head'|WM_Lang}"><br>
            <input type="text" id="addr" style="width:450px;height:30px;" placeholder="http://">
        </div>
        <div class="group-box-button">
            <button type="button" class="btn btn-warning btnNormal" onclick="ref_process()">{'btn_ok'|WM_Lang}</button>
            <button type="button" class="btn aNormal btnNormal margin-left-15" onclick="close_fancy()">{'btn_cancel'|WM_Lang}</button>
        </div>
    </div>
    <!-- 上傳課程介紹視窗 -->
    <div class="group-box" id="upload-box">
        <form name="intro-upload" id="intro-upload" accept-charset="UTF-8" lang="zh-tw" action="/teach/course/doajaxfileupload.php" method="POST" enctype="multipart/form-data">
            <div class="group-box-title">{'upload_videos_courses'|WM_Lang}</div>
            <div class="group-box-content">
                <input type="file" name="intro_file" id="intro_file"  size="30" class="cssInput" style="ime-mode:disabled;width:450px;height:30px;">
            </div>
            <div class="group-box-button">
                <button type="submit" class="btn btn-warning btnNormal">{'btn_upload'|WM_Lang}</button>
                <button type="button" class="btn aNormal btnNormal margin-left-15" onclick="close_fancy()">{'btn_cancel'|WM_Lang}</button>
            </div>
            <input type="hidden" name="basePath" value="{$basePath}" >  
        </form>
    </div>
    <form name="editFm" action="/teach/course/m_course_property.php" method="post" enctype="multipart/form-data" style="display:none">
        <input type="hidden" name="ticket" value="" >
        <input type="hidden" name="csid" value="0" >
    </form>
</div>

<script type="text/javascript" src="{$appRoot}/public/js/third_party/tagmanager/bootstrap-tagsinput.js"></script>
<script type="text/javascript" src="{$appRoot}/theme/default/bootstrap/js/bootstrap-tooltip.js"></script>
<script type="text/javascript" src="{$appRoot}/lib/popup/popup.js"></script>
<script type="text/javascript" src="{$appRoot}/mooc/public/js/site_header.js"></script>
<script type="text/javascript" src="{$appRoot}/lib/jquery/jquery-ui-1.8.22.custom.min.js"></script>
<script type="text/javascript" src="{$appRoot}/lib/jquery.form.min.js"></script>
<script type="text/javascript" src="{$appRoot}/public/js/third_party/jquery-placeholder/jquery.placeholder.js"></script>

<script type="text/javascript">
    
    var xmlDocs  = null, xmlHttp = null;
    var appFrameId = "{$frameId}";
    var appCoursePictureBrowser;
    var appNewCourse = {$appNewCourseForJS};
    var videoFileAbsolutePath = "{$videoFileAbsolutePath}";
    var appCsid = "{$appCsid}";
    var selGpIDs = [{$selgpids}];
    var pselGpIDs = [{$pselgpids}];
    var selteach = [{$selteach}];
    var selassistant = [{$selassistant}];
    var selinstructor = [{$selinstructor}];
    var nowlang = "{$nowlang}";
    var show_ref = "{$data.show_ref}";
    var course_type = "{$data.course_type}";
    var editLimit = "{$editLimit}";
    var msg_mp4 = "{'msg_mp4'|WM_Lang}";
    var msg_title = "{'msg_title'|WM_Lang}";
    var msg_url = "{'msg_url'|WM_Lang}";
    var msg_begdate = "{'msg_begdate'|WM_Lang}";
    var msg_endate = "{'msg_endate'|WM_Lang}";
    var self_study = "{'self_study'|WM_Lang}";
    var counseling_programs = "{'counseling_programs'|WM_Lang}";
    var msg_setpic = "{'msg_setpic'|WM_Lang}";
    var msg_started_course = "{'btn_started_course'|WM_Lang}";
    var msgCreateSuccess = "{'msg_create_success'|WM_Lang}";
    var btn_more_lang = "{'more_lang'|WM_Lang}";
    var btn_less_lang = "{'less_lang'|WM_Lang}";
    var MSG_SAVE_FAIL = '{$msg_save_fail}', MSG_SAVE_SUCCESS = '{$msg_save_success}';
    var enployOptionVal = '{$data.en_option}';
    var studyOptionVal = '{$data.st_option}';
    var use_calander = '{$use_calander}';
    
    var MSG_DELETE_SUCCESS = "{'msg_delete_success'|WM_Lang}";
    var MSG_DELETE_FAIL    = "{'msg_delete_fail'|WM_Lang}";
    var MSG_DELETE_NOTHING = "{'msg_delete_nothing'|WM_Lang}";
    var btnClearText = "{'btn_clear_text'|WM_Lang}";
    
    var sid = '{$sid}';
    var cid = '{$cid}';
    var env = '{$env}';
	
    function courseMemberAclSetting() {ldelim}
        {if $editLimit eq 2}
            return;
        {elseif $is_teacher eq 'Y'}
            $('#tagsTeacher span[data-role="remove"]').remove();
        {else}
            $('#tagsTeacher span[data-role="remove"]').remove();
            $('#tagsInstructor span[data-role="remove"]').remove();
            $('#tagsAssistant span[data-role="remove"]').remove();
        {/if}
    {rdelim}
	
    {literal}
        $(document).ready(function() {
            // 解決 IE8 placeholder 不能顯示問題
            $('input').placeholder();

            var options = {
                dataType: 'json',
                beforeSubmit: showRequest,
                success: showResponse
            };
            $('#intro-upload').ajaxForm(options);

            // 課程群組視窗
            /*
            $("a#sel_group").fancybox({
                'padding': 0,
                'margin': 0,
                'modal': true
            });
            */

            // 平台課程群組視窗
            $("a#sel_poratl_group").fancybox({
                'padding': 0,
                'margin': 0,
                'modal': true
            });

            // 參考資料視窗
            $("a#edit_ref").fancybox({
                'padding': 0,
                'margin': 0,
                'modal': true
            });

            // 上傳視窗
            $("a#upload_mv").fancybox({
                'padding': 0,
                'margin': 0,
                'modal': true
            });

            // 未設定課程類型時顯示的區塊
            if (course_type == '' && editLimit > 3) {
                $(".showlist").hide();
                $('.course-type').show();
                $('.course-group').show();
                $('.portal-group').show();
                $('#next_btn').show();
                $('#course_detail').hide();
                $('#open_btn').hide();
            } else {
                $('#next_btn').hide();
            }

            // 通過條件checkbox
            var cbxVehicle = new Array();
            $("input:checkbox:checked[name='sel_formal[]']").each(function(i) {
                cbxVehicle[i] = this.value;
            });
            if (0 < cbxVehicle.length) {
                $("#chk_formal_all").prop("checked", true)
                    .parent().find('.chk-icon').eq(0).removeClass('icon-check-off').addClass('icon-check-on');
            }
            $("input[name='sel_formal[]']").bind("click", function() {
                var count = $("input[name='sel_formal[]']").length;

                var cbxVehicle = new Array();
                $("input:checkbox:checked[name='sel_formal[]']").each(function(i) {
                    cbxVehicle[i] = this.value;
                });

                if (0 == cbxVehicle.length) {
                    $("#chk_formal_all").prop("checked", false)
                        .parent().find('.chk-icon').eq(0).removeClass('icon-check-on').addClass('icon-check-off');
                } else {
                    $("#chk_formal_all").prop("checked", true)
                        .parent().find('.chk-icon').eq(0).removeClass('icon-check-off').addClass('icon-check-on');
                }
            });

            // 可見習通過條件checkbox
            var cbxVehicle = new Array();
            $("input:checkbox:checked[name='sel_gallery[]']").each(function(i) {
                cbxVehicle[i] = this.value;
            });
            if (0 < cbxVehicle.length) {
                $("#chk_gallery_all").prop("checked", true)
                    .parent().find('.chk-icon').eq(0).removeClass('icon-check-off').addClass('icon-check-on');
            }
            $("input[name='sel_gallery[]']").bind("click", function() {
                var count = $("input[name='sel_gallery[]']").length;

                var cbxVehicle = new Array();
                $("input:checkbox:checked[name='sel_gallery[]']").each(function(i) {
                    cbxVehicle[i] = this.value;
                });

                if (0 == cbxVehicle.length) {
                    $("#chk_gallery_all").prop("checked", false)
                        .parent().find('.chk-icon').eq(0).removeClass('icon-check-on').addClass('icon-check-off');
                } else {
                    $("#chk_gallery_all").prop("checked", true)
                        .parent().find('.chk-icon').eq(0).removeClass('icon-check-off').addClass('icon-check-on');
                }
            });


            // tab切換css
            $("#set-tab li a").click(function() {
                $("#set-tab li").removeClass('set-li-active');
                $(this).parent().addClass('set-li-active');
                $("#set-click").children().removeClass('setup-active');
            });

            $("#set-click").click(function() {
                $("#set-tab li").removeClass('set-li-active');
                $(this).children().addClass('setup-active');
            });

            // 預設顯示課程資訊頁
            show_page('info');

            // 課程分類tags
            $('#course_kind').tagsinput({
                itemValue: 'value',
                itemText: 'text'
            });

            // 平台課程分類tags
            $('#portal_kind').tagsinput({
                itemValue: 'value',
                itemText: 'text'
            });

            // 老師權限tags
            $('#teach_auth').tagsinput({
                itemValue: 'value',
                itemText: 'text'
            });

            // 助教權限tags
            $('#assistant_auth').tagsinput({
                itemValue: 'value',
                itemText: 'text'
            });

            // 講師權限tags
            $('#instructor_auth').tagsinput({
                itemValue: 'value',
                itemText: 'text'
            });

            // 學習目標刪除按鈕
            $(".remove_icon").click(function() {
                $(this).prev('input').hide();
                $(this).hide();
                $(this).next('br').hide();
                $(this).prev('input').val('');
            });

            // 參考資料移除按鈕
            $(".remove_icon1").click(function() {
                $(this).prev('input').hide();
                $(this).hide();
                $(this).next('br').hide();
                $(this).prev('input').val('');
                $(this).prev('input').prev('input').val('');
                if (!$('#ref_area input').is(":visible")) {
                    $('#ref_area').css('padding-top', 10);
                }
            });

            // 參考資料fix
            if (show_ref) {
                $('#ref_area').css('padding', 0);
            }

            // 預設勾選課程分類
            /*
            $("input:checkbox[name='kinds[]']").each(function(i) {
                for (var p = 0; p < selGpIDs.length; p++) {
                    if (this.value == selGpIDs[p]) {
                        $(this).attr('checked', true);
                    }
                }
            });
        
            $("input:checkbox:checked[name='kinds[]']").each(function(i) {
                var text = $(this).parent().next().html();
                $('#course_kind').tagsinput('add', {
                    "value": this.value,
                    "text": text,
                    "continent": this.id
                });
            });

            $('#course_kind').on('itemRemoved', function(event) {
                $("#" + event.item.continent).prop("checked", false);
            });
            */
		    
            var selGpArray = json2array(selGpIDs);

            for (var i = 0; i < selGpArray.length; i++) {
                $('#course_kind').tagsinput('add', selGpArray[i]);
            }

            if (env === 'teach') {
                $('#saveForm1').find("span[data-role='remove']").remove();
            }

            var teachArray = json2array(selteach);

            for (var i = 0; i < teachArray.length; i++) {
                $('#teach_auth').tagsinput('add', teachArray[i]);
            }

            var instructorArray = json2array(selinstructor);
            for (var i = 0; i < instructorArray.length; i++) {
                $('#instructor_auth').tagsinput('add', instructorArray[i]);
            }

            var assistantArray = json2array(selassistant);
            for (var i = 0; i < assistantArray.length; i++) {
                $('#assistant_auth').tagsinput('add', assistantArray[i]);
            }

            courseMemberAclSetting();

            /* 用 icon 來控制資料是否顯示的 checkbox */
            $(".chk-icon").on('click', function() {
                var obj = $(this);
                var chk = obj.parent().find('input[type="checkbox"]');
                if (chk.is(":checked")) {
                    obj.attr('class', 'chk-icon icon-check-off');
                    chk.prop('checked', false);
                } else {
                    obj.attr('class', 'chk-icon icon-check-on');
                    chk.prop('checked', true);
                }
                chk.trigger("onchange");
                // chk.trigger("blur");
            });

            switchEnployTimeSetting(enployOptionVal, false);
            switchStudyTimeSetting(studyOptionVal, false, use_calander);

            $('#en_begin').datepicker({
                changeMonth: true,
                changeYear: true,
                numberOfMonths: 1,
                dateFormat: 'yy-mm-dd',
                showButtonPanel: true,
                beforeShow: function(input) {
                    setTimeout(function() {
                        var buttonPane = $(input).datepicker("widget").find(".ui-datepicker-buttonpane");
                        var btn = $('<button class="ui-datepicker-current ui-state-default ui-priority-secondary ui-corner-all" type="button">' + btnClearText + '</button>');
                        btn.unbind("click").bind("click", function() {
                            $.datepicker._clearDate(input);
                        });
                        btn.appendTo(buttonPane);
                    }, 1);
                }
            });

            $('#en_end').datepicker({
                changeMonth: true,
                changeYear: true,
                numberOfMonths: 1,
                dateFormat: 'yy-mm-dd',
                showButtonPanel: true,
                beforeShow: function(input) {
                    setTimeout(function() {
                        var buttonPane = $(input).datepicker("widget").find(".ui-datepicker-buttonpane");
                        var btn = $('<button class="ui-datepicker-current ui-state-default ui-priority-secondary ui-corner-all" type="button">' + btnClearText + '</button>');
                        btn.unbind("click").bind("click", function() {
                            $.datepicker._clearDate(input);
                        });
                        btn.appendTo(buttonPane);
                    }, 1);
                }
            });

            $('#st_begin').datepicker({
                changeMonth: true,
                changeYear: true,
                numberOfMonths: 1,
                dateFormat: 'yy-mm-dd',
                showButtonPanel: true,
                beforeShow: function(input) {
                    setTimeout(function() {
                        var buttonPane = $(input).datepicker("widget").find(".ui-datepicker-buttonpane");
                        var btn = $('<button class="ui-datepicker-current ui-state-default ui-priority-secondary ui-corner-all" type="button">' + btnClearText + '</button>');
                        btn.unbind("click").bind("click", function() {
                            $.datepicker._clearDate(input);
                        });
                        btn.appendTo(buttonPane);
                    }, 1);
                }
            });

            $('#st_end').datepicker({
                changeMonth: true,
                changeYear: true,
                numberOfMonths: 1,
                dateFormat: 'yy-mm-dd',
                showButtonPanel: true,
                beforeShow: function(input) {
                    setTimeout(function() {
                        var buttonPane = $(input).datepicker("widget").find(".ui-datepicker-buttonpane");
                        var btn = $('<button class="ui-datepicker-current ui-state-default ui-priority-secondary ui-corner-all" type="button">' + btnClearText + '</button>');
                        btn.unbind("click").bind("click", function() {
                            $.datepicker._clearDate(input);
                        });
                        btn.appendTo(buttonPane);
                    }, 1);
                }
            });
        });

        function showRequest(formData, jqForm, options) {
            if (window.console != null) {
                /*console.log(formData);*/
            }
            var obj = document.getElementById('intro-upload');
            var pngRe = /\.(mp4)$/i;
            txt = obj.intro_file.value;
            if (txt.length > 0) {
                if (!pngRe.test(txt)) {
                    alert(msg_mp4);
                    obj.intro_file.focus();
                    return false;
                }
            }

        }

        // 上傳課程影片後
        function showResponse(responseText, statusText, xhr, form) {
            if (window.console != null) {
                /*console.log(responseText);*/
            }

            if (window.console) {console.log(responseText, statusText, xhr, form);}

            var timestamp = new Date().getTime();
            $("#coursePictureArea2 img").attr('src', '/base/' + sid + '/course/' + cid + '/content/public/course_introduce.jpg?' + timestamp);
            close_fancy();
            alert(responseText.msg);
        }


        // 下一步
        function next_step() {
            $('.showlist').show();
            $('.course-type').hide();
            $('.course-group').hide();
            $('.portal-group').hide();
            $('#course_detail').show();
            $('#open_btn').show();
            $('#next_btn').hide();
        }

        // 取消
        function cancel() {
            $('.showlist').show();
            $('.course-type').hide();
            $('.course-group').hide();
            $('.portal-group').hide();
            $(".set-button").hide();
        }

        // 課程分類確定鈕
        /*
        function group_tags() {
            close_fancy();
            $('#course_kind').tagsinput('removeAll');
            $("input:checkbox:checked[name='kinds[]']").each(function(i) {
                var text = $(this).parent().next().html();
                $('#course_kind').tagsinput('add', {
                    "value": this.value,
                    "text": text,
                    "continent": this.id
                });
            });

            $('#course_kind').on('itemRemoved', function(event) {
                $("#" + event.item.continent).prop("checked", false);
            });
        }
        */

        // 平台課程分類確定鈕
        function portal_group_tags() {
            close_fancy();
            $('#portal_kind').tagsinput('removeAll');
            $("input:checkbox:checked[name='pkinds[]']").each(function(i) {
                var text = $(this).parent().next().html();
                $('#portal_kind').tagsinput('add', {
                    "value": this.value,
                    "text": text,
                    "continent": this.id
                });
            });

            $('#portal_kind').on('itemRemoved', function(event) {
                $("#" + event.item.continent).prop("checked", false);
            });
        }

        // 學習目標新增鈕
        function add_item(kind) {
            $('#' + kind + '_area').append('<input type="text" name="' + kind + '[]" style="width:560px;margin-bottom:12px;"><div class="remove_icon"></div><br>');
            $(".remove_icon").click(function() {
                $(this).prev('input').hide();
                $(this).hide();
                $(this).next('br').hide();
                $(this).prev('input').val('');
            });
        }

        // 參考資料新增處理
        function ref_process() {
            var title = $('#title').val();
            var addr = $('#addr').val();

            if ('' == title) {
                alert(msg_title);
                return false;
            }

            if ('' == addr) {
                alert(msg_url);
                return false;
            }

            $('#ref_area').append('<input type="hidden" name="r_addr[]" value="' + addr + '"><input type="text" name="r_title[]" value="' + title + '" style="width:560px;font-size:13px;color:#08b1b3;text-decoration:underline;" readonly><div class="remove_icon1"></div><br>');
            $('#ref_area').css('padding', 0);
            $(".remove_icon1").click(function() {
                $(this).prev('input').hide();
                $(this).hide();
                $(this).next('br').hide();
                $(this).prev('input').val('');
                $(this).prev('input').prev('input').val('');
                if (!$('#ref_area input').is(":visible")) {
                    $('#ref_area').css('padding-top', 10);
                }

            });

            $('#title').val('');
            $('#addr').val('');

            close_fancy();
        }



        function uploadFiles() {
            $('#shit-upload').submit();
        }

        // 關閉 fancybox
        function close_fancy() {
            $.fancybox.close();
        }

        function switchEnployTimeSetting(val, init) {
            if (val == 0) {
                $('.enployTimeSelect').prop('disabled', 'disabled');
            } else {
                $('.enployTimeSelect').prop('disabled', false);
                
                // 當「單選」指定日期radio時
                if (init) {
                    var y = '';
                    var m = '';
                    var d = '';
                    if ($('#en_begin').val() == '') {
                        y = new Date().getFullYear();
                        m = new Date().getMonth() + 1;
                        if (m < 10) m = '0' + m;
                        $('#en_begin').val(y + '-' + m + '-01');
                    }

                    if ($('#en_end').val() == '') {
                        y = new Date().getFullYear();
                        m = new Date().getMonth() + 1;
                        if (m < 10) m = '0' + m;
                        d = new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0, 23, 59, 59).getDate();
                        if (d < 10) d = '0' + d;
                        $('#en_end').val(y + '-' + m + '-' + d);
                    }
                }
            }
        }

        function switchStudyTimeSetting(val, init, use) {
            if (val == 0) {
                $('.studyTimeSelect').prop('disabled', 'disabled');
                $('#ck_sync_st_end').prop('disabled', 'disabled');
                $('#ck_sync_st_end').prop('checked', false);

            } else {
                $('.studyTimeSelect').prop('disabled', false);
                $('#ck_sync_st_end').prop('disabled', false);
                $('#ck_sync_st_end').prop('checked', true);

                if (use) {
                    $('#ck_sync_st_end').prop('checked', true);
                } else {
                    $('#ck_sync_st_end').prop('checked', false);
                }

                // 當「單選」指定日期radio時
                if (init) {
                    var y = '';
                    var m = '';
                    var d = '';
                    if ($('#st_begin').val() == '') {
                        y = new Date().getFullYear();
                        m = new Date().getMonth() + 1;
                        if (m < 10) m = '0' + m;
                        $('#st_begin').val(y + '-' + m + '-01');
                    }

                    if ($('#st_end').val() == '') {
                        y = new Date().getFullYear();
                        m = new Date().getMonth() + 1;
                        if (m < 10) m = '0' + m;
                        d = new Date(new Date().getFullYear(), new Date().getMonth() + 1, 0, 23, 59, 59).getDate();
                        if (d < 10) d = '0' + d;
                        $('#st_end').val(y + '-' + m + '-' + d);
                    }
                }
            }
        }

        //儲存
        function save_step(step) {
            console.log('im in save_step');
            console.log(step);
            if (1 == step) { // 已確認課程類型執行.

                if ($("input[name='en_option']:checked").val() == 0) {
                    $('#en_begin').val('');
                    $('#en_end').val('');
                }

                if ($("input[name='st_option']:checked").val() == 0) {
                    $('#st_begin').val('');
                    $('#st_end').val('');
                }

            } else if (99 == step) { // 都還沒確認課程類型執行.
                step = 0;
                var next = 1;
            }

            $.ajax({
                url: "/teach/course/m_course_save.php",
                dataType: 'json',
                data: $("#saveForm" + step).serialize(),
                type: "POST",
                success: function(response) {
                    // 移除錯誤提示
                    $("#saveForm" + step + " input, textarea, select").tooltip('destroy').removeClass('alert-lcms-error');
                    hideMessage();
                    if (false == response.flag) {
                        if (response.error != '' && response.error != null) {
                            var msg = response.error;
                            /* 後端驗證顯示訊息 */
                            var firstObj;
                            $.each(msg, function(i, v) {
                                var errorObj;
                                if (v.id == "content") {
                                    errorObj = $("#saveForm" + step + " textarea[name='" + v.id + "']");
                                } else if (v.id === 'en_begin' || v.id === 'st_begin') {
                                    errorObj = $("#saveForm" + step + " select[name='" + v.id + "']");
                                } else {
                                    errorObj = $("#saveForm" + step + " input[name='" + v.id + "']");
                                }
                                if (i == 0) {
                                    firstObj = errorObj;
                                }

                                errorObj.attr('data-original-title', v.message)
                                    .tooltip('toggle')
                                    .addClass('alert-lcms-error');
                            });
                            firstObj.focus();
                            return;
                        } else {
                            showMessage('error', step, response.text);
                        }
                    } else {
                        if (appNewCourse) {
                            alert(msgCreateSuccess);
                            document.editFm.ticket.value = response.text;
                            document.editFm.csid.value = response.id;
                            document.editFm.submit();
                            response.text = msgCreateSuccess;
                        }
                        showMessage('success', step, response.text);
                        if (step == 1) {
                            var arr_kinds = [];
                            $("input:checkbox:checked[name='kinds[]']").each(function(i) {
                                var text = $(this).parent().next().html();
                                arr_kinds.push(text);

                            });
                        } else if (step == 5) {
                            $("#btnOpenCs").addClass('disabled').removeClass('btn-danger').text(msg_started_course);
                        }

                        if (next == 1) {
                            next_step();
                        }
                    }

                }
            });
        }

        // 通過條件全選
        function check_all(type) {
            if ($("#chk_" + type + "_all").prop("checked")) {
                $("input[name='sel_" + type + "[]']").each(function() {
                    $(this).prop("checked", true);
                });
            } else {
                $("input[name='sel_" + type + "[]']").each(function() {
                    $(this).prop("checked", false);
                });
            }
        }

        // 選擇課程類型區塊編輯鈕
        function show_edit(page) {
            $("#" + page + " > .showlist").hide();
            $("." + page).show();
            $(".set-button").show();
        }

        // 顯示tabs
        function show_page(tab) {
            $(".course_info").hide();
            hideMessage();
            $("#" + tab).show();
        }

        // 課程群組展開
        function Expand(pid) {
            var obj = document.getElementById("mGroup_" + pid);
            if (obj != null) {
                if (obj.style.display == "none") {
                    obj.style.display = "";
                    obj = document.getElementById("micon_" + pid);
                    if (obj != null) {
                        obj.src = "/public/images/course_set/-.png";
                    }
                } else {
                    obj.style.display = "none";
                    obj = document.getElementById("micon_" + pid);
                    if (obj != null) {
                        obj.src = "/public/images/course_set/+.png";
                    }
                }
            }
        }

        // 平台課程群組展開
        function pExpand(pid) {
            var obj = document.getElementById("pGroup_" + pid);
            if (obj != null) {
                if (obj.style.display == "none") {
                    obj.style.display = "";
                    obj = document.getElementById("picon_" + pid);
                    if (obj != null) {
                        obj.src = "/public/images/course_set/-.png";
                    }
                } else {
                    obj.style.display = "none";
                    obj = document.getElementById("picon_" + pid);
                    if (obj != null) {
                        obj.src = "/public/images/course_set/+.png";
                    }
                }
            }
        }
    {/literal}
    {if $editLimit eq 2}
        {literal}
            // 新增老師
            function select_teacher() {
                var dValue = $('#teach_auth').tagsinput('items');
                dValue = JSON.stringify(dValue);
                var win = new WinMTeacherSelect('setTeacherValue', encodeURIComponent(dValue));
                win.run();
            }

            function setTeacherValue(arr) {
                // $('#teach_auth').tagsinput('removeAll');

                var nameArray = arr[1].split(',');
                var idArray = arr[0].split(',');
                $('#teach_auth').tagsinput('removeAll');
                for (var i = 0; i < idArray.length; i++) {
                    $('#teach_auth').tagsinput('add', { "value": idArray[i] , "text": nameArray[i] }); 
                }

            }
        {/literal}
    {/if}
    {if $editLimit > 0}
        {literal}
            // 新增助教
            function select_assistant() {
                var dValue = $('#assistant_auth').tagsinput('items');
                dValue = JSON.stringify(dValue);
                var win = new WinMTeacherSelect('setAssistantValue', encodeURIComponent(dValue));
                win.run();
            }

            function setAssistantValue(arr) {

                // $('#assistant_auth').tagsinput('removeAll');

                var nameArray = arr[1].split(',');
                var idArray = arr[0].split(',');
                $('#assistant_auth').tagsinput('removeAll');
                for (var i = 0; i < idArray.length; i++) {
                    $('#assistant_auth').tagsinput('add', {
                        "value": idArray[i],
                        "text": nameArray[i]
                    });
                }

            }
        {/literal}
    {/if}
    {if $editLimit > 0}
        {literal}
        // 新增講師
        function select_instructor() {
            var dValue = $('#instructor_auth').tagsinput('items');
            dValue = JSON.stringify(dValue);

            var win = new WinMTeacherSelect('setInstructorValue', encodeURIComponent(dValue));
            win.run();
        }

        function setInstructorValue(arr) {

            // $('#instructor_auth').tagsinput('removeAll');

            var nameArray = arr[1].split(',');
            var idArray = arr[0].split(',');
            $('#instructor_auth').tagsinput('removeAll');
            for (var i = 0; i < idArray.length; i++) {
                $('#instructor_auth').tagsinput('add', {
                    "value": idArray[i],
                    "text": nameArray[i]
                });
            }

        }
        {/literal}
    {/if}
    {literal}
        /* APP - 課程圖片設定 - Begin */
        /**
         * 課程圖片設定
         * 取得瀏覽的檔名。※※※※ 函數名稱不可改 ※※※※
         */
        function setPictureFilename(filename, classify) {
            appCoursePictureAction(appCsid, 'setup', filename, classify);
        };

        /**
         * 開啟圖片檔案列表
         **/
        function appCoursePictureBrowseFile() {
            var browserWidth = parseInt(screen.width * 0.6);
            var browserHeight = parseInt(screen.height * 0.6);
            var browserStyle = 'width=' + browserWidth + ',height=' + browserHeight + ',status=0,toolbar=0,menubar=0,scrollbars=1,resizable=1';

            appCoursePictureBrowser = window.open('/lib/app_listfiles.php?from=course', 'appCoursePictureBrowser', browserStyle);
            if (appCoursePictureBrowser.closed === false) {
                // 已經開啟，則focus就好
                appCoursePictureBrowser.focus();
            }
        }

        /**
         * 處理課程圖片的AJAX動作
         * @param {String} courseId: 編碼後的課程ID
         * @param {String} action: setup / remove
         * @param {String} filename: 圖片檔案名稱
         * @param {String} classify: 分類：公用/私人
         **/
        function appCoursePictureAction(courseId, action, filename, classify) {
            var txt, $xml;

            if (courseId != '' && action != '') {
                // 課程編號與動作代號不為空，才可以處理圖片設定
                txt = "<manifest>";
                txt += "<cid>" + courseId + "</cid>";
                txt += "<file>" + filename + "</file>";
                txt += "<action>" + action + "</action>";
                txt += "<classify>" + classify + "</classify>";
                txt += "</manifest>";
                $xml = $.parseXML(txt);
                $.ajax(
                    '../../lib/app_course_picture_ajax.php', {
                        'type': 'POST',
                        'processData': false,
                        'data': $xml,
                        'dataType': 'html',
                        'success': function(data) {
                            if (typeof appCoursePictureBrowser !== 'undefined' && appCoursePictureBrowser.closed === false) {
                                // 如果瀏覽檔案的對話視窗沒有關閉，則關閉之
                                appCoursePictureBrowser.close();
                            }
                            if (data === 'fail') {
                                alert(MSG_COURSE_PICTURE_FAIL);
                            } else {
                                d = new Date();
                                top.frames[appFrameId].$('#coursePicture').attr('src', '/lib/app_show_course_picture.php?' + d.getTime() + '&courseId=' + courseId);
                                if (action === 'remove') {
                                    // 成功移除圖片後，disable移除的按鈕
                                    top.frames[appFrameId].$('#appCourseImageRemove').attr('disabled', 'disabled');
                                } else {
                                    // 成功設定圖片後，將移除按鈕的disable拿掉
                                    top.frames[appFrameId].$('#appCourseImageRemove').removeAttr('disabled');
                                }
                                if (appNewCourse === false) {
                                    alert(msg_setpic);
                                }
                            }
                        }
                    }
                );
            }
            txt = null;
        }
        /* APP - 課程圖片設定 - End */

        // 顯示訊息
        function showMessage(type, step, errstr) {
            var alertType = '';
            if (type == 'success') {
                alertType = 'alert-success';
            } else if (type == 'error') {
                alertType = 'alert-error';
            }
            if (step == 0) {
                var obj = $("#error-msg-1");
            } else {
                var obj = $("#error-msg-2");
            }
            obj.find('.alert').remove();
            var errcontent = $('<div class="alert ' + alertType + '" style="text-align: center;"></div>').append('<button type="button" class="close" data-dismiss="alert">×</button>')
                .append(errstr);
            obj.append(errcontent).fadeIn("slow");
            // 修正置頂位置
            var new_position = obj.offset();
            window.scrollTo(new_position.left, new_position.top - 50);
        }
        // 顯示訊息
        function hideMessage() {
            $("#error-msg-1, #error-msg-2").hide();
        }

        // 顯示更多語言
        function showOtherLangTitle(btn) {
            if ($('.otherLangTitle').is(':visible')) {
                $('.otherLangTitle').hide();
                btn.innerText = btn_more_lang;
            } else {
                $('.otherLangTitle').show();
                btn.innerText = btn_less_lang;
            }
        }

        //顯示教材使用
        function showContent(val) {
            if (val) {
                $('#span_content').show();
            } else {
                $('#span_content').hide();
            }
        }

        // 教材類別 (content)
        function select_content() {
            var win = new WinContentSelect('setContentValue');
            win.run();
        }

        function setContentValue(arr) {
            if (typeof(arr[1]) != 'undefined') {
                document.getElementById('QueryTxt').value = arr[1];
            }
            if (typeof(arr[0]) != 'undefined') {
                document.getElementById('content_id').value = arr[0];
            }
        }

        /* 刪除課程代表圖 */
        $(function() {
            $('.dropbtn').click(function() {
                $.ajax({
                    'type': 'POST',
                    'url': '/mooc/controllers/course_ajax.php',
                    'data': {'action': 'delCoursePic', 'csid': $("input[name='csid']").val()},
                    'dataType': 'json',
                    'success': function(data) {
                        if (data.code === 1) {
                            alert(MSG_DELETE_SUCCESS);
                            var osrc = $('#coursePicture').attr('src');
                            var timestamp = new Date().getTime();
                            if (osrc.indexOf('&timestamp=') === -1) {
                                $('#coursePicture').attr('src', osrc + '&timestamp=' + timestamp);
                            } else {
                                $('#coursePicture').attr('src', osrc.substr(0, osrc.indexOf('&timestamp=')) + '&timestamp=' + timestamp);
                            }
                        } else if (data.code === 0) {
                            alert(MSG_DELETE_NOTHING);
                        } else {
                            alert(MSG_DELETE_FAIL);
                        }
                    },
                    'error': function() {
                        if (window.console) {console.log('error');}
                    }
                });
            });
        });

        /* 刪除課程介紹影片 */
        $(function() {
            $('#delete_mv').click(function() {
                $.ajax({
                    'type': 'POST',
                    'url': '/mooc/controllers/course_ajax.php',
                    'data': {'action': 'delCourseMv', 'csid': $("input[name='csid']").val()},
                    'dataType': 'json',
                    'success': function(data) {
                        if (data.code === 1) {
                            alert(MSG_DELETE_SUCCESS);
                            var timestamp = new Date().getTime();
                            // <img src="/base/10001/course/10000001/content/public/course_introduce.jpg?1471848381">
                            $('#coursePictureArea2 img').attr('src', '/theme/default/app/default-course-picture.jpg?' + timestamp);
                        } else if (data.code === 0) {
                            alert(MSG_DELETE_NOTHING);
                        } else {
                            alert(MSG_DELETE_FAIL);
                        }
                    },
                    'error': function() {
                        if (window.console) {console.log('error');}
                    }
                });
            });
        });
    {/literal}
</script>