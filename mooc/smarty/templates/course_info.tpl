{include file = "common/tiny_header.tpl"}
{include file = "common/site_header.tpl"}
<style type="text/css">
{literal}
#course_info_header {
    background-repeat: no-repeat;
    background-position: 50% 20%;
    background-size: cover;
}

#course_info_container {
    padding-left: 0px;
    padding-right: 0px;
}

#course_info_container_row {
    margin:30px 0px 0px 0px;
}

.course-image-1 img {
    width: 384px;
    height: 217px;
}

.course-image {
    max-width: 392px;
    max-height: 224px;
    border: 4px solid #FFFFFF;
    border-radius: 5px;
    padding: 0px;
}

.jp-video-360p{
    width:100% !important;
    height:217px;
    max-width: 384px;
    max-height: 217px;
    border: 0;
}

.jp-video-play{
    width:100%;
    height:217px;
    max-width: 384px;
    max-height: 217px;
    margin-top:-225px !important;
}

.jp-video-play-icon {
    margin-left: -385px;
}

.course-name {
    font-family: "微軟正黑體", Arial, Helvetica, sans-serif;
    font-size: 32px;
    color: #FFFFFF;
    font-weight: bold;
    line-height: 32px;
    white-space: nowrap;
    word-wrap: break-word;
    overflow: hidden;
    text-overflow: ellipsis;
}

.course-time {
    font-family: "微軟正黑體", Arial, Helvetica, sans-serif;
    color: #FFFFFF;
    font-size: 18px;
    font-weight: normal;
    line-height: 32px;
    text-align: left;
}

.qrcode {
    margin-top: 15px;
}

.qrcode .share .pic {
    width: 40px;
    border: 1px solid rgba(255,255,255,0.4);
    border-radius: 2px;
    margin-right: 14px;
    text-align: center;
    padding: 5px;
}

.qrcode .share > .pic > a > div {
    margin: 0 auto;
}

#main {
    min-height: calc(100vh - 300px);
}

#course_info_content {
    background-color: #FFFFFF;
}

#course_tab_course_set {
    margin-left: 4em;
}

.course_info {
    display:none;
    text-align:left;
    background-color: #FFFFFF;
}

.course-content {
    text-align:left;
    color: #000;
    line-height: 20px;
}

.course-content pre{
    background-color: transparent;
    border-width: 0;
    font-family: '微軟正黑體', Arial, Helvetica, sans-serif;
    font-size: 16px;
    color: #5a5a5a;
    line-height: 2em;
    padding: 0px;
}

.course-subtitle, .course-goal,.course-aud,.course-ref,.course-pass,.course-gallery-pass {
    text-align:left;
    color: #000;
    font-size: 1.1em;
    line-height: 20px;
    word-break: break-all;
    padding: 1em;
}

.font-red img, .font-red span {
    vertical-align: middle;
}

.font-red {
    font-size: 1.2em;
    color: #F00;
    font-weight: bold;
}

.course-set-circle {
    background-image: url('/public/images/course_info/arrangement_67.gif');
    height:25px;
    width:25px;
    display:inline-block;
    vertical-align: middle;
    border:0;
}

.course-set {
    display:inline-block;
    margin-left: 20px;
    background-color: #cccccc;
    border-radius: 23px;
    width: 580px;
    color:#000000;
    font-size:1.2em;
    min-height: 35px;
    line-height: 35px;
    text-align:center;
    overflow: hidden;
    vertical-align: middle;
}

.course-set-line {
    background-image: url('/public/images/course_info/arrangement_70.gif');
    background-repeat: no-repeat;
    height: 30px;
    margin-top: 13px;
}

.teacher {
    background-color: #FFFFFF;
    border: none;
    padding-top:30px;
}

.teach-list {
    border: none;
    position: relative;
    width: 152px;
    margin:auto;
}

.teach-pic {
    border: none;
    height: 152px;
    width: 152px;
    float:left;
}

.teach-pic img {
    height: 150px;
    width: 150px;
    border: #FFFFFF 1px solid;
    border-radius: 50%;
}

.teach-data {
    border: none;
    font-size:18px;
    font-weight: bold;
    color: #5a5a5a;
    line-height: 42px;
    text-align: center;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    width: 150px;
}

#course_info_relative{
    background-color: #EDEDED;
}

/*large Desktop*/
@media (min-width: 1200px) {
}

/*平板直向、平板橫向*/
@media (min-width: 768px) and (max-width: 992px) {
}

/*5.5 手機尺寸 - 直式*/
@media (max-width: 414px) {
    #course_info_container_row {
        margin:15px;
        text-align: center;
    }

    .course-image-1 img {
        max-width: 100%;
    }

    .course-name {
        margin-top: 15px;
        white-space: initial;
    }

    .course-time {
        text-align: center;
    }

    .share {
        display: inline-table;
    }

    #course_tab_course_set {
        margin-left: 1em;
    }

    .course_info > ul {
        padding-left: 0px;
    }

    .course-set {
        margin-left : 10px;
        width: 85%;
    }

    .course-set-line {
        height: 0px;
    }
}

/*5.5 手機尺寸 - 直式*/
@media (max-width: 320px) {
    #course_tab_course_intro {
        font-size:24px;
    }

    #course_tab_course_set {
        font-size:24px;
    }
}


{/literal}
</style>
<link href="/lib/jplayer/skin/blue.monday/jplayer.blue.monday.css" rel="stylesheet" type="text/css" />
<script type="text/javascript" src="{$appRoot}/lib/jplayer/jquery.jplayer.min.js"></script>
<div id="main">
<div id="course_info_header" style="background-image: url(/lib/app_show_course_picture.php?courseId={$courseData.cpic}{if $assignSch neq $schoolId}&sId={$courseData.spic}{/if});">
    <div style="width:100%;background-color: rgba(0,0,0,0.6);">
    <div id="course_info_container" class="container">
        <div id="course_info_container_row" class="row">
            <div class="course-image col-lg-5 col-md-5 col-sm-12 col-xs-12">
                <div class="course-image-1">
                    {if $courseData.introVideo == ''}
                        <img src="/lib/app_show_course_picture.php?courseId={$courseData.cpic}{if $assignSch neq $schoolId}&sId={$courseData.spic}{/if}" />
                    {else}
                    <div id="jp_container_1" class="jp-video jp-video-360p">
                        <div class="jp-type-playlist">
                            <div id="jquery_jplayer_1" class="jp-jplayer"></div>
                            <div class="jp-gui">
                                <div class="jp-video-play">
                                    <a href="javascript:;" class="jp-video-play-icon" tabindex="1">play</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    {/if}
                </div>
            </div>
            <div class="col-lg-7 col-md-7 col-sm-12 col-xs-12" style="padding-right: 0px;">
                <div class="row">
                    <div class="course-name col-md-12 col-sm-12 col-xs-12" title="{$courseData.caption|WM_Title}">{$courseData.caption|WM_Title}</div>
                </div>
                <div class="row" style="margin-top: 15px;">
                    <div class="col-md-8 col-xs-12" style="padding-left:0px;padding-right: 0px;">
                        {if $courseData.status!=3 && $courseData.status!=4}
                        <div class="course-time col-md-12 col-sm-12 col-xs-12">
                        {'during_registration'|WM_Lang}：{$courseData.enployDateStr}
                        </div>
                        {/if}
                        <div class="course-time col-md-12 col-sm-12 col-xs-12">
                        {'during_counseling'|WM_Lang}：{$courseData.studyDateStr}
                        </div>
                        <div class="qrcode col-md-12 col-sm-12 col-xs-12">
                            <div class="share">{$shareIcon}</div>
                            <div id="inline-ln-{$courseData.course_id}" class="inline-ln">
                                <form class="well">
                                    <div>{'linesharenote'|WM_Lang}</div>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="course-action">
                            {if $profile.username === 'guest'}
                                {if $courseData.enStatus == '1'}{*可報名*}
                                    <button type="button" class="btn btn-primary btn-blue btnAction" onclick="loginreturn();">{'loginsign'|WM_Lang}</button>
                                {else}{*不可報名*}
                                    <button type="button" class="btn btn-primary btn-gray btnAction">{$courseData.enDenyMsg}</button>
                                {/if}
                            {else}
                                {if $courseData.status==1 || $courseData.status==3}{*沒有上課的時間限制*}
                                    {if $courseData.hasMajored ==1 || $courseData.hasMajored ==2}{*正式生 或 旁聽生*}
                                        {if $courseData.hasMajored ==2 && $courseData.status==3}{*使用者身份是旁聽生，但課程是不允許旁聽*}
                                            <button type="button" class="btn btn-primary btn-gray btnAction" onclick="alert('{'notallowattend'|WM_Lang}');">{'notallowattend'|WM_Lang}</button>
                                        {else}
                                            <button type="button" class="btn btn-primary btn-blue btnAction" onclick="gotoCourse('{$courseData.course_id}{if $assignSch neq $schoolId}/{$assignSch}{/if}');">{'attendclass'|WM_Lang}</button>
                                        {/if}
                                    {else}{*使用者沒有學生身份*}
                                        {if $courseData.enStatus == '1'}{*可報名*}
                                            {if $courseData.courseReviewing == '1'}{*報名需要審核*}
                                                <button type="button" class="btn btn-primary btn-gray btnAction">{'enroll_reviewing'|WM_Lang}</button>
                                            {else}{*報名不需要審核，報名即為正式生*}
                                                <button type="button" class="btn btn-primary btn-blue btnAction" onclick="enployCourse({$courseData.course_id}{if $assignSch neq $schoolId},{$assignSch}{/if});">{'signcourse'|WM_Lang}</button>
                                            {/if}
                                        {else}{*不可報名*}
                                            <button type="button" class="btn btn-primary btn-gray btnAction">{$courseData.enDenyMsg}</button>
                                        {/if}
                                    {/if}
                                {elseif $courseData.status==2 || $courseData.status==4}{*有上課的時間限制*}
                                    {if $courseData.hasMajored ==1 || $courseData.hasMajored ==2}{*正式生 或 旁聽生*}
                                        {if $courseData.st_period == '0'}{*可上課(期間內)*}
                                            {if $courseData.hasMajored ==2 && $courseData.status==4}{*使用者身份是旁聽生，但課程是不允許旁聽*}
                                                <button type="button" class="btn btn-primary btn-gray btnAction" onclick="alert('{'notallowattend'|WM_Lang}');">{'notallowattend'|WM_Lang}</button>
                                            {else}
                                                <button type="button" class="btn btn-primary btn-blue btnAction" onclick="gotoCourse('{$courseData.course_id}{if $assignSch neq $schoolId}/{$assignSch}{/if}');">{'attendclass'|WM_Lang}</button>
                                            {/if}
                                        {elseif $courseData.st_period == '1'}{*不可上課(過期)*}
                                            <button type="button" class="btn btn-primary btn-gray btnAction" onclick="alert('{'courseclose'|WM_Lang}。');">{'courseclosed'|WM_Lang}</button>
                                        {elseif $courseData.st_period == '2'}{*不可上課(未開始)*}
                                            <button type="button" class="btn btn-primary btn-gray btnAction" onclick="alert('{'notstart'|WM_Lang}');">{'notstart'|WM_Lang}</button>
                                        {/if}
                                    {else}
                                        {if $courseData.enStatus == '1'}{*可報名*}
                                            {if $courseData.st_period == '1'}{*課程過期*}
                                                <button type="button" class="btn btn-primary btn-gray btnAction" onclick="alert('{'courseclose'|WM_Lang}。');">{'courseclosed'|WM_Lang}</button>
                                            {else}
                                                {if $courseData.courseReviewing == '1'}{*報名需要審核*}
                                                    <button type="button" class="btn btn-primary btn-gray btnAction">{'enroll_reviewing'|WM_Lang}</button>
                                                {else}{*報名不需要審核，報名即為正式生*}
                                                    <button type="button" class="btn btn-primary btn-blue btnAction" onclick="enployCourse({$courseData.course_id}{if $assignSch neq $schoolId},{$assignSch}{/if});">{'signcourse'|WM_Lang}</button>
                                                {/if}
                                            {/if}
                                        {else}{*不可報名*}
                                            <button type="button" class="btn btn-primary btn-gray btnAction">{$courseData.enDenyMsg}</button>
                                        {/if}
                                    {/if}
                                {/if}
                            {/if}
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 col-xs-12">
                        <iframe id="iframe-qrcode" src="{$courseData.qrcode_url}" scrolling="no" style="padding:0px;width:180px;height:180px;border:0px;"></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>
<div id="course_info_content" class="container">
    <div class="col-md-9 col-sm-12 col-xs-12">
        <div id="course-tabs" class="row subtitle heading-bottom-border">
            <div class="col-md-12 col-sm-12 col-xs-12" style="padding-left: 0px;">
                {if $nowlang=='en'}
                    <h1 id="course_tab_course_intro" class="course_categroy_tab active" style="cursor:pointer;font-size:24px" onclick="show_page('course_intro');">{'course_introduction'|WM_Lang}</h1>
                    <h1 id="course_tab_course_set" class="course_categroy_tab" style="cursor:pointer;font-size:24px;" onclick="show_page('course_set');">{'course_plan'|WM_Lang}</h1>
                {else}
                    <h1 id="course_tab_course_intro" class="course_categroy_tab active" style="cursor:pointer;" onclick="show_page('course_intro');">{'course_introduction'|WM_Lang}</h1>
                    <h1 id="course_tab_course_set" class="course_categroy_tab" style="cursor:pointer;" onclick="show_page('course_set');">{'course_plan'|WM_Lang}</h1>
                {/if}
            </div>
        </div>

        <div id="course_intro" class="row course_info">
            {if $courseData.subhead neq null}
            <div class="course-subtitle">
                <h4>{$courseData.subhead}</h4>
            </div>
            {/if}
            <div class="course-content" style="font-size: 16px;color: #5a5a5a;line-height: 2em;word-wrap: break-word;white-space:pre-line;margin-top: -30px;">
                {$courseData.content}
            </div>
            {if $courseData.texts|trim} 
                <div style="font-size: 16px;color: #5a5a5a;line-height: 2em;word-wrap: break-word;white-space:pre-line;">
                    <div style="width:12px;height:12px;background:#60ffcc;display: inline-block;"></div><div style="font-size: 18px;font-weight: bold;color: #5a5a5a;display: inline-block;">&nbsp;{'th_book'|WM_Lang}</div>
                    {$courseData.texts}
                </div>
            {/if}
            {if 'goal'|in_array:$courseData.is_use && $courseData.goal|@count >= 1}
            <div class="course-goal">
                <div class="font-red"><img src="{$appRoot}/public/images/course_info/object_78.png" width="12" height="12"><span>&nbsp;{'learning_objectives'|WM_Lang}</span></div>
                <ul>
                    {foreach from=$courseData.goal key=k_goal item=v_goal}
                    <li>{$v_goal}</li>
                    {/foreach}
                </ul>
            </div>
            {/if}
            {if 'audience'|in_array:$courseData.is_use && $courseData.audience|@count >= 1}
            <div class="course-aud">
                <div class="font-red"><img src="{$appRoot}/public/images/course_info/object_78.png" width="12" height="12"><span>&nbsp;{'learning_objects'|WM_Lang}</span></div>
                <ul>
                    {foreach from=$courseData.audience key=k_audience item=v_audience}
                    <li>{$v_audience}</li>
                    {/foreach}
                </ul>
            </div>
            {/if}
            {if 'ref'|in_array:$courseData.is_use && $courseData.ref_title|@count >= 1}
            <div class="course-ref">
                <div class="font-red"><img src="{$appRoot}/public/images/course_info/object_78.png" width="12" height="12"><span>&nbsp;{'reference_material'|WM_Lang}</span></div>
                <ul>
                    {foreach from=$courseData.ref_title key=k_texts item=v_texts}
                    <a href="{$courseData.ref_url.$k_texts}" target="_blank" style="color:#000;text-decoration:none">
                        <li>{$v_texts}</li>
                    </a>
                    {/foreach}
                </ul>
            </div>
            {/if}
            {if 'formal_score'|in_array:$courseData.is_use || 'formal_time'|in_array:$courseData.is_use || 'formal_process'|in_array:$courseData.is_use}
            <div class="course-pass" style="margin-bottom:10px">
                <div class="font-red"><img src="{$appRoot}/public/images/course_info/object_78.png" width="12" height="12"><span>&nbsp;{'pass_condition'|WM_Lang}</span></div>
                {if 'formal_score'|in_array:$courseData.is_use}
                <div style="margin:0 20px 0 6px;">{'grade1'|WM_Lang}：{$courseData.fair_grade} {'fraction'|WM_Lang}</div>
                {/if}
                {if 'formal_time'|in_array:$courseData.is_use}
                <div style="margin:0 20px 0 6px;">{'reading_time'|WM_Lang}：{$courseData.formal.time} {'unit_hour'|WM_Lang}</div>
                {/if}
                {if 'formal_process'|in_array:$courseData.is_use}
                <div>
                    <div style="margin-left: 6px;float:left;"> {'teaching_schedule'|WM_Lang}：</div>
                    <div style="width:515px;height:14px;float:left;border-radius: 10px;margin-top:3px;border: 1px solid #eee;background-color: #eee;" class="progress progress-warning progress-striped">
                        <div id="progressBar" class="bar" style="background-color: #048e89;width: {$courseData.formal.percent}%;"></div>
                    </div>
                      {$courseData.formal.percent}%
                </div>
                {/if}
            </div>
            {/if}
            {*
            {if 'null'|in_array:$courseData.is_use && $courseData.fair_grade >= 1}
            <div class="course-pass" style="margin-bottom:10px">
                <div class="font-red"><img src="{$appRoot}/public/images/course_info/object_78.png" width="12" height="12"><span>&nbsp;{'pass_condition'|WM_Lang}</span></div>
                <div style="margin:0 20px 0 6px;">{'grade1'|WM_Lang}：{$courseData.fair_grade} {'fraction'|WM_Lang}</div>
            </div>
            {/if}
            *}
            {if 'gallery_time'|in_array:$courseData.is_use || 'gallery_process'|in_array:$courseData.is_use}
            <div class="course-gallery-pass">
                <div class="font-red"><img src="{$appRoot}/public/images/course_info/object_78.png" width="12" height="12"><span>&nbsp;{'novitiate'|WM_Lang}</span></div>
                {if 'gallery_time'|in_array:$courseData.is_use}
                <div style="margin:0 20px 0 6px;">{'reading_time'|WM_Lang}：{$courseData.gallery.time} {'unit_hour'|WM_Lang}</div>
                {/if}
                {if 'gallery_process'|in_array:$courseData.is_use}
                <div>
                    <div style="margin-left: 6px;float:left;"> {'teaching_schedule'|WM_Lang}：</div>
                    <div style="width:515px;height:14px;float:left;border-radius: 10px;margin-top:3px;border: 1px solid #eee;background-color: #eee;" class="progress progress-warning progress-striped">
                        <div class="bar" style="background-color: #048e89;width: {$courseData.gallery.percent}%;"></div>
                    </div>
                      {$courseData.gallery.percent}%
                </div>
                {/if}
            </div>
            {/if}
        </div>
        <div id="course_set" class="course_info">
            <ul style="list-style: none;">
                {foreach from=$titleAry item=title name=titleFor}
                <li style="margin-top: 10px;">
                    <div class="course-set-circle"></div>
                    <div class="course-set breakword">{$title}</div>
                </li>
                {if not $smarty.foreach.titleFor.last}
                <div class="course-set-line"></div>
                {/if}
                {/foreach}
            </ul>
        </div>
    </div>
    <div class="col-md-3 col-sm-12 col-xs-12">
        <div class="teacher">
            <div class="teach-list">
                {if $courseData.teachers|@count >= 1}
                    {assign var=count value=0}
                    {foreach from=$courseData.teachers key=k item=teacherVal}
                    <div style="padding-top:10px;padding-bottom:10px;">
                        <div class="teach-pic"><img src='{$appRoot}/co_showuserpic.php?a={$teacherVal.id}' onerror="javascript:this.src='{$appRoot}/theme/default/learn/co_pic.gif'"></div>
                        <div class="teach-data">{'teacher'|WM_Lang}&nbsp;/&nbsp;{$teacherVal.realname}</div>
                        <div class="clearboth-block"></div>
                    </div>
                    {/foreach}
                {/if}
            </div>
        </div>
    </div>
</div>

{if $courseData.relativeCourses|@count >= 1}
<div id="course_info_relative">
    <div class="container">
    <div class="col-md-12 col-sm-12 col-xs-12">
        <div id="course-tabs" class="row subtitle heading-bottom-border">
            <h1 id="course_tab_new" class="course_categroy_tab active">{'related_courses'|WM_Lang}</h1>
        </div>
    </div>
    {foreach from=$courseData.relativeCourses key=k item=v}
    {include file = "common/course_item.tpl"}
    {/foreach}
    </div>
</div>
{/if}
</div>

{include file = "common/site_footer.tpl"}
{*
{if !$pageEmbed}
<form name="frmGoCategory" id="frmGoCategory" method="POST" action="/mooc/explorer.php">
    <input type="hidden" name="groupId" value="">
</form>
<form name="frmCancelCourse" id="frmCancelCourse" method="POST" action="/mooc/course_cancel.php">
    <input type="hidden" name="cancelCourseId" value="">
</form>
{/if}
*}

<script type="text/javascript">
    var nowlang = '{$nowlang}';
    var justPreview = '1';
    var ser = '';
    var slang = '';
    var lang = '{$nowlang}';
    var csid = '{$courseData.course_id}';
</script>
<script type="text/javascript" src="{$appRoot}/mooc/public/js/course_share.js?{$smarty.now}"></script>
<script type="text/javascript">
    var introVideoPath = '{$courseData.introVideo}';
    var introVideoPosterPath = '{$courseData.introVideoPreview}';
    var confirmsign = "{'confirmsign'|WM_Lang}";
    var confirmwithdrawal = "{'confirmwithdrawal'|WM_Lang}";
{literal}
    /**
    * 切換到探索課程的某一群組
    */
   function gotoCategory(grpid) {
       document.frmGoCategory.groupId.value = grpid;
       document.frmGoCategory.submit();
   }

   function gotoCourse(csid) {
       document.location.href = "/" + csid;
   }

   function enployCourse(csid, sid) {
       if (confirm(confirmsign)) {
           if (sid != null) {
               document.location.href = "/enploy/" + csid + "/" + sid;
           } else {
               document.location.href = "/enploy/" + csid;
           }
       }
   }

   function cancelCourse(csid) {
       if (confirm(confirmwithdrawal)) {
           document.frmCancelCourse.cancelCourseId.value = csid;
           document.frmCancelCourse.submit();
       }
   }

    var nowShowBlockTab = '';
    function show_page(tab) {
        if (tab == nowShowBlockTab) return;
        // 隱藏現在的tab與block
        $('.course_categroy_tab.active').removeClass('active');
        // 顯示點選的block
        $('#course_tab_'+tab).addClass('active');
        // 儲存目前所點選的tab
        nowShowBlockTab = tab;

        $(".course_info").hide();
        $("#" + tab).show();
        fix();
    }

   function fix() {
       var height = $(".main").height() + $(".header").height() + $(".nav-adv-course").height();

       if (height < $(window).height()) {
           var fix = $(window).height() - height - 40;
           $("#fix").css("padding-bottom", fix);
       }
   }

   $(document).ready(function() {
       if (introVideoPath.length > 0) {
           $("#jquery_jplayer_1").jPlayer({
                ready: function() {
                   $(this).jPlayer("setMedia", {
                       m4v: introVideoPath,
                       poster: introVideoPosterPath
                   });
                },
                play: function() {
                    if (document.getElementById("jp_video_0") != null) {
                        document.getElementById("jp_video_0").setAttribute("controls", "controls");
                    }
                },
                swfPath: "/lib/jplayer",
                supplied: "m4v",
                size: {
                   width: "100%",
                   height: "217px",
                   cssClass: "jp-video-360p"
                },
                smoothPlayBar: true,
                keyEnabled: true
           });
       }

       show_page('course_intro');

       $("#course_tab a").click(function() {
           $("#course_tab li").removeClass('title-li-active');
           $(this).children().addClass('title-li-active');
       });
       $("#qr-link").fancybox({
           maxWidth: 800,
           maxHeight: 600,
           fitToView: false,
           width: 400,
           height: 400,
           autoSize: false,
           closeClick: false,
           openEffect: 'none',
           closeEffect: 'none'
       });
   });


   $(window).resize(function() {
       fix();
   });

   //如是 Guest 身分，須先登入後報名，將課程ID用 GET 傳出，以利登入後返回原課程網頁
   function loginreturn() {
       document.location.href = "/mooc/login.php?reurl=" + encodeURIComponent(location.href);
   }

{/literal}
</script>
