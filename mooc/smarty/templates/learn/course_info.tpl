<style type="text/css">
{literal}
.title {
    font-size: 1.8em;
    line-height: 1.5em;
    color: #000000;
    font-weight: bold;
    margin: 11px auto 0px auto;
    width: 1000px;    
}

.set-title {
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
    color: #F3800F;
    background-color: #ececec;
 }
.set-li-active a, .set-li-active a:hover {
    color: #F3800F;
    background-color: #ffffff;
}

.course_info {
    padding:20px 20px;
    display:none;
}

.course_info input,.course_info select {
    font-size:13px;
    color:#353535;
    height:30px;
    padding:0;
    margin:0;
    
}.course-set-circle {
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
    width: 546px;
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
    height: 50px;
}

.qrcode .share .pic {
    margin-right: 0.5em;
}
{/literal}
</style>
<div class="title">{'course_introduction'|WM_Lang}</div>
<div class="box" style="width: 1000px; margin: auto; margin-bottom: 3em; display: table;">
    <div id="teachers-info" style="background-color: #F3F3F3; width: 15em; min-height: 56em; float: left; border-bottom-left-radius: 4px; padding-top: 3em;">
        {if $courseData.teachers|@count >= 1}
            {foreach from=$courseData.teachers key=k item=teacherVal}
            <div style="padding-bottom: 2.6em; padding-left: 1em; padding-right: 1em;">
                <div>
                    <div class="pic" style="-webkit-border-radius: 4px; -moz-border-radius: 4px; -ms-border-radius: 4px; -o-border-radius: 4px; -webkit-box-shadow: 0 -1px 2px 0 rgba(0, 0, 0, 0.05); -moz-box-shadow: 0 -1px 2px 0 rgba(0, 0, 0, 0.05); -ms-box-shadow: 0 -1px 2px 0 rgba(0, 0, 0, 0.05); -o-box-shadow: 0 -1px 2px 0 rgba(0, 0, 0, 0.55); border: 1px solid #CACACA;  background-size: cover; width: 120px; height: 160px; padding: 7px; margin: auto auto; margin-bottom: 0.6em; background-color: #FFFFFF;">
                        <img src="{$appRoot}/co_showuserpic.php?a={$teacherVal.id}" onerror="javascript:this.src='{$appRoot}/theme/default/learn/co_pic.gif'" style="background-size: cover; background-position: 50% 50%; background-repeat: no-repeat no-repeat; width: 120px; height: 160px; background-color: #FFFFFF;">
                    </div>
                    <div class="" title="" style="line-height: 1.7em;">
                        <span style="font-weight: bold; font-size: 1.2em;">{$teacherVal.realname}</span>
                        <span style="color: #F3800F;">({$teacherVal.title})</span>
                    </div>
                    <div class="" title="" style="line-height: 1.7em;">{$teacherVal.email}</div>
                </div>
            </div>
            {/foreach}
        {/if}
    </div> 
    <div style="margin-top: 3em;  margin-left: 18.5em;"> 
        <div id="course_detail" style="width:691px; border: 1px solid #C4C4C4; border-radius: 5px; min-height: 50em; margin-bottom: 2em;">
            <div class="set-title">
                <ul id="set-tab" style="list-style: none; margin: 0;">
                    <li class="set-li set-li-active" >
                        <a href="javascript:;" onclick="show_page('intro');">{'course_introduction'|WM_Lang}</a>
                    </li>
                    <li class="set-li" >
                        <a href="javascript:;" onclick="show_page('info');">{'course_plan'|WM_Lang}</a>
                    </li>
                </ul>
            </div>
            <!-- 課程介紹 -->
            <div class="course_info" id="intro" style="padding-bottom: 0em;">
                <div style="height: 152px; margin-bottom: 1em; margin-top: 0.8em;">
                    <div style="float: left; margin-right: 1em;"><img width="270" height="151" src="/lib/app_show_course_picture.php?courseId={$courseData.cpic}{if $assignSch neq $schoolId}&sId={$courseData.spic}{/if}" style="height: 151px;"></div>
                    <div>
                        <div title="{$courseData.caption|WM_Title}" style="font-weight: bold; font-size: 1.9em; line-height: 1.5em; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; width: 13em;">{$courseData.caption|WM_Title}</div>
                        <div style="font-size: 1.3em; line-height: 1.9em;">
                            <span style="color: #F94050">■ {'during_registration'|WM_Lang}: </span>
                            {$courseData.enployDateStr}
                        </div>
                        <div style="font-size: 1.3em; line-height: 1.9em;">
                            <span style="color: #F94050">■ {'during_counseling'|WM_Lang}: </span>
                            <span>{$courseData.studyDateStr}</span>
                        </div>
                        <div class="course-share" style="margin-top: 0.4em;">
                            <div class="qrcode">
                                <div class="share">{$shareIcon}</div>
                                <div id="inline-ln-{$courseData.course_id}" class="inline-ln">
                                    <form class="well">
                                        <div>{'linesharenote'|WM_Lang}</div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="margin-bottom: 0.7em;">
                    <pre style="background-color: transparent; border-width: 0; padding: 0em; font-size: 1.3em; line-height: 2em; font-family: '微軟正黑體', Arial, Helvetica, sans-serif;">{$courseData.content}</pre>
                </div>
            </div>
            <!-- 課程安排 -->
            <div class="course_info" id="info" style="padding-top: 3em;">
                <ul style="list-style: none;">
                    {foreach from=$titleAry item=title name=titleFor}
                    <li style="">
                        <div class="course-set-circle"></div>
                        <div class="course-set breakword">{$title}</div>
                    </li>
                    {if not $smarty.foreach.titleFor.last}
                    <div class="course-set-line"></div>
                    {/if}
                    {/foreach}
                </ul>            
            </div>
            <div style="text-align: center; margin-bottom: 1em;">
                <a id="qr-link" data-fancybox-type='iframe' href="{$courseData.qrcode_url}" style="margin: 0.5em 0; font-size: 1.2em;" class="btn btn-primary btn-pink" title="{$courseData.caption|WM_Title}">{'btn_show_qrcode'|WM_Lang}</a>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{$appRoot}/mooc/public/js/course_share.js"></script>
<script type="text/javascript" src="{$appRoot}/public/js/learn/course_info.js"></script>